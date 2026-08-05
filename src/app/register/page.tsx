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
          background: #000 !important;
          min-height: 100dvh;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
        }

        .bg-container {
          position: fixed;
          top: 0; left: 0; width: 100%; height: 100%;
          z-index: 1;
          background: url('https://bbmkts.com/uploads/img_698c136c1de743_20541444.png') center/cover no-repeat;
          filter: brightness(0.35);
        }

        .bg-overlay {
          position: fixed;
          top: 0; left: 0; width: 100%; height: 100%;
          background: radial-gradient(circle at center, transparent 0%, black 90%);
          z-index: 2;
        }

        .auth-card {
          position: relative;
          z-index: 10;
          background: rgba(255, 255, 255, 0.97);
          border: 1.5px solid #1ad5fa;
          border-radius: 28px;
          padding: 28px 26px;
          width: 88%;
          max-width: 380px;
          box-shadow: 0 15px 40px rgba(0,0,0,0.6), 0 0 20px rgba(26,213,250,0.15);
          margin: 20px auto;
        }

        .auth-title {
          font-size: 1.4rem;
          font-weight: 700;
          letter-spacing: 2px;
          text-align: center;
          color: #1ad5fa;
          text-transform: uppercase;
          margin-bottom: 20px !important;
        }

        .auth-label {
          display: block;
          font-size: 0.78rem;
          font-weight: 700;
          color: #333;
          margin-bottom: 5px;
          margin-left: 2px;
        }

        .auth-input {
          width: 100%;
          background: #fff;
          border: 1.5px solid #eee;
          padding: 11px 14px;
          font-size: 16px !important;
          border-radius: 14px;
          outline: none;
          font-family: 'Lexend', sans-serif;
          box-sizing: border-box;
          color: #222;
          transition: border-color 0.2s, box-shadow 0.2s;
          -webkit-appearance: none;
        }
        .auth-input:focus {
          border-color: #1ad5fa;
          box-shadow: 0 0 0 3px rgba(26,213,250,0.12);
        }

        .auth-input-wrap { position: relative; }
        .toggle-pw {
          position: absolute;
          right: 14px; top: 50%;
          transform: translateY(-50%);
          background: none; border: none;
          cursor: pointer; color: #999;
          font-size: 1rem; padding: 0; line-height: 1;
        }

        .btn-gaming {
          background: linear-gradient(135deg, #1ad5fa, #007bff);
          border: none;
          font-weight: 700;
          padding: 13px;
          width: 100%;
          color: white;
          border-radius: 14px;
          margin-top: 8px;
          cursor: pointer;
          font-size: 0.95rem;
          letter-spacing: 1px;
          text-transform: uppercase;
          font-family: 'Lexend', sans-serif;
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }
        .btn-gaming:active { transform: scale(0.97); opacity: 0.9; }
        .btn-gaming:disabled { opacity: 0.7; cursor: not-allowed; }

        .auth-error {
          background: rgba(239,68,68,0.1);
          border: 1px solid rgba(239,68,68,0.3);
          color: #dc2626;
          padding: 10px 14px;
          border-radius: 10px;
          font-size: 0.82rem;
          text-align: center;
          margin-bottom: 8px;
        }

        .auth-footer {
          text-align: center;
          margin-top: 16px;
          font-size: 0.82rem;
          color: #666;
        }
        .auth-footer a { color: #1ad5fa; font-weight: 700; text-decoration: none; }

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
