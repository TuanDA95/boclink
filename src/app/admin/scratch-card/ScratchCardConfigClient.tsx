"use client";

import { useState } from "react";
import { CreditCard, Save, Copy, Check, Info, Shield, Eye, EyeOff, TestTube } from "lucide-react";
import { TELCOS } from "@/lib/scratchCard";

interface Props {
  initialSettings: Record<string, string>;
  webhookUrl: string;
}

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

export default function ScratchCardConfigClient({ initialSettings, webhookUrl }: Props) {
  const [form, setForm] = useState({
    CARD_PARTNER_ID: initialSettings.CARD_PARTNER_ID || "",
    CARD_PARTNER_KEY: initialSettings.CARD_PARTNER_KEY || "",
    CARD_API_URL: initialSettings.CARD_API_URL || "https://doithe1s.vn/api/charging-ws/v2",
    CARD_SANDBOX: initialSettings.CARD_SANDBOX || "true",
    CARD_DISCOUNT_VIETTEL: initialSettings.CARD_DISCOUNT_VIETTEL || "15",
    CARD_DISCOUNT_VINAPHONE: initialSettings.CARD_DISCOUNT_VINAPHONE || "15",
    CARD_DISCOUNT_MOBIFONE: initialSettings.CARD_DISCOUNT_MOBIFONE || "15",
    CARD_DISCOUNT_ZING: initialSettings.CARD_DISCOUNT_ZING || "16",
    CARD_DISCOUNT_GATE: initialSettings.CARD_DISCOUNT_GATE || "16",
    CARD_DISCOUNT_GARENA: initialSettings.CARD_DISCOUNT_GARENA || "18",
    CARD_DISCOUNT_VCOIN: initialSettings.CARD_DISCOUNT_VCOIN || "18",
  });

  const [showKey, setShowKey] = useState(false);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: "success" | "error"; text: string } | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage(null);

    try {
      const res = await fetch("/api/admin/scratch-card", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(form),
      });

      const data = await res.json();
      if (!res.ok) {
        setMessage({ type: "error", text: data.error || "Lỗi lưu cấu hình thẻ cào" });
        return;
      }

      setMessage({ type: "success", text: "✅ Đã lưu cấu hình Cổng Gạch Thẻ Cào thành công!" });
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
        <h1 style={{ fontSize: 24, fontWeight: 700, color: "#e2e8f0" }}>Cấu hình Cổng Nạp Thẻ Cào / Thẻ Game</h1>
        <p style={{ color: "#64748b", fontSize: 13, marginTop: 4 }}>
          Tích hợp cổng gạch thẻ tự động (Doithe1s, Doithegiare, CardVIP, Doithe247...) để quy đổi thẻ cào điện thoại & thẻ game thành số dư.
        </p>
      </div>

      <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 24 }}>
        {/* Card 1: API Connection Details */}
        <div className="glass-card" style={{ padding: 24 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 20, paddingBottom: 12, borderBottom: "1px solid rgba(255,255,255,0.06)" }}>
            <CreditCard size={20} color="#f59e0b" />
            <h2 style={{ fontSize: 16, fontWeight: 700, color: "#e2e8f0" }}>Thông tin Kết nối Cổng Gạch Thẻ</h2>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))", gap: 16, marginBottom: 16 }}>
            <div>
              <label className="label">Partner ID (Mã đối tác) *</label>
              <input
                className="input"
                style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace" }}
                placeholder="123456789"
                value={form.CARD_PARTNER_ID}
                onChange={(e) => setForm({ ...form, CARD_PARTNER_ID: e.target.value })}
                required
              />
            </div>

            <div>
              <label className="label">Partner Key / Secret Key *</label>
              <div style={{ position: "relative" }}>
                <input
                  className="input"
                  type={showKey ? "text" : "password"}
                  style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace", paddingRight: 40 }}
                  placeholder="Secret key cung cấp bởi Cổng Gạch Thẻ"
                  value={form.CARD_PARTNER_KEY}
                  onChange={(e) => setForm({ ...form, CARD_PARTNER_KEY: e.target.value })}
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowKey(!showKey)}
                  style={{ position: "absolute", right: 12, top: "50%", transform: "translateY(-50%)", background: "none", border: "none", color: "#64748b", cursor: "pointer" }}
                >
                  {showKey ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>
          </div>

          <div style={{ marginBottom: 20 }}>
            <label className="label">API Endpoint URL Gạch Thẻ *</label>
            <input
              className="input"
              style={{ height: 42, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", fontFamily: "monospace" }}
              placeholder="https://doithe1s.vn/api/charging-ws/v2"
              value={form.CARD_API_URL}
              onChange={(e) => setForm({ ...form, CARD_API_URL: e.target.value })}
              required
            />
            <span style={{ fontSize: 11, color: "#64748b", marginTop: 4, display: "block" }}>
              Mặc định: <code>https://doithe1s.vn/api/charging-ws/v2</code> hoặc đường dẫn API của bên gạch thẻ bạn đăng ký.
            </span>
          </div>

          {/* Webhook Callback URL */}
          <div style={{ background: "rgba(245,158,11,0.06)", border: "1px solid rgba(245,158,11,0.2)", borderRadius: 12, padding: 16 }}>
            <div>
              <label className="label" style={{ color: "#fbbf24", fontWeight: 700 }}>Webhook Callback URL (Cấu hình trên Cổng Gạch Thẻ)</label>
              <div style={{ display: "flex", gap: 8, alignItems: "center", marginTop: 6, flexWrap: "wrap" }}>
                <input
                  readOnly
                  value={webhookUrl}
                  className="input"
                  style={{ height: 40, background: "#0d0f1a", border: "1px solid rgba(255,255,255,0.08)", color: "#94a3b8", fontFamily: "monospace", flex: "1 1 220px", fontSize: 12.5 }}
                />
                <CopyButton text={webhookUrl} />
              </div>
              <span style={{ fontSize: 11, color: "#94a3b8", marginTop: 6, display: "block" }}>
                💡 Copy URL trên điền vào mục Callback URL / Webhook URL trên trang web gạch thẻ để tự động nhận kết quả duyệt thẻ.
              </span>
            </div>
          </div>

          {/* Sandbox Toggle */}
          <div style={{ display: "flex", alignItems: "center", gap: 12, marginTop: 20 }}>
            <input
              type="checkbox"
              id="card_sandbox"
              checked={form.CARD_SANDBOX === "true"}
              onChange={(e) => setForm({ ...form, CARD_SANDBOX: e.target.checked ? "true" : "false" })}
              style={{ width: 18, height: 18, accentColor: "#f59e0b", cursor: "pointer" }}
            />
            <label htmlFor="card_sandbox" style={{ fontSize: 14, color: "#e2e8f0", cursor: "pointer", fontWeight: 500 }}>
              Bật chế độ thử nghiệm Thẻ Cào (Sandbox / Auto-Simulate Test)
            </label>
          </div>
        </div>

        {/* Card 2: Discount Rates Config (% Chiết khấu) */}
        <div className="glass-card" style={{ padding: 24 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 16, paddingBottom: 12, borderBottom: "1px solid rgba(255,255,255,0.06)" }}>
            <Shield size={20} color="#10b981" />
            <h2 style={{ fontSize: 16, fontWeight: 700, color: "#e2e8f0" }}>Cấu hình Mức Chiết khấu Thẻ (%)</h2>
          </div>
          <p style={{ fontSize: 13, color: "#94a3b8", marginBottom: 20 }}>
            Số tiền thực nhận của người dùng = Mệnh giá thẻ - (Mệnh giá x % Chiết khấu).
          </p>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: 16 }}>
            {TELCOS.map((telco) => {
              const formKey = `CARD_DISCOUNT_${telco.code}` as keyof typeof form;
              return (
                <div key={telco.code}>
                  <label className="label" style={{ display: "flex", justifyContent: "space-between" }}>
                    <span>{telco.name} ({telco.code})</span>
                    <span style={{ color: "#f59e0b" }}>Chiết khấu: {form[formKey]}%</span>
                  </label>
                  <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                    <input
                      type="number"
                      min="0"
                      max="100"
                      className="input"
                      style={{ height: 40, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
                      value={form[formKey]}
                      onChange={(e) => setForm({ ...form, [formKey]: e.target.value })}
                      required
                    />
                    <span style={{ color: "#64748b", fontSize: 14 }}>%</span>
                  </div>
                </div>
              );
            })}
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
              background: "#f59e0b",
              color: "#0f172a",
              border: "none",
              borderRadius: 8,
              fontSize: 15,
              fontWeight: 700,
              cursor: "pointer",
              display: "inline-flex",
              alignItems: "center",
              gap: 8,
            }}
          >
            {saving ? <span className="spinner" style={{ width: 16, height: 16 }} /> : <><Save size={18} /> Lưu Cấu Hình Thẻ Cào</>}
          </button>
        </div>
      </form>
    </div>
  );
}
