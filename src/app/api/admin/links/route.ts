import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { linkSchema } from "@/lib/validations";
import { resolveOriginalUrl } from "@/lib/url-resolver";

// GET all links (admin)
export async function GET(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const { searchParams } = new URL(req.url);
  const page = parseInt(searchParams.get("page") || "1");
  const limit = parseInt(searchParams.get("limit") || "20");
  const search = searchParams.get("search") || "";

  const where = search
    ? {
        OR: [
          { title: { contains: search } },
          { slug: { contains: search } },
          { originalUrl: { contains: search } },
        ],
      }
    : {};

  const [links, total] = await Promise.all([
    prisma.link.findMany({
      where,
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * limit,
      take: limit,
      include: {
        _count: { select: { purchases: true } },
        user: { select: { name: true, email: true } },
      },
    }),
    prisma.link.count({ where }),
  ]);

  return NextResponse.json({ links, total, page, limit });
}

// POST create link (admin)
export async function POST(req: NextRequest) {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const body = await req.json();
  const parsed = linkSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
  }

  const existing = await prisma.link.findUnique({ where: { slug: parsed.data.slug } });
  if (existing) {
    return NextResponse.json({ error: "Mã code đã tồn tại" }, { status: 409 });
  }

  const resolvedTarget = await resolveOriginalUrl(parsed.data.originalUrl);

  const data = {
    ...parsed.data,
    originalUrl: resolvedTarget,
    title: parsed.data.title || resolvedTarget,
    userId: session.user.id,
  };

  const link = await prisma.link.create({ data });
  return NextResponse.json({ link }, { status: 201 });
}
