import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";
import {
  TELCOS,
  DECLARED_VALUES,
  calculateRealAmount,
  submitScratchCardToGateway,
} from "@/lib/scratchCard";

export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json({ error: "Chưa đăng nhập" }, { status: 401 });
    }

    const body = await req.json().catch(() => ({}));
    const { telco, code, serial } = body;
    const declaredValue = body.declaredValue ?? body.amount;

    if (!telco || !declaredValue || !code || !serial) {
      return NextResponse.json({ error: "Vui lòng nhập đầy đủ thông tin thẻ" }, { status: 400 });
    }

    // Kiểm tra telco & mệnh giá hợp lệ
    const validTelco = TELCOS.find((t) => t.code === telco.toUpperCase());
    if (!validTelco) {
      return NextResponse.json({ error: "Loại thẻ cào không hợp lệ" }, { status: 400 });
    }

    const numDeclaredValue = Number(declaredValue);
    if (!DECLARED_VALUES.includes(numDeclaredValue)) {
      return NextResponse.json({ error: "Mệnh giá thẻ khai báo không hợp lệ" }, { status: 400 });
    }

    const cleanCode = String(code).trim();
    const cleanSerial = String(serial).trim();

    if (cleanCode.length < 6 || cleanSerial.length < 6) {
      return NextResponse.json({ error: "Mã thẻ và Seri phải có ít nhất 6 ký tự" }, { status: 400 });
    }

    // Đọc chiết khấu % cho nhà mạng này từ cài đặt
    const discountSettingKey = `CARD_DISCOUNT_${validTelco.code}`;
    const discountPercentStr = await getSetting(discountSettingKey, String(validTelco.defaultDiscount));
    const discountPercent = Number(discountPercentStr) || validTelco.defaultDiscount;

    const realValue = calculateRealAmount(numDeclaredValue, discountPercent);
    const requestId = `CARD_${Date.now()}_${Math.random().toString(36).slice(2, 6).toUpperCase()}`;

    // Đọc thông tin kết nối cổng gạch thẻ từ DB settings
    const partnerId = await getSetting("CARD_PARTNER_ID", process.env.CARD_PARTNER_ID || "");
    const partnerKey = await getSetting("CARD_PARTNER_KEY", process.env.CARD_PARTNER_KEY || "");
    const apiUrl = await getSetting("CARD_API_URL", process.env.CARD_API_URL || "https://doithe1s.vn/api/charging-ws/v2");
    const isSandbox = (await getSetting("CARD_SANDBOX", "true")) === "true";

    // Tạo bản ghi Deposit trong cơ sở dữ liệu
    const deposit = await prisma.deposit.create({
      data: {
        userId: session.user.id,
        amount: numDeclaredValue,
        realValue: realValue,
        method: "SCRATCH_CARD",
        status: "PENDING",
        cardTelco: validTelco.code,
        cardCode: cleanCode,
        cardSerial: cleanSerial,
        declaredValue: numDeclaredValue,
        cardRequestId: requestId,
        cardMessage: "Đã gửi thẻ cào lên hệ thống xử lý",
      },
    });

    // Nếu đang bật chế độ Thử nghiệm (Sandbox)
    if (isSandbox || !partnerId || !partnerKey) {
      console.log(`[ScratchCard] [SANDBOX MODE] Tự động mô phỏng duyệt thẻ cào: ${requestId}`);
      
      // Giả lập xử lý thành công sau 2 giây nếu mã thẻ không chứa từ "ERROR" hay "FAIL"
      const isTestFail = cleanCode.toUpperCase().includes("FAIL") || cleanCode.toUpperCase().includes("ERROR");

      setTimeout(async () => {
        try {
          if (isTestFail) {
            await prisma.deposit.update({
              where: { id: deposit.id },
              data: {
                status: "FAILED",
                cardMessage: "Mã thẻ cào hoặc Số Seri không chính xác (Sandbox Test)",
              },
            });
          } else {
            await prisma.$transaction([
              prisma.deposit.update({
                where: { id: deposit.id },
                data: {
                  status: "SUCCESS",
                  cardMessage: "Nạp thẻ cào thành công (Sandbox Test)",
                  confirmedAt: new Date(),
                },
              }),
              prisma.user.update({
                where: { id: session.user.id },
                data: {
                  balance: { increment: realValue },
                },
              }),
            ]);
          }
        } catch (err) {
          console.error("[ScratchCard Sandbox Auto-process Error]:", err);
        }
      }, 2500);

      return NextResponse.json({
        success: true,
        depositId: deposit.id,
        requestId,
        realValue,
        discountPercent,
        message: "Thẻ cào đã được gửi lên hệ thống thử nghiệm xử lý",
      });
    }

    // Gửi request lên Cổng Gạch Thẻ thực tế
    const gatewayRes = await submitScratchCardToGateway(
      {
        telco: validTelco.code,
        code: cleanCode,
        serial: cleanSerial,
        declaredValue: numDeclaredValue,
        requestId,
      },
      partnerId,
      partnerKey,
      apiUrl
    );

    // Cập nhật thông báo từ cổng gạch thẻ
    if (gatewayRes.status === 2 || gatewayRes.status === 3 || gatewayRes.status === 4) {
      const safeMsg = String(gatewayRes.message || "Nạp thẻ thất bại").slice(0, 190);
      await prisma.deposit.update({
        where: { id: deposit.id },
        data: {
          status: "FAILED",
          cardMessage: safeMsg,
        },
      });

      return NextResponse.json(
        { error: `Nạp thẻ thất bại: ${safeMsg}` },
        { status: 400 }
      );
    }

    return NextResponse.json({
      success: true,
      depositId: deposit.id,
      requestId,
      realValue,
      discountPercent,
      message: gatewayRes.message || "Thẻ cào đã được tiếp nhận và đang chờ gạch thẻ",
    });
  } catch (error: any) {
    console.error("[POST /api/deposit/card Error]:", error);
    return NextResponse.json({ error: error.message || "Lỗi xử lý nạp thẻ cào" }, { status: 500 });
  }
}
