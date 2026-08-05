import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

/**
 * POST /api/deposit/simulate
 * Body: { depositId: string }
 * Giả lập nạp tiền thành công cho môi trường Test Sandbox
 */
export async function POST(req: NextRequest) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const { depositId } = await req.json();
  if (!depositId) {
    return NextResponse.json({ error: "Thiếu depositId" }, { status: 400 });
  }

  const deposit = await prisma.deposit.findUnique({
    where: { id: depositId },
  });

  if (!deposit) {
    return NextResponse.json({ error: "Không tìm thấy đơn nạp tiền" }, { status: 404 });
  }

  if (deposit.status === "SUCCESS") {
    return NextResponse.json({ success: true, message: "Đơn này đã được xử lý trước đó" });
  }

  // Giả lập xử lý webhook thành công
  const txId = Math.floor(Math.random() * 9000000) + 1000000;
  await prisma.$transaction([
    prisma.deposit.update({
      where: { id: depositId },
      data: {
        status: "SUCCESS",
        sepayTxId: txId,
        sepayGateway: "SANDBOX_SIMULATOR",
        confirmedAt: new Date(),
      },
    }),
    prisma.user.update({
      where: { id: deposit.userId },
      data: {
        balance: {
          increment: deposit.amount,
        },
      },
    }),
  ]);

  return NextResponse.json({
    success: true,
    message: `🧪 [SANDBOX TEST] Giả lập nạp tiền thành công +${deposit.amount.toLocaleString("vi-VN")} đ vào tài khoản`,
  });
}
