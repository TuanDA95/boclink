import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { randomBytes } from "crypto";

// GET - lấy thông tin API token của user hiện tại
export async function GET() {
  const session = await auth();
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const user = await prisma.user.findUnique({
    where: { id: session.user.id },
    select: { apiToken: true },
  });

  return NextResponse.json({ apiToken: user?.apiToken });
}

// POST - tạo lại API token (reset)
export async function POST(req: NextRequest) {
  const session = await auth();
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const newToken = randomBytes(20).toString("hex");
  const user = await prisma.user.update({
    where: { id: session.user.id },
    data: { apiToken: newToken },
    select: { apiToken: true },
  });

  return NextResponse.json({ apiToken: user.apiToken });
}
