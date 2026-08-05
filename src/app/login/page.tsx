"use client";

import { useState, Suspense } from "react";
import { signIn } from "next-auth/react";
import { useRouter, useSearchParams } from "next/navigation";
import Link from "next/link";

function LoginForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const callbackUrl = searchParams.get("callbackUrl") || "/dashboard";

  const [form, setForm] = useState({ account: "", password: "" });
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const result = await signIn("credentials", {
        account: form.account,
        password: form.password,
        redirect: false,
      });
      if (result?.error) {
        setError("Tài khoản hoặc mật khẩu không đúng");
        return;
      }
      router.push(callbackUrl);
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
          overflow: hidden;
          height: 100dvh;
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
          padding: 32px 28px;
          width: 88%;
          max-width: 360px;
          box-shadow: 0 15px 40px rgba(0,0,0,0.6), 0 0 20px rgba(26,213,250,0.15);
        }

        .auth-title {
          font-size: 1.5rem;
          font-weight: 700;
          letter-spacing: 2px;
          text-align: center;
          color: #1ad5fa;
          text-transform: uppercase;
          margin-bottom: 24px !important;
        }

        .auth-label {
          display: block;
          font-size: 0.8rem;
          font-weight: 700;
          color: #333;
          margin-bottom: 6px;
          margin-left: 2px;
        }

        .auth-input {
          width: 100%;
          background: #fff;
          border: 1.5px solid #eee;
          padding: 12px 15px;
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
          box-shadow: 0 0 0 4px rgba(26,213,250,0.12);
        }

        .auth-input-wrap {
          position: relative;
        }
        .toggle-pw {
          position: absolute;
          right: 14px;
          top: 50%;
          transform: translateY(-50%);
          background: none;
          border: none;
          cursor: pointer;
          color: #999;
          font-size: 1rem;
          padding: 0;
          line-height: 1;
        }

        .btn-gaming {
          background: linear-gradient(135deg, #1ad5fa, #007bff);
          border: none;
          font-weight: 700;
          padding: 14px;
          width: 100%;
          color: white;
          border-radius: 14px;
          margin-top: 8px;
          cursor: pointer;
          font-size: 1rem;
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
          font-size: 0.85rem;
          text-align: center;
          margin-bottom: 8px;
        }

        .auth-footer {
          text-align: center;
          margin-top: 18px;
          font-size: 0.85rem;
          color: #666;
        }
        .auth-footer a {
          color: #ff4757;
          font-weight: 700;
          text-decoration: none;
        }

        .form-mb { margin-bottom: 14px; }

        .spinner-sm {
          width: 18px; height: 18px;
          border: 2px solid rgba(255,255,255,0.3);
          border-top-color: white;
          border-radius: 50%;
          display: inline-block;
          animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media screen and (max-height: 500px) {
          .auth-card { padding: 16px; }
          .auth-title { margin-bottom: 12px !important; font-size: 1.2rem; }
          .form-mb { margin-bottom: 8px; }
        }
      `}</style>

      <div className="bg-container" />
      <div className="bg-overlay" />

      <div className="auth-card">
        <h3 className="auth-title">Đăng Nhập</h3>

        <form onSubmit={handleSubmit} autoComplete="off">
          <div className="form-mb">
            <label className="auth-label">Tên tài khoản</label>
            <input
              id="account"
              className="auth-input"
              type="text"
              placeholder="Nhập username hoặc email"
              autoComplete="username"
              value={form.account}
              onChange={(e) => setForm({ ...form, account: e.target.value })}
              required
            />
          </div>

          <div className="form-mb">
            <label className="auth-label">Mật khẩu</label>
            <div className="auth-input-wrap">
              <input
                id="password"
                className="auth-input"
                type={showPw ? "text" : "password"}
                placeholder="••••••••"
                autoComplete="current-password"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
                style={{ paddingRight: 44 }}
                required
              />
              <button
                type="button"
                className="toggle-pw"
                onClick={() => setShowPw(!showPw)}
                tabIndex={-1}
              >
                {showPw ? "🙈" : "👁️"}
              </button>
            </div>
          </div>

          {error && <div className="auth-error">{error}</div>}

          <button type="submit" className="btn-gaming" disabled={loading} id="login-btn">
            {loading ? <span className="spinner-sm" /> : null}
            {loading ? "Đang xử lý..." : "VÀO HỆ THỐNG"}
          </button>
        </form>

        <div className="auth-footer">
          Chưa có tài khoản?{" "}
          <Link href="/register">Đăng ký ngay</Link>
        </div>
      </div>
    </>
  );
}

export default function LoginPage() {
  return (
    <Suspense>
      <LoginForm />
    </Suspense>
  );
}
