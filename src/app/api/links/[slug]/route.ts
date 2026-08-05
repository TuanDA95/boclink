import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params;

  const link = await prisma.link.findUnique({
    where: { slug, isActive: true },
    select: {
      id: true,
      slug: true,
      title: true,
      description: true,
      price: true,
      adDuration: true,
      clicks: true,
    },
  });

  if (!link) {
    return NextResponse.json({ error: "Link không tồn tại" }, { status: 404 });
  }

  // Tăng click count
  await prisma.link.update({
    where: { slug },
    data: { clicks: { increment: 1 } },
  });

  return NextResponse.json({ link });
}
