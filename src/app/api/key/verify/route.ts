import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

/**
 * GET /api/key/verify?keyCode=...&hwid=...
 * Verify license key for Game/IPA/Dylib application
 */
export async function GET(req: NextRequest) {
  const { searchParams } = new URL(req.url);
  const keyCode = searchParams.get("keyCode") || searchParams.get("key");
  const hwid = searchParams.get("hwid") || searchParams.get("device_id");

  if (!keyCode) {
    return NextResponse.json(
      { status: "error", message: "Thiếu tham số keyCode" },
      { status: 400 }
    );
  }

  const keyRecord = await prisma.key.findUnique({
    where: { keyCode },
  });

  if (!keyRecord) {
    return NextResponse.json(
      { status: "error", message: "Key không tồn tại" },
      { status: 404 }
    );
  }

  // Check expiration
  if (new Date() > new Date(keyRecord.expiresAt)) {
    return NextResponse.json(
      { status: "error", message: "Key đã hết hạn" },
      { status: 400 }
    );
  }

  // Check or bind HWID
  if (hwid) {
    if (!keyRecord.hwid) {
      // First time use -> bind HWID
      await prisma.key.update({
        where: { id: keyRecord.id },
        data: { hwid, isUsed: true },
      });
    } else if (keyRecord.hwid !== hwid) {
      return NextResponse.json(
        { status: "error", message: "Key không khớp với thiết bị (HWID mismatch)" },
        { status: 403 }
      );
    }
  }

  return NextResponse.json({
    status: "success",
    message: "Key hợp lệ",
    key: keyRecord.keyCode,
    hwid: keyRecord.hwid || hwid || null,
    expiresAt: keyRecord.expiresAt,
    isUsed: keyRecord.isUsed,
  });
}
