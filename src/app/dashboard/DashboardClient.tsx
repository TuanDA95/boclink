"use client";

import { useState } from "react";
import { signOut } from "next-auth/react";

declare const Swal: any;

interface UserData {
  id: string;
  name: string | null;
  email: string;
  balance: number;
  apiToken: string;
  role: string;
}

interface LinkItem {
  id: string;
  slug: string;
  originalUrl: string;
  title: string;
  clicks: number;
  isActive: boolean;
  createdAt: string;
}

interface DepositItem {
  id: string;
  amount: number;
  declaredValue?: number | null;
  sepayGateway?: string | null;
  cardTelco?: string | null;
  method: string;
  status: string;
  paymentContent: string | null;
  createdAt: string;
}


interface PurchaseItem {
  id: string;
  amount: number;
  createdAt: string;
  link: {
    slug: string;
    title: string;
    originalUrl: string;
    price: number;
  };
}

interface Props {
  user: UserData;
  links: LinkItem[];
  deposits: DepositItem[];
  purchases: PurchaseItem[];
  domain: string;
}

type DepositTab = "bank" | "card";
type MainTab = "purchases" | "deposits";


const DEPOSIT_METHODS_LABEL: Record<string, string> = {
  BANK_TRANSFER: "Ngân hàng",
  SCRATCH_CARD: "Thẻ cào",
  CRYPTO: "Tiền điện tử",
};

