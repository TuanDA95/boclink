import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET() {
  try {
    const session = await auth();
    if (!session?.user) {
      return NextResponse.json({ error: "Chưa đăng nhập" }, { status: 401 });
    }

    const deposits = await prisma.deposit.findMany({
      where: {
        userId: session.user.id,
        method: "SCRATCH_CARD",
      },
      orderBy: { createdAt: "desc" },
      take: 20,
    });

    const user = await prisma.user.findUnique({
      where: { id: session.user.id },
      select: { balance: true },
    });

    return NextResponse.json({
      deposits,
      balance: user?.balance || 0,
    });
  } catch (error) {
    return NextResponse.json({ error: "Lỗi tải lịch sử nạp thẻ cào" }, { status: 500 });
  }
}
