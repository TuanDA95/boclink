import { auth } from "@/lib/auth";
import { headers } from "next/headers";
import { redirect } from "next/navigation";
import { getSetting } from "@/lib/settings";
import SepayConfigClient from "./SepayConfigClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Cấu hình SePay - Admin" };

const SEPAY_SETTING_KEYS = [
  "SEPAY_BANK_NAME",
  "SEPAY_BANK_ACCOUNT",
  "SEPAY_BANK_OWNER",
  "SEPAY_PAYMENT_PREFIX",
  "SEPAY_WEBHOOK_SECRET",
  "SEPAY_MERCHANT_ID",
  "SEPAY_SECRET_KEY",
  "SEPAY_SANDBOX",
];

export default async function AdminSepayPage() {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") {
    redirect("/");
  }

  const headersList = await headers();
  const host = headersList.get("host") || "localhost:3000";
  const protocol = process.env.NODE_ENV === "production" ? "https" : "http";
  const webhookUrl = `${protocol}://${host}/api/webhook/sepay`;

  const initialSettings: Record<string, string> = {};

  for (const key of SEPAY_SETTING_KEYS) {
    initialSettings[key] = await getSetting(
      key,
      key === "SEPAY_BANK_NAME"
        ? "MBBank"
        : key === "SEPAY_PAYMENT_PREFIX"
        ? "SUB2S"
        : key === "SEPAY_SANDBOX"
        ? "false"
        : ""
    );
  }

  return <SepayConfigClient initialSettings={initialSettings} webhookUrl={webhookUrl} />;
}
