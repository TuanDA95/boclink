"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { KeyRound, Lock, Eye, EyeOff, ArrowLeft, CheckCircle2 } from "lucide-react";

declare const Swal: any;

interface Props {
  username: string;
}

export default function ChangePasswordClient({ username }: Props) {
  const router = useRouter();
  const [form, setForm] = useState({ currentPassword: "", newPassword: "", confirmPassword: "" });
  const [showPw, setShowPw] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!form.currentPassword || !form.newPassword || !form.confirmPassword) {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "warning", title: "Thiếu thông tin", text: "Vui lòng nhập đầy đủ các trường!", background: "#16161a", color: "#fff" });
      }
      return;
    }

    if (form.newPassword.length < 6) {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "warning", title: "Mật khẩu quá ngắn", text: "Mật khẩu mới phải có tối thiểu 6 ký tự!", background: "#16161a", color: "#fff" });
      }
      return;
    }

    if (form.newPassword !== form.confirmPassword) {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "warning", title: "Không trùng khớp", text: "Mật khẩu xác nhận không khớp!", background: "#16161a", color: "#fff" });
      }
      return;
    }

    setLoading(true);
    try {
      const res = await fetch("/api/user/change-password", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });
      const data = await res.json();

      if (res.ok) {
        if (typeof Swal !== "undefined") {
          await Swal.fire({
            icon: "success",
            title: "Thành công",
            text: data.message || "Đã đổi mật khẩu thành công!",
            timer: 2000,
            showConfirmButton: false,
            background: "#16161a",
            color: "#fff",
          });
        }
        router.push("/dashboard");
        router.refresh();
      } else {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: data.error || "Đổi mật khẩu thất bại", background: "#16161a", color: "#fff" });
        }
      }
    } catch {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "error", title: "Lỗi", text: "Lỗi kết nối máy chủ", background: "#16161a", color: "#fff" });
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700;800&display=swap');

        body {
          background: radial-gradient(circle at top right, #2d1b4e, #16161a) !important;
          font-family: 'Lexend', sans-serif !important;
          min-height: 100vh;
          margin: 0;
          color: #e0e0e0;
        }

        .cp-wrap {
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          padding: 24px 16px;
          box-sizing: border-box;
        }

        .cp-card {
          background: rgba(22, 22, 26, 0.95);
          border: 1px solid rgba(99, 102, 241, 0.3);
          border-radius: 24px;
          padding: 40px 32px;
          width: 100%;
          max-width: 460px;
          box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 25px rgba(99,102,241,0.12);
          backdrop-filter: blur(10px);
          box-sizing: border-box;
        }

        .cp-back {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          color: #818cf8;
          text-decoration: none;
          font-size: 0.85rem;
          font-weight: 600;
          margin-bottom: 24px;
          transition: all 0.2s;
        }
        .cp-back:hover { color: #a5b4fc; transform: translateX(-2px); }

        .cp-title-wrap {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 8px;
        }
        .cp-icon-badge {
          width: 42px;
          height: 42px;
          border-radius: 12px;
          background: rgba(99,102,241,0.15);
          border: 1px solid rgba(99,102,241,0.3);
          display: flex;
          align-items: center;
          justify-content: center;
          color: #818cf8;
          flex-shrink: 0;
        }
        .cp-title {
          font-size: 1.4rem;
          font-weight: 800;
          color: #f8fafc;
          margin: 0;
        }
        .cp-sub {
          color: #94a3b8;
          font-size: 0.85rem;
          margin-top: 4px;
          margin-bottom: 28px;
        }

        .field-group {
          margin-bottom: 20px;
        }
        .field-label {
          display: block;
          font-size: 0.82rem;
          font-weight: 600;
          color: #cbd5e1;
          margin-bottom: 8px;
        }

        .input-wrap {
          position: relative;
        }
        .cp-input {
          width: 100%;
          background: rgba(15, 23, 42, 0.6);
          border: 1px solid rgba(255, 255, 255, 0.12);
          border-radius: 12px;
          padding: 12px 42px 12px 14px;
          color: #f8fafc;
          font-size: 0.9rem;
          font-family: inherit;
          outline: none;
          box-sizing: border-box;
          transition: all 0.2s;
        }
        .cp-input:focus {
          border-color: #6366f1;
          box-shadow: 0 0 12px rgba(99,102,241,0.25);
          background: rgba(15, 23, 42, 0.85);
        }
        .cp-input::placeholder { color: #475569; }

        .pw-toggle-btn {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          background: none;
          border: none;
          color: #64748b;
          cursor: pointer;
          padding: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: color 0.15s;
        }
        .pw-toggle-btn:hover { color: #cbd5e1; }

        .btn-submit {
          width: 100%;
          padding: 14px;
          border-radius: 12px;
          border: none;
          font-weight: 800;
          font-size: 0.92rem;
          letter-spacing: 0.5px;
          text-transform: uppercase;
          cursor: pointer;
          background: linear-gradient(135deg, #6366f1, #8b5cf6);
          color: #fff;
          box-shadow: 0 4px 15px rgba(99,102,241,0.35);
          transition: all 0.25s;
          margin-top: 10px;
        }
        .btn-submit:hover {
          filter: brightness(1.1);
          box-shadow: 0 6px 20px rgba(99,102,241,0.5);
          transform: translateY(-1px);
        }
        .btn-submit:disabled {
          opacity: 0.6;
          cursor: not-allowed;
          filter: none;
          transform: none;
        }

        .spin-xs {
          width: 14px; height: 14px;
          border: 2px solid rgba(255,255,255,0.3);
          border-top-color: #fff;
          border-radius: 50%;
          animation: sp 0.7s linear infinite;
          display: inline-block;
          margin-right: 6px;
        }
        @keyframes sp { to { transform: rotate(360deg); } }

        @keyframes fadeInUp {
          from { opacity: 0; transform: translateY(16px); }
          to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp 0.4s ease; }
      `}</style>

      <div className="cp-wrap">
        <div className="cp-card fade-in">
          <Link href="/dashboard" className="cp-back">
            <ArrowLeft size={16} /> Quay lại Dashboard
          </Link>

          <div className="cp-title-wrap">
            <div className="cp-icon-badge">
              <KeyRound size={22} />
            </div>
            <div>
              <h1 className="cp-title">Đổi Mật Khẩu</h1>
            </div>
          </div>
          <p className="cp-sub">Cập nhật mật khẩu cho tài khoản <strong>{username}</strong></p>

          <form onSubmit={handleSubmit}>
            <div className="field-group">
              <label className="field-label">Mật khẩu hiện tại</label>
              <div className="input-wrap">
                <input
                  type={showPw ? "text" : "password"}
                  className="cp-input"
                  required
                  placeholder="Nhập mật khẩu hiện tại"
                  value={form.currentPassword}
                  onChange={(e) => setForm({ ...form, currentPassword: e.target.value })}
                />
                <button
                  type="button"
                  className="pw-toggle-btn"
                  onClick={() => setShowPw((v) => !v)}
                  title={showPw ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            <div className="field-group">
              <label className="field-label">Mật khẩu mới</label>
              <div className="input-wrap">
                <input
                  type={showPw ? "text" : "password"}
                  className="cp-input"
                  required
                  placeholder="Tối thiểu 6 ký tự"
                  value={form.newPassword}
                  onChange={(e) => setForm({ ...form, newPassword: e.target.value })}
                />
                <button
                  type="button"
                  className="pw-toggle-btn"
                  onClick={() => setShowPw((v) => !v)}
                  title={showPw ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            <div className="field-group">
              <label className="field-label">Xác nhận mật khẩu mới</label>
              <div className="input-wrap">
                <input
                  type={showPw ? "text" : "password"}
                  className="cp-input"
                  required
                  placeholder="Nhập lại mật khẩu mới"
                  value={form.confirmPassword}
                  onChange={(e) => setForm({ ...form, confirmPassword: e.target.value })}
                />
                <button
                  type="button"
                  className="pw-toggle-btn"
                  onClick={() => setShowPw((v) => !v)}
                  title={showPw ? "Ẩn mật khẩu" : "Hiện mật khẩu"}
                >
                  {showPw ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            <button type="submit" className="btn-submit" disabled={loading}>
              {loading ? <><span className="spin-xs" /> Đang cập nhật...</> : "Cập nhật mật khẩu"}
            </button>
          </form>
        </div>
      </div>
    </>
  );
}
