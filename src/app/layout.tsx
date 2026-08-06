import type { Metadata } from "next";
import { Inter } from "next/font/google";
import Script from "next/script";
import { getSetting } from "@/lib/settings";
import ChatWidget from "@/components/ChatWidget";
import "./globals.css";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans" });

export const metadata: Metadata = {
  title: {
    template: "%s | API Key",
    default: "API Key - Nền tảng rút gọn và bọc link",
  },
  description: "Truy cập link gốc qua quảng cáo hoặc mua với giá ưu đãi",
  icons: {
    icon: "/logo.png",
    shortcut: "/logo.png",
    apple: "/logo.png",
  },
};

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const chatUrl = await getSetting("CHAT_LINK_URL", "");

  return (
    <html lang="vi" className={inter.variable}>
      <head>
        <link rel="icon" href="/logo.png" type="image/png" />
        <link rel="shortcut icon" href="/logo.png" type="image/png" />
        <link rel="apple-touch-icon" href="/logo.png" />
        <link
          rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        />
      </head>
      <body>
        {children}
        <ChatWidget initialChatUrl={chatUrl} />
        <Script
          src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
          strategy="beforeInteractive"
        />
      </body>
    </html>
  );
}
