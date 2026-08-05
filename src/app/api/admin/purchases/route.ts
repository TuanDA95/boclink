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
    const q = searchParams.get("q") || "";

    const where: any = {};
    if (q.trim()) {
      where.OR = [
        { user: { name: { contains: q.trim(), mode: "insensitive" } } },
        { user: { email: { contains: q.trim(), mode: "insensitive" } } },
        { link: { slug: { contains: q.trim(), mode: "insensitive" } } },
        { link: { title: { contains: q.trim(), mode: "insensitive" } } },
      ];
    }

    const purchases = await prisma.purchase.findMany({
      where,
      include: {
        user: { select: { id: true, name: true, email: true } },
        link: { select: { id: true, slug: true, title: true, originalUrl: true } },
      },
      orderBy: { createdAt: "desc" },
      take: 100,
    });

    return NextResponse.json({ purchases });
  } catch (err: any) {
    return NextResponse.json({ error: err.message || "Lỗi lấy danh sách giao dịch mua link" }, { status: 500 });
  }
}
