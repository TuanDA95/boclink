"use client";

import { useState, useEffect } from "react";
import { useParams } from "next/navigation";

declare const Swal: any;

interface LinkData {
  id: string;
  title: string;
  adDuration: number;
  originalUrl: string;
}

interface AdLayer {
  id: string;
  name: string;
  region: "international" | "vietnam" | "all";
  enabled: boolean;
  url: string;
  order: number;
}

export default function AdPage() {
  const { slug } = useParams<{ slug: string }>();
  const [link, setLink] = useState<LinkData | null>(null);
  const [timeLeft, setTimeLeft] = useState<number | null>(null);
  const [revealed, setRevealed] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [generatedKey, setGeneratedKey] = useState<string | null>(null);
  const [copiedKey, setCopiedKey] = useState(false);
  const [adLayers, setAdLayers] = useState<AdLayer[]>([]);

  // Fetch link data
  useEffect(() => {
    const fetchLink = async () => {
      try {
        const res = await fetch(`/api/links/${slug}/ad`);
        if (!res.ok) { setError("Link không tồn tại"); return; }
        const data = await res.json();
        setLink(data.link);
        setTimeLeft(data.link.adDuration);
      } finally {
        setLoading(false);
      }
    };
    fetchLink();
  }, [slug]);

  // Fetch ad layers
  useEffect(() => {
    fetch("/api/links/ad-layers")
      .then((r) => r.json())
      .then((d) => {
        const sorted = (d.layers || []).sort((a: AdLayer, b: AdLayer) => (a.order ?? 0) - (b.order ?? 0));
        setAdLayers(sorted);
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    if (timeLeft === null || timeLeft <= 0) {
      if (timeLeft === 0) {
        setRevealed(true);
        fetch("/api/key/create", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ slug, durationDays: 1 }),
        })
          .then((res) => res.json())
          .then((data) => { if (data.keyCode) setGeneratedKey(data.keyCode); })
          .catch(() => {});
      }
      return;
    }
    const timer = setTimeout(() => setTimeLeft((t) => (t ?? 1) - 1), 1000);
    return () => clearTimeout(timer);
  }, [timeLeft, slug]);

  const handleCopyKey = async () => {
    if (!generatedKey) return;
    await navigator.clipboard.writeText(generatedKey);
    setCopiedKey(true);
    setTimeout(() => setCopiedKey(false), 2000);
  };

  const progress = link ? ((link.adDuration - (timeLeft ?? 0)) / link.adDuration) * 100 : 0;
  const circumference = 2 * Math.PI * 45;

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;700;800&display=swap');

        body {
          background: radial-gradient(circle at top right, #2d1b4e, #16161a) !important;
          font-family: 'Lexend', sans-serif !important;
          min-height: 100vh;
          margin: 0;
          color: #e0e0e0;
        }

        .ad-wrap {
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          padding: 20px 16px;
        }

        .card-gaming {
          background: rgba(22, 22, 26, 0.95);
          border: 1px solid rgba(26, 213, 250, 0.3);
          border-radius: 24px;
          padding: 40px 30px;
          width: 100%;
          max-width: 520px;
          box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 20px rgba(26,213,250,0.08);
          backdrop-filter: blur(10px);
          text-align: center;
        }

        .title-glitch {
          font-weight: 800;
          letter-spacing: 2px;
          background: linear-gradient(to right, #1ad5fa, #667eea);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          text-transform: uppercase;
          font-size: 1.1rem;
          margin-bottom: 6px;
        }

        .countdown-ring-wrap {
          position: relative;
          width: 130px;
          height: 130px;
          margin: 24px auto;
        }

        .countdown-number {
          position: absolute;
          inset: 0;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
        }

        .ad-placeholder {
          background: rgba(255,255,255,0.03);
          border: 1px dashed rgba(26,213,250,0.2);
          border-radius: 14px;
          padding: 28px 20px;
          margin-bottom: 20px;
          color: #555;
          font-size: 0.85rem;
        }

        .key-box {
          background: rgba(99,102,241,0.1);
          border: 1px solid rgba(99,102,241,0.25);
          border-radius: 14px;
          padding: 16px;
          margin-bottom: 20px;
          text-align: left;
        }

        .key-display {
          display: flex;
          align-items: center;
          justify-content: space-between;
          background: #0d0f1a;
          padding: 10px 14px;
          border-radius: 10px;
          font-family: monospace;
          font-size: 1.1rem;
          font-weight: 700;
          color: #e2e8f0;
          gap: 10px;
          margin-top: 8px;
        }

        .btn-copy {
          background: #1e2030;
          border: none;
          color: #e2e8f0;
          padding: 6px 14px;
          border-radius: 7px;
          cursor: pointer;
          font-size: 0.78rem;
          transition: 0.2s;
          white-space: nowrap;
        }
        .btn-copy.copied { background: rgba(16,185,129,0.2); color: #10b981; }

        .btn-open-link {
          display: block;
          width: 100%;
          padding: 14px;
          background: linear-gradient(45deg, #1ad5fa, #667eea);
          color: #000;
          font-weight: 800;
          border-radius: 12px;
          text-decoration: none;
          text-transform: uppercase;
          letter-spacing: 1px;
          font-size: 0.9rem;
          border: none;
          cursor: pointer;
          transition: all 0.25s;
          box-shadow: 0 4px 20px rgba(26,213,250,0.3);
        }
        .btn-open-link:hover {
          filter: brightness(1.1);
          box-shadow: 0 6px 28px rgba(26,213,250,0.5);
          transform: translateY(-1px);
        }

        .back-link {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          color: #555;
          text-decoration: none;
          font-size: 0.82rem;
          margin-bottom: 20px;
          transition: color 0.2s;
        }
        .back-link:hover { color: #1ad5fa; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
          width: 40px; height: 40px;
          border: 3px solid rgba(26,213,250,0.2);
          border-top-color: #1ad5fa;
          border-radius: 50%;
          animation: spin 0.9s linear infinite;
          margin: 0 auto;
        }
        @keyframes fadeInUp {
          from { opacity:0; transform: translateY(14px); }
          to   { opacity:1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp 0.4s ease; }
      `}</style>

      <div className="ad-wrap">
        <div style={{ width: "100%", maxWidth: 520 }}>
          <a href={`/l/${slug}`} className="back-link">
            ← Quay lại
          </a>

          {loading && (
            <div className="card-gaming">
              <div className="spinner" />
              <p style={{ color: "#555", marginTop: 16, fontSize: "0.85rem" }}>Đang tải...</p>
            </div>
          )}

          {!loading && (error || !link) && (
            <div className="card-gaming">
              <p style={{ color: "#ef4444", fontSize: "1rem", marginBottom: 16 }}>{error || "Link không tồn tại"}</p>
              <a href="/" className="btn-open-link" style={{ display: "inline-block", width: "auto", padding: "10px 28px" }}>
                Về trang chủ
              </a>
            </div>
          )}

          {!loading && link && (
            <div className="card-gaming">
              <p style={{ color: "#a0a0a0", fontSize: "0.82rem", marginBottom: 4 }}>Bạn đang xem quảng cáo cho</p>
              <h1 className="title-glitch">{link.title}</h1>

              {!revealed ? (
                <>
                  {/* Circular countdown */}
                  <div className="countdown-ring-wrap">
                    <svg width="130" height="130" style={{ transform: "rotate(-90deg)" }}>
                      <circle cx="65" cy="65" r="45" fill="none" stroke="rgba(255,255,255,0.06)" strokeWidth="8" />
                      <circle
                        cx="65" cy="65" r="45" fill="none"
                        stroke="url(#cyanGrad)" strokeWidth="8"
                        strokeLinecap="round"
                        strokeDasharray={circumference}
                        strokeDashoffset={circumference - (progress / 100) * circumference}
                        style={{ transition: "stroke-dashoffset 1s linear" }}
                      />
                      <defs>
                        <linearGradient id="cyanGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                          <stop offset="0%" stopColor="#1ad5fa" />
                          <stop offset="100%" stopColor="#667eea" />
                        </linearGradient>
                      </defs>
                    </svg>
                    <div className="countdown-number">
                      <span style={{ fontSize: "2.2rem", fontWeight: 800, lineHeight: 1, color: "#1ad5fa" }}>{timeLeft}</span>
                      <span style={{ fontSize: "0.72rem", color: "#666", marginTop: 2 }}>giây</span>
                    </div>
                  </div>

                  {/* Ad area */}
                  {/* Dynamic Ad Layers — load URL qua iframe */}
                  <div style={{ marginBottom: 16 }}>
                    {adLayers.length > 0 ? (
                      adLayers.map((layer) => (
                        <iframe
                          key={layer.id}
                          src={layer.url}
                          title={layer.name}
                          data-region={layer.region}
                          style={{
                            width: "100%",
                            minHeight: 90,
                            border: "none",
                            borderRadius: 10,
                            marginBottom: 8,
                            background: "transparent",
                          }}
                          allow="autoplay"
                          scrolling="no"
                        />
                      ))
                    ) : (
                      <div className="ad-placeholder">
                        📺 Khu vực hiển thị quảng cáo
                        <br />
                        <span style={{ fontSize: "0.75rem" }}>(Chưa cấu hình lớp quảng cáo)</span>
                      </div>
                    )}
                  </div>

                  <p style={{ color: "#666", fontSize: "0.85rem" }}>
                    Vui lòng chờ <span style={{ color: "#1ad5fa", fontWeight: 700 }}>{timeLeft} giây</span> để nhận link miễn phí...
                  </p>
                </>
              ) : (
                <div className="fade-in">
                  <div style={{ fontSize: "3rem", marginBottom: 12 }}>🎉</div>
                  <p style={{ color: "#10b981", fontWeight: 700, fontSize: "1.1rem", marginBottom: 20 }}>
                    Link của bạn đã sẵn sàng!
                  </p>

                  {/* License Key */}
                  {generatedKey && (
                    <div className="key-box">
                      <div style={{ fontSize: "0.75rem", color: "#818cf8", fontWeight: 700, textTransform: "uppercase", letterSpacing: 1 }}>
                        🔑 License Key của bạn (Hạn 24h)
                      </div>
                      <div className="key-display">
                        <span>{generatedKey}</span>
                        <button
                          className={`btn-copy ${copiedKey ? "copied" : ""}`}
                          onClick={handleCopyKey}
                        >
                          {copiedKey ? "✓ Đã chép" : "Copy Key"}
                        </button>
                      </div>
                    </div>
                  )}

                  <a
                    href={link.originalUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn-open-link"
                    id="open-link-btn"
                  >
                    🔗 MỞ LINK GỐC
                  </a>
                </div>

              )}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
