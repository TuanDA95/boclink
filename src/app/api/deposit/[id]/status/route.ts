import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const session = await auth();
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const { id } = await params;

  const deposit = await prisma.deposit.findFirst({
    where: {
      id,
      userId: session.user.id, // Đảm bảo chỉ user của deposit mới xem được
    },
    select: {
      id: true,
      status: true,
      amount: true,
      method: true,
      confirmedAt: true,
      expiredAt: true,
    },
  });

  if (!deposit) {
    return NextResponse.json({ error: "Không tìm thấy đơn nạp" }, { status: 404 });
  }

  // Tự động cancel nếu hết hạn
  if (
    deposit.status === "PENDING" &&
    deposit.expiredAt &&
    deposit.expiredAt < new Date()
  ) {
    await prisma.deposit.update({
      where: { id },
      data: { status: "CANCELLED" },
    });
    return NextResponse.json({ ...deposit, status: "CANCELLED" });
  }

  return NextResponse.json(deposit);
}
