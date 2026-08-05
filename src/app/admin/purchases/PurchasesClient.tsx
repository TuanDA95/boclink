"use client";

import { useState } from "react";
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
}

export default function PurchasesClient({ initialPurchases }: Props) {
  const [purchases, setPurchases] = useState<PurchaseItem[]>(initialPurchases);
  const [searchTerm, setSearchTerm] = useState("");
  const [loading, setLoading] = useState(false);

  // Quick stats calculation
  const totalPurchases = purchases.length;
  const totalRevenue = purchases.reduce((acc, curr) => acc + (curr.amount || 0), 0);
  const purchases24h = purchases.filter((p) => {
    const timeDiff = Date.now() - new Date(p.createdAt).getTime();
    return timeDiff <= 24 * 60 * 60 * 1000;
  }).length;

  const handleSearch = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/purchases?q=${encodeURIComponent(searchTerm)}`);
      const data = await res.json();
      if (res.ok && data.purchases) {
        setPurchases(data.purchases);
      }
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  };

  const filtered = purchases.filter((p) => {
    if (!searchTerm.trim()) return true;
    const term = searchTerm.toLowerCase();
    return (
      (p.user.name && p.user.name.toLowerCase().includes(term)) ||
      p.user.email.toLowerCase().includes(term) ||
      p.link.slug.toLowerCase().includes(term) ||
      (p.link.title && p.link.title.toLowerCase().includes(term))
    );
  });

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
            <div className="stat-num">{totalPurchases}</div>
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
            Danh sách giao dịch ({filtered.length})
          </div>
          <form className="search-wrap" onSubmit={handleSearch}>
            <Search size={16} color="#64748b" />
            <input
              type="text"
              placeholder="Tìm theo email, tên, mã link..."
              className="search-input"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </form>
        </div>

        {filtered.length === 0 ? (
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
                {filtered.map((item, idx) => {
                  const dateStr = new Date(item.createdAt).toLocaleString("vi-VN", {
                    hour12: false,
                  });
                  return (
                    <tr key={item.id}>
                      <td>{idx + 1}</td>
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
      </div>
    </>
  );
}
