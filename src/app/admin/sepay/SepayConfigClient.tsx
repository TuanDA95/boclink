"use client";

import { useState } from "react";
import { Building2, CreditCard, Shield, Copy, Check, Save, Info, RefreshCw, KeyRound, Globe, Eye, EyeOff } from "lucide-react";

interface Props {
  initialSettings: Record<string, string>;
  webhookUrl: string;
}

const SUPPORTED_BANKS = [
  { code: "MBBank", name: "MBBank (Ngân hàng Quân Đội)" },
  { code: "Vietcombank", name: "Vietcombank (VCB)" },
  { code: "BIDV", name: "BIDV" },
  { code: "VietinBank", name: "VietinBank" },
  { code: "Agribank", name: "Agribank" },
  { code: "Techcombank", name: "Techcombank (TCB)" },
  { code: "ACB", name: "ACB" },
  { code: "VPBank", name: "VPBank" },
  { code: "TPBank", name: "TPBank" },
  { code: "Sacombank", name: "Sacombank" },
  { code: "HDBank", name: "HDBank" },
  { code: "VIB", name: "VIB" },
];

function CopyButton({ text }: { text: string }) {
  const [copied, setCopied] = useState(false);
  const handleCopy = async () => {
    await navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };
  return (
    <button
      type="button"
      onClick={handleCopy}
      style={{ display: "inline-flex", alignItems: "center", gap: 6, padding: "8px 16px", background: "rgba(99,102,241,0.15)", border: "1px solid rgba(99,102,241,0.3)", color: "#818cf8", borderRadius: 8, cursor: "pointer", fontSize: 13, fontWeight: 500 }}
    >
      {copied ? <Check size={14} /> : <Copy size={14} />}
      {copied ? "Đã chép" : "Copy URL"}
    </button>
  );
}

