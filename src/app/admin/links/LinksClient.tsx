"use client";

import { useState, useEffect, useCallback } from "react";
import { useRouter } from "next/navigation";
import {
  Search,
  Copy,
  Check,
  Plus,
  Trash2,
  ToggleLeft,
  ToggleRight,
  RefreshCw,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  Code2,
} from "lucide-react";

interface Link {
  id: string;
  slug: string;
  originalUrl: string;
  title: string | null;
  price: number;
  adDuration: number;
  isActive: boolean;
  clicks: number;
  createdAt: string;
  user?: { name: string | null; email: string } | null;
  _count?: { purchases: number };
}

interface Props {
  initialLinks: Link[];
  total: number;
  apiToken: string;
  domain: string;
}

function CopyButton({ text, label = "Copy", size = "sm" }: { text: string; label?: string; size?: "sm" | "md" }) {
  const [copied, setCopied] = useState(false);
  const handleCopy = async () => {
    await navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };
  return (
    <button
      onClick={handleCopy}
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: 6,
        padding: size === "sm" ? "6px 14px" : "8px 18px",
        background: copied ? "rgba(16,185,129,0.15)" : "#1e2030",
        border: `1px solid ${copied ? "rgba(16,185,129,0.3)" : "rgba(255,255,255,0.1)"}`,
        color: copied ? "#10b981" : "#e2e8f0",
        borderRadius: 6,
        cursor: "pointer",
        fontSize: 13,
        fontWeight: 500,
        flexShrink: 0,
        transition: "all 0.15s",
      }}
    >
      {copied ? <Check size={12} /> : <Copy size={12} />}
      {copied ? "Đã chép" : label}
    </button>
  );
}

