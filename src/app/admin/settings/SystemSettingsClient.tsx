"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import {
  Save,
  Plus,
  Trash2,
  Globe,
  ToggleLeft,
  ToggleRight,
  ChevronDown,
  ChevronUp,
  Link2,
} from "lucide-react";

declare const Swal: any;

export interface AdLayer {
  id: string;
  name: string;
  region: "international" | "vietnam" | "all";
  enabled: boolean;
  url: string;
  order: number;
}

interface Props {
  initialFreeEnabled: boolean;
  initialDefaultPrice: number;
  initialAdLayers: AdLayer[];
  initialInterstitialAdLayers: AdLayer[];
}

function genId() {
  return Math.random().toString(36).slice(2, 10);
}

const REGION_LABELS: Record<string, { label: string; emoji: string; color: string }> = {
  international: { label: "Quốc tế", emoji: "🌍", color: "#6366f1" },
  vietnam:       { label: "Việt Nam", emoji: "🇻🇳", color: "#ef4444" },
  all:           { label: "Tất cả",   emoji: "🌐", color: "#10b981" },
};

function toast(msg: string, icon = "success") {
  if (typeof Swal !== "undefined") {
    Swal.fire({ toast: true, position: "top-end", icon, title: msg, showConfirmButton: false, timer: 2000 });
  }
}

