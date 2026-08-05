import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import { getSetting } from "@/lib/settings";
import ScratchCardConfigClient from "./ScratchCardConfigClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Cấu hình Thẻ Cào - Admin" };

const SCRATCH_CARD_KEYS = [
  "CARD_PARTNER_ID",
  "CARD_PARTNER_KEY",
  "CARD_API_URL",
  "CARD_SANDBOX",
  "CARD_DISCOUNT_VIETTEL",
  "CARD_DISCOUNT_VINAPHONE",
  "CARD_DISCOUNT_MOBIFONE",
  "CARD_DISCOUNT_ZING",
  "CARD_DISCOUNT_GATE",
  "CARD_DISCOUNT_GARENA",
  "CARD_DISCOUNT_VCOIN",
];

export default async function AdminScratchCardPage() {
  const session = await auth();
  if (!session?.user || session.user.role !== "ADMIN") {
    redirect("/login");
  }

  const initialSettings: Record<string, string> = {};
  for (const key of SCRATCH_CARD_KEYS) {
    initialSettings[key] = await getSetting(key, "");
  }

  const appUrl = process.env.NEXT_PUBLIC_APP_URL || "http://localhost:3000";
  const webhookUrl = `${appUrl}/api/webhook/card`;

  return <ScratchCardConfigClient initialSettings={initialSettings} webhookUrl={webhookUrl} />;
}
