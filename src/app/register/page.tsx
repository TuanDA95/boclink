"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { signIn } from "next-auth/react";
import Link from "next/link";

export default function RegisterPage() {
  const router = useRouter();
  const [form, setForm] = useState({ account: "", email: "", password: "", confirmPassword: "" });
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    if (form.password !== form.confirmPassword) {
      setError("Mật khẩu xác nhận không khớp");
      return;
    }
    setLoading(true);
    try {
      const res = await fetch("/api/auth/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Đăng ký thất bại");
        return;
      }

      // Tự động đăng nhập bằng Tài khoản người dùng vừa tạo
      await signIn("credentials", { account: form.account, password: form.password, redirect: false });
      router.push("/dashboard");
      router.refresh();
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;700&display=swap');

        body {
          margin: 0 !important;
          padding: 0 !important;
          font-family: 'Lexend', sans-serif !important;
          background: linear-gradient(135deg, #ffffff 0%, #fffdf0 50%, #fefce8 100%) !important;
          min-height: 100dvh;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
        }

        .bg-container {
          position: fixed;
          top: 0; left: 0; width: 100%; height: 100%;
          z-index: 1;
          background: radial-gradient(circle at top right, rgba(254, 240, 138, 0.25), transparent 70%), radial-gradient(circle at bottom left, rgba(253, 230, 138, 0.15), transparent 70%);
        }

        .bg-overlay {
          display: none;
        }

        .auth-card {
          position: relative;
          z-index: 10;
          background: #ffffff;
          border: 1px solid #fef08a;
          border-radius: 28px;
          padding: 28px 26px;
          width: 88%;
          max-width: 380px;
          box-shadow: 0 16px 40px rgba(234, 179, 8, 0.08), 0 4px 15px rgba(0, 0, 0, 0.03);
          margin: 20px auto;
        }

        .auth-title {
          font-size: 1.4rem;
          font-weight: 700;
          letter-spacing: 2px;
          text-align: center;
          color: #d97706;
          text-transform: uppercase;
          margin-bottom: 20px !important;
        }

        .auth-label {
          display: block;
          font-size: 0.8rem;
          font-weight: 700;
          color: #475569;
          margin-bottom: 5px;
          margin-left: 2px;
        }

        .auth-input {
          width: 100%;
          background: #fffdf7;
          border: 1.5px solid #fef08a;
          padding: 11px 14px;
          font-size: 16px !important;
          border-radius: 14px;
          outline: none;
          font-family: 'Lexend', sans-serif;
          box-sizing: border-box;
          color: #1e293b;
          transition: border-color 0.2s, box-shadow 0.2s;
          -webkit-appearance: none;
        }
        .auth-input:focus {
          border-color: #f59e0b;
          box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        }

        .auth-input-wrap { position: relative; }
        .toggle-pw {
          position: absolute;
          right: 14px; top: 50%;
          transform: translateY(-50%);
          background: none; border: none;
          cursor: pointer; color: #94a3b8;
          font-size: 1rem; padding: 0; line-height: 1;
        }

        .btn-gaming {
          background: linear-gradient(135deg, #fbbf24, #d97706);
          border: none;
          font-weight: 700;
          padding: 13px;
          width: 100%;
          color: white;
          border-radius: 14px;
          margin-top: 6px;
          cursor: pointer;
          font-size: 0.95rem;
          letter-spacing: 1px;
          text-transform: uppercase;
          font-family: 'Lexend', sans-serif;
          box-shadow: 0 4px 14px rgba(217, 119, 6, 0.25);
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }
        .btn-gaming:hover {
          background: linear-gradient(135deg, #f59e0b, #b45309);
          box-shadow: 0 6px 18px rgba(217, 119, 6, 0.35);
        }
        .btn-gaming:active { transform: scale(0.97); opacity: 0.9; }
        .btn-gaming:disabled { opacity: 0.7; cursor: not-allowed; }

        .auth-error {
          background: rgba(239,68,68,0.1);
          border: 1px solid rgba(239,68,68,0.3);
          color: #dc2626;
          padding: 10px 14px;
          border-radius: 10px;
          font-size: 0.85rem;
          text-align: center;
          margin-bottom: 8px;
        }

        .auth-footer {
          text-align: center;
          margin-top: 16px;
          font-size: 0.85rem;
          color: #64748b;
        }
        .auth-footer a { color: #d97706; font-weight: 700; text-decoration: none; }

        .form-mb { margin-bottom: 12px; }

        .spinner-sm {
          width: 18px; height: 18px;
          border: 2px solid rgba(255,255,255,0.3);
          border-top-color: white;
          border-radius: 50%;
          display: inline-block;
          animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
      `}</style>

      <div className="bg-container" />
      <div className="bg-overlay" />

      <div className="auth-card">
        <h3 className="auth-title">Đăng Ký</h3>

        <form onSubmit={handleSubmit} autoComplete="off">
          {/* Tài khoản người dùng */}
          <div className="form-mb">
            <label className="auth-label">Tài khoản đăng nhập</label>
            <input
              id="account"
              className="auth-input"
              type="text"
              placeholder="Nhập tên tài khoản (Ví dụ: user123)"
              value={form.account}
              onChange={(e) => setForm({ ...form, account: e.target.value })}
              required
              minLength={3}
              maxLength={30}
            />
          </div>

          {/* Email (Tùy chọn) */}
          <div className="form-mb">
            <label className="auth-label">Email (Không bắt buộc)</label>
            <input
              id="reg-email"
              className="auth-input"
              type="email"
              placeholder="Tùy chọn (Ví dụ: name@example.com)"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </div>

          {/* Mật khẩu */}
          <div className="form-mb">
            <label className="auth-label">Mật khẩu</label>
            <div className="auth-input-wrap">
              <input
                id="reg-password"
                className="auth-input"
                type={showPw ? "text" : "password"}
                placeholder="Tối thiểu 6 ký tự"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
                style={{ paddingRight: 44 }}
                minLength={6}
                required
              />
              <button type="button" className="toggle-pw" onClick={() => setShowPw(!showPw)} tabIndex={-1}>
                {showPw ? "🙈" : "👁️"}
              </button>
            </div>
          </div>

          {/* Xác nhận mật khẩu */}
          <div className="form-mb">
            <label className="auth-label">Xác nhận mật khẩu</label>
            <input
              id="confirm-password"
              className="auth-input"
              type="password"
              placeholder="Nhập lại mật khẩu"
              value={form.confirmPassword}
              onChange={(e) => setForm({ ...form, confirmPassword: e.target.value })}
              required
            />
          </div>

          {error && <div className="auth-error">{error}</div>}

          <button type="submit" className="btn-gaming" disabled={loading} id="register-btn">
            {loading ? <span className="spinner-sm" /> : null}
            {loading ? "Đang xử lý..." : "TẠO TÀI KHOẢN"}
          </button>
        </form>

        <div className="auth-footer">
          Đã có tài khoản?{" "}
          <Link href="/login">Đăng nhập ngay</Link>
        </div>
      </div>
    </>
  );
}