export default function SystemSettingsClient({
  initialFreeEnabled,
  initialDefaultPrice,
  initialAdLayers,
  initialInterstitialAdLayers,
}: Props) {
  const router = useRouter();
  const [freeEnabled,          setFreeEnabled]          = useState(initialFreeEnabled);
  const [defaultPrice,         setDefaultPrice]         = useState(initialDefaultPrice.toString());
  const [adLayers,             setAdLayers]             = useState<AdLayer[]>(initialAdLayers);
  const [interstitialAdLayers, setInterstitialAdLayers] = useState<AdLayer[]>(initialInterstitialAdLayers);

  const [savingGeneral, setSavingGeneral] = useState(false);
  const [savingLayers,  setSavingLayers]  = useState(false);
  const [savingAd,      setSavingAd]      = useState(false);

  /* ── Save all settings ── */
  const saveAll = async () => {
    // Validate URL với các bọc link đang BẬT
    const invalidWrap = adLayers.find((l) => l.enabled && !l.url.trim());
    if (invalidWrap) {
      toast(`Bọc link "${invalidWrap.name}" đang bật nhưng chưa có đường dẫn`, "warning");
      return;
    }
    const invalidAd = interstitialAdLayers.find((l) => l.enabled && !l.url.trim());
    if (invalidAd) {
      toast(`Quảng cáo hình ảnh "${invalidAd.name}" đang bật nhưng chưa có đường dẫn`, "warning");
      return;
    }

    setSavingGeneral(true);
    setSavingLayers(true);
    setSavingAd(true);

    const firstInterstitialUrl = interstitialAdLayers.find((l) => l.enabled && l.url.trim())?.url.trim() || "";

    try {
      const res = await fetch("/api/admin/settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          FREE_LINK_ENABLED: freeEnabled ? "true" : "false",
          DEFAULT_LINK_PRICE: defaultPrice,
          AD_LAYERS: JSON.stringify(adLayers),
          INTERSTITIAL_AD_LAYERS: JSON.stringify(interstitialAdLayers),
          INTERSTITIAL_AD_URL: firstInterstitialUrl,
        }),
      });
      const data = await res.json();
      if (res.ok) {
        toast("Đã lưu cấu hình thành công!");
        router.refresh();
      } else {
        toast(data.error || "Lỗi lưu cấu hình", "error");
      }
    } catch {
      toast("Lỗi kết nối", "error");
    } finally {
      setSavingGeneral(false);
      setSavingLayers(false);
      setSavingAd(false);
    }
  };

  const saveGeneral = saveAll;
  const saveLayers  = saveAll;

  const saveInterstitialAd = async () => {
    const invalidAd = interstitialAdLayers.find((l) => l.enabled && !l.url.trim());
    if (invalidAd) {
      toast(`Quảng cáo "${invalidAd.name}" đang bật nhưng chưa có đường dẫn`, "warning");
      return;
    }

    setSavingAd(true);
    const firstInterstitialUrl = interstitialAdLayers.find((l) => l.enabled && l.url.trim())?.url.trim() || "";

    try {
      const res = await fetch("/api/admin/settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          INTERSTITIAL_AD_LAYERS: JSON.stringify(interstitialAdLayers),
          INTERSTITIAL_AD_URL: firstInterstitialUrl,
        }),
      });
      const data = await res.json();
      if (res.ok) { toast("Đã lưu quảng cáo hình ảnh!"); router.refresh(); }
      else toast(data.error || "Lỗi lưu", "error");
    } catch { toast("Lỗi kết nối", "error"); }
    finally { setSavingAd(false); }
  };

  /* ── CRUD for Bọc link (adLayers) ── */
  const addLayer = () => {
    const layer: AdLayer = { id: genId(), name: "Bọc link mới", region: "all", enabled: true, url: "", order: adLayers.length };
    setAdLayers((p) => [...p, layer]);
  };

  const upd = (id: string, patch: Partial<AdLayer>) =>
    setAdLayers((p) => p.map((l) => l.id === id ? { ...l, ...patch } : l));

  const del = (id: string) => {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Xoá bọc link này?",
        icon: "warning", showCancelButton: true,
        confirmButtonColor: "#ef4444", cancelButtonColor: "#444",
        confirmButtonText: "Xoá", cancelButtonText: "Huỷ",
        background: "#16161a", color: "#fff",
      }).then((r: any) => { if (r.isConfirmed) setAdLayers((p) => p.filter((l) => l.id !== id)); });
    } else {
      setAdLayers((p) => p.filter((l) => l.id !== id));
    }
  };

  const move = (id: string, dir: "up" | "down") => {
    setAdLayers((prev) => {
      const idx = prev.findIndex((l) => l.id === id);
      const to  = dir === "up" ? idx - 1 : idx + 1;
      if (to < 0 || to >= prev.length) return prev;
      const a = [...prev];
      [a[idx], a[to]] = [a[to], a[idx]];
      return a.map((l, i) => ({ ...l, order: i }));
    });
  };

  /* ── CRUD for Quảng cáo hình ảnh (interstitialAdLayers) ── */
  const addInterstitialLayer = () => {
    const layer: AdLayer = { id: genId(), name: "Quảng cáo hình ảnh mới", region: "all", enabled: true, url: "", order: interstitialAdLayers.length };
    setInterstitialAdLayers((p) => [...p, layer]);
  };

  const updInterstitial = (id: string, patch: Partial<AdLayer>) =>
    setInterstitialAdLayers((p) => p.map((l) => l.id === id ? { ...l, ...patch } : l));

  const delInterstitial = (id: string) => {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Xoá quảng cáo hình ảnh này?",
        icon: "warning", showCancelButton: true,
        confirmButtonColor: "#ef4444", cancelButtonColor: "#444",
        confirmButtonText: "Xoá", cancelButtonText: "Huỷ",
        background: "#16161a", color: "#fff",
      }).then((r: any) => { if (r.isConfirmed) setInterstitialAdLayers((p) => p.filter((l) => l.id !== id)); });
    } else {
      setInterstitialAdLayers((p) => p.filter((l) => l.id !== id));
    }
  };

  const moveInterstitial = (id: string, dir: "up" | "down") => {
    setInterstitialAdLayers((prev) => {
      const idx = prev.findIndex((l) => l.id === id);
      const to  = dir === "up" ? idx - 1 : idx + 1;
      if (to < 0 || to >= prev.length) return prev;
      const a = [...prev];
      [a[idx], a[to]] = [a[to], a[idx]];
      return a.map((l, i) => ({ ...l, order: i }));
    });
  };

  return (
    <>
      <style>{`
        .sc-title { font-size: 26px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px; }
        .sc-sub   { color: #64748b; font-size: 0.88rem; margin-bottom: 32px; }

        .sc-card {
          background: rgba(255,255,255,0.03);
          border: 1px solid rgba(255,255,255,0.08);
          border-radius: 16px; overflow: hidden; margin-bottom: 20px;
        }
        .sc-card-hd {
          padding: 18px 22px; border-bottom: 1px solid rgba(255,255,255,0.06);
          display: flex; align-items: center; justify-content: space-between;
        }
        .sc-card-title {
          font-size: 0.95rem; font-weight: 700; color: #e2e8f0;
          display: flex; align-items: center; gap: 8px;
        }
        .sc-card-body { padding: 22px; }
        .sc-card-ft {
          padding: 14px 22px; border-top: 1px solid rgba(255,255,255,0.05);
          background: rgba(0,0,0,0.15); display: flex; justify-content: flex-end;
        }

        /* toggle row */
        .tog-row {
          display: flex; align-items: center; justify-content: space-between; padding: 10px 0;
        }
        .tog-label { font-size: 0.9rem; color: #e2e8f0; font-weight: 600; }
        .tog-desc  { font-size: 0.76rem; color: #64748b; margin-top: 3px; }
        .tog-btn   { background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; transition: transform .15s; }
        .tog-btn:hover { transform: scale(1.05); }

        /* price */
        .price-row { display: flex; align-items: center; gap: 12px; margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.05); flex-wrap: wrap; }
        .price-label { font-size: 0.88rem; font-weight: 600; color: #e2e8f0; flex-shrink: 0; }
        .price-wrap  { position: relative; }
        .price-input {
          background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
          border-radius: 10px; padding: 9px 46px 9px 14px; color: #e2e8f0; font-size: 0.88rem;
          outline: none; box-sizing: border-box; width: 180px; transition: border-color .2s;
        }
        .price-input:focus { border-color: rgba(99,102,241,.5); }
        .price-unit { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: .74rem; color: #64748b; font-weight: 600; pointer-events: none; }
        .price-hint { font-size: .75rem; color: #64748b; }

        /* save btn */
        .btn-save {
          display: inline-flex; align-items: center; gap: 6px;
          padding: 9px 18px; border-radius: 10px; border: none;
          font-weight: 700; font-size: .82rem; cursor: pointer;
          background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff;
          transition: filter .2s;
        }
        .btn-save:hover { filter: brightness(1.1); }
        .btn-save:disabled { opacity: .55; cursor: not-allowed; filter: none; }

        /* add layer btn */
        .btn-add {
          display: inline-flex; align-items: center; gap: 6px;
          padding: 8px 16px; border-radius: 10px;
          border: 1px dashed rgba(99,102,241,.5);
          background: rgba(99,102,241,.06); color: #818cf8;
          font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .2s;
        }
        .btn-add:hover { background: rgba(99,102,241,.12); border-color: #818cf8; }

        /* layer card */
        .layer-card {
          border: 1px solid rgba(255,255,255,0.07); border-radius: 12px;
          background: rgba(255,255,255,0.02); margin-bottom: 10px;
          transition: border-color .2s;
        }
        .layer-card:hover { border-color: rgba(255,255,255,.12); }
        .layer-card.off { opacity: .5; }

        .layer-row {
          display: flex; align-items: center; gap: 10px;
          padding: 12px 14px; flex-wrap: wrap;
        }

        /* name input inline */
        .layer-name-input {
          flex: 1; background: transparent; border: none; border-bottom: 1px solid transparent;
          color: #e2e8f0; font-size: .88rem; font-weight: 600; outline: none;
          padding: 2px 4px; transition: border-color .2s;
        }
        .layer-name-input:hover { border-bottom-color: rgba(255,255,255,.15); }
        .layer-name-input:focus { border-bottom-color: rgba(99,102,241,.5); }

        /* region badge */
        .badge-region {
          font-size: .7rem; font-weight: 700; padding: 2px 9px;
          border-radius: 6px; letter-spacing: .5px; white-space: nowrap; flex-shrink: 0;
        }

        /* url row */
        .url-row {
          display: flex; align-items: center; gap: 8px;
          padding: 0 14px 12px; 
        }
        .url-icon { color: #64748b; flex-shrink: 0; }
        .url-input {
          flex: 1; background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.08);
          border-radius: 8px; padding: 8px 12px; color: #a5f3fc;
          font-size: .82rem; font-family: monospace; outline: none;
          transition: border-color .2s; box-sizing: border-box;
        }
        .url-input:focus { border-color: rgba(99,102,241,.4); }
        .url-input::placeholder { color: #475569; }

        /* region select */
        .region-select {
          background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
          border-radius: 8px; padding: 7px 10px; color: #e2e8f0;
          font-size: .8rem; outline: none; cursor: pointer;
        }
        .region-select option { background: #1e2030; }

        /* icon buttons */
        .i-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; padding: 3px; border-radius: 5px; transition: color .15s; }
        .i-del { color: #ef4444; opacity: .5; } .i-del:hover { opacity: 1; background: rgba(239,68,68,.1); }
        .i-move { color: #64748b; } .i-move:hover { color: #e2e8f0; } .i-move:disabled { opacity: .2; cursor: default; }
        .i-tog  { color: inherit; padding: 0; }

        /* empty */
        .empty { text-align: center; padding: 32px 0; color: #475569; font-size: .88rem; }
        .empty-icon { font-size: 2.2rem; display: block; margin-bottom: 8px; }

        /* spinner */
        .spin-xs { width:13px; height:13px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:sp .7s linear infinite; display:inline-block; }
        @keyframes sp { to { transform:rotate(360deg); } }
      `}</style>

      <h1 className="sc-title">Cấu hình hệ thống</h1>
      <p className="sc-sub">Quản lý chức năng và quảng cáo của API Key</p>

      {/* ═══ CARD 1 – Cấu hình chung ═══ */}
      <div className="sc-card">
        <div className="sc-card-hd">
          <div className="sc-card-title">⚙️ Cấu hình chung</div>
        </div>
        <div className="sc-card-body">
          {/* Free link toggle */}
          <div className="tog-row">
            <div>
              <div className="tog-label">Chức năng Link Miễn Phí</div>
              <div className="tog-desc">Khi TẮT, nút "Vượt link miễn phí" sẽ bị ẩn — người dùng chỉ có thể mua link.</div>
            </div>
            <button className="tog-btn" onClick={() => setFreeEnabled((v) => !v)}>
              {freeEnabled
                ? <ToggleRight size={40} color="#10b981" />
                : <ToggleLeft  size={40} color="#475569" />}
            </button>
          </div>

          {/* Default price */}
          <div className="price-row">
            <span className="price-label">💰 Giá link mặc định</span>
            <div className="price-wrap">
              <input
                type="number" min={0} step={1000}
                className="price-input"
                value={defaultPrice}
                onChange={(e) => setDefaultPrice(e.target.value)}
              />
              <span className="price-unit">đồng</span>
            </div>
            <span className="price-hint">Gợi ý khi tạo link mới</span>
          </div>
        </div>
        <div className="sc-card-ft">
          <button className="btn-save" onClick={saveGeneral} disabled={savingGeneral}>
            {savingGeneral ? <><span className="spin-xs" /> Đang lưu...</> : <><Save size={13} /> Lưu</>}
          </button>
        </div>
      </div>

      {/* ═══ CARD 2 – Quảng cáo hình ảnh (interstitial) ═══ */}
      <div className="sc-card">
        <div className="sc-card-hd">
          <div className="sc-card-title">
            🖼️ Quảng cáo hình ảnh ({interstitialAdLayers.length})
            <span style={{ fontSize: ".72rem", color: "#64748b", fontWeight: 400 }}>
              — Cấu hình nhiều bước quảng cáo khi người dùng nhấn vào ảnh tại trang vượt link
            </span>
          </div>
          <button className="btn-add" onClick={addInterstitialLayer}>
            <Plus size={14} /> Thêm quảng cáo
          </button>
        </div>
        <div className="sc-card-body" style={{ paddingTop: 14 }}>
          {interstitialAdLayers.length === 0 ? (
            <div className="empty">
              <span className="empty-icon">📭</span>
              Chưa có quảng cáo hình ảnh nào.<br />Nhấn <strong>Thêm quảng cáo</strong> để bắt đầu.
            </div>
          ) : (
            interstitialAdLayers.map((layer, idx) => {
              const rm = REGION_LABELS[layer.region];
              return (
                <div key={layer.id} className={`layer-card${layer.enabled ? "" : " off"}`}>
                  <div className="layer-row">
                    <button className="i-btn i-move" onClick={() => moveInterstitial(layer.id, "up")} disabled={idx === 0} title="Lên">
                      <ChevronUp size={15} />
                    </button>
                    <button className="i-btn i-move" onClick={() => moveInterstitial(layer.id, "down")} disabled={idx === interstitialAdLayers.length - 1} title="Xuống">
                      <ChevronDown size={15} />
                    </button>

                    <input
                      className="layer-name-input"
                      value={layer.name}
                      placeholder="Tên quảng cáo"
                      onChange={(e) => updInterstitial(layer.id, { name: e.target.value })}
                    />

                    <select
                      className="region-select"
                      value={layer.region}
                      onChange={(e) => updInterstitial(layer.id, { region: e.target.value as AdLayer["region"] })}
                    >
                      <option value="all">🌐 Tất cả</option>
                      <option value="international">🌍 Quốc tế</option>
                      <option value="vietnam">🇻🇳 Việt Nam</option>
                    </select>

                    <span
                      className="badge-region"
                      style={{ background: `${rm.color}18`, color: rm.color, border: `1px solid ${rm.color}30` }}
                    >
                      {rm.emoji}
                    </span>

                    <button className="i-btn i-tog" onClick={() => updInterstitial(layer.id, { enabled: !layer.enabled })} title={layer.enabled ? "Tắt" : "Bật"}>
                      {layer.enabled
                        ? <ToggleRight size={26} color="#10b981" />
                        : <ToggleLeft  size={26} color="#475569" />}
                    </button>

                    <button className="i-btn i-del" onClick={() => delInterstitial(layer.id)} title="Xoá">
                      <Trash2 size={14} />
                    </button>
                  </div>

                  <div className="url-row">
                    <Globe size={14} className="url-icon" />
                    <input
                      className="url-input"
                      type="url"
                      value={layer.url}
                      placeholder="https://ads.example.com/show?id=..."
                      onChange={(e) => updInterstitial(layer.id, { url: e.target.value })}
                    />
                  </div>
                </div>
              );
            })
          )}
        </div>
        {interstitialAdLayers.length > 0 && (
          <div className="sc-card-ft">
            <button className="btn-save" onClick={saveInterstitialAd} disabled={savingAd}>
              {savingAd ? <><span className="spin-xs" /> Đang lưu...</> : <><Save size={13} /> Lưu quảng cáo hình ảnh</>}
            </button>
          </div>
        )}
      </div>

      {/* ═══ CARD 3 – Bọc link ═══ */}
      <div className="sc-card">
        <div className="sc-card-hd">
          <div className="sc-card-title">
            🔗 Bọc link ({adLayers.length})
            <span style={{ fontSize: ".72rem", color: "#64748b", fontWeight: 400 }}>
              — Chèn vào trang đếm ngược khi vượt link miễn phí
            </span>
          </div>
          <button className="btn-add" onClick={addLayer}>
            <Plus size={14} /> Thêm bọc link
          </button>
        </div>

        <div className="sc-card-body" style={{ paddingTop: 14 }}>
          {adLayers.length === 0 ? (
            <div className="empty">
              <span className="empty-icon">📭</span>
              Chưa có bọc link nào.<br />Nhấn <strong>Thêm bọc link</strong> để bắt đầu.
            </div>
          ) : (
            adLayers.map((layer, idx) => {
              const rm = REGION_LABELS[layer.region];
              return (
                <div key={layer.id} className={`layer-card${layer.enabled ? "" : " off"}`}>
                  {/* Row 1: controls + name + region + toggle + delete */}
                  <div className="layer-row">
                    {/* Move up/down */}
                    <button className="i-btn i-move" onClick={() => move(layer.id, "up")} disabled={idx === 0} title="Lên">
                      <ChevronUp size={15} />
                    </button>
                    <button className="i-btn i-move" onClick={() => move(layer.id, "down")} disabled={idx === adLayers.length - 1} title="Xuống">
                      <ChevronDown size={15} />
                    </button>

                    {/* Inline name */}
                    <input
                      className="layer-name-input"
                      value={layer.name}
                      placeholder="Tên bọc link"
                      onChange={(e) => upd(layer.id, { name: e.target.value })}
                    />

                    {/* Region selector */}
                    <select
                      className="region-select"
                      value={layer.region}
                      onChange={(e) => upd(layer.id, { region: e.target.value as AdLayer["region"] })}
                    >
                      <option value="all">🌐 Tất cả</option>
                      <option value="international">🌍 Quốc tế</option>
                      <option value="vietnam">🇻🇳 Việt Nam</option>
                    </select>

                    {/* Region badge */}
                    <span
                      className="badge-region"
                      style={{ background: `${rm.color}18`, color: rm.color, border: `1px solid ${rm.color}30` }}
                    >
                      {rm.emoji}
                    </span>

                    {/* Enable toggle */}
                    <button className="i-btn i-tog" onClick={() => upd(layer.id, { enabled: !layer.enabled })} title={layer.enabled ? "Tắt" : "Bật"}>
                      {layer.enabled
                        ? <ToggleRight size={26} color="#10b981" />
                        : <ToggleLeft  size={26} color="#475569" />}
                    </button>

                    {/* Delete */}
                    <button className="i-btn i-del" onClick={() => del(layer.id)} title="Xoá">
                      <Trash2 size={14} />
                    </button>
                  </div>

                  {/* Row 2: URL input */}
                  <div className="url-row">
                    <Globe size={14} className="url-icon" />
                    <input
                      className="url-input"
                      type="url"
                      value={layer.url}
                      placeholder="https://ads.example.com/redirect?..."
                      onChange={(e) => upd(layer.id, { url: e.target.value })}
                    />
                  </div>
                </div>
              );
            })
          )}
        </div>

        {adLayers.length > 0 && (
          <div className="sc-card-ft">
            <button className="btn-save" onClick={saveLayers} disabled={savingLayers}>
              {savingLayers ? <><span className="spin-xs" /> Đang lưu...</> : <><Save size={13} /> Lưu bọc link</>}
            </button>
          </div>
        )}
      </div>
    </>
  );
}
