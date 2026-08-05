import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";
import { TELCOS } from "@/lib/scratchCard";
import CardDepositClient from "./CardDepositClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Nạp tiền Thẻ Cào - Sub2S" };

export default async function ScratchCardDepositPage() {
  const session = await auth();
  if (!session?.user) {
    redirect("/login");
  }

  // Lấy danh sách % chiết khấu cho từng nhà mạng
  const discounts: Record<string, number> = {};
  for (const t of TELCOS) {
    const val = await getSetting(`CARD_DISCOUNT_${t.code}`, String(t.defaultDiscount));
    discounts[t.code] = Number(val) || t.defaultDiscount;
  }

  // Lấy lịch sử 20 giao dịch nạp thẻ cào gần nhất
  const initialDeposits = await prisma.deposit.findMany({
    where: {
      userId: session.user.id,
      method: "SCRATCH_CARD",
    },
    orderBy: { createdAt: "desc" },
    take: 20,
  });

  const user = await prisma.user.findUnique({
    where: { id: session.user.id },
    select: { balance: true },
  });

  const formattedDeposits = initialDeposits.map((d) => ({
    id: d.id,
    cardTelco: d.cardTelco || "N/A",
    cardCode: d.cardCode || "N/A",
    cardSerial: d.cardSerial || "N/A",
    declaredValue: d.declaredValue || d.amount,
    realValue: d.realValue || d.amount,
    status: d.status,
    cardMessage: d.cardMessage || "",
    createdAt: d.createdAt.toISOString(),
  }));

  return (
    <CardDepositClient
      discounts={discounts}
      initialDeposits={formattedDeposits}
      currentBalance={user?.balance || 0}
    />
  );
}
