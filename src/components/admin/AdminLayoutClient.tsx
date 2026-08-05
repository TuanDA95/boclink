"use client";

import { useState } from "react";
import AdminSidebar from "./Sidebar";
import { Menu, Zap } from "lucide-react";
import Link from "next/link";

export default function AdminLayoutClient({
  children,
}: {
  children: React.ReactNode;
}) {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div style={{ display: "flex", minHeight: "100vh", position: "relative" }}>
      {/* Sidebar — on desktop: in-flow sticky; on mobile: fixed off-canvas drawer */}
      <AdminSidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      {/* Content wrapper */}
      <div style={{ flex: 1, display: "flex", flexDirection: "column", minWidth: 0, overflow: "hidden" }}>

        {/* Mobile Top Header — hidden on desktop via CSS, visible on mobile */}
        <header
          className="admin-mobile-header"
          style={{
            height: 60,
            background: "#0d0e1a",
            borderBottom: "1px solid rgba(255,255,255,0.08)",
            padding: "0 16px",
            alignItems: "center",
            justifyContent: "space-between",
            position: "sticky",
            top: 0,
            zIndex: 100,
            flexShrink: 0,
          }}
        >
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <button
              onClick={() => setSidebarOpen(true)}
              style={{
                background: "rgba(255,255,255,0.06)",
                border: "1px solid rgba(255,255,255,0.1)",
                color: "#e2e8f0",
                padding: 8,
                borderRadius: 8,
                cursor: "pointer",
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
              }}
              aria-label="Mở menu điều hướng"
            >
              <Menu size={20} />
            </button>

            <Link href="/admin" style={{ display: "flex", alignItems: "center", gap: 8, textDecoration: "none" }}>
              <div
                style={{
                  width: 28,
                  height: 28,
                  background: "linear-gradient(135deg, #6366f1, #8b5cf6)",
                  borderRadius: 6,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >
                <Zap size={14} color="white" />
              </div>
              <span style={{ fontWeight: 700, fontSize: 16, color: "#e2e8f0" }}>Sub2S</span>
              <span
                style={{
                  fontSize: 9,
                  background: "rgba(99,102,241,0.2)",
                  color: "#818cf8",
                  padding: "2px 6px",
                  borderRadius: 4,
                  fontWeight: 600,
                  letterSpacing: "0.5px",
                }}
              >
                ADMIN
              </span>
            </Link>
          </div>
        </header>

        {/* Main page content */}
        <main style={{ flex: 1, overflowY: "auto" }}>
          <div
            className="admin-main-content"
            style={{ maxWidth: 1200, margin: "0 auto", padding: "32px 24px" }}
          >
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
