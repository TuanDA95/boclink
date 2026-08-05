"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { CreditCard, ArrowLeft, CheckCircle2, XCircle, Clock, ShieldCheck, RefreshCw, Sparkles } from "lucide-react";
import { TELCOS, DECLARED_VALUES, calculateRealAmount } from "@/lib/scratchCard";

interface DepositRecord {
  id: string;
  cardTelco: string;
  cardCode: string;
  cardSerial: string;
  declaredValue: number;
  realValue: number;
  status: "PENDING" | "SUCCESS" | "FAILED" | "CANCELLED";
  cardMessage: string;
  createdAt: string;
}

interface Props {
  discounts: Record<string, number>;
  initialDeposits: DepositRecord[];
  currentBalance: number;
}

function formatVND(amount: number) {
  return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(amount);
}

export default function CardDepositClient({ discounts, initialDeposits, currentBalance }: Props) {
  const router = useRouter();
  const [telco, setTelco] = useState("VIETTEL");
  const [declaredValue, setDeclaredValue] = useState<number>(50000);
  const [code, setCode] = useState("");
  const [serial, setSerial] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [successMsg, setSuccessMsg] = useState("");
  const [deposits, setDeposits] = useState<DepositRecord[]>(initialDeposits);
  const [balance, setBalance] = useState(currentBalance);

  const selectedTelcoObj = TELCOS.find((t) => t.code === telco) || TELCOS[0];
  const discountPercent = discounts[telco] ?? selectedTelcoObj.defaultDiscount;
  const realValue = calculateRealAmount(declaredValue, discountPercent);

  const handleRefresh = async () => {
    try {
      const res = await fetch("/api/deposit/card/history");
      if (res.ok) {
        const data = await res.json();
        if (data.deposits) setDeposits(data.deposits);
        if (data.balance !== undefined) setBalance(data.balance);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccessMsg("");

    if (!code.trim() || !serial.trim()) {
      setError("Vui lòng nhập đầy đủ Mã thẻ cào và Số Seri");
      return;
    }

    setLoading(true);
    try {
      const res = await fetch("/api/deposit/card", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          telco,
          declaredValue,
          code: code.trim(),
          serial: serial.trim(),
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Nạp thẻ thất bại");
        return;
      }

      setSuccessMsg(`✅ ${data.message || "Đã nạp thẻ thành công! Hệ thống đang tiến hành gạch thẻ."}`);
      setCode("");
      setSerial("");
      handleRefresh();
      router.refresh();
    } catch (err) {
      setError("Lỗi kết nối tới máy chủ. Vui lòng thử lại!");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: 840, margin: "40px auto", padding: "0 16px" }} className="animate-fade-in">
      {/* Header Back & Title */}
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 24 }}>
        <button
          onClick={() => router.push("/deposit")}
          style={{ display: "inline-flex", alignItems: "center", gap: 6, background: "none", border: "none", color: "#94a3b8", cursor: "pointer", fontSize: 14 }}
        >
          <ArrowLeft size={16} /> Chọn phương thức nạp tiền
        </button>
        <div style={{ fontSize: 13, color: "#94a3b8" }}>
          Số dư tài khoản: <strong style={{ color: "#818cf8", fontSize: 15 }}>{formatVND(balance)}</strong>
        </div>
      </div>

      <div style={{ marginBottom: 24 }}>
        <h1 style={{ fontSize: 24, fontWeight: 700, color: "#e2e8f0" }}>Nạp tiền qua Thẻ Cào Điện Thoại & Thẻ Game</h1>
        <p style={{ color: "#64748b", fontSize: 13, marginTop: 4 }}>
          Hỗ trợ Viettel, Vinaphone, Mobifone, Zing, Gate, Garena, Vcoin với tỉ lệ gạch thẻ tự động 24/7.
        </p>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 24 }}>
        {/* Form Card */}
        <div className="glass-card" style={{ padding: 24 }}>
          <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 18 }}>
            {/* Telco Selector */}
            <div>
              <label className="label">1. Chọn loại thẻ cào *</label>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 8, marginTop: 6 }}>
                {TELCOS.map((t) => {
                  const isSelected = telco === t.code;
                  return (
                    <button
                      key={t.code}
                      type="button"
                      onClick={() => setTelco(t.code)}
                      style={{
                        padding: "10px 8px",
                        borderRadius: 8,
                        border: isSelected ? "2px solid #f59e0b" : "1px solid rgba(255,255,255,0.08)",
                        background: isSelected ? "rgba(245,158,11,0.12)" : "#11131f",
                        color: isSelected ? "#f59e0b" : "#e2e8f0",
                        fontSize: 13,
                        fontWeight: isSelected ? 700 : 500,
                        cursor: "pointer",
                        textAlign: "center",
                      }}
                    >
                      {t.name}
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Declared Value Selector */}
            <div>
              <label className="label">2. Chọn mệnh giá khai báo *</label>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 8, marginTop: 6 }}>
                {DECLARED_VALUES.map((val) => {
                  const isSelected = declaredValue === val;
                  return (
                    <button
                      key={val}
                      type="button"
                      onClick={() => setDeclaredValue(val)}
                      style={{
                        padding: "8px 4px",
                        borderRadius: 8,
                        border: isSelected ? "2px solid #6366f1" : "1px solid rgba(255,255,255,0.08)",
                        background: isSelected ? "rgba(99,102,241,0.15)" : "#11131f",
                        color: isSelected ? "#818cf8" : "#94a3b8",
                        fontSize: 12.5,
                        fontWeight: isSelected ? 700 : 500,
                        cursor: "pointer",
                      }}
                    >
                      {val.toLocaleString("vi-VN")} đ
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Discount Summary Box */}
            <div style={{ background: "rgba(16,185,129,0.08)", border: "1px solid rgba(16,185,129,0.2)", borderRadius: 10, padding: 12 }}>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#94a3b8" }}>
                <span>Mệnh giá khai báo:</span>
                <strong style={{ color: "#e2e8f0" }}>{formatVND(declaredValue)}</strong>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#94a3b8", marginTop: 4 }}>
                <span>Chiết khấu nhà mạng:</span>
                <span style={{ color: "#f59e0b", fontWeight: 600 }}>-{discountPercent}%</span>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", fontSize: 14, color: "#10b981", fontWeight: 700, marginTop: 8, paddingTop: 8, borderTop: "1px dashed rgba(255,255,255,0.1)" }}>
                <span>Thực nhận số dư:</span>
                <span style={{ fontSize: 16 }}>+{formatVND(realValue)}</span>
              </div>
            </div>

            {/* Code & Serial Inputs */}
            <div>
              <label className="label">Mã thẻ cào (PIN) *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace", letterSpacing: 1 }}
                placeholder="Nhập mã mã thẻ cào bên dưới lớp cào"
                value={code}
                onChange={(e) => setCode(e.target.value)}
                required
              />
            </div>

            <div>
              <label className="label">Số Seri thẻ *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace", letterSpacing: 1 }}
                placeholder="Nhập số seri in trên thẻ"
                value={serial}
                onChange={(e) => setSerial(e.target.value)}
                required
              />
            </div>

            {/* Error or Success Alert */}
            {error && <div style={{ color: "#ef4444", fontSize: 13, background: "rgba(239,68,68,0.1)", padding: "10px 14px", borderRadius: 8, border: "1px solid rgba(239,68,68,0.2)" }}>{error}</div>}
            {successMsg && <div style={{ color: "#10b981", fontSize: 13, background: "rgba(16,185,129,0.1)", padding: "10px 14px", borderRadius: 8, border: "1px solid rgba(16,185,129,0.2)" }}>{successMsg}</div>}

            {/* Submit Button */}
            <button
              type="submit"
              disabled={loading}
              style={{
                height: 44,
                background: "linear-gradient(135deg, #f59e0b, #d97706)",
                color: "#0f172a",
                border: "none",
                borderRadius: 8,
                fontWeight: 700,
                fontSize: 15,
                cursor: "pointer",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                gap: 8,
              }}
            >
              {loading ? <span className="spinner" style={{ width: 16, height: 16 }} /> : <><CreditCard size={18} /> Nạp Thẻ Cào Ngay</>}
            </button>
          </form>
        </div>

        {/* History Card */}
        <div className="glass-card" style={{ padding: 24, display: "flex", flexDirection: "column" }}>
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 16 }}>
            <h2 style={{ fontSize: 16, fontWeight: 700, color: "#e2e8f0" }}>Lịch sử Nạp Thẻ Cào</h2>
            <button
              onClick={handleRefresh}
              style={{ background: "none", border: "none", color: "#818cf8", cursor: "pointer", display: "inline-flex", alignItems: "center", gap: 4, fontSize: 12.5 }}
            >
              <RefreshCw size={13} /> Làm mới
            </button>
          </div>

          {deposits.length === 0 ? (
            <div style={{ textAlign: "center", padding: "40px 0", color: "#64748b", fontSize: 13 }}>
              Bạn chưa có giao dịch nạp thẻ cào nào.
            </div>
          ) : (
            <div style={{ flex: 1, overflowY: "auto", display: "flex", flexDirection: "column", gap: 10, maxHeight: 460 }}>
              {deposits.map((dep) => (
                <div
                  key={dep.id}
                  style={{
                    background: "#11131f",
                    border: "1px solid rgba(255,255,255,0.06)",
                    borderRadius: 10,
                    padding: 12,
                    display: "flex",
                    flexDirection: "column",
                    gap: 6,
                  }}
                >
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                    <span style={{ fontWeight: 700, color: "#e2e8f0", fontSize: 13 }}>
                      {dep.cardTelco} - {formatVND(dep.declaredValue)}
                    </span>
                    <StatusBadge status={dep.status} />
                  </div>

                  <div style={{ fontSize: 11.5, color: "#64748b", fontFamily: "monospace" }}>
                    PIN: {dep.cardCode} | Seri: {dep.cardSerial}
                  </div>

                  <div style={{ display: "flex", justifyContent: "space-between", fontSize: 12, color: "#94a3b8", marginTop: 2 }}>
                    <span>Thực nhận: <strong style={{ color: "#10b981" }}>+{formatVND(dep.realValue || dep.declaredValue)}</strong></span>
                    <span>{new Date(dep.createdAt).toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" })}</span>
                  </div>

                  {dep.cardMessage && (
                    <div style={{ fontSize: 11, color: dep.status === "FAILED" ? "#ef4444" : "#94a3b8", fontStyle: "italic", marginTop: 2 }}>
                      {dep.cardMessage}
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function StatusBadge({ status }: { status: string }) {
  switch (status) {
    case "SUCCESS":
      return (
        <span style={{ display: "inline-flex", alignItems: "center", gap: 4, fontSize: 11, color: "#10b981", background: "rgba(16,185,129,0.12)", padding: "2px 8px", borderRadius: 12, fontWeight: 600 }}>
          <CheckCircle2 size={12} /> Thành công
        </span>
      );
    case "FAILED":
      return (
        <span style={{ display: "inline-flex", alignItems: "center", gap: 4, fontSize: 11, color: "#ef4444", background: "rgba(239,68,68,0.12)", padding: "2px 8px", borderRadius: 12, fontWeight: 600 }}>
          <XCircle size={12} /> Thất bại
        </span>
      );
    default:
      return (
        <span style={{ display: "inline-flex", alignItems: "center", gap: 4, fontSize: 11, color: "#f59e0b", background: "rgba(245,158,11,0.12)", padding: "2px 8px", borderRadius: 12, fontWeight: 600 }}>
          <Clock size={12} /> Đang xử lý
        </span>
      );
  }
}
