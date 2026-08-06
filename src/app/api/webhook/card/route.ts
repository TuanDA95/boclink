import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";
import { verifyScratchCardCallback } from "@/lib/scratchCard";
import { formatVND } from "@/lib/sepay";

export async function POST(req: NextRequest) {
  try {
    let body: any = {};
    const contentType = req.headers.get("content-type") || "";

    if (contentType.includes("application/json")) {
      body = await req.json();
    } else {
      const formData = await req.formData();
      body = Object.fromEntries(formData.entries());
    }

    console.log("[ScratchCard Webhook Callback Received]:", body);

    const status = Number(body.status ?? body.status_code);
    const requestId = String(body.request_id || body.requestId || body.content || "").trim();
    const code = String(body.code || "").trim();
    const serial = String(body.serial || "").trim();
    const sign = String(body.sign || body.signature || "").trim();
    const callbackMessage = String(body.message || body.msg || "").trim();
    const realAmount = Number(body.amount || body.value || body.real_value || 0);

    if (!requestId) {
      return NextResponse.json({ error: "Thiếu request_id" }, { status: 400 });
    }

    // Đọc Partner Key để xác thực chữ ký callback
    const partnerKey = await getSetting("CARD_PARTNER_KEY", process.env.CARD_PARTNER_KEY || "");
    if (partnerKey && !verifyScratchCardCallback(code, serial, requestId, sign, partnerKey)) {
      console.warn(`[ScratchCard Webhook] Chữ ký callback không hợp lệ cho request: ${requestId}`);
      return NextResponse.json({ error: "Invalid signature" }, { status: 401 });
    }

    // Tìm bản ghi Deposit theo cardRequestId
    const deposit = await prisma.deposit.findUnique({
      where: { cardRequestId: requestId },
      include: { user: true },
    });

    if (!deposit) {
      console.warn(`[ScratchCard Webhook] Không tìm thấy đơn nạp với request_id: ${requestId}`);
      return NextResponse.json({ success: true, message: "Request not found" });
    }

    // Idempotency: nếu đơn đã hoàn tất thì bỏ qua
    if (deposit.status === "SUCCESS") {
      return NextResponse.json({ success: true, message: "Already processed" });
    }

    // Trường hợp Status 1: Nạp thẻ thành công
    if (status === 1) {
      // Tính toán số tiền thực nhận dựa trên chiết khấu đã lưu hoặc giá trị cổng trả về
      const creditAmount = realAmount > 0 ? realAmount : (deposit.realValue || deposit.amount);

      await prisma.$transaction([
        prisma.deposit.update({
          where: { id: deposit.id },
          data: {
            status: "SUCCESS",
            realValue: creditAmount,
            cardMessage: String(callbackMessage || "Nạp thẻ cào thành công").slice(0, 190),
            confirmedAt: new Date(),
          },
        }),
        prisma.user.update({
          where: { id: deposit.userId },
          data: {
            balance: { increment: creditAmount },
          },
        }),
      ]);

      console.log(
        `[ScratchCard Webhook] ✅ Gạch thẻ thành công! User: ${deposit.userId}, Thực nhận: ${formatVND(creditAmount)}`
      );
      return NextResponse.json({ success: true, status: 1 });
    }

    // Trường hợp Status 2 hoặc 3: Thẻ sai, sai mệnh giá, hoặc đã sử dụng
    if (status === 2 || status === 3 || status === 4) {
      await prisma.deposit.update({
        where: { id: deposit.id },
        data: {
          status: "FAILED",
          cardMessage: String(callbackMessage || (status === 2 ? "Sai mệnh giá thẻ" : "Mã thẻ cào hoặc Seri không hợp lệ")).slice(0, 190),
        },
      });

      console.warn(`[ScratchCard Webhook] ❌ Gạch thẻ thất bại: ${requestId} - ${callbackMessage}`);
      return NextResponse.json({ success: true, status });
    }

    return NextResponse.json({ success: true, status: "PENDING" });
  } catch (error: any) {
    console.error("[ScratchCard Webhook Error]:", error);
    return NextResponse.json({ error: error.message || "Internal error" }, { status: 500 });
  }
}

export async function GET(req: NextRequest) {
  // Cho phép cổng gạch thẻ test callback bằng GET request
  const { searchParams } = new URL(req.url);
  const status = Number(searchParams.get("status") || 1);
  const requestId = searchParams.get("request_id") || searchParams.get("requestId");

  if (!requestId) {
    return NextResponse.json({ message: "Sub2S Scratch Card Callback Endpoint is Active" });
  }

  const deposit = await prisma.deposit.findUnique({
    where: { cardRequestId: requestId },
  });

  if (!deposit) {
    return NextResponse.json({ error: "Không tìm thấy đơn nạp" }, { status: 404 });
  }

  if (deposit.status === "SUCCESS") {
    return NextResponse.json({ success: true, status: "SUCCESS" });
  }

  const creditAmount = deposit.realValue || deposit.amount;

  if (status === 1) {
    await prisma.$transaction([
      prisma.deposit.update({
        where: { id: deposit.id },
        data: {
          status: "SUCCESS",
          cardMessage: "Nạp thẻ cào thành công qua GET Callback",
          confirmedAt: new Date(),
        },
      }),
      prisma.user.update({
        where: { id: deposit.userId },
        data: {
          balance: { increment: creditAmount },
        },
      }),
    ]);
  } else {
    await prisma.deposit.update({
      where: { id: deposit.id },
      data: {
        status: "FAILED",
        cardMessage: "Thẻ cào bị từ chối qua GET Callback",
      },
    });
  }

  return NextResponse.json({ success: true, status });
}
