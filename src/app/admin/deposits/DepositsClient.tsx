"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { formatVND } from "@/lib/sepay";
import { Wallet, CheckCircle, Clock, XCircle, Check, Search, Filter, Trash2 } from "lucide-react";

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
  total: number;
  stats: StatItem[];
}

export default function DepositsClient({ initialDeposits, total, stats }: Props) {
  const router = useRouter();
  const [deposits, setDeposits] = useState<DepositItem[]>(initialDeposits);
  const [totalCount, setTotalCount] = useState<number>(total);
  const [currentStats, setCurrentStats] = useState<StatItem[]>(stats);
  const [loadingId, setLoadingId] = useState<string | null>(null);
  const [fetching, setFetching] = useState(false);

  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 15;

  const fetchDeposits = useCallback(async (page: number, q: string, st: string) => {
    setFetching(true);
    try {
      const res = await fetch(`/api/admin/deposits?page=${page}&limit=${pageSize}&status=${st}&q=${encodeURIComponent(q)}`);
      const data = await res.json();
      if (res.ok && data.deposits) {
        setDeposits(data.deposits);
        setTotalCount(data.total);
        if (data.stats) setCurrentStats(data.stats);
      }
    } catch (err) {
      console.error("Lỗi lấy danh sách nạp tiền:", err);
    } finally {
      setFetching(false);
    }
  }, []);

  const [isInitialMount, setIsInitialMount] = useState(true);

  useEffect(() => {
    if (isInitialMount) {
      setIsInitialMount(false);
      return;
    }
    const timer = setTimeout(() => {
      fetchDeposits(currentPage, search, statusFilter);
    }, 300);
    return () => clearTimeout(timer);
  }, [currentPage, search, statusFilter, fetchDeposits]);

  const handleSearchChange = (val: string) => {
    setSearch(val);
    setCurrentPage(1);
  };

  const handleFilterChange = (val: string) => {
    setStatusFilter(val);
    setCurrentPage(1);
  };

  const totalPages = Math.ceil(totalCount / pageSize) || 1;

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

  const handleDelete = async (dep: DepositItem) => {
    const userName = dep.user.name || dep.user.email;

    if (typeof Swal !== "undefined") {
      const confirm = await Swal.fire({
        title: "Xoá đơn nạp tiền?",
        html: `Bạn có chắc muốn <strong style="color:#ef4444">xoá vĩnh viễn</strong> đơn nạp của <strong>${userName}</strong>?<br/><br/>Hành động này <strong>không thể hoàn tác</strong>.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Xoá vĩnh viễn",
        cancelButtonText: "Huỷ",
        background: "#16161a",
        color: "#fff",
      });

      if (!confirm.isConfirmed) return;
    }

    setLoadingId(dep.id);
    try {
      const res = await fetch("/api/admin/deposits/delete", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ depositId: dep.id }),
      });
      const data = await res.json();

      if (res.ok) {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "success",
            title: "Đã xoá!",
            text: data.message || "Xoá đơn nạp tiền thành công",
            timer: 2000,
            showConfirmButton: false,
            background: "#16161a",
            color: "#fff",
          });
        }
        setDeposits((prev) => prev.filter((d) => d.id !== dep.id));
        setTotalCount((prev) => prev - 1);
      } else {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: data.error || "Xoá đơn nạp thất bại", background: "#16161a", color: "#fff" });
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
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 28, gap: 16, flexWrap: "wrap" }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 700 }}>Lịch sử Nạp tiền</h1>
          <p style={{ color: "#94a3b8", fontSize: 13, marginTop: 2 }}>
            Toàn bộ giao dịch nạp tiền vào hệ thống
          </p>
        </div>

        <div style={{ display: "flex", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
          {/* Status Filter */}
          <div style={{ position: "relative" }}>
            <select
              value={statusFilter}
              onChange={(e) => handleFilterChange(e.target.value)}
              style={{
                height: 38,
                padding: "0 12px",
                borderRadius: 8,
                background: "#11131f",
                border: "1px solid rgba(255,255,255,0.08)",
                color: "#e2e8f0",
                fontSize: 13,
                outline: "none",
                cursor: "pointer",
              }}
            >
              <option value="ALL">Tất cả trạng thái</option>
              <option value="SUCCESS">Thành công</option>
              <option value="PENDING">Chờ xử lý</option>
              <option value="FAILED">Thất bại</option>
              <option value="CANCELLED">Đã hủy</option>
            </select>
          </div>

          {/* Search Input */}
          <div style={{ position: "relative", maxWidth: 260, width: "100%" }}>
            <Search size={14} style={{ position: "absolute", left: 12, top: "50%", transform: "translateY(-50%)", color: "#64748b" }} />
            <input
              className="input"
              style={{ paddingLeft: 36, height: 38, fontSize: 13, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
              placeholder="Tìm theo email, Seri, PIN..."
              value={search}
              onChange={(e) => handleSearchChange(e.target.value)}
            />
          </div>
        </div>
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
                    <div style={{ display: "inline-flex", alignItems: "center", gap: 6 }}>
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
                      {(dep.status === "CANCELLED" || dep.status === "PENDING") && (
                        <button
                          onClick={() => handleDelete(dep)}
                          disabled={isLoading}
                          style={{
                            display: "inline-flex",
                            alignItems: "center",
                            gap: 6,
                            padding: "6px 10px",
                            background: "rgba(239,68,68,0.12)",
                            border: "1px solid rgba(239,68,68,0.3)",
                            color: "#ef4444",
                            borderRadius: 6,
                            cursor: isLoading ? "not-allowed" : "pointer",
                            fontSize: 12,
                            fontWeight: 600,
                            transition: "all 0.15s",
                          }}
                          title="Xoá vĩnh viễn đơn nạp này"
                        >
                          {isLoading ? (
                            <span className="spinner" style={{ width: 12, height: 12 }} />
                          ) : (
                            <Trash2 size={13} />
                          )}
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>

        {deposits.length === 0 && (
          <div style={{ textAlign: "center", padding: "40px 20px", color: "#94a3b8" }}>
            Không tìm thấy đơn nạp tiền nào
          </div>
        )}

        {/* Pagination Controls */}
        {deposits.length > 0 && (
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "16px 20px", borderTop: "1px solid rgba(255,255,255,0.06)", flexWrap: "wrap", gap: 12 }}>
            <div style={{ fontSize: 13, color: "#64748b" }}>
              Hiển thị <strong style={{ color: "#e2e8f0" }}>{(currentPage - 1) * pageSize + 1}</strong> - <strong style={{ color: "#e2e8f0" }}>{Math.min(currentPage * pageSize, totalCount)}</strong> trong <strong style={{ color: "#e2e8f0" }}>{totalCount}</strong> đơn nạp
            </div>

            {totalPages > 1 && (
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <button
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage(1)}
                  style={{ padding: "6px 12px", background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.08)", color: currentPage === 1 ? "#475569" : "#e2e8f0", borderRadius: 6, cursor: currentPage === 1 ? "not-allowed" : "pointer", fontSize: 12, fontWeight: 500 }}
                >
                  « Đầu
                </button>
                <button
                  disabled={currentPage === 1}
                  onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                  style={{ padding: "6px 12px", background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.08)", color: currentPage === 1 ? "#475569" : "#e2e8f0", borderRadius: 6, cursor: currentPage === 1 ? "not-allowed" : "pointer", fontSize: 12, fontWeight: 500 }}
                >
                  ‹ Trước
                </button>

                {Array.from({ length: totalPages }, (_, i) => i + 1)
                  .filter((p) => p === 1 || p === totalPages || Math.abs(p - currentPage) <= 2)
                  .map((p, idx, arr) => {
                    const prev = arr[idx - 1];
                    const showEllipsis = prev && p - prev > 1;
                    return (
                      <span key={p} style={{ display: "inline-flex", alignItems: "center" }}>
                        {showEllipsis && <span style={{ color: "#64748b", padding: "0 4px", fontSize: 12 }}>...</span>}
                        <button
                          onClick={() => setCurrentPage(p)}
                          style={{
                            padding: "6px 12px",
                            borderRadius: 6,
                            fontSize: 12,
                            fontWeight: p === currentPage ? 700 : 500,
                            background: p === currentPage ? "#4f46e5" : "rgba(255,255,255,0.05)",
                            color: p === currentPage ? "#ffffff" : "#e2e8f0",
                            border: p === currentPage ? "1px solid #6366f1" : "1px solid rgba(255,255,255,0.08)",
                            cursor: "pointer",
                          }}
                        >
                          {p}
                        </button>
                      </span>
                    );
                  })}

                <button
                  disabled={currentPage === totalPages}
                  onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                  style={{ padding: "6px 12px", background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.08)", color: currentPage === totalPages ? "#475569" : "#e2e8f0", borderRadius: 6, cursor: currentPage === totalPages ? "not-allowed" : "pointer", fontSize: 12, fontWeight: 500 }}
                >
                  Sau ›
                </button>
                <button
                  disabled={currentPage === totalPages}
                  onClick={() => setCurrentPage(totalPages)}
                  style={{ padding: "6px 12px", background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.08)", color: currentPage === totalPages ? "#475569" : "#e2e8f0", borderRadius: 6, cursor: currentPage === totalPages ? "not-allowed" : "pointer", fontSize: 12, fontWeight: 500 }}
                >
                  Cuối »
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
