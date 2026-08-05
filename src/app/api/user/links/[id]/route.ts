import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";

export async function PUT(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const { id } = await params;
  const link = await prisma.link.findUnique({ where: { id } });
  if (!link || link.userId !== session.user.id) {
    return NextResponse.json({ error: "Không tìm thấy link" }, { status: 404 });
  }

  const body = await req.json();
  const updated = await prisma.link.update({
    where: { id },
    data: {
      isActive: body.isActive ?? link.isActive,
      title: body.title ?? link.title,
    },
  });

  return NextResponse.json({ link: updated });
}

export async function DELETE(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const session = await auth();
  if (!session?.user?.id) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const { id } = await params;
  const link = await prisma.link.findUnique({ where: { id } });
  if (!link || link.userId !== session.user.id) {
    return NextResponse.json({ error: "Không tìm thấy link" }, { status: 404 });
  }

  await prisma.link.delete({ where: { id } });
  return NextResponse.json({ success: true });
}
