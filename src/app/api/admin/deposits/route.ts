import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const { searchParams } = new URL(req.url);
  const page = Math.max(1, parseInt(searchParams.get("page") || "1"));
  const limit = Math.max(1, parseInt(searchParams.get("limit") || "15"));
  const status = searchParams.get("status") || "ALL";
  const q = (searchParams.get("q") || "").trim();

  const whereConditions: any[] = [];

  if (status !== "ALL") {
    whereConditions.push({ status });
  }

  if (q) {
    whereConditions.push({
      OR: [
        { user: { email: { contains: q } } },
        { user: { name: { contains: q } } },
        { cardSerial: { contains: q } },
        { cardCode: { contains: q } },
        { cardRequestId: { contains: q } },
        { paymentContent: { contains: q } },
      ],
    });
  }

  const where = whereConditions.length > 0 ? { AND: whereConditions } : {};

  const [deposits, total, stats] = await Promise.all([
    prisma.deposit.findMany({
      where,
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * limit,
      take: limit,
      include: { user: { select: { name: true, email: true } } },
    }),
    prisma.deposit.count({ where }),
    prisma.deposit.groupBy({
      by: ["status"],
      _count: true,
      _sum: { amount: true },
    }),
  ]);

  const totalPages = Math.ceil(total / limit) || 1;

  return NextResponse.json({ deposits, total, totalPages, page, limit, stats });
}
