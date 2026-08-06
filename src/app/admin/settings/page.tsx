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

  const [freeLinkRaw, defaultPriceRaw, adLayersRaw, interstitialAdLayersRaw, interstitialAdUrl, chatLinkUrl] = await Promise.all([
    getSetting("FREE_LINK_ENABLED", "true"),
    getSetting("DEFAULT_LINK_PRICE", "5000"),
    getSetting("AD_LAYERS", "[]"),
    getSetting("INTERSTITIAL_AD_LAYERS", "[]"),
    getSetting("INTERSTITIAL_AD_URL", ""),
    getSetting("CHAT_LINK_URL", ""),
  ]);

  let adLayers: AdLayer[] = [];
  try { adLayers = JSON.parse(adLayersRaw); } catch { adLayers = []; }

  let interstitialAdLayers: AdLayer[] = [];
  try { interstitialAdLayers = JSON.parse(interstitialAdLayersRaw); } catch { interstitialAdLayers = []; }

  // Fallback nếu chưa có INTERSTITIAL_AD_LAYERS mà có INTERSTITIAL_AD_URL cũ
  if (interstitialAdLayers.length === 0 && interstitialAdUrl.trim()) {
    interstitialAdLayers = [{
      id: "legacy-1",
      name: "Quảng cáo hình ảnh",
      region: "all",
      enabled: true,
      url: interstitialAdUrl.trim(),
      order: 0,
    }];
  }

  const isFreeEnabled = freeLinkRaw === "true" || freeLinkRaw === "1";

  return (
    <SystemSettingsClient
      initialFreeEnabled={isFreeEnabled}
      initialDefaultPrice={parseFloat(defaultPriceRaw) || 5000}
      initialAdLayers={adLayers}
      initialInterstitialAdLayers={interstitialAdLayers}
      initialChatLinkUrl={chatLinkUrl}
    />
  );
}

