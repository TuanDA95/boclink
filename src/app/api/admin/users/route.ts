import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const { searchParams } = new URL(req.url);
  const page = parseInt(searchParams.get("page") || "1");
  const limit = parseInt(searchParams.get("limit") || "20");

  const [users, total] = await Promise.all([
    prisma.user.findMany({
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * limit,
      take: limit,
      select: {
        id: true,
        email: true,
        name: true,
        role: true,
        balance: true,
        createdAt: true,
        _count: { select: { purchases: true, deposits: true } },
      },
    }),
    prisma.user.count(),
  ]);

  return NextResponse.json({ users, total, page, limit });
}

// PATCH update user balance or role
export async function PATCH(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const { id, balance, role } = await req.json();
  if (!id) return NextResponse.json({ error: "Thiếu user id" }, { status: 400 });

  const user = await prisma.user.update({
    where: { id },
    data: {
      ...(balance !== undefined && { balance: parseFloat(balance) }),
      ...(role !== undefined && { role }),
    },
    select: { id: true, email: true, name: true, role: true, balance: true },
  });

  return NextResponse.json({ user });
}
