import { auth } from "@/lib/auth";
import { redirect } from "next/navigation";
import { getSetting } from "@/lib/settings";
import SystemSettingsClient, { AdLayer } from "./SystemSettingsClient";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Cấu hình hệ thống - Admin" };
export const dynamic = "force-dynamic";
export const revalidate = 0;

export default async function AdminSettingsPage() {
  const session = await auth();
  if (session?.user?.role !== "ADMIN") redirect("/");

  const [freeLinkRaw, defaultPriceRaw, adLayersRaw, interstitialAdUrl] = await Promise.all([
    getSetting("FREE_LINK_ENABLED", "true"),
    getSetting("DEFAULT_LINK_PRICE", "5000"),
    getSetting("AD_LAYERS", "[]"),
    getSetting("INTERSTITIAL_AD_URL", ""),
  ]);

  let adLayers: AdLayer[] = [];
  try { adLayers = JSON.parse(adLayersRaw); } catch { adLayers = []; }

  const isFreeEnabled = freeLinkRaw === "true" || freeLinkRaw === "1";

  return (
    <SystemSettingsClient
      initialFreeEnabled={isFreeEnabled}
      initialDefaultPrice={parseFloat(defaultPriceRaw) || 5000}
      initialAdLayers={adLayers}
      initialInterstitialAdUrl={interstitialAdUrl}
    />
  );
}

