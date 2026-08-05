import { prisma } from "@/lib/prisma";
import { formatVND } from "@/lib/sepay";
import {
  Link2,
  Users,
  UserPlus,
  DollarSign,
  Calendar,
  Zap,
  ArrowUpRight,
  Clock,
} from "lucide-react";
import Link from "next/link";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Dashboard - Admin" };
export const dynamic = "force-dynamic";
export const revalidate = 0;

async function getStats() {
  const startOfToday = new Date();
  startOfToday.setHours(0, 0, 0, 0);

  const startOfMonth = new Date(startOfToday.getFullYear(), startOfToday.getMonth(), 1);

  const [
    totalLinks,
    totalUsers,
    todayUsers,
    totalRevenueAgg,
    monthRevenueAgg,
    todayRevenueAgg,
    recentDeposits,
    topLinks,
  ] = await Promise.all([
    prisma.link.count(),
    prisma.user.count({ where: { role: "USER" } }),
    prisma.user.count({
      where: { role: "USER", createdAt: { gte: startOfToday } },
    }),
    prisma.deposit.aggregate({
      where: { status: "SUCCESS" },
      _sum: { amount: true },
    }),
    prisma.deposit.aggregate({
      where: { status: "SUCCESS", createdAt: { gte: startOfMonth } },
      _sum: { amount: true },
    }),
    prisma.deposit.aggregate({
      where: { status: "SUCCESS", createdAt: { gte: startOfToday } },
      _sum: { amount: true },
    }),
    prisma.deposit.findMany({
      where: { status: "SUCCESS" },
      orderBy: { createdAt: "desc" },
      take: 6,
      include: { user: { select: { name: true, email: true } } },
    }),
    prisma.link.findMany({
      orderBy: { clicks: "desc" },
      take: 5,
      select: { id: true, title: true, slug: true, clicks: true, price: true },
    }),
  ]);

  return {
    totalLinks,
    totalUsers,
    todayUsers,
    totalRevenue: totalRevenueAgg._sum.amount || 0,
    monthRevenue: monthRevenueAgg._sum.amount || 0,
    todayRevenue: todayRevenueAgg._sum.amount || 0,
    recentDeposits,
    topLinks,
  };
}

export default async function AdminDashboard() {
  const stats = await getStats();

  const cards = [
    {
      label: "Doanh thu tổng",
      value: formatVND(stats.totalRevenue),
      icon: DollarSign,
      color: "#10b981",
      bg: "rgba(16, 185, 129, 0.12)",
    },
    {
      label: "Doanh thu tháng này",
      value: formatVND(stats.monthRevenue),
      icon: Calendar,
      color: "#6366f1",
      bg: "rgba(99, 102, 241, 0.12)",
    },
    {
      label: "Doanh thu hôm nay",
      value: formatVND(stats.todayRevenue),
      icon: Zap,
      color: "#06b6d4",
      bg: "rgba(6, 182, 212, 0.12)",
    },
    {
      label: "Tổng số user",
      value: stats.totalUsers.toLocaleString("vi-VN"),
      icon: Users,
      color: "#8b5cf6",
      bg: "rgba(139, 92, 246, 0.12)",
    },
    {
      label: "User mới hôm nay",
      value: stats.todayUsers.toLocaleString("vi-VN"),
      icon: UserPlus,
      color: "#f59e0b",
      bg: "rgba(245, 158, 11, 0.12)",
    },
    {
      label: "Tổng số link",
      value: stats.totalLinks.toLocaleString("vi-VN"),
      icon: Link2,
      color: "#ec4899",
      bg: "rgba(236, 72, 153, 0.12)",
    },
  ];

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: 32 }}>
        <h1 style={{ fontSize: 28, fontWeight: 700, color: "#e2e8f0" }}>Dashboard</h1>
        <p style={{ color: "#94a3b8", marginTop: 4 }}>Tổng quan hệ thống & doanh thu Sub2S</p>
      </div>

      {/* Stat Cards - responsive grid */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
          gap: 16,
          marginBottom: 28,
        }}
      >
        {cards.map((card) => (
          <div key={card.label} className="stat-card" style={{ padding: "18px 20px" }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <div>
                <p style={{ color: "#94a3b8", fontSize: 13, marginBottom: 6, fontWeight: 500 }}>
                  {card.label}
                </p>
                <p style={{ fontSize: 20, fontWeight: 800, color: "#e2e8f0" }}>{card.value}</p>
              </div>
              <div
                style={{
                  width: 44,
                  height: 44,
                  borderRadius: 12,
                  background: card.bg,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >
                <card.icon size={20} color={card.color} />
              </div>
            </div>
          </div>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(300px, 1fr))", gap: 20 }}>
        {/* Recent Deposits */}
        <div className="glass-card">
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
            <h2 style={{ fontWeight: 600, fontSize: 16, color: "#f8fafc" }}>Nạp tiền gần đây</h2>
            <Link href="/admin/deposits" style={{ color: "#6366f1", fontSize: 13, textDecoration: "none", display: "flex", alignItems: "center", gap: 4 }}>
              Xem tất cả <ArrowUpRight size={14} />
            </Link>
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {stats.recentDeposits.length === 0 && (
              <p style={{ color: "#94a3b8", textAlign: "center", padding: "20px 0" }}>Chưa có giao dịch</p>
            )}
            {stats.recentDeposits.map((dep) => (
              <div key={dep.id} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "10px 0", borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                <div>
                  <p style={{ fontSize: 14, fontWeight: 500, color: "#e2e8f0" }}>{dep.user.name || dep.user.email}</p>
                  <p style={{ fontSize: 12, color: "#94a3b8", display: "flex", alignItems: "center", gap: 4, marginTop: 2 }}>
                    <Clock size={11} />
                    {new Date(dep.createdAt).toLocaleString("vi-VN", { hour12: false })}
                  </p>
                </div>
                <span style={{ color: "#10b981", fontWeight: 700, fontSize: 14 }}>
                  +{formatVND(dep.amount)}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* Top Links */}
        <div className="glass-card">
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
            <h2 style={{ fontWeight: 600, fontSize: 16, color: "#f8fafc" }}>Top Link xem nhiều</h2>
            <Link href="/admin/links" style={{ color: "#6366f1", fontSize: 13, textDecoration: "none", display: "flex", alignItems: "center", gap: 4 }}>
              Xem tất cả <ArrowUpRight size={14} />
            </Link>
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {stats.topLinks.map((link, i) => (
              <div key={link.id} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "10px 0", borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  <span style={{ fontSize: 13, color: "#6366f1", fontWeight: 700, width: 22 }}>#{i + 1}</span>
                  <div>
                    <p style={{ fontSize: 14, fontWeight: 500, color: "#e2e8f0" }}>{link.title || link.slug}</p>
                    <p style={{ fontSize: 12, color: "#94a3b8" }}>/{link.slug}</p>
                  </div>
                </div>
                <div style={{ textAlign: "right" }}>
                  <p style={{ fontSize: 14, fontWeight: 600, color: "#e2e8f0" }}>{link.clicks.toLocaleString()} clicks</p>
                  <p style={{ fontSize: 12, color: "#94a3b8" }}>{formatVND(link.price)}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
