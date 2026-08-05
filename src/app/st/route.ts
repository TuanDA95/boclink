import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { extractTargetUrlFromReq, resolveOriginalUrl } from "@/lib/url-resolver";

/**
 * GET /st?token={API_TOKEN}&url={TARGET_URL}&code={OPTIONAL_CODE}
 * Quicklink API – tạo link ngắn nhanh, redirect về trang link bọc
 */
export async function GET(req: NextRequest) {
  const { searchParams } = new URL(req.url);
  const token = searchParams.get("token");
  const rawUrl = extractTargetUrlFromReq(req.url) || searchParams.get("url");
  const code = searchParams.get("code");

  if (!token || !rawUrl) {
    return NextResponse.json(
      { status: "error", message: "Thiếu tham số token hoặc url" },
      { status: 400 }
    );
  }

  // Gỡ các lớp bọc nếu là link nội bộ (bọc lần 2, 3...)
  const targetUrl = await resolveOriginalUrl(rawUrl);

  // Validate URL
  try {
    new URL(targetUrl);
  } catch {
    return NextResponse.json(
      { status: "error", message: "URL không hợp lệ" },
      { status: 400 }
    );
  }

  // Tìm user theo apiToken
  const user = await prisma.user.findUnique({
    where: { apiToken: token },
    select: { id: true },
  });

  if (!user) {
    return NextResponse.json(
      { status: "error", message: "Token không hợp lệ" },
      { status: 401 }
    );
  }

  // Tạo slug tự động hoặc dùng code được cung cấp
  let slug = code
    ? code.toLowerCase().replace(/[^a-z0-9-]/g, "")
    : Math.random().toString(36).slice(2, 8);

  // Kiểm tra slug trùng
  const existing = await prisma.link.findUnique({ where: { slug } });
  if (existing) {
    // Nếu slug đã tồn tại và là của user này → trả về luôn
    if (existing.userId === user.id) {
      const baseUrl = new URL(req.url).origin;
      return NextResponse.redirect(`${baseUrl}/l/${slug}`, 302);
    }
    // Nếu slug đã tồn tại và của người khác → thêm random suffix
    slug = `${slug}-${Math.random().toString(36).slice(2, 5)}`;
  }

  // Tạo link mới
  const link = await prisma.link.create({
    data: {
      slug,
      originalUrl: targetUrl,
      title: targetUrl,
      userId: user.id,
    },
  });

  const baseUrl = new URL(req.url).origin;
  return NextResponse.redirect(`${baseUrl}/l/${link.slug}`, 302);
}
