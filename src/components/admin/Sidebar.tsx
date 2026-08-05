"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { signOut } from "next-auth/react";
import {
  LayoutDashboard,
  Link2,
  Users,
  Wallet,
  ShoppingBag,
  Settings,
  CreditCard,
  LogOut,
  Zap,
  SlidersHorizontal,
  X,
} from "lucide-react";

const navItems = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard },
  { href: "/admin/links", label: "Quản lý Link", icon: Link2 },
  { href: "/admin/users", label: "Người dùng", icon: Users },
  { href: "/admin/deposits", label: "Nạp tiền", icon: Wallet },
  { href: "/admin/purchases", label: "Lịch sử mua link", icon: ShoppingBag },
  { href: "/admin/sepay", label: "Cấu hình SePay", icon: Settings },
  { href: "/admin/scratch-card", label: "Cấu hình Thẻ Cào", icon: CreditCard },
  { href: "/admin/settings", label: "Cấu hình hệ thống", icon: SlidersHorizontal },
];

interface SidebarProps {
  isOpen?: boolean;
  onClose?: () => void;
}

export default function AdminSidebar({ isOpen, onClose }: SidebarProps) {
  const pathname = usePathname();

  return (
    <>
      {/* Mobile Backdrop */}
      {isOpen && (
        <div
          onClick={onClose}
          className="admin-mobile-backdrop"
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(0, 0, 0, 0.65)",
            backdropFilter: "blur(4px)",
            zIndex: 40,
          }}
        />
      )}

      <aside
        className={`admin-sidebar ${isOpen ? "open" : ""}`}
        style={{
          width: 250,
          background: "#0d0e1a",
          borderRight: "1px solid rgba(255,255,255,0.08)",
          display: "flex",
          flexDirection: "column",
          padding: "24px 16px",
        }}
      >
        {/* Logo & Close Button */}
        <div style={{ marginBottom: 32, paddingLeft: 8, display: "flex", alignItems: "center", justifyContent: "space-between" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <div
              style={{
                width: 32,
                height: 32,
                background: "linear-gradient(135deg, #6366f1, #8b5cf6)",
                borderRadius: 8,
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
              }}
            >
              <Zap size={16} color="white" />
            </div>
            <span style={{ fontWeight: 700, fontSize: 18, color: "#e2e8f0" }}>
              Sub2S
            </span>
            <span
              style={{
                fontSize: 10,
                background: "rgba(99,102,241,0.2)",
                color: "#818cf8",
                padding: "2px 6px",
                borderRadius: 4,
                fontWeight: 600,
              }}
            >
              ADMIN
            </span>
          </div>

          {onClose && (
            <button
              onClick={onClose}
              style={{
                background: "rgba(255,255,255,0.06)",
                border: "1px solid rgba(255,255,255,0.1)",
                color: "#94a3b8",
                cursor: "pointer",
                borderRadius: 8,
                padding: 6,
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
              }}
            >
              <X size={18} />
            </button>
          )}
        </div>

        {/* Nav */}
        <nav style={{ flex: 1, display: "flex", flexDirection: "column", gap: 4, overflowY: "auto" }}>
          {navItems.map(({ href, label, icon: Icon }) => {
            const isActive =
              href === "/admin" ? pathname === "/admin" : pathname.startsWith(href);
            return (
              <Link
                key={href}
                href={href}
                onClick={onClose}
                className={`sidebar-link${isActive ? " active" : ""}`}
              >
                <Icon size={16} />
                {label}
              </Link>
            );
          })}
        </nav>

        {/* Logout */}
        <button
          onClick={() => signOut({ callbackUrl: "/login" })}
          className="sidebar-link"
          style={{ border: "none", background: "none", cursor: "pointer", width: "100%", textAlign: "left", marginTop: 16 }}
        >
          <LogOut size={16} />
          Đăng xuất
        </button>
      </aside>
    </>
  );
}

