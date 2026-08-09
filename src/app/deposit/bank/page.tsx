"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import { Building2, Copy, Check, RefreshCw, ArrowLeft, Clock, TestTube } from "lucide-react";

function formatVND(amount: number) {
  return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(amount);
}

type DepositStatus = "PENDING" | "SUCCESS" | "FAILED" | "CANCELLED";

interface DepositInfo {
  depositId: string;
  paymentContent: string;
  bankAccount: string;
  bankName: string;
  bankOwner: string;
  amount: number;
  qrCodeUrl: string;
  expiredAt: string;
}

export default function BankDepositPage() {
  const router = useRouter();
  const [step, setStep] = useState<"form" | "pending">("form");
  const [amount, setAmount] = useState("");
  const [loading, setLoading] = useState(false);
  const [simulating, setSimulating] = useState(false);
  const [error, setError] = useState("");
  const [depositInfo, setDepositInfo] = useState<DepositInfo | null>(null);
  const [status, setStatus] = useState<DepositStatus>("PENDING");
  const [copied, setCopied] = useState<string | null>(null);
  const [timeLeft, setTimeLeft] = useState(3600); // 1 tiếng

  const quickAmounts = [50000, 100000, 200000, 500000, 1000000, 2000000];

  // Polling trạng thái
  const checkStatus = useCallback(async () => {
    if (!depositInfo || status !== "PENDING") return;
    try {
      const res = await fetch(`/api/deposit/${depositInfo.depositId}/status`);
      if (!res.ok) return;
      const data = await res.json();
      if (data.status && data.status !== status) {
        setStatus(data.status);
        if (data.status === "SUCCESS") {
          setTimeout(() => router.push(`/deposit/success?depositId=${depositInfo.depositId}`), 1000);
        }
      }
    } catch (e) {
      console.error("Polling error:", e);
    }
  }, [depositInfo, status, router]);

  useEffect(() => {
    if (step !== "pending") return;
    const interval = setInterval(checkStatus, 5000);
    return () => clearInterval(interval);
  }, [step, checkStatus]);

  // Countdown
  useEffect(() => {
    if (step !== "pending") return;
    const timer = setInterval(() => {
      setTimeLeft((t) => {
        if (t <= 1) {
          clearInterval(timer);
          setStatus("CANCELLED");
          return 0;
        }
        return t - 1;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, [step]);

  const formatTime = (sec: number) => {
    const m = Math.floor(sec / 60).toString().padStart(2, "0");
    const s = (sec % 60).toString().padStart(2, "0");
    return `${m}:${s}`;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    const numAmount = parseInt(amount);
    if (!amount || isNaN(numAmount) || numAmount < 10000) {
      setError("Số tiền nạp tối thiểu 10.000 VNĐ");
      return;
    }
    setLoading(true);
    try {
      const res = await fetch("/api/deposit/bank", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ amount: numAmount }),
      });
      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Không thể tạo đơn nạp tiền");
        return;
      }
      setDepositInfo(data);
      // Tính thời gian còn lại từ expiredAt server trả về (tránh desync client/server)
      if (data.expiredAt) {
        const secsLeft = Math.max(0, Math.floor((new Date(data.expiredAt).getTime() - Date.now()) / 1000));
        setTimeLeft(secsLeft);
      } else {
        setTimeLeft(3600);
      }
      setStep("pending");
    } catch (err) {
      setError("Lỗi kết nối tới máy chủ. Vui lòng thử lại.");
    } finally {
      setLoading(false);
    }
  };

  const handleSimulatePayment = async () => {
    if (!depositInfo) return;
    setSimulating(true);
    try {
      const res = await fetch("/api/deposit/simulate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ depositId: depositInfo.depositId }),
      });
      const data = await res.json();
      if (data.success) {
        setStatus("SUCCESS");
        setTimeout(() => router.push(`/deposit/success?depositId=${depositInfo.depositId}`), 1000);
      }
    } finally {
      setSimulating(false);
    }
  };

  const copy = async (text: string, key: string) => {
    await navigator.clipboard.writeText(text);
    setCopied(key);
    setTimeout(() => setCopied(null), 2000);
  };

  const CopyBtn = ({ text, id }: { text: string; id: string }) => (
    <button
      onClick={() => copy(text, id)}
      style={{ background: "rgba(99,102,241,0.1)", border: "1px solid rgba(99,102,241,0.2)", color: "#818cf8", padding: "6px 12px", borderRadius: 6, cursor: "pointer", display: "flex", alignItems: "center", gap: 6, fontSize: 12 }}
    >
      {copied === id ? <Check size={12} /> : <Copy size={12} />}
      {copied === id ? "Đã chép" : "Sao chép"}
    </button>
  );

  if (step === "form") {
    return (
      <div style={{ maxWidth: 520, margin: "40px auto", padding: "0 16px" }} className="animate-fade-in">
        <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 28 }}>
          <button onClick={() => router.back()} style={{ background: "rgba(255,255,255,0.05)", border: "1px solid rgba(255,255,255,0.08)", color: "#94a3b8", padding: "8px 12px", borderRadius: 8, cursor: "pointer" }}>
            <ArrowLeft size={16} />
          </button>
          <div>
            <h1 style={{ fontSize: 22, fontWeight: 700 }}>Nạp qua ngân hàng</h1>
            <p style={{ color: "#94a3b8", fontSize: 13 }}>Quét QR VietQR, tự động xác nhận</p>
          </div>
        </div>

        <div className="glass-card" style={{ padding: 20, marginBottom: 20, display: "flex", alignItems: "center", gap: 14, background: "rgba(20,184,166,0.08)", borderColor: "rgba(20,184,166,0.2)" }}>
          <Building2 size={24} color="#2dd4bf" />
          <div>
            <p style={{ fontWeight: 600, fontSize: 14 }}>Chuyển khoản qua VietQR</p>
            <p style={{ fontSize: 12, color: "#94a3b8" }}>Xác nhận tự động trong 30–60 giây</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 20 }}>
          <div className="form-group">
            <label className="label">Số tiền nạp (VNĐ) *</label>
            <input
              id="amount"
              className="input"
              type="number"
              min="10000"
              step="1000"
              placeholder="Nhập số tiền..."
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              style={{ fontSize: 20, fontWeight: 600, padding: "16px" }}
              required
            />
          </div>

          {/* Quick amounts */}
          <div>
            <p className="label">Chọn nhanh</p>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 8 }}>
              {quickAmounts.map((v) => (
                <button
                  key={v}
                  type="button"
                  onClick={() => setAmount(v.toString())}
                  style={{
                    padding: "10px",
                    borderRadius: 8,
                    border: `1px solid ${amount === v.toString() ? "rgba(99,102,241,0.5)" : "rgba(255,255,255,0.08)"}`,
                    background: amount === v.toString() ? "rgba(99,102,241,0.15)" : "rgba(255,255,255,0.04)",
                    color: amount === v.toString() ? "#818cf8" : "#94a3b8",
                    cursor: "pointer",
                    fontSize: 13,
                    fontWeight: 600,
                    transition: "all 0.15s",
                  }}
                >
                  {(v / 1000).toLocaleString()}k
                </button>
              ))}
            </div>
          </div>

          {error && (
            <div style={{ background: "rgba(239,68,68,0.1)", border: "1px solid rgba(239,68,68,0.2)", color: "#ef4444", padding: "12px 16px", borderRadius: 10, fontSize: 14 }}>
              {error}
            </div>
          )}

          <button type="submit" className="btn-primary" style={{ width: "100%", padding: 16, fontSize: 16 }} disabled={loading}>
            {loading ? <span className="spinner" /> : "Tạo mã chuyển khoản"}
          </button>
        </form>
      </div>
    );
  }

  // Pending step
  if (!depositInfo) return null;

  return (
    <div style={{ maxWidth: 520, margin: "40px auto", padding: "0 16px" }} className="animate-fade-in">
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
        <h1 style={{ fontSize: 20, fontWeight: 700 }}>Quét QR để chuyển khoản</h1>
        <div style={{ display: "flex", alignItems: "center", gap: 6, color: status === "PENDING" ? "#f59e0b" : status === "SUCCESS" ? "#10b981" : "#ef4444" }}>
          {status === "PENDING" && <><RefreshCw size={14} style={{ animation: "spin 1.5s linear infinite" }} /> <span style={{ fontSize: 13 }}>Đang chờ...</span></>}
          {status === "SUCCESS" && <><Check size={14} /> <span style={{ fontSize: 13 }}>Thành công!</span></>}
          {status === "CANCELLED" && <span style={{ fontSize: 13 }}>Hết hạn</span>}
        </div>
      </div>

      {status === "SUCCESS" ? (
        <div className="glass-card" style={{ textAlign: "center", padding: 40, borderColor: "rgba(16,185,129,0.3)" }}>
          <div style={{ width: 64, height: 64, background: "rgba(16,185,129,0.2)", borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", margin: "0 auto 16px" }}>
            <Check size={32} color="#10b981" />
          </div>
          <p style={{ fontSize: 20, fontWeight: 700, color: "#10b981" }}>Nạp tiền thành công!</p>
          <p style={{ color: "#94a3b8", marginTop: 8 }}>{formatVND(depositInfo.amount)} đã được cộng vào tài khoản</p>
        </div>
      ) : status === "CANCELLED" ? (
        <div className="glass-card" style={{ textAlign: "center", padding: 40 }}>
          <p style={{ fontSize: 18, fontWeight: 700, color: "#ef4444" }}>Đơn nạp đã hết hạn</p>
          <button className="btn-primary" style={{ marginTop: 20 }} onClick={() => { setStep("form"); setTimeLeft(3600); }}>
            Tạo đơn mới
          </button>
        </div>
      ) : (
        <>
          {/* Countdown */}
          <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 20, padding: "10px 16px", background: "rgba(245,158,11,0.08)", border: "1px solid rgba(245,158,11,0.2)", borderRadius: 10, color: "#f59e0b", fontSize: 14 }}>
            <Clock size={14} />
            <span>Hết hạn sau: <strong>{formatTime(timeLeft)}</strong></span>
          </div>

          {/* QR */}
          <div className="glass-card" style={{ textAlign: "center", marginBottom: 20 }}>
            <img
              src={depositInfo.qrCodeUrl}
              alt="QR VietQR"
              width={220}
              height={220}
              style={{ borderRadius: 12, margin: "0 auto", display: "block", background: "white", padding: 8 }}
            />
            <p style={{ fontSize: 13, color: "#94a3b8", marginTop: 12 }}>
              Mở app ngân hàng → Quét mã QR
            </p>

            {/* Sandbox Simulation Button */}
            <div style={{ marginTop: 16, paddingTop: 16, borderTop: "1px dashed rgba(255,255,255,0.08)" }}>
              <button
                onClick={handleSimulatePayment}
                disabled={simulating}
                style={{
                  width: "100%",
                  padding: "10px",
                  background: "rgba(99,102,241,0.12)",
                  border: "1px solid rgba(99,102,241,0.25)",
                  color: "#818cf8",
                  borderRadius: 8,
                  cursor: "pointer",
                  fontSize: 13,
                  fontWeight: 600,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  gap: 6,
                }}
              >
                {simulating ? <span className="spinner" style={{ width: 14, height: 14 }} /> : <><TestTube size={15} /> 🧪 [DEV / TEST SANDBOX] Giả lập nạp tiền thành công (1-click)</>}
              </button>
            </div>
          </div>

          {/* Info */}
          <div className="glass-card" style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            {[
              { label: "Ngân hàng", value: depositInfo.bankName, id: "bank" },
              { label: "Số tài khoản", value: depositInfo.bankAccount, id: "acc" },
              { label: "Chủ tài khoản", value: depositInfo.bankOwner, id: "owner" },
              { label: "Số tiền", value: formatVND(depositInfo.amount), id: "amount" },
              { label: "Nội dung chuyển khoản", value: depositInfo.paymentContent, id: "content", highlight: true },
            ].map((item) => (
              <div key={item.id} style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                <div>
                  <p style={{ fontSize: 12, color: "#94a3b8" }}>{item.label}</p>
                  <p style={{ fontWeight: item.highlight ? 700 : 500, fontSize: item.highlight ? 16 : 14, color: item.highlight ? "#818cf8" : "#e2e8f0", fontFamily: item.highlight ? "monospace" : "inherit" }}>
                    {item.value}
                  </p>
                </div>
                <CopyBtn text={item.value} id={item.id} />
              </div>
            ))}
          </div>

          <div style={{ marginTop: 16, padding: "12px 16px", background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.15)", borderRadius: 10, fontSize: 13, color: "#94a3b8" }}>
            ⚠️ <strong>Bắt buộc</strong> nhập đúng nội dung <strong style={{ color: "#818cf8" }}>{depositInfo.paymentContent}</strong> để hệ thống tự động xác nhận
          </div>
        </>
      )}
    </div>
  );
}
