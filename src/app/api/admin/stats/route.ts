import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const [
    totalLinks,
    totalUsers,
    totalRevenue,
    todayClicks,
    todayDeposits,
    recentDeposits,
    topLinks,
  ] = await Promise.all([
    prisma.link.count(),
    prisma.user.count({ where: { role: "USER" } }),
    prisma.deposit.aggregate({
      where: { status: "SUCCESS" },
      _sum: { amount: true },
    }),
    prisma.link.aggregate({
      _sum: { clicks: true },
    }),
    prisma.deposit.count({
      where: { status: "SUCCESS", confirmedAt: { gte: today } },
    }),
    prisma.deposit.findMany({
      where: { status: "SUCCESS" },
      orderBy: { confirmedAt: "desc" },
      take: 5,
      include: { user: { select: { email: true, name: true } } },
    }),
    prisma.link.findMany({
      orderBy: { clicks: "desc" },
      take: 5,
      select: { id: true, title: true, slug: true, clicks: true, price: true },
    }),
  ]);

  return NextResponse.json({
    totalLinks,
    totalUsers,
    totalRevenue: totalRevenue._sum.amount || 0,
    totalClicks: todayClicks._sum.clicks || 0,
    todayDeposits,
    recentDeposits,
    topLinks,
  });
}
