import { prisma } from "@/lib/prisma";
import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import DepositsClient from "./DepositsClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Lịch sử Nạp tiền" };

export default async function AdminDepositsPage() {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    redirect("/");
  }

  const [deposits, total, stats] = await Promise.all([
    prisma.deposit.findMany({
      orderBy: { createdAt: "desc" },
      take: 15,
      include: { user: { select: { name: true, email: true } } },
    }),
    prisma.deposit.count(),
    prisma.deposit.groupBy({
      by: ["status"],
      _count: true,
      _sum: { amount: true },
    }),
  ]);

  return (
    <DepositsClient
      initialDeposits={JSON.parse(JSON.stringify(deposits))}
      total={total}
      stats={JSON.parse(JSON.stringify(stats))}
    />
  );
}