export default function LinksManageClient({ initialLinks, total, apiToken, domain }: Props) {
  const router = useRouter();
  const [links, setLinks] = useState<Link[]>(initialLinks);
  const [totalLinks, setTotalLinks] = useState(total);
  const [search, setSearch] = useState("");
  const [searchInput, setSearchInput] = useState("");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(false);
  const [creating, setCreating] = useState(false);
  const [error, setError] = useState("");
  const [currentApiToken, setCurrentApiToken] = useState(apiToken);
  const [regenerating, setRegenerating] = useState(false);

  // Form tạo link
  const [code, setCode] = useState("");
  const [targetUrl, setTargetUrl] = useState("");

  const quicklinkUrl = `${domain}/st?token=${currentApiToken}&url=`;
  const devApiUrl = `${domain}/api/devst?token=${currentApiToken}&url=`;

  const phpCodeSample = `$token = '${currentApiToken}';
$url    = 'https://your-target-url.com';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "${domain}/api/devst?" . http_build_query([
        'token' => $token,
        'url'   => $url,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo "Lỗi kết nối";
} else {
    $data = json_decode($response, true);
    if ($httpCode === 200 && ($data['status'] ?? '') === 'success') {
        echo $data['short_url'];
    } else {
        echo "Lỗi: " . ($data['message'] ?? 'Không rõ nguyên nhân');
    }
}`;

  const fetchLinks = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        limit: "20",
        ...(search ? { search } : {}),
      });
      const res = await fetch(`/api/admin/links?${params}`);
      const data = await res.json();
      setLinks(data.links || []);
      setTotalLinks(data.total || 0);
    } finally {
      setLoading(false);
    }
  }, [page, search]);

  useEffect(() => {
    fetchLinks();
  }, [fetchLinks]);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    if (!code.trim()) { setError("Nhập mã code"); return; }
    if (!targetUrl.trim()) { setError("Nhập Target URL"); return; }

    setCreating(true);
    try {
      const res = await fetch("/api/admin/links", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          slug: code.toLowerCase().trim(),
          originalUrl: targetUrl.trim(),
        }),
      });
      const data = await res.json();
      if (!res.ok) { setError(data.error || "Lỗi tạo link"); return; }
      setCode("");
      setTargetUrl("");
      fetchLinks();
    } finally {
      setCreating(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Xác nhận xóa link này?")) return;
    await fetch(`/api/admin/links/${id}`, { method: "DELETE" });
    setLinks((l) => l.filter((x) => x.id !== id));
    setTotalLinks((t) => t - 1);
  };

  const handleToggle = async (link: Link) => {
    await fetch(`/api/admin/links/${link.id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...link, isActive: !link.isActive }),
    });
    setLinks((l) => l.map((x) => x.id === link.id ? { ...x, isActive: !x.isActive } : x));
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearch(searchInput);
    setPage(1);
  };

  const handleRegenerateToken = async () => {
    if (!confirm("Tạo lại API Token sẽ làm vô hiệu token cũ. Tiếp tục?")) return;
    setRegenerating(true);
    try {
      const res = await fetch("/api/user/api-token", { method: "POST" });
      const data = await res.json();
      setCurrentApiToken(data.apiToken);
    } finally {
      setRegenerating(false);
    }
  };

  const totalPages = Math.ceil(totalLinks / 20);

  return (
    <div className="animate-fade-in">
      {/* Header */}
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24, flexWrap: "wrap", gap: 16 }}>
        <h1 style={{ fontSize: 24, fontWeight: 700, color: "#e2e8f0" }}>Quản lý</h1>
        <form onSubmit={handleSearch} style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
          <div style={{ position: "relative" }}>
            <Search size={14} style={{ position: "absolute", left: 12, top: "50%", transform: "translateY(-50%)", color: "#64748b" }} />
            <input
              className="input"
              style={{ paddingLeft: 36, width: 220, height: 38, fontSize: 13, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
              placeholder="Tìm mã code..."
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
            />
          </div>
          <button type="submit" style={{ height: 38, padding: "0 20px", background: "#4f46e5", color: "white", border: "none", borderRadius: 8, cursor: "pointer", fontSize: 13, fontWeight: 600 }}>
            Lọc
          </button>
        </form>
      </div>

      {/* Quick Create Form */}
      <form onSubmit={handleCreate} style={{ display: "flex", gap: 12, marginBottom: 20, alignItems: "flex-end", flexWrap: "wrap" }}>
        <div style={{ flex: "1 1 160px" }}>
          <label style={{ display: "block", fontSize: 12, color: "#64748b", marginBottom: 6, fontWeight: 500 }}>Code</label>
          <input
            className="input"
            style={{ height: 40, fontSize: 14, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
            placeholder="abc"
            value={code}
            onChange={(e) => setCode(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, ""))}
          />
        </div>
        <div style={{ flex: "2 1 240px" }}>
          <label style={{ display: "block", fontSize: 12, color: "#64748b", marginBottom: 6, fontWeight: 500 }}>Target URL</label>
          <input
            className="input"
            style={{ height: 40, fontSize: 14, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)" }}
            placeholder="https://google.com/..."
            value={targetUrl}
            onChange={(e) => setTargetUrl(e.target.value)}
            type="url"
          />
        </div>
        <button
          type="submit"
          disabled={creating}
          style={{ height: 40, padding: "0 24px", background: "#4f46e5", color: "white", border: "none", borderRadius: 8, cursor: "pointer", fontSize: 14, fontWeight: 600, display: "flex", alignItems: "center", gap: 6, flexShrink: 0 }}
        >
          {creating ? <span className="spinner" style={{ width: 14, height: 14 }} /> : <><Plus size={16} /> TẠO</>}
        </button>
      </form>

      {error && (
        <div style={{ background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.15)", color: "#ef4444", padding: "10px 14px", borderRadius: 8, fontSize: 13, marginBottom: 16 }}>
          {error}
        </div>
      )}

      {/* Quicklink API */}
      <div style={{ marginBottom: 12 }}>
        <p style={{ fontSize: 12, color: "#6366f1", fontWeight: 600, marginBottom: 6 }}>Quicklink API</p>
        <div style={{ display: "flex", alignItems: "center", gap: 0, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", borderRadius: 8, overflow: "hidden" }}>
          <input
            readOnly
            value={quicklinkUrl}
            style={{ flex: 1, background: "transparent", border: "none", outline: "none", padding: "10px 14px", color: "#94a3b8", fontSize: 13, fontFamily: "monospace" }}
          />
          <CopyButton text={quicklinkUrl} />
        </div>
      </div>

      {/* Developer API */}
      <div style={{ marginBottom: 20 }}>
        <p style={{ fontSize: 12, color: "#6366f1", fontWeight: 600, marginBottom: 6 }}>Developer API</p>
        <div style={{ display: "flex", alignItems: "center", gap: 0, background: "#11131f", border: "1px solid rgba(255,255,255,0.08)", borderRadius: 8, overflow: "hidden" }}>
          <input
            readOnly
            value={devApiUrl}
            style={{ flex: 1, background: "transparent", border: "none", outline: "none", padding: "10px 14px", color: "#94a3b8", fontSize: 13, fontFamily: "monospace" }}
          />
          <CopyButton text={devApiUrl} />
        </div>
      </div>

      {/* PHP Code Sample */}
      <div style={{ marginBottom: 28 }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 8 }}>
          <p style={{ fontSize: 13, color: "#94a3b8", display: "flex", alignItems: "center", gap: 6 }}>
            <Code2 size={14} /> Sử dụng phản hồi JSON (PHP)
          </p>
          <div style={{ display: "flex", gap: 8 }}>
            <CopyButton text={phpCodeSample} label="Copy code" />
            <button
              onClick={handleRegenerateToken}
              disabled={regenerating}
              style={{ display: "inline-flex", alignItems: "center", gap: 6, padding: "6px 14px", background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.15)", color: "#ef4444", borderRadius: 6, cursor: "pointer", fontSize: 12 }}
              title="Tạo lại API Token"
            >
              <RefreshCw size={12} className={regenerating ? "animate-spin" : ""} />
              Reset Token
            </button>
          </div>
        </div>
        <pre style={{
          background: "#0d0f1a",
          border: "1px solid rgba(255,255,255,0.06)",
          borderRadius: 10,
          padding: "20px 24px",
          fontSize: 12.5,
          lineHeight: 1.7,
          color: "#94a3b8",
          overflow: "auto",
          fontFamily: "'Fira Code', 'Cascadia Code', 'JetBrains Mono', monospace",
          whiteSpace: "pre",
        }}>
          {phpCodeSample.split("\n").map((line, i) => {
            // Syntax highlighting đơn giản
            let colored = line;
            if (line.trim().startsWith("$")) {
              colored = `<span style="color:#f8fafc">${line}</span>`;
            } else if (line.trim().startsWith("//") || line.trim().startsWith("#")) {
              colored = `<span style="color:#475569">${line}</span>`;
            } else if (line.includes("curl_")) {
              colored = line.replace(/curl_\w+/g, (m) => `<span style="color:#818cf8">${m}</span>`);
            } else if (line.includes("echo") || line.includes("if") || line.includes("else")) {
              colored = line.replace(/\b(echo|if|else)\b/g, (m) => `<span style="color:#c084fc">${m}</span>`);
            }
            return (
              <div key={i} style={{ display: "flex", gap: 16 }}>
                <span style={{ color: "#334155", userSelect: "none", minWidth: 20, textAlign: "right" }}>{i + 1}</span>
                <span dangerouslySetInnerHTML={{ __html: colored }} />
              </div>
            );
          })}
        </pre>
      </div>

      {/* Links Table */}
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 12 }}>
        <p style={{ fontSize: 14, color: "#94a3b8" }}>
          {loading ? "Đang tải..." : `${totalLinks} link`}
        </p>
      </div>

      <div className="glass-card admin-table-container" style={{ padding: 0, borderRadius: 16 }}>
        <table style={{ width: "100%", borderCollapse: "collapse" }}>
          <thead>
            <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.06)", background: "rgba(255,255,255,0.02)" }}>
              {["Code / URL", "Link bọc", "Clicks", "Trạng thái", ""].map((h) => (
                <th key={h} style={{ padding: "12px 16px", textAlign: "left", fontSize: 11, color: "#64748b", fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.6px" }}>
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {links.map((link) => (
              <tr key={link.id} className="table-row">
                <td style={{ padding: "13px 16px", maxWidth: 300 }}>
                  <p style={{ fontWeight: 700, fontSize: 14, color: "#e2e8f0", fontFamily: "monospace" }}>{link.slug}</p>
                  <p style={{ fontSize: 12, color: "#475569", marginTop: 2, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap", maxWidth: 260 }} title={link.originalUrl}>
                    {link.originalUrl}
                  </p>
                </td>
                <td style={{ padding: "13px 16px" }}>
                  <a
                    href={`/l/${link.slug}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    style={{ display: "inline-flex", alignItems: "center", gap: 4, fontSize: 13, color: "#6366f1", textDecoration: "none" }}
                  >
                    /l/{link.slug} <ExternalLink size={11} />
                  </a>
                </td>
                <td style={{ padding: "13px 16px", fontSize: 14, color: "#94a3b8" }}>
                  {link.clicks.toLocaleString()}
                </td>
                <td style={{ padding: "13px 16px" }}>
                  <button onClick={() => handleToggle(link)} style={{ background: "none", border: "none", cursor: "pointer", padding: 0 }}>
                    {link.isActive
                      ? <ToggleRight size={22} color="#10b981" />
                      : <ToggleLeft size={22} color="#475569" />}
                  </button>
                </td>
                <td style={{ padding: "13px 16px" }}>
                  <div style={{ display: "flex", gap: 6 }}>
                    <CopyButton text={`${domain}/l/${link.slug}`} label="Copy link" size="sm" />
                    <button
                      onClick={() => handleDelete(link.id)}
                      style={{ background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.12)", color: "#ef4444", padding: "6px 10px", borderRadius: 6, cursor: "pointer" }}
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {links.length === 0 && !loading && (
          <div style={{ textAlign: "center", padding: "40px 20px", color: "#475569" }}>
            Chưa có link nào
          </div>
        )}
      </div>

      {/* Pagination */}
      {totalPages > 1 && (
        <div style={{ display: "flex", justifyContent: "center", gap: 8, marginTop: 20 }}>
          <button
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page === 1}
            style={{ padding: "8px 12px", background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "#94a3b8", borderRadius: 6, cursor: page === 1 ? "not-allowed" : "pointer", opacity: page === 1 ? 0.4 : 1 }}
          >
            <ChevronLeft size={16} />
          </button>
          <span style={{ padding: "8px 16px", fontSize: 13, color: "#94a3b8" }}>
            {page} / {totalPages}
          </span>
          <button
            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
            disabled={page === totalPages}
            style={{ padding: "8px 12px", background: "rgba(255,255,255,0.04)", border: "1px solid rgba(255,255,255,0.08)", color: "#94a3b8", borderRadius: 6, cursor: page === totalPages ? "not-allowed" : "pointer", opacity: page === totalPages ? 0.4 : 1 }}
          >
            <ChevronRight size={16} />
          </button>
        </div>
      )}
    </div>
  );
}
