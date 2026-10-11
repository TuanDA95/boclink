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
    const prefix = await getSetting("SEPAY_PAYMENT_PREFIX", "NAP");
    const paymentCode = payload.code?.toUpperCase() || extractCodeFromContent(payload.content, prefix);

    const now = new Date();
    let deposit = null;

    if (paymentCode) {
      // Ưu tiên tìm đơn PENDING khớp cả mã VÀ số tiền, chưa hết hạn
      deposit = await prisma.deposit.findFirst({
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
    }

    // Fallback bổ sung: nếu chưa tìm thấy bằng paymentCode, quét kiểm tra các đơn PENDING đang mở
    // xem paymentContent có nằm trong nội dung chuyển khoản không
    if (!deposit) {
      const pendingDeposits = await prisma.deposit.findMany({
        where: {
          method: "BANK_TRANSFER",
          status: "PENDING",
          OR: [{ expiredAt: null }, { expiredAt: { gt: now } }],
        },
        include: { user: true },
      });

      const normalizedPayload = (payload.content || "").replace(/[\s._-]+/g, "").toUpperCase();

      // Ưu tiên khớp cả số tiền
      deposit =
        pendingDeposits.find((d) => {
          if (!d.paymentContent) return false;
          const normalizedCode = d.paymentContent.replace(/[\s._-]+/g, "").toUpperCase();
          return d.amount === payload.transferAmount && normalizedPayload.includes(normalizedCode);
        }) || null;

      // Nếu vẫn chưa thấy, tìm đơn khớp mã dù số tiền lệch
      if (!deposit) {
        deposit =
          pendingDeposits.find((d) => {
            if (!d.paymentContent) return false;
            const normalizedCode = d.paymentContent.replace(/[\s._-]+/g, "").toUpperCase();
            return normalizedPayload.includes(normalizedCode);
          }) || null;
      }
    }

    if (!deposit) {
      // Kiểm tra xem có đơn PENDING nào đã hết hạn không → log cảnh báo để admin biết
      const expiredDeposit = await prisma.deposit.findFirst({
        where: {
          paymentContent: paymentCode || undefined,
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
          `[SePay Webhook] Không tìm thấy đơn nạp PENDING phù hợp — mã: ${paymentCode || "N/A"}, ` +
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
  if (!content) return null;
  const cleanPrefix = prefix.replace(/[^a-zA-Z0-9]/g, "");
  if (!cleanPrefix) return null;

  // 1. Match chính xác dạng PREFIX + 6 số (cho phép có khoảng trắng giữa prefix và số, ví dụ NAP123456 hoặc NAP 123456)
  const pattern6Digits = new RegExp(`(?:^|[^a-zA-Z0-9])(${cleanPrefix})\\s*(\\d{6})`, "i");
  const match6 = content.match(pattern6Digits);
  if (match6) {
    return `${match6[1]}${match6[2]}`.toUpperCase();
  }

  // 2. Match theo prefix bất kỳ theo sau là chuỗi alphanumeric (hỗ trợ mã cũ dài hoặc ký tự khác)
  const fallbackPattern = new RegExp(`(${cleanPrefix}[A-Z0-9_-]+)`, "i");
  const fallbackMatch = content.match(fallbackPattern);
  if (fallbackMatch) {
    return fallbackMatch[1].toUpperCase();
  }

  return null;
}
