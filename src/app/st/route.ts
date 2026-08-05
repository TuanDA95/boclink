import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import {
  extractTargetUrlFromReq,
  resolveOriginalUrl,
  getAppOrigin,
} from "@/lib/url-resolver";

/**
 * GET /st?token={API_TOKEN}&url={TARGET_URL}&code={OPTIONAL_CODE}
 * Quicklink API (Tương thích LinkController.php):
 * - Khi gọi bình thường: Redirect 302 về {origin}/key/{code} (hoặc text error khi thất bại)
 * - Khi gọi với format=json hoặc Accept: application/json: Trả về JSON { status, short_url, code, original_url }
 */
export async function GET(req: NextRequest) {
  const { searchParams } = new URL(req.url);
  const token = searchParams.get("token");
  const rawUrl = extractTargetUrlFromReq(req.url) || searchParams.get("url");
  const code = searchParams.get("code");
  const isJsonReq =
    searchParams.get("format") === "json" ||
    searchParams.get("json") === "1" ||
    req.headers.get("accept")?.includes("application/json");

  const sendError = (msg: string, statusCode = 400) => {
    if (isJsonReq) {
      return NextResponse.json({ status: "error", message: msg }, { status: statusCode });
    }
    return new NextResponse(msg, { status: statusCode, headers: { "Content-Type": "text/plain; charset=utf-8" } });
  };

  if (!token || !rawUrl) {
    return sendError("URL không hợp lệ", 400);
  }

  // Gỡ các lớp bọc nếu là link nội bộ (bọc lần 2, 3...)
  const targetUrl = await resolveOriginalUrl(rawUrl);

  // Validate URL
  try {
    new URL(targetUrl);
  } catch {
    return sendError("URL không hợp lệ", 400);
  }

  // 1. Tìm user theo apiToken truyền vào
  let user = await prisma.user.findUnique({
    where: { apiToken: token },
    select: { id: true },
  });

  // 2. Nếu không tìm thấy (token từ DB cũ hoặc token chung) -> fallback lấy tài khoản ADMIN
  if (!user) {
    user = await prisma.user.findFirst({
      where: { role: "ADMIN" },
      select: { id: true },
    });
  }

  if (!user) {
    return sendError("Token không hợp lệ", 401);
  }

  // Tạo slug tự động hoặc dùng code được cung cấp
  let slug = code
    ? code.toLowerCase().replace(/[^a-z0-9-]/g, "")
    : Math.random().toString(36).slice(2, 8);

  const origin = getAppOrigin(req);

  // Kiểm tra slug trùng
  const existing = await prisma.link.findUnique({ where: { slug } });
  if (existing) {
    if (existing.userId === user.id) {
      const shortUrl = `${origin}/l/${existing.slug}`;
      if (isJsonReq) {
        return NextResponse.json({
          status: "success",
          short_url: shortUrl,
          code: existing.slug,
          original_url: existing.originalUrl,
        });
      }
      return NextResponse.redirect(shortUrl, 302);
    }
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

  const shortUrl = `${origin}/l/${link.slug}`;

  if (isJsonReq) {
    return NextResponse.json({
      status: "success",
      short_url: shortUrl,
      code: link.slug,
      original_url: link.originalUrl,
    });
  }

  return NextResponse.redirect(shortUrl, 302);
}


