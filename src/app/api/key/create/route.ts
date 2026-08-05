import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { randomBytes } from "crypto";

/**
 * POST /api/key/create
 * Body: { slug?: string, durationDays?: number, token?: string }
 */
export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const { slug, durationDays = 1, token } = body;

  let userId: string | undefined = undefined;
  let linkId: string | undefined = undefined;

  if (token) {
    const user = await prisma.user.findUnique({ where: { apiToken: token } });
    if (user) userId = user.id;
  }

  if (slug) {
    const link = await prisma.link.findUnique({ where: { slug } });
    if (link) {
      linkId = link.id;
      if (!userId && link.userId) userId = link.userId;
    }
  }

  // Generate unique keyCode format KEY-XXXX-XXXX
  const randomPart1 = randomBytes(3).toString("hex").toUpperCase();
  const randomPart2 = randomBytes(3).toString("hex").toUpperCase();
  const keyCode = `KEY-${randomPart1}-${randomPart2}`;

  const now = new Date();
  const expiresAt = new Date(now.getTime() + durationDays * 24 * 60 * 60 * 1000);

  const keyRecord = await prisma.key.create({
    data: {
      keyCode,
      userId,
      linkId,
      durationDays: parseInt(durationDays.toString()),
      expiresAt,
    },
  });

  return NextResponse.json({
    status: "success",
    keyCode: keyRecord.keyCode,
    expiresAt: keyRecord.expiresAt,
    durationDays: keyRecord.durationDays,
  });
}
