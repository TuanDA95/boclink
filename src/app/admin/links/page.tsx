import { prisma } from "@/lib/prisma";
import { auth } from "@/lib/auth";
import { headers } from "next/headers";
import LinksClient from "./LinksClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Quản lý Link" };

export default async function AdminLinksPage() {
  const session = await auth();
  const headersList = await headers();

  // Lấy domain từ request headers
  const host = headersList.get("host") || "localhost:3000";
  const protocol = process.env.NODE_ENV === "production" ? "https" : "http";
  const domain = `${protocol}://${host}`;

  const [links, total, user] = await Promise.all([
    prisma.link.findMany({
      orderBy: { createdAt: "desc" },
      take: 20,
      include: {
        _count: { select: { purchases: true } },
        user: { select: { name: true, email: true } },
      },
    }),
    prisma.link.count(),
    session?.user?.id
      ? prisma.user.findUnique({
          where: { id: session.user.id },
          select: { apiToken: true },
        })
      : null,
  ]);

  return (
    <LinksClient
      initialLinks={JSON.parse(JSON.stringify(links))}
      total={total}
      apiToken={user?.apiToken || ""}
      domain={domain}
    />
  );
}
