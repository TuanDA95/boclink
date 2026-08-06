"use client";

import { useState, useEffect, useCallback } from "react";
import { Search, ShoppingBag, DollarSign, Calendar, ExternalLink, User } from "lucide-react";

export interface PurchaseItem {
  id: string;
  amount: number;
  createdAt: string;
  user: {
    id: string;
    name: string | null;
    email: string;
  };
  link: {
    id: string;
    slug: string;
    title: string | null;
    originalUrl: string;
  };
}

interface Props {
  initialPurchases: PurchaseItem[];
  total: number;
  totalRevenue: number;
  purchases24h: number;
}

export default function PurchasesClient({ initialPurchases, total, totalRevenue: initRevenue, purchases24h: init24h }: Props) {
  const [purchases, setPurchases] = useState<PurchaseItem[]>(initialPurchases);
  const [totalCount, setTotalCount] = useState<number>(total);
  const [totalRevenue, setTotalRevenue] = useState<number>(initRevenue);
  const [purchases24h, setPurchases24h] = useState<number>(init24h);
  const [searchTerm, setSearchTerm] = useState("");
  const [loading, setLoading] = useState(false);

  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 15;

  const fetchPurchases = useCallback(async (page: number, q: string) => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/purchases?page=${page}&limit=${pageSize}&q=${encodeURIComponent(q)}`);
      const data = await res.json();
      if (res.ok && data.purchases) {
        setPurchases(data.purchases);
        setTotalCount(data.total);
        if (data.totalRevenue !== undefined) setTotalRevenue(data.totalRevenue);
        if (data.purchases24h !== undefined) setPurchases24h(data.purchases24h);
      }
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  }, []);

  const [isInitialMount, setIsInitialMount] = useState(true);

  useEffect(() => {
    if (isInitialMount) {
      setIsInitialMount(false);
      return;
    }
    const timer = setTimeout(() => {
      fetchPurchases(currentPage, searchTerm);
    }, 300);
    return () => clearTimeout(timer);
  }, [currentPage, searchTerm, fetchPurchases]);

  const handleSearchChange = (val: string) => {
    setSearchTerm(val);
    setCurrentPage(1);
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    fetchPurchases(1, searchTerm);
  };

  const totalPages = Math.ceil(totalCount / pageSize) || 1;

  return (
    <>
      <style>{`
        .sc-title { font-size: 26px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px; }
        .sc-sub { color: #64748b; font-size: 0.88rem; margin-bottom: 28px; }

        .stat-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 16px;
          margin-bottom: 24px;
        }
        .stat-card {
          background: rgba(255,255,255,0.03);
          border: 1px solid rgba(255,255,255,0.08);
          border-radius: 16px;
          padding: 20px;
          display: flex;
          align-items: center;
          gap: 16px;
        }
        .stat-icon {
          width: 44px; height: 44px;
          border-radius: 12px;
          display: flex; align-items: center; justify-content: center;
        }
        .stat-num { font-size: 1.4rem; font-weight: 800; color: #e2e8f0; }
        .stat-label { font-size: 0.8rem; color: #64748b; }

        .table-card {
          background: rgba(255,255,255,0.03);
          border: 1px solid rgba(255,255,255,0.08);
          border-radius: 16px;
          overflow: hidden;
        }
        .table-hd {
          padding: 18px 24px;
          border-bottom: 1px solid rgba(255,255,255,0.06);
          display: flex;
          align-items: center;
          justify-content: space-between;
          flex-wrap: wrap;
          gap: 16px;
        }
        .search-wrap {
          display: flex;
          align-items: center;
          gap: 8px;
          background: rgba(255,255,255,0.05);
          border: 1px solid rgba(255,255,255,0.1);
          border-radius: 10px;
          padding: 8px 14px;
          max-width: 300px;
          width: 100%;
        }
        .search-input {
          background: transparent;
          border: none;
          outline: none;
          color: #e2e8f0;
          font-size: 0.88rem;
          width: 100%;
        }

        .adm-table {
          width: 100%;
          border-collapse: collapse;
          text-align: left;
        }
        .adm-table th {
          background: rgba(255,255,255,0.02);
          padding: 14px 20px;
          color: #94a3b8;
          font-size: 0.8rem;
          font-weight: 600;
          text-transform: uppercase;
          border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .adm-table td {
          padding: 16px 20px;
          border-bottom: 1px solid rgba(255,255,255,0.04);
          font-size: 0.88rem;
          color: #cbd5e1;
        }
        .adm-table tr:hover td {
          background: rgba(255,255,255,0.02);
        }

        .user-cell {
          display: flex;
          flex-direction: column;
        }
        .user-name { font-weight: 600; color: #f8fafc; }
        .user-email { font-size: 0.78rem; color: #64748b; }

        .slug-text {
          color: #e2e8f0;
          font-weight: 700;
          font-family: monospace;
          font-size: 0.92rem;
        }

        .price-badge {
          font-weight: 700;
          color: #10b981;
        }

        .empty-box {
          padding: 48px;
          text-align: center;
          color: #64748b;
          font-size: 0.95rem;
        }
      `}</style>

      <h1 className="sc-title">Lịch sử mua link</h1>
      <p className="sc-sub">Theo dõi toàn bộ các giao dịch mua link của người dùng trên hệ thống</p>

      {/* Stats */}
      <div className="stat-grid">
        <div className="stat-card">
          <div className="stat-icon" style={{ background: "rgba(99, 102, 241, 0.15)", color: "#6366f1" }}>
            <ShoppingBag size={22} />
          </div>
          <div>
            <div className="stat-num">{totalCount}</div>
            <div className="stat-label">Tổng lượt mua</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon" style={{ background: "rgba(16, 185, 129, 0.15)", color: "#10b981" }}>
            <DollarSign size={22} />
          </div>
          <div>
            <div className="stat-num">{totalRevenue.toLocaleString("vi-VN")} đ</div>
            <div className="stat-label">Tổng doanh thu mua link</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon" style={{ background: "rgba(245, 158, 11, 0.15)", color: "#f59e0b" }}>
            <Calendar size={22} />
          </div>
          <div>
            <div className="stat-num">{purchases24h}</div>
            <div className="stat-label">Lượt mua 24h qua</div>
          </div>
        </div>
      </div>

      {/* Table Card */}
      <div className="table-card">
        <div className="table-hd">
          <div style={{ fontWeight: 600, color: "#e2e8f0", fontSize: "1rem" }}>
            Danh sách giao dịch ({totalCount})
          </div>
          <form className="search-wrap" onSubmit={handleSearch}>
            <Search size={16} color="#64748b" />
            <input
              type="text"
              placeholder="Tìm theo email, tên, mã link..."
              className="search-input"
              value={searchTerm}
              onChange={(e) => handleSearchChange(e.target.value)}
            />
          </form>
        </div>

        {purchases.length === 0 ? (
          <div className="empty-box">
            🛒 Chưa có giao dịch mua link nào.
          </div>
        ) : (
          <div style={{ overflowX: "auto" }}>
            <table className="adm-table">
              <thead>
                <tr>
                  <th style={{ width: 50 }}>#</th>
                  <th>Người mua</th>
                  <th>Mã code / Title</th>
                  <th>Link gốc</th>
                  <th>Giá tiền</th>
                  <th>Thời gian</th>
                </tr>
              </thead>
              <tbody>
                {purchases.map((item, idx) => {
                  const indexNum = (currentPage - 1) * pageSize + idx + 1;
                  const dateStr = new Date(item.createdAt).toLocaleString("vi-VN", {
                    hour12: false,
                  });
                  return (
                    <tr key={item.id}>
                      <td>{indexNum}</td>
                      <td>
                        <div className="user-cell">
                          <span className="user-name">{item.user.name || "Khách hàng"}</span>
                          <span className="user-email">{item.user.email}</span>
                        </div>
                      </td>
                      <td>
                        <div>
                          <span className="slug-text">{item.link.slug}</span>
                          {item.link.title && (
                            <div style={{ fontSize: "0.78rem", color: "#64748b", marginTop: 2 }}>
                              {item.link.title}
                            </div>
                          )}
                        </div>
                      </td>
                      <td>
                        <a
                          href={item.link.originalUrl}
                          target="_blank"
                          rel="noreferrer"
                          style={{ color: "#38bdf8", textDecoration: "none", display: "inline-flex", alignItems: "center", gap: 4 }}
                        >
                          Bấm vào đây để mở link <ExternalLink size={13} />
                        </a>
                      </td>
                      <td>
                        <span className="price-badge">
                          {(item.amount || 0).toLocaleString("vi-VN")} đ
                        </span>
                      </td>
                      <td>
                        <span style={{ fontSize: "0.82rem", color: "#94a3b8" }}>{dateStr}</span>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination Controls */}
        {purchases.length > 0 && (
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "16px 24px", borderTop: "1px solid rgba(255,255,255,0.06)", flexWrap: "wrap", gap: 12 }}>
            <div style={{ fontSize: "0.83rem", color: "#64748b" }}>
              Hiển thị <strong style={{ color: "#e2e8f0" }}>{(currentPage - 1) * pageSize + 1}</strong> - <strong style={{ color: "#e2e8f0" }}>{Math.min(currentPage * pageSize, totalCount)}</strong> trong <strong style={{ color: "#e2e8f0" }}>{totalCount}</strong> giao dịch
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
    </>
  );
}
