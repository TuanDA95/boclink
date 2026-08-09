import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { verifySePayWebhook, SePayWebhookPayload, formatVND } from "@/lib/sepay";
import { getSetting } from "@/lib/settings";

export async function POST(req: NextRequest) {
  try {
    const rawBody = await req.text();
    const signature = req.headers.get("x-sepay-signature") || req.headers.get("x-signature") || "";
    const authHeader = req.headers.get("authorization") || req.headers.get("x-sepay-secret") || "";

    const webhookSecret = await getSetting("SEPAY_WEBHOOK_SECRET", process.env.SEPAY_WEBHOOK_SECRET || "");

    // Xác thực chữ ký / Secret Key từ SePay
    if (!verifySePayWebhook(rawBody, signature, authHeader, webhookSecret)) {
      console.warn("[SePay Webhook] Chữ ký không hợp lệ");
      return NextResponse.json({ success: false, error: "Invalid signature" }, { status: 401 });
    }

    const payload: SePayWebhookPayload = JSON.parse(rawBody);

    // Chỉ xử lý giao dịch tiền vào
    if (payload.transferType !== "in") {
      return NextResponse.json({ success: true });
    }

    // Idempotency (DB-level): sepayTxId có @unique constraint → dùng findUnique để tận dụng index
    const alreadyProcessed = await prisma.deposit.findUnique({
      where: { sepayTxId: payload.id },
      select: { id: true },
    });
    if (alreadyProcessed) {
      console.log(`[SePay Webhook] Giao dịch ${payload.id} đã xử lý trước đó (deposit: ${alreadyProcessed.id})`);
      return NextResponse.json({ success: true }); // Đã xử lý, trả 200 để SePay không retry
    }

    // Tìm Deposit record theo mã thanh toán trong nội dung
    const prefix = await getSetting("SEPAY_PAYMENT_PREFIX", "SUB2S");
    const paymentCode = payload.code || extractCodeFromContent(payload.content, prefix);

    if (!paymentCode) {
      console.log(`[SePay Webhook] Không tìm thấy mã thanh toán trong nội dung: ${payload.content}`);
      return NextResponse.json({ success: true });
    }

    const now = new Date();

    // Ưu tiên tìm đơn PENDING khớp cả mã VÀ số tiền, chưa hết hạn
    let deposit = await prisma.deposit.findFirst({
      where: {
        paymentContent: paymentCode,
        amount: payload.transferAmount,
        status: "PENDING",
        OR: [{ expiredAt: null }, { expiredAt: { gt: now } }],
      },
      include: { user: true },
    });

    // Fallback: tìm đơn PENDING bất kỳ cùng mã, chưa hết hạn (người dùng chuyển sai số tiền)
    if (!deposit) {
      deposit = await prisma.deposit.findFirst({
        where: {
          paymentContent: paymentCode,
          status: "PENDING",
          OR: [{ expiredAt: null }, { expiredAt: { gt: now } }],
        },
        include: { user: true },
      });
    }

    if (!deposit) {
      // Kiểm tra xem có đơn PENDING nào đã hết hạn không → log cảnh báo để admin biết
      const expiredDeposit = await prisma.deposit.findFirst({
        where: {
          paymentContent: paymentCode,
          status: "PENDING",
          expiredAt: { lte: now },
        },
        select: { id: true, expiredAt: true, userId: true },
      });

      if (expiredDeposit) {
        console.warn(
          `[SePay Webhook] ⚠️ Giao dịch ${payload.id} (${formatVND(payload.transferAmount)}) khớp mã ${paymentCode} ` +
          `nhưng đơn ${expiredDeposit.id} đã HẾT HẠN lúc ${expiredDeposit.expiredAt?.toISOString()}. ` +
          `Admin cần duyệt thủ công nếu giao dịch hợp lệ.`
        );
      } else {
        console.log(
          `[SePay Webhook] Không tìm thấy đơn nạp PENDING phù hợp — mã: ${paymentCode}, ` +
          `số tiền: ${formatVND(payload.transferAmount)}, txId: ${payload.id}`
        );
      }

      return NextResponse.json({ success: true });
    }

    // FIX RACE CONDITION: Dùng updateMany với guard `status: "PENDING"` (atomic)
    // Nếu 2 webhook đến cùng lúc, chỉ 1 request thành công (count=1), còn lại nhận count=0
    const updated = await prisma.deposit.updateMany({
      where: {
        id: deposit.id,
        status: "PENDING", // Guard: chỉ update nếu vẫn còn PENDING
      },
      data: {
        status: "SUCCESS",
        amount: payload.transferAmount, // Cập nhật đúng số tiền thực tế người dùng đã chuyển
        sepayTxId: payload.id,
        sepayGateway: payload.gateway,
        confirmedAt: now,
      },
    });

    if (updated.count === 0) {
      // Concurrent request khác đã xử lý trước → idempotent response
      console.log(`[SePay Webhook] Đơn ${deposit.id} đã được xử lý bởi request song song. Bỏ qua.`);
      return NextResponse.json({ success: true });
    }

    // Cộng tiền cho user (chỉ chạy khi update deposit thành công)
    await prisma.user.update({
      where: { id: deposit.userId },
      data: {
        balance: { increment: payload.transferAmount },
      },
    });

    console.log(
      `[SePay Webhook] ✅ Nạp tiền thành công: user=${deposit.userId}, thực nhận=${formatVND(payload.transferAmount)}, txId=${payload.id}`
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("[SePay Webhook] Lỗi:", error);
    return NextResponse.json({ success: false, error: "Internal error" }, { status: 500 });
  }
}

function extractCodeFromContent(content: string, prefix: string): string | null {
  const regex = new RegExp(`(${prefix}[A-Z0-9_-]+)`, "i");
  const match = content.match(regex);
  return match ? match[1].toUpperCase() : null;
}
