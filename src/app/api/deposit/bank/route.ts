import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { depositBankSchema } from "@/lib/validations";
import {
  generatePaymentCode,
  generateVietQRUrl,
  getBankBin,
} from "@/lib/sepay";
import { getSetting } from "@/lib/settings";

export async function POST(req: NextRequest) {
  const session = await auth();
  if (!session?.user) {
    return NextResponse.json({ error: "Vui lòng đăng nhập" }, { status: 401 });
  }

  const body = await req.json();
  const parsed = depositBankSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
  }

  const { amount } = parsed.data;

  // Hủy các đơn PENDING quá hạn của user này
  await prisma.deposit.updateMany({
    where: {
      userId: session.user.id,
      method: "BANK_TRANSFER",
      status: "PENDING",
      expiredAt: { lt: new Date() },
    },
    data: { status: "CANCELLED" },
  });

  // Lấy cấu hình SePay (từ DB hoặc .env)
  const bankAccount = await getSetting("SEPAY_BANK_ACCOUNT", "0123456789");
  const bankName = await getSetting("SEPAY_BANK_NAME", "MBBank");
  const bankOwner = await getSetting("SEPAY_BANK_OWNER", "NGUYEN VAN A");
  const prefix = await getSetting("SEPAY_PAYMENT_PREFIX", "SUB2S");

  // Tạo mã thanh toán duy nhất, truyền prefix từ DB vào hàm
  const finalContent = generatePaymentCode(session.user.id, prefix);

  const bankBin = getBankBin(bankName);
  const qrCodeUrl = generateVietQRUrl(bankAccount, bankBin, amount, finalContent, bankOwner);

  // Tạo Deposit record
  const deposit = await prisma.deposit.create({
    data: {
      userId: session.user.id,
      amount,
      method: "BANK_TRANSFER",
      status: "PENDING",
      paymentContent: finalContent,
      bankAccount,
      bankName,
      qrCodeUrl,
      expiredAt: new Date(Date.now() + 15 * 60 * 1000), // 15 phút
    },
  });

  return NextResponse.json({
    depositId: deposit.id,
    paymentContent: finalContent,
    bankAccount,
    bankName,
    bankOwner,
    amount,
    qrCodeUrl,
    expiredAt: deposit.expiredAt,
  });
}
