"use client";

import { useState } from "react";
import { Search, Shield, User as UserIcon, Wallet, Edit, Plus, Minus, KeyRound, Check, X } from "lucide-react";

interface UserItem {
  id: string;
  name: string | null;
  email: string;
  role: "ADMIN" | "USER";
  balance: number;
  createdAt: string;
  _count?: { purchases?: number; deposits?: number; links?: number };
}

interface Props {
  initialUsers: UserItem[];
  total: number;
}

export default function UsersClient({ initialUsers, total }: Props) {
  const [users, setUsers] = useState<UserItem[]>(initialUsers);
  const [search, setSearch] = useState("");
  const [selectedUser, setSelectedUser] = useState<UserItem | null>(null);
  const [adjustAmount, setAdjustAmount] = useState("");
  const [adjustType, setAdjustType] = useState<"ADD" | "SUBTRACT">("ADD");
  const [newRole, setNewRole] = useState<"ADMIN" | "USER">("USER");
  const [updating, setUpdating] = useState(false);
  const [message, setMessage] = useState("");

  const filteredUsers = users.filter(
    (u) =>
      (u.email?.toLowerCase() ?? "").includes(search.toLowerCase()) ||
      (u.name?.toLowerCase() ?? "").includes(search.toLowerCase())
  );

  const openAdjustModal = (user: UserItem) => {
    setSelectedUser(user);
    setAdjustAmount("");
    setAdjustType("ADD");
    setNewRole(user.role);
    setMessage("");
  };

  const handleSaveUser = async () => {
    if (!selectedUser) return;
    setUpdating(true);
    setMessage("");

    try {
      let finalBalance = selectedUser.balance;
      if (adjustAmount.trim()) {
        const val = parseFloat(adjustAmount);
        if (isNaN(val) || val <= 0) {
          setMessage("Số tiền điều chỉnh không hợp lệ");
          setUpdating(false);
          return;
        }
        finalBalance = adjustType === "ADD" ? selectedUser.balance + val : Math.max(0, selectedUser.balance - val);
      }

      const res = await fetch("/api/admin/users", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: selectedUser.id,
          balance: finalBalance,
          role: newRole,
        }),
      });

      if (!res.ok) {
        setMessage("Lỗi cập nhật người dùng");
        return;
      }

      const data = await res.json();
      setUsers(users.map((u) => (u.id === selectedUser.id ? { ...u, balance: data.user.balance, role: data.user.role } : u)));
      setSelectedUser(null);
    } finally {
      setUpdating(false);
    }
  };

  return (
    <div className="animate-fade-in">
      {/* Header */}
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24, gap: 16, flexWrap: "wrap" }}>
        <div>
          <h1 style={{ fontSize: 24, fontWeight: 700, color: "#e2e8f0" }}>Quản lý Thành viên</h1>
          <p style={{ color: "#64748b", fontSize: 13, marginTop: 4 }}>Quản lý thông tin, phân quyền và số dư của tài khoản người dùng.</p>
        </div>

        <div style={{ position: "relative", maxWidth: 280, width: "100%" }}>
          <Search size={14} style={{ position: "absolute", left: 12, top: "50%", transform: "translateY(-50%)", color: "#64748b" }} />
          <input
            className="input"
            style={{ paddingLeft: 36, height: 38, fontSize: 13, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
            placeholder="Tìm theo email / tên..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      {/* Users Table */}
      <div className="glass-card admin-table-container" style={{ padding: 0, borderRadius: 16 }}>
        <table style={{ width: "100%", borderCollapse: "collapse" }}>
          <thead>
            <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.06)", background: "rgba(255,255,255,0.02)" }}>
              {["Thành viên", "Role", "Số dư", "Ngày đăng ký", "Thao tác"].map((h) => (
                <th key={h} style={{ padding: "12px 16px", textAlign: "left", fontSize: 11, color: "#64748b", fontWeight: 600, textTransform: "uppercase" }}>
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {filteredUsers.map((user) => (
              <tr key={user.id} className="table-row">
                <td style={{ padding: "13px 16px" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <div style={{ width: 34, height: 34, borderRadius: "50%", background: "rgba(99,102,241,0.15)", border: "1px solid rgba(99,102,241,0.2)", display: "flex", alignItems: "center", justifyContent: "center", color: "#818cf8" }}>
                      <UserIcon size={16} />
                    </div>
                    <div>
                      <p style={{ fontWeight: 600, fontSize: 14, color: "#e2e8f0" }}>{user.name || "N/A"}</p>
                      <p style={{ fontSize: 12, color: "#64748b" }}>{user.email}</p>
                    </div>
                  </div>
                </td>
                <td style={{ padding: "13px 16px" }}>
                  <span style={{
                    padding: "3px 10px",
                    borderRadius: 12,
                    fontSize: 11,
                    fontWeight: 700,
                    background: user.role === "ADMIN" ? "rgba(99,102,241,0.15)" : "rgba(255,255,255,0.06)",
                    color: user.role === "ADMIN" ? "#818cf8" : "#94a3b8",
                    border: `1px solid ${user.role === "ADMIN" ? "rgba(99,102,241,0.3)" : "rgba(255,255,255,0.08)"}`,
                  }}>
                    {user.role}
                  </span>
                </td>
                <td style={{ padding: "13px 16px", fontSize: 14, fontWeight: 700, color: "#10b981" }}>
                  {user.balance.toLocaleString("vi-VN")} đ
                </td>
                <td style={{ padding: "13px 16px", fontSize: 12, color: "#64748b" }}>
                  {new Date(user.createdAt).toLocaleDateString("vi-VN")}
                </td>
                <td style={{ padding: "13px 16px" }}>
                  <button
                    onClick={() => openAdjustModal(user)}
                    style={{ display: "inline-flex", alignItems: "center", gap: 6, padding: "6px 12px", background: "rgba(255,255,255,0.06)", border: "1px solid rgba(255,255,255,0.1)", color: "#e2e8f0", borderRadius: 6, cursor: "pointer", fontSize: 12, fontWeight: 500 }}
                  >
                    <Edit size={13} /> Sửa / Nạp tiền
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {filteredUsers.length === 0 && (
          <div style={{ textAlign: "center", padding: "40px 20px", color: "#475569" }}>
            Không tìm thấy thành viên nào
          </div>
        )}
      </div>

      {/* Edit / Balance Modal */}
      {selectedUser && (
        <div style={{ position: "fixed", inset: 0, background: "rgba(0,0,0,0.7)", backdropFilter: "blur(4px)", display: "flex", alignItems: "center", justifyContent: "center", zIndex: 100, padding: 16 }}>
          <div className="glass-card admin-modal-content" style={{ width: 440, padding: 24, position: "relative" }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700 }}>Chỉnh sửa thành viên</h3>
              <button onClick={() => setSelectedUser(null)} style={{ background: "none", border: "none", color: "#64748b", cursor: "pointer" }}>
                <X size={18} />
              </button>
            </div>

            <p style={{ fontSize: 13, color: "#94a3b8", marginBottom: 16 }}>
              Email: <strong>{selectedUser.email}</strong>
            </p>

            {/* Current Balance */}
            <div style={{ background: "#11131f", padding: "12px 16px", borderRadius: 8, marginBottom: 16, display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <span style={{ fontSize: 13, color: "#64748b" }}>Số dư hiện tại</span>
              <span style={{ fontSize: 16, fontWeight: 700, color: "#10b981" }}>{selectedUser.balance.toLocaleString("vi-VN")} đ</span>
            </div>

            {/* Adjust Balance Field */}
            <div style={{ marginBottom: 16 }}>
              <label style={{ display: "block", fontSize: 12, color: "#94a3b8", marginBottom: 6, fontWeight: 500 }}>Điều chỉnh số dư</label>
              <div style={{ display: "flex", gap: 8, marginBottom: 8 }}>
                <button
                  onClick={() => setAdjustType("ADD")}
                  style={{
                    flex: 1, padding: "8px", borderRadius: 6, border: "1px solid", fontSize: 13, fontWeight: 600, cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center", gap: 4,
                    background: adjustType === "ADD" ? "rgba(16,185,129,0.15)" : "#11131f",
                    borderColor: adjustType === "ADD" ? "rgba(16,185,129,0.3)" : "rgba(255,255,255,0.08)",
                    color: adjustType === "ADD" ? "#10b981" : "#64748b",
                  }}
                >
                  <Plus size={14} /> Cộng tiền
                </button>
                <button
                  onClick={() => setAdjustType("SUBTRACT")}
                  style={{
                    flex: 1, padding: "8px", borderRadius: 6, border: "1px solid", fontSize: 13, fontWeight: 600, cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center", gap: 4,
                    background: adjustType === "SUBTRACT" ? "rgba(239,68,68,0.15)" : "#11131f",
                    borderColor: adjustType === "SUBTRACT" ? "rgba(239,68,68,0.3)" : "rgba(255,255,255,0.08)",
                    color: adjustType === "SUBTRACT" ? "#ef4444" : "#64748b",
                  }}
                >
                  <Minus size={14} /> Trừ tiền
                </button>
              </div>
              <input
                className="input"
                type="number"
                placeholder="Nhập số tiền (VNĐ)..."
                value={adjustAmount}
                onChange={(e) => setAdjustAmount(e.target.value)}
                style={{ height: 40, fontSize: 14, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
              />
            </div>

            {/* Role Select */}
            <div style={{ marginBottom: 20 }}>
              <label style={{ display: "block", fontSize: 12, color: "#94a3b8", marginBottom: 6, fontWeight: 500 }}>Phân quyền Role</label>
              <select
                value={newRole}
                onChange={(e) => setNewRole(e.target.value as "ADMIN" | "USER")}
                className="input"
                style={{ height: 40, fontSize: 14, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", color: "#e2e8f0" }}
              >
                <option value="USER">USER (Thành viên thường)</option>
                <option value="ADMIN">ADMIN (Quản trị viên)</option>
              </select>
            </div>

            {message && (
              <div style={{ color: "#ef4444", fontSize: 13, marginBottom: 12 }}>{message}</div>
            )}

            <div style={{ display: "flex", gap: 10, justifyContent: "flex-end" }}>
              <button
                onClick={() => setSelectedUser(null)}
                style={{ padding: "8px 16px", background: "rgba(255,255,255,0.06)", border: "1px solid rgba(255,255,255,0.1)", color: "#94a3b8", borderRadius: 6, cursor: "pointer", fontSize: 13 }}
              >
                Hủy
              </button>
              <button
                onClick={handleSaveUser}
                disabled={updating}
                style={{ padding: "8px 20px", background: "#4f46e5", color: "white", border: "none", borderRadius: 6, cursor: "pointer", fontSize: 13, fontWeight: 600, display: "flex", alignItems: "center", gap: 6 }}
              >
                {updating ? <span className="spinner" style={{ width: 14, height: 14 }} /> : <><Check size={14} /> Lưu lại</>}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
