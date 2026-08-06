import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền truy cập" }, { status: 403 });
    }

    const { searchParams } = new URL(req.url);
    const page = Math.max(1, parseInt(searchParams.get("page") || "1"));
    const limit = Math.max(1, parseInt(searchParams.get("limit") || "15"));
    const q = (searchParams.get("q") || "").trim();

    const where: any = {};
    if (q) {
      where.OR = [
        { user: { name: { contains: q } } },
        { user: { email: { contains: q } } },
        { link: { slug: { contains: q } } },
        { link: { title: { contains: q } } },
      ];
    }

    const [purchases, total, aggregate, count24h] = await Promise.all([
      prisma.purchase.findMany({
        where,
        include: {
          user: { select: { id: true, name: true, email: true } },
          link: { select: { id: true, slug: true, title: true, originalUrl: true } },
        },
        orderBy: { createdAt: "desc" },
        skip: (page - 1) * limit,
        take: limit,
      }),
      prisma.purchase.count({ where }),
      prisma.purchase.aggregate({
        _sum: { amount: true },
      }),
      prisma.purchase.count({
        where: {
          createdAt: {
            gte: new Date(Date.now() - 24 * 60 * 60 * 1000),
          },
        },
      }),
    ]);

    const totalPages = Math.ceil(total / limit) || 1;
    const totalRevenue = aggregate._sum.amount || 0;

    return NextResponse.json({
      purchases,
      total,
      totalPages,
      page,
      limit,
      totalRevenue,
      purchases24h: count24h,
    });
  } catch (err: any) {
    return NextResponse.json({ error: err.message || "Lỗi lấy danh sách giao dịch mua link" }, { status: 500 });
  }
}
