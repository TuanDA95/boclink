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
  const method = searchParams.get("method");
  const status = searchParams.get("status");

  const where = {
    ...(method && { method: method as "BANK_TRANSFER" | "CARD" }),
    ...(status && { status: status as "PENDING" | "SUCCESS" | "FAILED" | "CANCELLED" }),
  };

  const [deposits, total] = await Promise.all([
    prisma.deposit.findMany({
      where,
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * limit,
      take: limit,
      include: {
        user: { select: { id: true, email: true, name: true } },
      },
    }),
    prisma.deposit.count({ where }),
  ]);

  return NextResponse.json({ deposits, total, page, limit });
}
