import { prisma } from "@/lib/prisma";
import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import PurchasesClient from "./PurchasesClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Lịch sử mua link - Admin API Key" };
export const dynamic = "force-dynamic";
export const revalidate = 0;

export default async function AdminPurchasesPage() {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    redirect("/");
  }

  const purchases = await prisma.purchase.findMany({
    include: {
      user: { select: { id: true, name: true, email: true } },
      link: { select: { id: true, slug: true, title: true, originalUrl: true } },
    },
    orderBy: { createdAt: "desc" },
    take: 2000,
  });

  return <PurchasesClient initialPurchases={JSON.parse(JSON.stringify(purchases))} />;
}
