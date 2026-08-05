import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { resolveOriginalUrl } from "@/lib/url-resolver";

// Endpoint công khai dùng cho trang quảng cáo - trả về link gốc sau khi xem xong quảng cáo
export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params;

  const link = await prisma.link.findUnique({
    where: { slug, isActive: true },
    select: {
      id: true,
      title: true,
      adDuration: true,
      originalUrl: true,
    },
  });

  if (!link) {
    return NextResponse.json({ error: "Link không tồn tại" }, { status: 404 });
  }

  const resolvedUrl = await resolveOriginalUrl(link.originalUrl);
  const isUrlTitle = !link.title || link.title.startsWith("http://") || link.title.startsWith("https://");
  const displayTitle = isUrlTitle ? `Get link - ${slug}` : link.title;

  return NextResponse.json({
    link: {
      ...link,
      originalUrl: resolvedUrl,
      title: displayTitle,
    },
  });
}

