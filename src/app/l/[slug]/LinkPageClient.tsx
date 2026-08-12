"use client";

import { useState, useEffect, useRef } from "react";
import { useRouter } from "next/navigation";

declare const Swal: any;

function formatNum(amount: number) {
  return new Intl.NumberFormat("vi-VN").format(amount);
}

interface LinkData {
  id: string;
  slug: string;
  title: string;
  description?: string | null;
  price: number;
  adDuration: number;
}

interface Props {
  link: LinkData;
  isLoggedIn: boolean;
  alreadyPurchased: boolean;
  userBalance: number;
  freeLinkEnabled?: boolean;
  adClickUrl?: string;
  adUrls?: string[];              // dùng để bọc link khi bấm Tiếp tục
  interstitialAdUrls?: string[];  // danh sách URL quảng cáo hình ảnh
  interstitialAdUrl?: string;     // URL mở khi nhấn ảnh (fallback)
}

type Screen = "loading" | "main" | "interstitial" | "revealed";

const FALLBACK_AD_URL = "https://ngocbonggaming.github.io/direc-2/";
const UNLOCKED_IMG = "/unlock.png";
const LOCKED_IMG = "/lock.png";


export default function LinkPageClient({ link, isLoggedIn, alreadyPurchased, userBalance, freeLinkEnabled = true, adClickUrl = "", adUrls = [], interstitialAdUrls = [], interstitialAdUrl = "" }: Props) {
  const router = useRouter();
  const [screen, setScreen] = useState<Screen>("main");
  const [imageClicked, setImageClicked] = useState(false);
  const [btnEnabled, setBtnEnabled] = useState(false);
  const [currentImg, setCurrentImg] = useState(LOCKED_IMG);
  const [originalUrl, setOriginalUrl] = useState<string | null>(null);
  const [buying, setBuying] = useState(false);
  const [redirecting, setRedirecting] = useState(false);
  const [currentAdIndex, setCurrentAdIndex] = useState(0);
  const [currentInterstitialStep, setCurrentInterstitialStep] = useState(0);
  const [isWaitingStepTimer, setIsWaitingStepTimer] = useState(false);
  const [stepTimer, setStepTimer] = useState(0);
  const swalLoaded = useRef(false);

  // adUrls chỉ dùng để bọc URL khi "Tiếp tục"
  const wrapAdUrls = adUrls && adUrls.length > 0 ? adUrls : (adClickUrl ? [adClickUrl] : []);
  const totalAdLayers = wrapAdUrls.length;

  // Danh sách các URL quảng cáo hình ảnh (interstitial)
  const activeInterstitialUrls = interstitialAdUrls && interstitialAdUrls.length > 0
    ? interstitialAdUrls
    : (interstitialAdUrl.trim() ? [interstitialAdUrl.trim()] : []);
  const totalInterstitialSteps = activeInterstitialUrls.length;

  const startFreeFlow = () => {
    setImageClicked(false);
    setBtnEnabled(false);
    setCurrentImg(LOCKED_IMG);
    setCurrentAdIndex(0);
    setCurrentInterstitialStep(0);
    setIsWaitingStepTimer(false);
    setStepTimer(0);
  };

  const onImageClick = () => {
    if (imageClicked || isWaitingStepTimer) return;

    if (totalInterstitialSteps === 0) {
      setCurrentImg(UNLOCKED_IMG);
      setBtnEnabled(true);
      setImageClicked(true);
      return;
    }

    const currentUrl = activeInterstitialUrls[currentInterstitialStep];
    if (currentUrl && currentUrl.trim()) {
      window.open(currentUrl.trim(), "_blank");
    }

    setIsWaitingStepTimer(true);
    setStepTimer(5);

    const stepIdx = currentInterstitialStep;
    const interval = setInterval(() => {
      setStepTimer((prev) => {
        if (prev <= 1) {
          clearInterval(interval);
          setIsWaitingStepTimer(false);
          const nextStep = stepIdx + 1;
          if (nextStep >= totalInterstitialSteps) {
            setCurrentImg(UNLOCKED_IMG);
            setBtnEnabled(true);
            setImageClicked(true);
          } else {
            setCurrentInterstitialStep(nextStep);
          }
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  };

  const continueToFreeLink = async () => {
    setRedirecting(true);
    try {
      const res = await fetch(`/api/links/${link.slug}/ad`);
      if (!res.ok) {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: "Link không tồn tại", background: "#16161a", color: "#fff" });
        }
        return;
      }
      const data = await res.json();
      const originalUrl: string = data?.link?.originalUrl;
      if (!originalUrl) {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: "Không tìm thấy link gốc", background: "#16161a", color: "#fff" });
        }
        return;
      }

      // Bọc URL theo thứ tự cấu hình:
      // link cuối = adUrl[n-1] + (adUrl[n-2] + ... + (adUrl[0] + originalUrl) ...)
      // Bắt đầu từ adUrl[0]: wrapped = adUrl[0] + originalUrl
      // Rồi: wrapped = adUrl[1] + wrapped
      // ... cho đến hết
      let wrappedUrl = originalUrl;
      for (const adUrl of wrapAdUrls) {
        const trimmed = adUrl.trim();
        if (!trimmed) continue;
        if (trimmed.endsWith("=")) {
          wrappedUrl = trimmed + encodeURIComponent(wrappedUrl);
        } else if (trimmed.endsWith("&") || trimmed.endsWith("?")) {
          wrappedUrl = trimmed + "url=" + encodeURIComponent(wrappedUrl);
        } else if (trimmed.includes("?")) {
          wrappedUrl = trimmed + "&url=" + encodeURIComponent(wrappedUrl);
        } else {
          wrappedUrl = trimmed + (trimmed.endsWith("/") ? "?url=" : "/?url=") + encodeURIComponent(wrappedUrl);
        }
      }

      window.location.href = wrappedUrl;
    } catch {
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "error", title: "Lỗi", text: "Lỗi kết nối", background: "#16161a", color: "#fff" });
      }
    } finally {
      setRedirecting(false);
    }
  };



  const handleBuy = async () => {
    if (!isLoggedIn) {
      if (typeof Swal !== "undefined") {
        Swal.fire({
          icon: "warning",
          title: "Yêu cầu đăng nhập",
          text: "Bạn cần đăng nhập để thực hiện giao dịch này!",
          confirmButtonText: "Đến trang Đăng nhập",
          background: "#16161a",
          color: "#fff",
          confirmButtonColor: "#6366f1",
        }).then(() => {
          router.push(`/login?callbackUrl=/l/${link.slug}`);
        });
      } else {
        router.push(`/login?callbackUrl=/l/${link.slug}`);
      }
      return;
    }

    if (userBalance < link.price) {
      if (typeof Swal !== "undefined") {
        Swal.fire({
          icon: "error",
          title: "Số dư không đủ",
          text: `Bạn cần nạp thêm ít nhất ${formatNum(link.price - userBalance)}đ để mua link này.`,
          confirmButtonText: "Đến trang Nạp tiền",
          background: "#16161a",
          color: "#fff",
          confirmButtonColor: "#ffc107",
        }).then(() => {
          router.push("/deposit");
        });
      } else {
        router.push("/deposit");
      }
      return;
    }

    if (typeof Swal !== "undefined") {
      const result = await Swal.fire({
        title: "Thanh toán link VIP",
        html: `Bạn sẽ bị trừ <b style="color:#ffc107">${formatNum(link.price)}đ</b> để nhận link gốc ngay lập tức.`,
        icon: "info",
        showCancelButton: true,
        confirmButtonColor: "#ffc107",
        cancelButtonColor: "#444",
        confirmButtonText: "Thanh toán ngay",
        cancelButtonText: "Hủy",
        color: "#fff",
        background: "#16161a",
      });
      if (!result.isConfirmed) return;
    }

    setBuying(true);
    try {
      const res = await fetch("/api/links/purchase", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ slug: link.slug }),
      });
      const data = await res.json();
      if (!res.ok) {
        if (typeof Swal !== "undefined") {
          Swal.fire({ icon: "error", title: "Lỗi", text: data.error || "Mua link thất bại", background: "#16161a", color: "#fff" });
        }
        return;
      }
      setOriginalUrl(data.originalUrl);
      if (typeof Swal !== "undefined") {
        await Swal.fire({
          icon: "success",
          title: data.alreadyPurchased ? "Đã sở hữu link!" : "Thanh toán thành công!",
          text: data.alreadyPurchased
            ? "Bạn đã mua link này trong vòng 12h. Đang mở link gốc..."
            : `Đã trừ ${data.amount ? data.amount.toLocaleString("vi-VN") + "đ" : ""}. Đang mở link gốc...`,
          timer: 1800,
          showConfirmButton: false,
          background: "#16161a",
          color: "#fff",
        });
      }
      window.open(data.originalUrl, "_blank");
    } finally {
      setBuying(false);
    }
  };

  const copyCode = () => {
    navigator.clipboard.writeText(link.slug).then(() => {
      if (typeof Swal !== "undefined") {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "success",
          title: "Đã copy mã link!",
          showConfirmButton: false,
          timer: 1500,
          background: "#16161a",
          color: "#fff",
        });
      }
    });
  };

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

        .lp-wrap {
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          padding: 20px 16px;
        }

        .lp-container {
          width: 100%;
          max-width: 680px;
        }

        .card-gaming {
          background: rgba(22, 22, 26, 0.95);
          border: 1px solid rgba(26, 213, 250, 0.3);
          border-radius: 28px;
          padding: 48px 36px;
          width: 100%;
          box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 25px rgba(26, 213, 250, 0.1);
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
          font-size: 1.5rem;
          margin-bottom: 1rem;
        }

        .link-code-box {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 12px;
          padding: 14px 22px;
          background: rgba(255,255,255,0.04);
          border: 1px dashed rgba(26, 213, 250, 0.5);
          border-radius: 16px;
          margin-bottom: 28px;
          flex-wrap: wrap;
        }

        .link-code-label {
          color: #a0a0a0;
          font-size: 0.85rem;
          text-transform: uppercase;
          letter-spacing: 1px;
        }

        .link-code-value {
          font-weight: 700;
          color: #1ad5fa;
          letter-spacing: 3px;
          font-size: 1.1rem;
        }

        .btn-copy-code {
          background: transparent;
          border: 1px solid #1ad5fa;
          color: #1ad5fa;
          border-radius: 8px;
          padding: 5px 14px;
          font-size: 0.82rem;
          cursor: pointer;
          transition: 0.2s;
        }
        .btn-copy-code:hover { background: rgba(26,213,250,0.12); }

        .options-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 22px;
          margin-bottom: 8px;
        }
        .options-grid.single-col {
          grid-template-columns: 1fr;
          max-width: 360px;
          margin-left: auto;
          margin-right: auto;
        }
        @media (max-width: 480px) {
          .options-grid { grid-template-columns: 1fr; }
          .card-gaming { padding: 32px 20px; }
        }

        .option-box {
          background: rgba(255,255,255,0.02);
          border: 1px solid rgba(255,255,255,0.08);
          border-radius: 24px;
          padding: 34px 22px;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          position: relative;
          overflow: hidden;
          transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
          min-height: 280px;
        }

        .option-box.free-box {
          border: 1.5px solid #1ad5fa;
          background: rgba(22, 24, 30, 0.95);
          box-shadow: 0 0 20px rgba(26, 213, 250, 0.12);
        }
        .option-box.free-box:hover {
          transform: translateY(-6px);
          border-color: #1ad5fa;
          box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 30px rgba(26, 213, 250, 0.3);
        }

        .option-icon-cyan {
          font-size: 2.8rem;
          color: #1ad5fa;
          margin-bottom: 10px;
          line-height: 1;
        }

        .option-title-cyan {
          color: #1ad5fa;
          font-weight: 800;
          font-size: 1.5rem;
          letter-spacing: 1.5px;
          margin-bottom: 14px;
        }

        .layer-badge-cyan {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 7px 20px;
          background: rgba(17, 45, 55, 0.8);
          border: 1px solid rgba(26, 213, 250, 0.3);
          border-radius: 20px;
          color: #1ad5fa;
          font-size: 0.9rem;
          font-weight: 600;
          margin-bottom: 18px;
        }

        .free-desc-text {
          font-size: 1.05rem;
          color: #ffffff;
          font-weight: 600;
          margin-bottom: 22px;
        }
        .free-desc-text strong {
          font-weight: 900;
          margin-right: 4px;
        }

        .option-box.buy-box {
          border-color: rgba(255, 193, 7, 0.35);
        }
        .option-box.buy-box:hover {
          transform: translateY(-8px);
          background: rgba(255, 193, 7, 0.04);
          border-color: #ffc107;
          box-shadow: 0 10px 30px rgba(0,0,0,0.3), 0 0 15px rgba(255,193,7,0.15);
        }

        .option-icon { font-size: 2.8rem; display: block; margin-bottom: 10px; }
        .option-title { font-weight: 700; font-size: 1.3rem; margin-bottom: 8px; }
        .option-title.free-title { color: #1ad5fa; }
        .option-title.buy-title { color: #ffc107; }

        .layer-badge {
          display: inline-block;
          padding: 4px 14px;
          background: rgba(26, 213, 250, 0.1);
          border-radius: 8px;
          color: #1ad5fa;
          font-size: 0.82rem;
          margin-bottom: 10px;
        }
        .layer-badge.buy-badge {
          background: rgba(255,193,7,0.1);
          color: #ffc107;
        }

        .price-tag {
          font-size: 1.8rem;
          font-weight: 800;
          color: #fff;
          text-shadow: 0 0 12px rgba(255,255,255,0.2);
        }

        .btn-custom {
          width: 100%;
          padding: 15px;
          font-weight: 800;
          border-radius: 14px;
          text-transform: uppercase;
          border: none;
          letter-spacing: 1px;
          font-size: 0.95rem;
          cursor: pointer;
          transition: all 0.25s;
          margin-top: 14px;
        }


        .btn-free {
          background: transparent;
          border: 2px solid #1ad5fa;
          color: #1ad5fa;
          border-radius: 14px;
          font-weight: 800;
          font-size: 0.9rem;
        }
        .btn-free:hover {
          background: rgba(26, 213, 250, 0.15);
          color: #1ad5fa;
          box-shadow: 0 0 20px rgba(26,213,250,0.4);
        }
        .btn-free:disabled { opacity: 0.4; cursor: not-allowed; }

        .btn-buy {
          background: linear-gradient(45deg, #ffc107, #ff9800);
          color: #000;
          box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        }
        .btn-buy:hover {
          transform: scale(1.02);
          box-shadow: 0 6px 20px rgba(255,193,7,0.5);
          filter: brightness(1.08);
        }
        .btn-buy:disabled { opacity: 0.6; cursor: not-allowed; filter: none; transform: none; }

        /* Loader */
        .loader-wrap {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          min-height: 250px;
        }
        .spinner-cyber {
          width: 50px;
          height: 50px;
          border: 3px solid rgba(26, 213, 250, 0.15);
          border-top-color: #1ad5fa;
          border-radius: 50%;
          animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
          to { transform: rotate(360deg); }
        }

        /* Interstitial */
        .interstitial-img-wrap {
          position: relative;
          border-radius: 16px;
          overflow: hidden;
          cursor: pointer;
          margin: 20px 0;
          border: 2px solid rgba(26, 213, 250, 0.4);
          transition: all 0.25s;
        }
        .interstitial-img-wrap:hover {
          border-color: #1ad5fa;
          box-shadow: 0 0 20px rgba(26, 213, 250, 0.3);
        }
        .interstitial-img-wrap img {
          width: 100%;
          max-height: 280px;
          object-fit: contain;
          display: block;
          background: rgba(0,0,0,0.4);
        }
        .tap-hint {
          position: absolute;
          bottom: 0;
          left: 0; right: 0;
          background: rgba(0,0,0,0.8);
          color: #1ad5fa;
          padding: 10px;
          font-size: 0.85rem;
          font-weight: 700;
          text-align: center;
          backdrop-filter: blur(4px);
        }

        .btn-continue {
          width: 100%;
          margin-top: 16px;
        }

        @keyframes pulse-glow {
          from { box-shadow: 0 0 10px rgba(26,213,250,0.3); }
          to   { box-shadow: 0 0 24px rgba(26,213,250,0.7); }
        }
        @keyframes fadeInUp {
          from { opacity:0; transform: translateY(16px); }
          to   { opacity:1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.45s ease; }
      `}</style>

      {/* LOADING SCREEN */}
      {screen === "loading" && (
        <div className="lp-wrap fade-in-up">
          <div className="lp-container">
            <div className="card-gaming loader-wrap">
              <div className="spinner-cyber" />
              <p style={{ marginTop: 18, color: "#a0a0a0", fontSize: "0.9rem", fontWeight: 600 }}>
                Đang kiểm tra kết nối an toàn...
              </p>
            </div>
          </div>
        </div>
      )}

      {/* MAIN SELECTION SCREEN */}
      {screen === "main" && !originalUrl && (
        <div className="lp-wrap fade-in-up">
          <div className="lp-container">
            <div className="card-gaming">
              {/* Link title above card */}
              {link.title && (
                <div style={{ color: "#a0a0a0", fontSize: "0.9rem", marginBottom: 12 }}>
                  {link.title}
                </div>
              )}

              <h3 className="title-glitch">CHỌN PHƯƠNG THỨC</h3>
              <p style={{ color: "#a0a0a0", marginBottom: 24, fontSize: "0.88rem" }}>
                Chọn phương thức phù hợp để truy cập liên kết bảo mật
              </p>

              {/* Code box */}
              <div className="link-code-box">
                <span className="link-code-label">MÃ LINK:</span>
                <span className="link-code-value">{link.slug}</span>
                <button className="btn-copy-code" onClick={copyCode}>
                  Copy
                </button>
              </div>

              {/* Options */}
              <div className={`options-grid${freeLinkEnabled ? "" : " single-col"}`}>
                {/* Free option — chỉ hiện khi freeLinkEnabled */}
                {freeLinkEnabled && (
                  <div className="option-box free-box">
                    <div style={{ display: "flex", flexDirection: "column", alignItems: "center" }}>
                      <div className="option-icon-cyan">⌛</div>
                      <div className="option-title-cyan">MIỄN PHÍ</div>

                      <div className="layer-badge-cyan">
                        <i className="bi bi-layers" style={{ marginRight: 6 }} />
                        {totalAdLayers > 0 ? `${totalAdLayers} lớp bảo vệ` : "Miễn phí"}
                      </div>

                      <div className="free-desc-text">
                        <strong>VN</strong> Vượt link mã
                      </div>
                    </div>
                    <button className="btn-custom btn-free" onClick={startFreeFlow}>
                      BẮT ĐẦU VƯỢT LINK
                    </button>
                  </div>
                )}

                {/* Buy option */}
                <div className="option-box buy-box">
                  <div>
                    <span className="option-icon">⚡</span>
                    <div className="option-title buy-title">MUA LINK</div>
                    <div className="layer-badge buy-badge">
                      <i className="bi bi-lightning-charge-fill" /> Bỏ qua quảng cáo
                    </div>
                    {link.price > 0 ? (
                      <div className="price-tag" style={{ marginTop: 8 }}>
                        {formatNum(link.price)}đ
                      </div>
                    ) : (
                      <div className="price-tag" style={{ marginTop: 8, fontSize: "1.2rem", color: "#10b981" }}>
                        Miễn phí
                      </div>
                    )}
                    {isLoggedIn && (
                      <p style={{ fontSize: "0.75rem", color: "#a0a0a0", marginTop: 6 }}>
                        Số dư: <span style={{ color: userBalance >= link.price ? "#10b981" : "#ef4444", fontWeight: 600 }}>
                          {formatNum(userBalance)}đ
                        </span>
                      </p>
                    )}
                  </div>
                  <button
                    className="btn-custom btn-buy"
                    onClick={handleBuy}
                    disabled={buying}
                  >
                    {buying ? (
                      <span style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
                        <span style={{ width: 14, height: 14, border: "2px solid rgba(0,0,0,0.3)", borderTopColor: "#000", borderRadius: "50%", display: "inline-block", animation: "spin 0.7s linear infinite" }} />
                        Đang xử lý...
                      </span>
                    ) : "MUA NGAY"}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* INTERSTITIAL SCREEN - click ad to unlock */}
      {screen === "interstitial" && (
        <div className="lp-wrap fade-in-up">
          <div className="lp-container">
            <div className="card-gaming">
              <h3 className="title-glitch">XÁC NHẬN ĐỂ NHẬN LINK</h3>
              <p style={{ color: "#a0a0a0", marginBottom: 20, fontSize: "0.88rem" }}>
                Để tránh spam vui lòng xác minh bạn không phải Robot.
              </p>

              <div
                className="interstitial-img-wrap"
                onClick={onImageClick}
                style={{ pointerEvents: (btnEnabled || isWaitingStepTimer) ? "none" : "auto" }}
              >
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={currentImg} alt="Xác minh" />
                <div className="tap-hint">
                  {btnEnabled
                    ? "Đã mở khóa! Bấm nút bên dưới ↓"
                    : isWaitingStepTimer
                      ? `Đang xử lý bước ${currentInterstitialStep + 1}/${totalInterstitialSteps}... vui lòng đợi ${stepTimer}s`
                      : totalInterstitialSteps > 0
                        ? `Nhấn vào ảnh để xem quảng cáo (Bước ${currentInterstitialStep + 1}/${totalInterstitialSteps})`
                        : "Nhấn vào ảnh để mở khóa"}
                </div>
              </div>

              <button
                className="btn-custom btn-free btn-continue"
                disabled={!btnEnabled || redirecting}
                onClick={continueToFreeLink}
              >
                {redirecting ? "ĐANG CHUYỂN HƯỚNG..." : "TIẾP TỤC →"}
              </button>


              <button
                onClick={() => setScreen("main")}
                style={{ background: "none", border: "none", color: "#666", cursor: "pointer", marginTop: 12, fontSize: "0.82rem", display: "block", margin: "12px auto 0" }}
              >
                ← Quay lại
              </button>
            </div>
          </div>
        </div>
      )}

      {/* REVEALED - after purchase */}
      {originalUrl && (
        <div className="lp-wrap fade-in-up">
          <div className="lp-container">
            <div className="card-gaming">
              <div style={{ fontSize: "3rem", marginBottom: 12 }}>✅</div>
              <h3 className="title-glitch">THÀNH CÔNG!</h3>
              <p style={{ color: "#10b981", fontWeight: 600, marginBottom: 20 }}>
                Thanh toán thành công! Link gốc đã sẵn sàng.
              </p>
              <a
                href={originalUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="btn-custom btn-buy"
                style={{ display: "block", textDecoration: "none" }}
              >
                🔗 MỞ LINK GỐC →
              </a>
            </div>
          </div>
        </div>
      )}

    </>
  );
}
