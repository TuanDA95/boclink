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

    // Idempotency: kiểm tra đã xử lý giao dịch này chưa
    const existing = await prisma.deposit.findFirst({
      where: { sepayTxId: payload.id },
    });
    if (existing) {
      return NextResponse.json({ success: true }); // Đã xử lý, trả 200 để SePay không retry
    }

    // Tìm Deposit record theo mã thanh toán trong nội dung
    const prefix = await getSetting("SEPAY_PAYMENT_PREFIX", "SUB2S");
    const paymentCode = payload.code || extractCodeFromContent(payload.content, prefix);

    if (!paymentCode) {
      console.log(`[SePay Webhook] Không tìm thấy mã thanh toán trong nội dung: ${payload.content}`);
      return NextResponse.json({ success: true });
    }

    // Ưu tiên tìm đơn nạp PENDING khớp cả mã thanh toán VÀ chính xác số tiền
    let deposit = await prisma.deposit.findFirst({
      where: {
        paymentContent: paymentCode,
        amount: payload.transferAmount,
        status: "PENDING",
      },
      include: { user: true },
    });

    // Nếu không khớp chính xác số tiền, tìm đơn PENDING bất kỳ có cùng mã
    if (!deposit) {
      deposit = await prisma.deposit.findFirst({
        where: {
          paymentContent: paymentCode,
          status: "PENDING",
        },
        include: { user: true },
      });
    }

    if (!deposit) {
      // Fallback: tìm kiếm LIKE với mã thanh toán (hỗ trợ userId cũ có dấu _ trong mã)
      deposit = await prisma.deposit.findFirst({
        where: {
          paymentContent: { contains: paymentCode },
          status: "PENDING",
        },
        include: { user: true },
      });
    }

    if (!deposit) {
      console.log(`[SePay Webhook] Không tìm thấy đơn nạp PENDING với mã: ${paymentCode}`);
      return NextResponse.json({ success: true });
    }

    // Cập nhật đơn nạp và cộng đúng số tiền thực nhận (payload.transferAmount) cho user
    await prisma.$transaction([
      prisma.deposit.update({
        where: { id: deposit.id },
        data: {
          status: "SUCCESS",
          amount: payload.transferAmount, // Cập nhật lại đúng số tiền thực tế người dùng đã chuyển
          sepayTxId: payload.id,
          sepayGateway: payload.gateway,
          confirmedAt: new Date(),
        },
      }),
      prisma.user.update({
        where: { id: deposit.userId },
        data: {
          balance: {
            increment: payload.transferAmount, // Cộng đúng số tiền thực nhận vào số dư tài khoản
          },
        },
      }),
    ]);

    console.log(
      `[SePay Webhook] ✅ Nạp tiền thành công: user=${deposit.userId}, thực nhận=${formatVND(payload.transferAmount)}, tạo đơn=${formatVND(deposit.amount)}, txId=${payload.id}`
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("[SePay Webhook] Lỗi:", error);
    return NextResponse.json({ success: false, error: "Internal error" }, { status: 500 });
  }
}

function extractCodeFromContent(content: string, prefix: string): string | null {
  // Regex mở rộng: chấp nhận cả dấu _ và - trong mã (backward compat với userId cũ dạng c_XXXX)
  const regex = new RegExp(`(${prefix}[A-Z0-9_-]+)`, "i");
  const match = content.match(regex);
  return match ? match[1].toUpperCase() : null;
}
