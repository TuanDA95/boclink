import { prisma } from "@/lib/prisma";
import { auth } from "@/lib/auth";
import { getSetting } from "@/lib/settings";
import { headers } from "next/headers";
import { redirect } from "next/navigation";
import DashboardClient from "./DashboardClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Dashboard - API Key" };

export default async function DashboardPage() {
  const session = await auth();
  if (!session?.user?.id) {
    redirect("/login?callbackUrl=/dashboard");
  }

  const headersList = await headers();
  const host = headersList.get("host") || "localhost:3000";
  const protocol = process.env.NODE_ENV === "production" ? "https" : "http";
  const domain = `${protocol}://${host}`;

  const since12h = new Date(Date.now() - 12 * 60 * 60 * 1000);

  const [user, links, deposits, purchases, bankDepositRaw, cardDepositRaw] = await Promise.all([
    prisma.user.findUnique({
      where: { id: session.user.id },
      select: { id: true, name: true, email: true, balance: true, apiToken: true, role: true },
    }),
    prisma.link.findMany({
      where: { userId: session.user.id },
      orderBy: { createdAt: "desc" },
    }),
    prisma.deposit.findMany({
      where: { userId: session.user.id },
      orderBy: { createdAt: "desc" },
      take: 20,
    }),
    prisma.purchase.findMany({
      where: { userId: session.user.id, createdAt: { gte: since12h } },
      include: { link: { select: { slug: true, title: true, originalUrl: true, price: true } } },
      orderBy: { createdAt: "desc" },
      take: 20,
    }),
    getSetting("BANK_DEPOSIT_ENABLED", "true"),
    getSetting("CARD_DEPOSIT_ENABLED", "true"),
  ]);

  if (!user) {
    redirect("/login");
  }

  const bankDepositEnabled = bankDepositRaw !== "false" && bankDepositRaw !== "0";
  const cardDepositEnabled = cardDepositRaw !== "false" && cardDepositRaw !== "0";

  return (
    <DashboardClient
      user={JSON.parse(JSON.stringify(user))}
      links={JSON.parse(JSON.stringify(links))}
      deposits={JSON.parse(JSON.stringify(deposits))}
      purchases={JSON.parse(JSON.stringify(purchases))}
      domain={domain}
      bankDepositEnabled={bankDepositEnabled}
      cardDepositEnabled={cardDepositEnabled}
    />
  );
}
