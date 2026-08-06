"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { formatVND } from "@/lib/sepay";
import { Wallet, CheckCircle, Clock, XCircle, Check } from "lucide-react";

declare const Swal: any;

interface DepositItem {
  id: string;
  userId: string;
  amount: number;
  realValue: number | null;
  method: string;
  status: string;
  paymentContent?: string | null;
  cardTelco?: string | null;
  cardCode?: string | null;
  cardSerial?: string | null;
  cardRequestId?: string | null;
  cardMessage?: string | null;
  createdAt: string;
  confirmedAt?: string | null;
  user: {
    name: string | null;
    email: string;
  };
}

interface StatItem {
  status: string;
  _count: number;
  _sum: { amount: number | null };
}

interface Props {
  initialDeposits: DepositItem[];
  stats: StatItem[];
}

export default function DepositsClient({ initialDeposits, stats }: Props) {
  const router = useRouter();
  const [deposits, setDeposits] = useState<DepositItem[]>(initialDeposits);
  const [loadingId, setLoadingId] = useState<string | null>(null);

  const successStat = stats.find((s) => s.status === "SUCCESS");
  const pendingStat = stats.find((s) => s.status === "PENDING");

  const statusMap: Record<string, { label: string; class: string }> = {
    SUCCESS: { label: "Thành công", class: "badge-success" },
    PENDING: { label: "Chờ xử lý", class: "badge-pending" },
    FAILED: { label: "Thất bại", class: "badge-failed" },
    CANCELLED: { label: "Đã hủy", class: "badge-failed" },
  };

  const handleApprove = async (dep: DepositItem) => {
    const userName = dep.user.name || dep.user.email;
    const creditAmount = dep.realValue ?? dep.amount;

    if (typeof Swal !== "undefined") {
      const confirm = await Swal.fire({
        title: "Duyệt đơn nạp tiền?",
        html: `Bạn có chắc chắn muốn duyệt đơn nạp này?<br/><br/>Số tiền <strong style="color:#10b981">${formatVND(creditAmount)}</strong> sẽ được cộng trực tiếp vào tài khoản <strong>${userName}</strong>.`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#10b981",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Đồng ý Duyệt & Cộng tiền",
        cancelButtonText: "Hủy",
        background: "#16161a",
        color: "#fff",
      });

      if (!confirm.isConfirmed) return;
    }

    setLoadingId(dep.id);
    try {
      const res = await fetch("/api/admin/deposits/approve", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ depositId: dep.id }),
      });
      const data = await res.json();

      if (res.ok) {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "success",
            title: "Thành công!",
            text: data.message || "Đã duyệt đơn nạp tiền thành công",
            timer: 2000,
            showConfirmButton: false,
            background: "#16161a",
            color: "#fff",
          });
        }
        setDeposits((prev) =>
          prev.map((d) =>
            d.id === dep.id
              ? { ...d, status: "SUCCESS", confirmedAt: new Date().toISOString(), cardMessage: "Đã duyệt thủ công bởi Admin" }
              : d
          )
        );
        router.refresh();
      } else {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: data.error || "Duyệt đơn nạp thất bại", background: "#16161a", color: "#fff" });
        }
      }
    } catch {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "error", title: "Lỗi", text: "Lỗi kết nối máy chủ", background: "#16161a", color: "#fff" });
      }
    } finally {
      setLoadingId(null);
    }
  };

  return (
    <div className="animate-fade-in">
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ fontSize: 24, fontWeight: 700 }}>Lịch sử Nạp tiền</h1>
        <p style={{ color: "#94a3b8", fontSize: 13, marginTop: 2 }}>
          Toàn bộ giao dịch nạp tiền vào hệ thống
        </p>
      </div>

      {/* Summary */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: 16, marginBottom: 28 }}>
        <div className="stat-card">
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <div style={{ padding: 10, background: "rgba(16,185,129,0.1)", borderRadius: 10 }}>
              <CheckCircle size={20} color="#10b981" />
            </div>
            <div>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>Đã xác nhận</p>
              <p style={{ fontWeight: 700, fontSize: 18 }}>{formatVND(successStat?._sum.amount || 0)}</p>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>{successStat?._count || 0} giao dịch</p>
            </div>
          </div>
        </div>
        <div className="stat-card">
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <div style={{ padding: 10, background: "rgba(245,158,11,0.1)", borderRadius: 10 }}>
              <Clock size={20} color="#f59e0b" />
            </div>
            <div>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>Đang chờ</p>
              <p style={{ fontWeight: 700, fontSize: 18 }}>{pendingStat?._count || 0}</p>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>đơn chờ xác nhận</p>
            </div>
          </div>
        </div>
        <div className="stat-card">
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <div style={{ padding: 10, background: "rgba(99,102,241,0.1)", borderRadius: 10 }}>
              <Wallet size={20} color="#818cf8" />
            </div>
            <div>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>Tổng giao dịch</p>
              <p style={{ fontWeight: 700, fontSize: 18 }}>{deposits.length}</p>
              <p style={{ fontSize: 12, color: "#94a3b8" }}>tất cả phương thức</p>
            </div>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="glass-card admin-table-container" style={{ padding: 0, borderRadius: 16 }}>
        <table style={{ width: "100%", borderCollapse: "collapse" }}>
          <thead>
            <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)" }}>
              {["Người dùng", "Số tiền", "Thực nhận", "Phương thức", "Chi tiết nạp / Mã TT", "Trạng thái", "Thời gian", "Thao tác"].map((h) => (
                <th key={h} style={{ padding: "14px 16px", textAlign: "left", fontSize: 12, color: "#94a3b8", fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.5px" }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {deposits.map((dep) => {
              const isCard = dep.method === "SCRATCH_CARD" || dep.method === "CARD";
              const isSuccess = dep.status === "SUCCESS";
              const isLoading = loadingId === dep.id;

              return (
                <tr key={dep.id} className="table-row">
                  <td style={{ padding: "14px 16px" }}>
                    <p style={{ fontWeight: 500, fontSize: 14 }}>{dep.user.name || "—"}</p>
                    <p style={{ fontSize: 12, color: "#94a3b8" }}>{dep.user.email}</p>
                  </td>
                  <td style={{ padding: "14px 16px", fontWeight: 600, fontSize: 14, color: isSuccess ? "#10b981" : "#e2e8f0" }}>
                    {formatVND(dep.amount)}
                  </td>
                  <td style={{ padding: "14px 16px", fontWeight: 600, fontSize: 14, color: isSuccess ? "#34d399" : "#94a3b8" }}>
                    {dep.realValue !== null && dep.realValue !== undefined ? formatVND(dep.realValue) : formatVND(dep.amount)}
                  </td>
                  <td style={{ padding: "14px 16px" }}>
                    <span
                      style={{
                        display: "inline-flex",
                        alignItems: "center",
                        gap: 4,
                        padding: "3px 10px",
                        borderRadius: 20,
                        fontSize: 12,
                        fontWeight: 600,
                        background: isCard ? "rgba(245,158,11,0.15)" : "rgba(16,185,129,0.15)",
                        color: isCard ? "#f59e0b" : "#10b981",
                        border: isCard ? "1px solid rgba(245,158,11,0.3)" : "1px solid rgba(16,185,129,0.3)",
                      }}
                    >
                      {isCard ? `💳 Thẻ cào (${dep.cardTelco || "N/A"})` : "🏦 Ngân hàng (VietQR)"}
                    </span>
                  </td>
                  <td style={{ padding: "14px 16px", fontSize: 12, color: "#94a3b8" }}>
                    {isCard ? (
                      <div>
                        <p style={{ margin: 0, color: "#e2e8f0", fontFamily: "monospace", fontSize: 12 }}>
                          Seri: <span style={{ color: "#fbbf24" }}>{dep.cardSerial || "—"}</span> | PIN: <span style={{ color: "#fbbf24" }}>{dep.cardCode || "—"}</span>
                        </p>
                        {dep.cardRequestId && (
                          <p style={{ margin: "2px 0 0", fontSize: 11, color: "#64748b", fontFamily: "monospace" }}>
                            ReqID: {dep.cardRequestId}
                          </p>
                        )}
                        {dep.cardMessage && (
                          <p style={{ margin: "2px 0 0", fontSize: 11, color: dep.status === "FAILED" ? "#ef4444" : "#94a3b8" }}>
                            {dep.cardMessage}
                          </p>
                        )}
                      </div>
                    ) : (
                      <span style={{ fontFamily: "monospace" }}>{dep.paymentContent || "—"}</span>
                    )}
                  </td>
                  <td style={{ padding: "14px 16px" }}>
                    <span className={`badge ${statusMap[dep.status]?.class || "badge-pending"}`}>
                      {statusMap[dep.status]?.label || dep.status}
                    </span>
                  </td>
                  <td style={{ padding: "14px 16px", fontSize: 12, color: "#94a3b8" }}>
                    {dep.confirmedAt
                      ? new Date(dep.confirmedAt).toLocaleString("vi-VN")
                      : new Date(dep.createdAt).toLocaleString("vi-VN")}
                  </td>
                  <td style={{ padding: "14px 16px" }}>
                    {isSuccess ? (
                      <span style={{ color: "#10b981", fontSize: 12, fontWeight: 600, display: "inline-flex", alignItems: "center", gap: 4 }}>
                        <Check size={14} /> Đã duyệt
                      </span>
                    ) : (
                      <button
                        onClick={() => handleApprove(dep)}
                        disabled={isLoading}
                        style={{
                          display: "inline-flex",
                          alignItems: "center",
                          gap: 6,
                          padding: "6px 12px",
                          background: "rgba(16,185,129,0.15)",
                          border: "1px solid rgba(16,185,129,0.3)",
                          color: "#10b981",
                          borderRadius: 6,
                          cursor: isLoading ? "not-allowed" : "pointer",
                          fontSize: 12,
                          fontWeight: 600,
                          transition: "all 0.15s",
                        }}
                        title="Duyệt thủ công và cộng tiền cho người dùng"
                      >
                        {isLoading ? (
                          <span className="spinner" style={{ width: 12, height: 12 }} />
                        ) : (
                          <>
                            <CheckCircle size={13} /> Duyệt đơn
                          </>
                        )}
                      </button>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
        {deposits.length === 0 && (
          <div style={{ textAlign: "center", padding: "40px 20px", color: "#94a3b8" }}>
            Chưa có giao dịch nạp tiền nào
          </div>
        )}
      </div>
    </div>
  );
}