export default function SepayConfigClient({ initialSettings, webhookUrl }: Props) {
  const [form, setForm] = useState({
    SEPAY_BANK_NAME: initialSettings.SEPAY_BANK_NAME || "MBBank",
    SEPAY_BANK_ACCOUNT: initialSettings.SEPAY_BANK_ACCOUNT || "",
    SEPAY_BANK_OWNER: initialSettings.SEPAY_BANK_OWNER || "",
    SEPAY_PAYMENT_PREFIX: initialSettings.SEPAY_PAYMENT_PREFIX || "SUB2S",
    SEPAY_WEBHOOK_SECRET: initialSettings.SEPAY_WEBHOOK_SECRET || "",
    SEPAY_MERCHANT_ID: initialSettings.SEPAY_MERCHANT_ID || "",
    SEPAY_SECRET_KEY: initialSettings.SEPAY_SECRET_KEY || "",
    SEPAY_SANDBOX: initialSettings.SEPAY_SANDBOX || "false",
  });

  const [showSecret, setShowSecret] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage(null);

    try {
      const res = await fetch("/api/admin/sepay", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });

      const data = await res.json();
      if (!res.ok) {
        setMessage({ type: "error", text: data.error || "Lỗi lưu cấu hình" });
        return;
      }

      setMessage({ type: "success", text: "✅ Đã lưu cấu hình SePay thành công!" });
    } catch (err) {
      setMessage({ type: "error", text: "Lỗi kết nối tới máy chủ" });
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="animate-fade-in" style={{ maxWidth: 860 }}>
      {/* Header */}
      <div style={{ marginBottom: 28 }}>
        <h1 style={{ fontSize: 24, fontWeight: 700, color: "#e2e8f0" }}>Cấu hình Thanh toán SePay</h1>
        <p style={{ color: "#64748b", fontSize: 13, marginTop: 4 }}>
          Quản lý tài khoản ngân hàng chuyển khoản VietQR và xác thực Webhook SePay.
        </p>
      </div>

      <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 24 }}>
        {/* Card 1: Bank Transfer (VietQR) */}
        <div className="glass-card" style={{ padding: 24 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 20, paddingBottom: 12, borderBottom: "1px solid rgba(255,255,255,0.06)" }}>
            <Building2 size={20} color="#2dd4bf" />
            <h2 style={{ fontSize: 16, fontWeight: 700, color: "#e2e8f0" }}>Chuyển khoản Ngân hàng (VietQR Tự động)</h2>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))", gap: 16, marginBottom: 16 }}>
            <div>
              <label className="label">Ngân hàng thụ hưởng *</label>
              <select
                className="input"
                style={{ height: 42, background: "#11131f", color: "#e2e8f0", border: "1px solid rgba(255,255,255,0.08)" }}
                value={form.SEPAY_BANK_NAME}
                onChange={(e) => setForm({ ...form, SEPAY_BANK_NAME: e.target.value })}
              >
                {SUPPORTED_BANKS.map((b) => (
                  <option key={b.code} value={b.code}>{b.name}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="label">Số tài khoản ngân hàng *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
                placeholder="Ví dụ: 0123456789"
                value={form.SEPAY_BANK_ACCOUNT}
                onChange={(e) => setForm({ ...form, SEPAY_BANK_ACCOUNT: e.target.value })}
                required
              />
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))", gap: 16, marginBottom: 20 }}>
            <div>
              <label className="label">Tên chủ tài khoản (Viết hoa không dấu) *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
                placeholder="NGUYEN VAN A"
                value={form.SEPAY_BANK_OWNER}
                onChange={(e) => setForm({ ...form, SEPAY_BANK_OWNER: e.target.value.toUpperCase() })}
                required
              />
            </div>

            <div>
              <label className="label">Prefix Nội dung Chuyển khoản *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace", fontWeight: 700 }}
                placeholder="SUB2S"
                value={form.SEPAY_PAYMENT_PREFIX}
                onChange={(e) => setForm({ ...form, SEPAY_PAYMENT_PREFIX: e.target.value.toUpperCase() })}
                required
              />
              <span style={{ fontSize: 11, color: "#64748b", marginTop: 4, display: "block" }}>
                Mã nội dung sẽ có dạng: <code>{form.SEPAY_PAYMENT_PREFIX || "SUB2S"}XXXXXX</code>
              </span>
            </div>
          </div>

          {/* Webhook API Key & Callback URL */}
          <div style={{ background: "rgba(99,102,241,0.06)", border: "1px solid rgba(99,102,241,0.15)", borderRadius: 12, padding: 16 }}>
            <div style={{ marginBottom: 16 }}>
              <label className="label" style={{ color: "#818cf8", fontWeight: 700 }}>
                API Key Webhook / Secret Key (Header: Authorization: Apikey API_KEY)
              </label>
              <div style={{ position: "relative" }}>
                <input
                  className="input"
                  type={showSecret ? "text" : "password"}
                  style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace", paddingRight: 40 }}
                  placeholder="whsec_345Y9bqKGVucghxcl5bprqKaBA2LuuMW"
                  value={form.SEPAY_WEBHOOK_SECRET}
                  onChange={(e) => setForm({ ...form, SEPAY_WEBHOOK_SECRET: e.target.value })}
                />
                <button
                  type="button"
                  onClick={() => setShowSecret(!showSecret)}
                  style={{ position: "absolute", right: 12, top: "50%", transform: "translateY(-50%)", background: "none", border: "none", color: "#64748b", cursor: "pointer" }}
                >
                  {showSecret ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
              <span style={{ fontSize: 11, color: "#94a3b8", marginTop: 6, display: "block" }}>
                💡 Điền đúng API Key bạn đặt trên <strong>my.sepay.vn → Webhook → Kiểu xác thực API Key</strong>. SePay sẽ tự động gửi kèm <code>Authorization: Apikey {form.SEPAY_WEBHOOK_SECRET || "YOUR_API_KEY"}</code>
              </span>
            </div>

            <div>
              <label className="label" style={{ color: "#818cf8" }}>Webhook Callback URL (Điền trên SePay Dashboard)</label>
              <div style={{ display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" }}>
                <input
                  readOnly
                  value={webhookUrl}
                  className="input"
                  style={{ height: 40, background: "#0d0f1a", border: "1px solid rgba(255,255,255,0.08)", color: "#94a3b8", fontFamily: "monospace", flex: "1 1 220px", fontSize: 12.5 }}
                />
                <CopyButton text={webhookUrl} />
              </div>
            </div>
          </div>
        </div>

        {/* Card 2: Card Payment Gateway */}
        <div className="glass-card" style={{ padding: 24 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 20, paddingBottom: 12, borderBottom: "1px solid rgba(255,255,255,0.06)" }}>
            <CreditCard size={20} color="#818cf8" />
            <h2 style={{ fontSize: 16, fontWeight: 700, color: "#e2e8f0" }}>Cổng Thanh Toán Thẻ Quốc Tế (SePay Gateway)</h2>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))", gap: 16, marginBottom: 16 }}>
            <div>
              <label className="label">Merchant ID (Cổng thẻ SePay)</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace" }}
                placeholder="MCH_123456"
                value={form.SEPAY_MERCHANT_ID}
                onChange={(e) => setForm({ ...form, SEPAY_MERCHANT_ID: e.target.value })}
              />
            </div>

            <div>
              <label className="label">Secret Key (Cổng thẻ SePay)</label>
              <input
                className="input"
                type="password"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace" }}
                placeholder="••••••••••••••••"
                value={form.SEPAY_SECRET_KEY}
                onChange={(e) => setForm({ ...form, SEPAY_SECRET_KEY: e.target.value })}
              />
            </div>
          </div>

          {/* Sandbox Toggle */}
          <div style={{ display: "flex", alignItems: "center", gap: 12, marginTop: 12 }}>
            <input
              type="checkbox"
              id="sandbox"
              checked={form.SEPAY_SANDBOX === "true"}
              onChange={(e) => setForm({ ...form, SEPAY_SANDBOX: e.target.checked ? "true" : "false" })}
              style={{ width: 18, height: 18, accentColor: "#6366f1", cursor: "pointer" }}
            />
            <label htmlFor="sandbox" style={{ fontSize: 14, color: "#e2e8f0", cursor: "pointer", fontWeight: 500 }}>
              Bật chế độ thử nghiệm (Sandbox / Test Mode)
            </label>
          </div>
        </div>

        {/* Message Alert */}
        {message && (
          <div style={{
            padding: "12px 16px",
            borderRadius: 10,
            fontSize: 14,
            background: message.type === "success" ? "rgba(16,185,129,0.1)" : "rgba(239,68,68,0.1)",
            border: `1px solid ${message.type === "success" ? "rgba(16,185,129,0.2)" : "rgba(239,68,68,0.2)"}`,
            color: message.type === "success" ? "#10b981" : "#ef4444",
          }}>
            {message.text}
          </div>
        )}

        {/* Save Button */}
        <div>
          <button
            type="submit"
            disabled={saving}
            style={{
              padding: "14px 32px",
              background: "#4f46e5",
              color: "white",
              border: "none",
              borderRadius: 8,
              fontSize: 15,
              fontWeight: 600,
              cursor: "pointer",
              display: "inline-flex",
              alignItems: "center",
              gap: 8,
            }}
          >
            {saving ? <span className="spinner" style={{ width: 16, height: 16 }} /> : <><Save size={18} /> Lưu Cấu Hình</>}
          </button>
        </div>
      </form>
    </div>
  );
}
