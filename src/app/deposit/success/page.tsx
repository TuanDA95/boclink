import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { formatVND } from "@/lib/sepay";
import { CheckCircle, ArrowRight } from "lucide-react";
import Link from "next/link";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Nạp tiền thành công" };

export default async function DepositSuccessPage({
  searchParams,
}: {
  searchParams: Promise<{ depositId?: string }>;
}) {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const { depositId } = await searchParams;

  const deposit = depositId
    ? await prisma.deposit.findFirst({
        where: { id: depositId, userId: session.user.id, status: "SUCCESS" },
      })
    : null;

  const user = await prisma.user.findUnique({
    where: { id: session.user.id },
    select: { balance: true },
  });

  return (
    <div style={{ maxWidth: 480, margin: "80px auto", padding: "0 16px", textAlign: "center" }} className="animate-fade-in">
      {/* Success icon */}
      <div style={{ position: "relative", width: 80, height: 80, margin: "0 auto 24px" }}>
        <div style={{ position: "absolute", inset: 0, borderRadius: "50%", background: "rgba(16,185,129,0.15)", animation: "pulse 2s ease infinite" }} />
        <div style={{ width: 80, height: 80, borderRadius: "50%", background: "rgba(16,185,129,0.2)", display: "flex", alignItems: "center", justifyContent: "center", position: "relative" }}>
          <CheckCircle size={40} color="#10b981" />
        </div>
      </div>

      <h1 style={{ fontSize: 28, fontWeight: 800, color: "#10b981", marginBottom: 8 }}>Nạp tiền thành công!</h1>

      {deposit && (
        <p style={{ fontSize: 32, fontWeight: 700, color: "#e2e8f0", margin: "16px 0" }}>
          +{formatVND(deposit.amount)}
        </p>
      )}

      <p style={{ color: "#94a3b8", marginBottom: 32 }}>
        Số dư hiện tại:{" "}
        <strong style={{ color: "#818cf8" }}>{formatVND(user?.balance || 0)}</strong>
      </p>

      <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        <Link href="/" className="btn-primary" style={{ justifyContent: "center" }}>
          Về trang chủ <ArrowRight size={16} />
        </Link>
        <Link href="/deposit" className="btn-secondary" style={{ justifyContent: "center" }}>
          Nạp thêm tiền
        </Link>
      </div>
    </div>
  );
}
