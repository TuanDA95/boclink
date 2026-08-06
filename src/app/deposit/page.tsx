import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { redirect } from "next/navigation";
import Link from "next/link";
import { formatVND } from "@/lib/sepay";
import { Wallet, CreditCard, Building2, Clock, ChevronRight, Phone } from "lucide-react";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Nạp tiền - API Key" };

export default async function DepositPage() {
  const session = await auth();
  if (!session?.user) redirect("/login");

  const user = await prisma.user.findUnique({
    where: { id: session.user.id },
    select: { balance: true },
  });

  const recentDeposits = await prisma.deposit.findMany({
    where: { userId: session.user.id },
    orderBy: { createdAt: "desc" },
    take: 5,
  });

  const methods = [
    {
      href: "/deposit/bank",
      icon: Building2,
      title: "Chuyển khoản Ngân hàng (VietQR)",
      desc: "Quét mã VietQR — Nạp tiền tự động 24/7 không cần chờ duyệt",
      badge: "Miễn phí",
      badgeColor: "#10b981",
      color: "#2dd4bf",
      bg: "rgba(20,184,166,0.1)",
    },
    {
      href: "/deposit/card",
      icon: CreditCard,
      title: "Thẻ Cào Điện Thoại & Thẻ Game",
      desc: "Viettel, Vinaphone, Mobifone, Zing, Gate, Garena, Vcoin...",
      badge: "Tự động 24/7",
      badgeColor: "#f59e0b",
      color: "#f59e0b",
      bg: "rgba(245,158,11,0.1)",
    },
  ];

  const statusMap: Record<string, { label: string; cls: string }> = {
    SUCCESS: { label: "Thành công", cls: "badge-success" },
    PENDING: { label: "Đang chờ", cls: "badge-pending" },
    FAILED: { label: "Thất bại", cls: "badge-failed" },
    CANCELLED: { label: "Đã hủy", cls: "badge-failed" },
  };

  return (
    <div className="animate-fade-in" style={{ maxWidth: 640, margin: "0 auto", padding: "40px 16px" }}>
      {/* Balance */}
      <div className="glass-card" style={{ marginBottom: 32, background: "linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1))", borderColor: "rgba(99,102,241,0.2)" }}>
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <div style={{ padding: 14, background: "rgba(99,102,241,0.2)", borderRadius: 14 }}>
            <Wallet size={24} color="#818cf8" />
          </div>
          <div>
            <p style={{ fontSize: 13, color: "#94a3b8" }}>Số dư tài khoản hiện tại</p>
            <p className="gradient-text" style={{ fontSize: 32, fontWeight: 800 }}>
              {formatVND(user?.balance || 0)}
            </p>
          </div>
        </div>
      </div>

      {/* Methods */}
      <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Chọn phương thức nạp tiền</h2>
      <div style={{ display: "flex", flexDirection: "column", gap: 12, marginBottom: 32 }}>
        {methods.map((m) => (
          <Link
            key={m.href}
            href={m.href}
            style={{ textDecoration: "none" }}
          >
            <div
              className="glass-card"
              style={{ display: "flex", alignItems: "center", gap: 16, cursor: "pointer", padding: 20 }}
            >
              <div style={{ padding: 12, background: m.bg, borderRadius: 12, flexShrink: 0 }}>
                <m.icon size={24} color={m.color} />
              </div>
              <div style={{ flex: 1 }}>
                <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 4 }}>
                  <p style={{ fontWeight: 600, fontSize: 15, color: "#e2e8f0" }}>{m.title}</p>
                  <span style={{ fontSize: 11, padding: "2px 8px", borderRadius: 20, background: `${m.badgeColor}20`, color: m.badgeColor, fontWeight: 600 }}>
                    {m.badge}
                  </span>
                </div>
                <p style={{ fontSize: 13, color: "#94a3b8" }}>{m.desc}</p>
              </div>
              <ChevronRight size={18} color="#94a3b8" />
            </div>
          </Link>
        ))}
      </div>

      {/* Recent Deposits */}
      {recentDeposits.length > 0 && (
        <>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 14 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600 }}>Giao dịch nạp gần đây</h2>
          </div>
          <div className="glass-card" style={{ padding: 0, overflow: "hidden" }}>
            {recentDeposits.map((dep, i) => (
              <div key={dep.id} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "14px 20px", borderBottom: i < recentDeposits.length - 1 ? "1px solid rgba(255,255,255,0.05)" : "none" }}>
                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  {dep.method === "SCRATCH_CARD" ? <CreditCard size={16} color="#f59e0b" /> : <Building2 size={16} color="#2dd4bf" />}
                  <div>
                    <p style={{ fontSize: 14, fontWeight: 500 }}>
                      {dep.method === "SCRATCH_CARD" ? `Thẻ Cào (${dep.cardTelco || "N/A"})` : "Chuyển khoản VietQR"}
                    </p>
                    <p style={{ fontSize: 12, color: "#94a3b8", display: "flex", alignItems: "center", gap: 4 }}>
                      <Clock size={10} />
                      {dep.createdAt.toLocaleDateString("vi-VN")}
                    </p>
                  </div>
                </div>
                <div style={{ textAlign: "right" }}>
                  <p style={{ fontSize: 14, fontWeight: 600, color: dep.status === "SUCCESS" ? "#10b981" : "#e2e8f0" }}>
                    {dep.status === "SUCCESS" ? "+" : ""}{formatVND(dep.realValue || dep.amount)}
                  </p>
                  <span className={`badge ${statusMap[dep.status]?.cls}`}>{statusMap[dep.status]?.label}</span>
                </div>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  );
}