export default function DashboardClient({ user, links, deposits, purchases, domain }: Props) {
  const [depositTab, setDepositTab] = useState<DepositTab>("bank");
  const [mainTab, setMainTab] = useState<MainTab>("purchases");
  const [bankAmount, setBankAmount] = useState("");
  const [bankStep, setBankStep] = useState<"input" | "qr">("input");
  const [qrData, setQrData] = useState<any>(null);
  const [generatingQR, setGeneratingQR] = useState(false);
  const [quickLink, setQuickLink] = useState("");
  const [buying, setBuying] = useState(false);
  const [localBalance, setLocalBalance] = useState(user.balance);

  const [cardTelco, setCardTelco] = useState("VIETTEL");
  const [cardAmount, setCardAmount] = useState("10000");
  const [cardSerial, setCardSerial] = useState("");
  const [cardCode, setCardCode] = useState("");
  const [cardLoading, setCardLoading] = useState(false);

  const toast = (msg: string, icon = "success") => {
    if (typeof Swal !== "undefined") {
      Swal.fire({ toast: true, position: "top-end", icon, title: msg, showConfirmButton: false, timer: 1800 });
    }
  };

  const copyText = (text: string, label: string) => {
    navigator.clipboard.writeText(text).then(() => toast(`Đã copy ${label}`));
  };

  const generateQR = async () => {
    const amount = parseInt(bankAmount);
    if (!amount || amount < 20000) {
      if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Thông báo", text: "Số tiền tối thiểu nạp là 20.000đ" });
      return;
    }
    setGeneratingQR(true);
    try {
      const res = await fetch("/api/deposit/bank", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ amount }),
      });
      const data = await res.json();
      if (!res.ok) {
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", text: data.error || "Lỗi hệ thống" });
        return;
      }
      setQrData(data);
      setBankStep("qr");
      startPaymentCheck(data.depositId);
    } catch {
      if (typeof Swal !== "undefined") Swal.fire({ icon: "error", text: "Lỗi kết nối máy chủ!" });
    } finally {
      setGeneratingQR(false);
    }
  };

  const startPaymentCheck = (depositId: string) => {
    const interval = setInterval(async () => {
      try {
        const res = await fetch(`/api/deposit/${depositId}/status`);
        if (!res.ok) return;
        const data = await res.json();
        if (data.status === "SUCCESS") {
          clearInterval(interval);
          let timerInterval: any;
          if (typeof Swal !== "undefined") {
            Swal.fire({
              title: "Thanh toán thành công! 🎉",
              html: "Đang cộng tiền vào tài khoản <b></b> giây...",
              icon: "success",
              timer: 5000,
              timerProgressBar: true,
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
                const b = Swal.getPopup().querySelector("b");
                timerInterval = setInterval(() => { b.textContent = `${Math.ceil(Swal.getTimerLeft() / 1000)}`; }, 100);
              },
              willClose: () => clearInterval(timerInterval),
            }).then(() => window.location.reload());
          }
        } else if (data.status === "CANCELLED") {
          clearInterval(interval);
          if (typeof Swal !== "undefined") {
            Swal.fire({ icon: "warning", title: "Giao dịch hết hạn", text: "Vui lòng tạo mã QR mới để tiếp tục nạp tiền." });
          }
          setBankStep("input");
          setBankAmount("");
          setQrData(null);
        }
      } catch {}
    }, 3000);
  };

  const handleQuickBuy = () => {
    const input = quickLink.trim();
    if (!input) {
      if (typeof Swal !== "undefined") Swal.fire("Thông báo", "Vui lòng nhập Link hoặc Mã link!", "warning");
      return;
    }
    const code = input.split("/").pop() || input;
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Xác nhận mua?",
        text: "Hệ thống sẽ trừ tiền vào số dư của bạn.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Đồng ý",
        cancelButtonText: "Hủy",
        confirmButtonColor: "#0d6efd",
      }).then((result: any) => {
        if (result.isConfirmed) executeBuy(code);
      });
    }
  };

  const executeBuy = async (code: string) => {
    setBuying(true);
    try {
      const res = await fetch("/api/links/purchase", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ slug: code }),
      });
      const data = await res.json();
      if (res.ok) {
        if (typeof Swal !== "undefined") {
          Swal.fire({
            icon: "success",
            title: data.alreadyPurchased ? "Đã sở hữu link!" : "Thanh toán thành công!",
            text: data.alreadyPurchased
              ? "Bạn đã mua link này trong vòng 12h. Đang mở link..."
              : `Đã trừ ${data.amount ? data.amount.toLocaleString("vi-VN") + "đ" : ""}. Đang mở link...`,
            timer: 2000,
            showConfirmButton: false,
          }).then(() => {
            if (data.originalUrl) window.open(data.originalUrl, "_blank");
            window.location.reload();
          });
        }
      } else {
        if (typeof Swal !== "undefined") Swal.fire("Thất bại", data.error || "Lỗi mua link", "error");
      }
    } finally {
      setBuying(false);
    }
  };

  const handleCardDeposit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!cardSerial || !cardCode) {
      if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", text: "Vui lòng nhập đầy đủ thông tin thẻ" });
      return;
    }
    setCardLoading(true);
    try {
      const res = await fetch("/api/deposit/card", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ telco: cardTelco, amount: parseInt(cardAmount), serial: cardSerial, code: cardCode }),
      });
      const data = await res.json();
      if (res.ok) {
        if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Đã gửi thẻ!", text: data.message || "Thẻ đang được xử lý, vui lòng chờ..." });
        setCardSerial(""); setCardCode("");
      } else {
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Lỗi", text: data.error || "Gửi thẻ thất bại" });
      }
    } finally {
      setCardLoading(false);
    }
  };

  const handleLogout = () => {
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Bạn có chắc chắn?",
        text: "Bạn sẽ đăng xuất khỏi hệ thống!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Đăng xuất",
        cancelButtonText: "Hủy",
      }).then((result: any) => {
        if (result.isConfirmed) signOut({ callbackUrl: "/login" });
      });
    } else {
      signOut({ callbackUrl: "/login" });
    }
  };

  const username = user.name || user.email.split("@")[0];

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700;800&display=swap');

        body {
          font-family: 'Lexend', sans-serif !important;
          background: #f0f2f5 !important;
          color: #2d3436 !important;
          margin: 0 !important;
          padding: 0 !important;
        }

        /* ---- NAVBAR ---- */
        .db-navbar {
          background: #fff;
          border-bottom: 1px solid #e9ecef;
          padding: 0 16px;
          display: flex;
          align-items: center;
          justify-content: space-between;
          height: 56px;
          position: sticky;
          top: 0;
          z-index: 100;
          box-shadow: 0 2px 10px rgba(0,0,0,0.06);
          gap: 8px;
        }

        .db-brand {
          font-weight: 800;
          font-size: 1.2rem;
          color: #0d6efd;
          text-decoration: none;
          letter-spacing: 0.5px;
          flex-shrink: 0;
        }

        .db-nav-right {
          display: flex;
          align-items: center;
          gap: 6px;
          flex-wrap: nowrap;
          overflow: hidden;
        }

        .balance-badge {
          background: #e8f4fd;
          border: 1px solid #bee3f8;
          color: #0d6efd;
          padding: 5px 10px;
          border-radius: 20px;
          font-size: 0.78rem;
          font-weight: 700;
          cursor: pointer;
          transition: 0.2s;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 4px;
          white-space: nowrap;
          flex-shrink: 0;
        }
        .balance-badge:hover { background: #d0e9fa; }

        .btn-nav {
          padding: 5px 10px;
          border-radius: 8px;
          font-size: 0.78rem;
          font-weight: 600;
          border: 1px solid #dee2e6;
          cursor: pointer;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 4px;
          background: #fff;
          color: #555;
          transition: 0.2s;
          font-family: 'Lexend', sans-serif;
          white-space: nowrap;
          flex-shrink: 0;
        }
        .btn-nav:hover { background: #f8f9fa; border-color: #aaa; color: #222; }
        .btn-nav-danger { color: #dc3545; border-color: #f5c6cb; background: #fff5f5; }
        .btn-nav-danger:hover { background: #fce8e8; color: #b02a37; }
        .btn-nav-primary { color: #0d6efd; border-color: #b6d4fe; background: #e7f1ff; }
        .btn-nav-primary:hover { background: #d0e3ff; }

        /* Trên màn hình rất nhỏ: ẩn text button, chỉ hiện icon */
        @media (max-width: 480px) {
          .db-navbar { padding: 0 10px; height: 52px; }
          .db-brand { font-size: 1rem; }
          .balance-badge { padding: 4px 8px; font-size: 0.72rem; gap: 3px; }
          .btn-nav { padding: 5px 8px; }
          .btn-nav .nav-label { display: none; }
        }

        /* ---- MAIN LAYOUT ---- */
        .db-main {
          max-width: 1100px;
          margin: 0 auto;
          padding: 16px 12px 60px;
        }
        @media (min-width: 640px) {
          .db-main { padding: 24px 16px 60px; }
        }

        /* Mobile-first: single column, then 2-col on wider */
        .db-row {
          display: grid;
          grid-template-columns: 1fr;
          gap: 16px;
          align-items: start;
        }
        @media (min-width: 800px) {
          .db-row { grid-template-columns: 320px 1fr; gap: 20px; }
        }
        @media (min-width: 1024px) {
          .db-row { grid-template-columns: 340px 1fr; }
        }

        /* ---- CARDS ---- */
        .db-card {
          background: #fff;
          border: none;
          border-radius: 16px;
          box-shadow: 0 4px 20px rgba(0,0,0,0.05);
          overflow: hidden;
          margin-bottom: 0;
        }

        .db-card-header {
          padding: 12px 16px;
          font-weight: 700;
          font-size: 0.78rem;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          border-bottom: 1px solid #f1f3f5;
          color: #495057;
          display: flex;
          align-items: center;
          gap: 8px;
        }
        .db-card-body { padding: 16px; }

        @media (min-width: 640px) {
          .db-card-header { padding: 14px 20px; font-size: 0.82rem; }
          .db-card-body { padding: 20px; }
        }

        /* ---- DEPOSIT TABS ---- */
        .dep-tabs {
          display: flex;
          border-bottom: 1px solid #f1f3f5;
          background: #fafbfc;
        }
        .dep-tab {
          flex: 1;
          padding: 12px;
          text-align: center;
          font-size: 0.78rem;
          font-weight: 700;
          cursor: pointer;
          background: transparent;
          border: none;
          border-bottom: 2px solid transparent;
          color: #868e96;
          transition: 0.2s;
          font-family: 'Lexend', sans-serif;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }
        .dep-tab.active {
          color: #0d6efd;
          border-bottom-color: #0d6efd;
          background: #fff;
        }
        .dep-tab:hover:not(.active) { color: #495057; background: #f1f3f5; }

        /* ---- FORM ---- */
        .db-label {
          font-size: 0.75rem;
          font-weight: 700;
          color: #868e96;
          margin-bottom: 6px;
          display: block;
          text-transform: uppercase;
          letter-spacing: 0.3px;
        }
        .db-input {
          width: 100%;
          background: #f8f9fa;
          border: 1px solid #e9ecef;
          border-radius: 10px;
          padding: 10px 12px;
          color: #2d3436;
          font-size: 0.9rem;
          outline: none;
          font-family: 'Lexend', sans-serif;
          box-sizing: border-box;
          transition: 0.2s;
        }
        .db-input:focus {
          border-color: #0d6efd;
          background: #fff;
          box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        }
        .db-select {
          width: 100%;
          background: #f8f9fa;
          border: 1px solid #e9ecef;
          border-radius: 10px;
          padding: 10px 12px;
          color: #2d3436;
          font-size: 0.88rem;
          outline: none;
          font-family: 'Lexend', sans-serif;
          box-sizing: border-box;
          cursor: pointer;
          transition: 0.2s;
        }
        .db-select:focus { border-color: #0d6efd; background: #fff; }

        .preview-amount {
          font-size: 1.2rem;
          font-weight: 800;
          color: #0d6efd;
          text-align: center;
          min-height: 2rem;
          padding: 6px;
        }

        .btn-primary-full {
          width: 100%;
          padding: 12px;
          background: #0d6efd;
          border: none;
          border-radius: 10px;
          color: #fff;
          font-weight: 700;
          font-size: 0.88rem;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          cursor: pointer;
          font-family: 'Lexend', sans-serif;
          transition: 0.2s;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }
        .btn-primary-full:hover { background: #0a58ca; }
        .btn-primary-full:disabled { opacity: 0.65; cursor: not-allowed; }

        .btn-secondary-full {
          width: 100%;
          padding: 10px;
          background: #f8f9fa;
          border: 1px solid #dee2e6;
          border-radius: 10px;
          color: #495057;
          font-weight: 600;
          font-size: 0.85rem;
          cursor: pointer;
          font-family: 'Lexend', sans-serif;
          transition: 0.2s;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          margin-top: 10px;
        }
        .btn-secondary-full:hover { background: #e9ecef; }

        /* ---- QR STEP ---- */
        .qr-img {
          width: 100%;
          max-width: 180px;
          border-radius: 12px;
          display: block;
          margin: 0 auto 14px;
          border: 1px solid #dee2e6;
        }

        .addinfo-box {
          background: #e7f5ff;
          border: 1px dashed #74c0fc;
          border-radius: 10px;
          padding: 10px 14px;
          text-align: center;
          font-weight: 800;
          color: #1971c2;
          font-size: 0.95rem;
          letter-spacing: 2px;
          cursor: pointer;
          margin: 10px 0;
          transition: 0.2s;
          word-break: break-all;
        }
        .addinfo-box:hover { background: #d0ebff; }
        .addinfo-box small {
          display: block;
          color: #74c0fc;
          font-size: 0.7rem;
          font-weight: 400;
          letter-spacing: 0;
          margin-top: 3px;
        }

        .qr-info-row {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          padding: 8px 0;
          border-bottom: 1px solid #f1f3f5;
          font-size: 0.84rem;
          gap: 8px;
        }
        .qr-info-label { color: #868e96; flex-shrink: 0; }
        .qr-info-value {
          color: #2d3436;
          font-weight: 600;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 4px;
          transition: 0.2s;
          text-align: right;
          word-break: break-all;
        }
        .qr-info-value:hover { color: #0d6efd; }

        .pulse-badge {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          background: rgba(25,135,84,0.08);
          border: 1px solid rgba(25,135,84,0.2);
          color: #198754;
          padding: 7px 14px;
          border-radius: 20px;
          font-size: 0.8rem;
          font-weight: 700;
          margin-top: 12px;
          animation: pulse-g 1.5s ease-in-out infinite;
          width: 100%;
          justify-content: center;
          box-sizing: border-box;
        }
        .pulse-dot {
          width: 8px; height: 8px;
          background: #198754;
          border-radius: 50%;
          animation: pulse-g 1.5s ease-in-out infinite;
          flex-shrink: 0;
        }

        /* ---- STATS ROW ---- */
        .stat-card {
          background: #fff;
          border-radius: 14px;
          padding: 14px 16px;
          box-shadow: 0 2px 12px rgba(0,0,0,0.05);
          text-align: center;
        }
        .stat-label {
          font-size: 0.68rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          color: #868e96;
          margin-bottom: 4px;
        }
        .stat-value {
          font-size: 1.1rem;
          font-weight: 800;
          color: #2d3436;
        }

        /* ---- QUICK BUY ---- */
        .quickbuy-wrap {
          display: flex;
          gap: 0;
          border-radius: 10px;
          overflow: hidden;
          border: 1px solid #e9ecef;
        }
        .quickbuy-wrap .db-input {
          border: none;
          border-radius: 0;
          background: #f8f9fa;
          flex: 1;
          min-width: 0;
        }
        .quickbuy-wrap .db-input:focus {
          box-shadow: none;
          background: #fff;
          border-right: 1px solid #e9ecef;
        }
        .btn-quickbuy {
          padding: 10px 14px;
          background: #ffc107;
          border: none;
          color: #000;
          font-weight: 800;
          font-size: 0.78rem;
          text-transform: uppercase;
          cursor: pointer;
          white-space: nowrap;
          font-family: 'Lexend', sans-serif;
          transition: 0.2s;
          display: flex;
          align-items: center;
          gap: 4px;
          flex-shrink: 0;
        }
        .btn-quickbuy:hover { background: #ffca2c; }
        .btn-quickbuy:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ---- MAIN TABS ---- */
        .main-tabs {
          display: flex;
          border-bottom: 1px solid #dee2e6;
          margin-bottom: 0;
          background: #fff;
          border-radius: 16px 16px 0 0;
          overflow: hidden;
        }
        .main-tab {
          padding: 12px 14px;
          font-size: 0.75rem;
          font-weight: 700;
          cursor: pointer;
          background: none;
          border: none;
          color: #868e96;
          border-bottom: 2px solid transparent;
          transition: 0.2s;
          font-family: 'Lexend', sans-serif;
          text-transform: uppercase;
          letter-spacing: 0.3px;
          flex: 1;
          text-align: center;
        }
        @media (min-width: 640px) {
          .main-tab { padding: 13px 20px; flex: none; font-size: 0.8rem; }
        }
        .main-tab.active { color: #0d6efd; border-bottom-color: #0d6efd; }
        .main-tab:hover:not(.active) { color: #495057; background: #f8f9fa; }

        /* ---- TABLE ---- */
        .db-table-wrap {
          background: #fff;
          border-radius: 0 0 16px 16px;
          overflow: hidden;
          box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .db-table { width: 100%; border-collapse: collapse; }
        .db-table th {
          padding: 10px 12px;
          text-align: left;
          font-size: 0.7rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          color: #868e96;
          background: #f8f9fa;
          border-bottom: 1px solid #f1f3f5;
          white-space: nowrap;
        }
        .db-table td {
          padding: 11px 12px;
          font-size: 0.83rem;
          border-bottom: 1px solid #f8f9fa;
          color: #495057;
          vertical-align: middle;
        }
        @media (min-width: 640px) {
          .db-table th { padding: 11px 18px; font-size: 0.72rem; }
          .db-table td { padding: 12px 18px; font-size: 0.85rem; }
        }
        .db-table tr:hover td { background: #fdfdfe; }
        .db-table tr:last-child td { border-bottom: none; }

        /* Mobile: purchases dạng card, deposits dạng scroll ngang */
        @media (max-width: 639px) {
          .tbl-purchases { display: none !important; }
          .mobile-purchase-list { display: block; }
          .mobile-purchase-card {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            flex-direction: column;
            gap: 5px;
          }
          .mobile-purchase-card:last-child { border-bottom: none; }
          .mpc-slug {
            font-family: monospace;
            font-weight: 700;
            color: #0d6efd;
            font-size: 0.9rem;
          }
          .mpc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
          }
          .mpc-label { color: #868e96; }
          .mpc-link {
            color: #0d6efd;
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
          }
          .tbl-deposits-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
          .tbl-deposits { min-width: 560px; }
        }
        @media (min-width: 640px) {
          .mobile-purchase-list { display: none !important; }
          .tbl-purchases { display: table !important; }
        }

        .badge-success { background: rgba(25,135,84,0.1); color: #198754; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
        .badge-warning { background: rgba(255,193,7,0.12); color: #997404; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
        .badge-danger  { background: rgba(220,53,69,0.1); color: #dc3545; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }
        .badge-info    { background: rgba(13,202,240,0.1); color: #0dcaf0; padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 700; }

        .btn-copy-link {
          display: inline-flex; align-items: center; gap: 4px;
          background: transparent;
          border: 1px solid #0d6efd;
          color: #0d6efd;
          border-radius: 8px;
          padding: 4px 10px;
          font-size: 0.75rem;
          font-weight: 600;
          cursor: pointer;
          white-space: nowrap;
          transition: background 0.15s, color 0.15s, box-shadow 0.15s;
        }
        .btn-copy-link:hover {
          background: rgba(13,110,253,0.12);
          box-shadow: 0 0 8px rgba(13,110,253,0.25);
        }
        .btn-copy-link:active { transform: scale(0.97); }

        .link-slug { font-family: monospace; color: #0d6efd; font-weight: 700; font-size: 0.9rem; }
        .link-orig {
          color: #868e96;
          font-size: 0.78rem;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          max-width: 220px;
          display: block;
        }

        .empty-state {
          text-align: center;
          padding: 36px 20px;
          color: #adb5bd;
          font-size: 0.88rem;
        }

        .form-mb { margin-bottom: 14px; }
        .hint-text { font-size: 0.72rem; color: #adb5bd; margin-top: 6px; text-align: center; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-sm {
          width: 16px; height: 16px;
          border: 2px solid rgba(255,255,255,0.4);
          border-top-color: white;
          border-radius: 50%;
          display: inline-block;
          animation: spin 0.7s linear infinite;
        }
        .spinner-sm-dark {
          width: 16px; height: 16px;
          border: 2px solid rgba(0,0,0,0.15);
          border-top-color: #000;
          border-radius: 50%;
          display: inline-block;
          animation: spin 0.7s linear infinite;
        }
        @keyframes pulse-g { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }
      `}</style>

      {/* ===== NAVBAR ===== */}
      <nav className="db-navbar">
        <a href="/dashboard" className="db-brand">Sub2S</a>
        <div className="db-nav-right">
          <span
            className="balance-badge"
            onClick={() => copyText(username, "username")}
            title="Click để copy tên tài khoản"
          >
            <i className="bi bi-person-fill" /> {username}
          </span>
          <span className="balance-badge" style={{ cursor: "default" }}>
            <i className="bi bi-wallet2" /> {localBalance.toLocaleString("vi-VN")}đ
          </span>
          {user.role === "ADMIN" && (
            <a href="/admin" className="btn-nav btn-nav-primary">
              <i className="bi bi-gear-fill" /> <span className="nav-label">Admin</span>
            </a>
          )}
          <button className="btn-nav btn-nav-danger" onClick={handleLogout}>
            <i className="bi bi-box-arrow-right" /> <span className="nav-label">Đăng xuất</span>
          </button>
        </div>
      </nav>

      <main className="db-main">
        {/* ===== ROW: DEPOSIT + RIGHT ===== */}
        <div className="db-row">
          {/* LEFT: DEPOSIT */}
          <div>
            <div className="db-card">
              <div className="dep-tabs">
                <button className={`dep-tab${depositTab === "bank" ? " active" : ""}`} onClick={() => setDepositTab("bank")}>
                  <i className="bi bi-bank" /> Ngân Hàng
                </button>
                <button className={`dep-tab${depositTab === "card" ? " active" : ""}`} onClick={() => setDepositTab("card")}>
                  <i className="bi bi-credit-card" /> Thẻ Cào
                </button>
              </div>

              {/* BANK TAB */}
              {depositTab === "bank" && (
                <div className="db-card-body">
                  {bankStep === "input" ? (
                    <>
                      <div className="form-mb">
                        <label className="db-label">Số tiền muốn nạp (đ)</label>
                        <input
                          className="db-input"
                          type="number"
                          placeholder="VD: 50000"
                          min={20000}
                          value={bankAmount}
                          onChange={(e) => setBankAmount(e.target.value)}
                        />
                        <div className="preview-amount">
                          {bankAmount ? Number(bankAmount).toLocaleString("vi-VN") + " đ" : ""}
                        </div>
                      </div>
                      <button className="btn-primary-full" onClick={generateQR} disabled={generatingQR}>
                        {generatingQR ? <span className="spinner-sm" /> : <i className="bi bi-qr-code" />}
                        {generatingQR ? "Đang tạo mã QR..." : "THANH TOÁN QR"}
                      </button>
                      <p className="hint-text">Tối thiểu 20.000đ — Cộng tiền tự động</p>
                    </>
                  ) : (
                    <div>
                      {qrData?.qrCodeUrl && (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img src={qrData.qrCodeUrl} alt="QR Code" className="qr-img" />
                      )}
                      <div
                        className="addinfo-box"
                        onClick={() => copyText(qrData?.paymentContent, "nội dung CK")}
                      >
                        {qrData?.paymentContent}
                        <small><i className="bi bi-clipboard" /> Nội dung chuyển khoản — Click để copy</small>
                      </div>
                      <div>
                        {[
                          ["Ngân hàng", qrData?.bankName],
                          ["Số tài khoản", qrData?.bankAccount],
                          ["Chủ tài khoản", qrData?.bankOwner],
                          ["Số tiền", `${Number(qrData?.amount || 0).toLocaleString("vi-VN")} đ`],
                        ].map(([label, val]) => (
                          <div className="qr-info-row" key={label}>
                            <span className="qr-info-label">{label}</span>
                            <span className="qr-info-value" onClick={() => copyText(val!, label!)}>
                              {val} <i className="bi bi-clipboard" style={{ fontSize: "0.7rem" }} />
                            </span>
                          </div>
                        ))}
                      </div>
                      <div className="pulse-badge">
                        <span className="pulse-dot" />
                        Đang chờ thanh toán...
                      </div>
                      <button className="btn-secondary-full" onClick={() => { setBankStep("input"); setBankAmount(""); setQrData(null); }}>
                        <i className="bi bi-arrow-left" /> Quay lại
                      </button>
                    </div>
                  )}
                </div>
              )}

              {/* CARD TAB */}
              {depositTab === "card" && (
                <div className="db-card-body">
                  <form onSubmit={handleCardDeposit}>
                    <div className="form-mb">
                      <label className="db-label">Nhà mạng</label>
                      <select className="db-select" value={cardTelco} onChange={(e) => setCardTelco(e.target.value)}>
                        <option value="VIETTEL">Viettel</option>
                        <option value="VINAPHONE">Vinaphone</option>
                        <option value="MOBIFONE">Mobifone</option>
                        <option value="VIETNAMOBILE">Vietnamobile</option>
                        <option value="GMOBILE">Gmobile</option>
                      </select>
                    </div>
                    <div className="form-mb">
                      <label className="db-label">Mệnh giá thẻ</label>
                      <select className="db-select" value={cardAmount} onChange={(e) => setCardAmount(e.target.value)}>
                        {["10000","20000","50000","100000","200000","500000"].map(v => (
                          <option key={v} value={v}>{Number(v).toLocaleString("vi-VN")}đ</option>
                        ))}
                      </select>
                    </div>
                    <div className="form-mb">
                      <label className="db-label">Số serial</label>
                      <input className="db-input" placeholder="Nhập số serial thẻ" value={cardSerial} onChange={(e) => setCardSerial(e.target.value)} required />
                    </div>
                    <div className="form-mb">
                      <label className="db-label">Mã thẻ</label>
                      <input className="db-input" placeholder="Nhập mã thẻ" value={cardCode} onChange={(e) => setCardCode(e.target.value)} required />
                    </div>
                    <button type="submit" className="btn-primary-full" style={{ background: "#198754" }} disabled={cardLoading}>
                      {cardLoading ? <span className="spinner-sm" /> : <i className="bi bi-phone" />}
                      {cardLoading ? "Đang gửi..." : "NẠP THẺ"}
                    </button>
                    <p className="hint-text">Thẻ sai mệnh giá bị trừ phí. Xử lý trong 1–5 phút.</p>
                  </form>
                </div>
              )}
            </div>
          </div>

          {/* RIGHT: QUICK BUY + STATS */}
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            {/* Quick Buy */}
            <div className="db-card">
              <div className="db-card-header">
                <i className="bi bi-lightning-charge-fill" style={{ color: "#ffc107" }} />
                Mua Link Nhanh
              </div>
              <div className="db-card-body">
                <div className="quickbuy-wrap">
                  <input
                    className="db-input"
                    id="quickLinkInput"
                    placeholder="Nhập link hoặc mã code (VD: abc123)"
                    value={quickLink}
                    onChange={(e) => setQuickLink(e.target.value)}
                    onKeyDown={(e) => { if (e.key === "Enter") handleQuickBuy(); }}
                  />
                  <button className="btn-quickbuy btn-quick-buy" onClick={handleQuickBuy} disabled={buying}>
                    {buying ? <span className="spinner-sm-dark" /> : <i className="bi bi-cart-fill" />}
                    {buying ? "..." : "MUA LINK"}
                  </button>
                </div>
                <p className="hint-text" style={{ textAlign: "left", marginTop: 8 }}>
                  Nhập link đầy đủ hoặc chỉ mã code
                </p>
              </div>
            </div>

            {/* Stats */}
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
              {[
                { label: "Số dư", value: `${localBalance.toLocaleString("vi-VN")}đ`, color: "#0d6efd" },
                { label: "Mua 12h qua", value: purchases.length, color: "#ffc107" },
              ].map(({ label, value, color }) => (
                <div key={label} className="stat-card">
                  <div className="stat-label">{label}</div>
                  <div className="stat-value" style={{ color }}>{value}</div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* ===== BOTTOM TABS ===== */}
        <div style={{ marginTop: 24 }}>
          <div className="main-tabs">
            <button className={`main-tab${mainTab === "purchases" ? " active" : ""}`} onClick={() => setMainTab("purchases")}>
              <i className="bi bi-cart-check" /> Link đã mua (12h) ({purchases.length})
            </button>
            <button className={`main-tab${mainTab === "deposits" ? " active" : ""}`} onClick={() => setMainTab("deposits")}>
              <i className="bi bi-clock-history" /> Nạp tiền ({deposits.length})
            </button>
          </div>

          <div className="db-table-wrap">
            {/* TAB: PURCHASES */}
            {mainTab === "purchases" && (
              purchases.length === 0 ? (
                <div className="empty-state">
                  <i className="bi bi-inbox" style={{ fontSize: "2rem", display: "block", marginBottom: 8 }} />
                  Chưa có link nào được mua trong 12 giờ gần đây
                </div>
              ) : (
                <>
                  {/* Desktop table */}
                  <table className="db-table tbl-purchases">
                    <thead>
                      <tr>
                        <th>Mã code</th>
                        <th>Link gốc</th>
                        <th>Giá</th>
                        <th>Thời gian</th>
                      </tr>
                    </thead>
                    <tbody>
                      {purchases.map((p) => (
                        <tr key={p.id}>
                          <td style={{ color: "#000", fontWeight: 500, fontFamily: "monospace" }}>{p.link.slug}</td>
                          <td>
                            <a
                              href={p.link.originalUrl}
                              target="_blank"
                              rel="noopener noreferrer"
                              style={{
                                color: "#0d6efd",
                                fontWeight: 600,
                                fontSize: "0.85rem",
                                textDecoration: "none",
                                display: "inline-flex",
                                alignItems: "center",
                                gap: 4,
                              }}
                            >
                              Bấm vào đây để mở link <i className="bi bi-box-arrow-up-right" style={{ fontSize: "0.75rem" }} />
                            </a>
                          </td>
                          <td><span className="badge-warning">-{p.amount.toLocaleString("vi-VN")}đ</span></td>
                          <td style={{ color: "#868e96", fontSize: "0.78rem" }}>{new Date(p.createdAt).toLocaleString("vi-VN")}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>

                  {/* Mobile card list */}
                  <div className="mobile-purchase-list">
                    {purchases.map((p) => (
                      <div key={p.id} className="mobile-purchase-card">
                        <span className="mpc-slug">{p.link.slug}</span>
                        <div className="mpc-row">
                          <span className="mpc-label">Link</span>
                          <a
                            href={p.link.originalUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mpc-link"
                          >
                            Mở link <i className="bi bi-box-arrow-up-right" style={{ fontSize: "0.7rem" }} />
                          </a>
                        </div>
                        <div className="mpc-row">
                          <span className="mpc-label">Giá</span>
                          <span className="badge-warning">-{p.amount.toLocaleString("vi-VN")}đ</span>
                        </div>
                        <div className="mpc-row">
                          <span className="mpc-label">Thời gian</span>
                          <span style={{ color: "#868e96", fontSize: "0.78rem" }}>{new Date(p.createdAt).toLocaleString("vi-VN")}</span>
                        </div>
                      </div>
                    ))}
                  </div>
                </>
              )
            )}

            {/* TAB: DEPOSITS */}
            {mainTab === "deposits" && (
              deposits.length === 0 ? (
                <div className="empty-state">
                  <i className="bi bi-inbox" style={{ fontSize: "2rem", display: "block", marginBottom: 8 }} />
                  Chưa có lịch sử nạp tiền
                </div>
              ) : (
                <div className="tbl-deposits-wrap">
                  <table className="db-table tbl-deposits">
                    <thead>
                      <tr>
                        <th>Phương thức</th>
                        <th>Mệnh giá</th>
                        <th>Thực nhận</th>
                        <th>Cổng nạp</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                      </tr>
                    </thead>
                    <tbody>
                      {deposits.map((d) => {
                        const declared = d.declaredValue ? `${Number(d.declaredValue).toLocaleString("vi-VN")}đ` : `${Number(d.amount).toLocaleString("vi-VN")}đ`;
                        const gateway = d.sepayGateway || (d.method === "SCRATCH_CARD" ? (d.cardTelco || "Thẻ cào") : "Chuyển khoản");
                        return (
                          <tr key={d.id}>
                            <td style={{ fontWeight: 600, fontSize: "0.85rem" }}>{DEPOSIT_METHODS_LABEL[d.method] || d.method}</td>
                            <td style={{ color: "#495057", fontSize: "0.85rem" }}>{declared}</td>
                            <td><span className="badge-success">+{d.amount.toLocaleString("vi-VN")}đ</span></td>
                            <td style={{ color: "#495057", fontSize: "0.85rem" }}>{gateway}</td>
                            <td style={{ fontFamily: "monospace", fontSize: "0.82rem" }}>{d.paymentContent || "—"}</td>
                            <td>
                              <span className={
                                d.status === "SUCCESS" ? "badge-success" :
                                d.status === "PENDING" ? "badge-warning" : "badge-danger"
                              }>
                                {d.status === "SUCCESS" ? "Thành công" : d.status === "PENDING" ? "Đang xử lý" : d.status}
                              </span>
                            </td>
                            <td style={{ color: "#868e96", fontSize: "0.78rem" }}>{new Date(d.createdAt).toLocaleString("vi-VN")}</td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              )
            )}

          </div>
        </div>

      </main>
    </>
  );
}
