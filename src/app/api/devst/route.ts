import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { extractTargetUrlFromReq, resolveOriginalUrl } from "@/lib/url-resolver";

/**
 * Developer API
 * GET  /api/devst?token={API_TOKEN}&url={TARGET_URL}&code={OPTIONAL_CODE}
 * POST /api/devst  body: { token, url, code? }
 *
 * Trả về JSON:
 * { "status": "success", "short_url": "...", "code": "...", "original_url": "..." }
 */
async function handleRequest(token: string | null, rawUrl: string | null, code: string | null, origin: string) {
  if (!token || !rawUrl) {
    return NextResponse.json(
      { status: "error", message: "Thiếu tham số token hoặc url" },
      { status: 400 }
    );
  }

  // Gỡ bọc nếu là link nội bộ (bọc lần 2, 3...)
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

  // Tạo hoặc dùng slug được cung cấp
  let slug = code
    ? code.toLowerCase().replace(/[^a-z0-9-]/g, "")
    : Math.random().toString(36).slice(2, 8);

  // Kiểm tra trùng slug
  const existing = await prisma.link.findUnique({ where: { slug } });
  if (existing) {
    if (existing.userId === user.id) {
      // Link đã tồn tại thuộc user này → trả về luôn
      return NextResponse.json({
        status: "success",
        short_url: `${origin}/l/${existing.slug}`,
        code: existing.slug,
        original_url: existing.originalUrl,
      });
    }
    slug = `${slug}-${Math.random().toString(36).slice(2, 5)}`;
  }

  const link = await prisma.link.create({
    data: {
      slug,
      originalUrl: targetUrl,
      title: targetUrl,
      userId: user.id,
    },
  });

  return NextResponse.json({
    status: "success",
    short_url: `${origin}/l/${link.slug}`,
    code: link.slug,
    original_url: link.originalUrl,
  });
}

export async function GET(req: NextRequest) {
  const { searchParams, origin } = new URL(req.url);
  const rawUrl = extractTargetUrlFromReq(req.url) || searchParams.get("url");
  return handleRequest(
    searchParams.get("token"),
    rawUrl,
    searchParams.get("code"),
    origin
  );
}

export async function POST(req: NextRequest) {
  const { origin } = new URL(req.url);
  const body = await req.json().catch(() => ({}));
  return handleRequest(body.token, body.url, body.code ?? null, origin);
}
