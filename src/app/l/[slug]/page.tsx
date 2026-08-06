import { auth } from "@/lib/auth";
import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";
import { notFound } from "next/navigation";
import LinkPageClient from "./LinkPageClient";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  return {
    title: `Get link - ${slug}`,
    description: "Liên kết được rút gọn và bảo vệ qua hệ thống API Key.",
  };
}

export default async function LinkPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const session = await auth();

  const link = await prisma.link.findUnique({
    where: { slug, isActive: true },
    select: {
      id: true,
      slug: true,
      title: true,
      description: true,
      price: true,
      adDuration: true,
    },
  });

  if (!link) notFound();

  let alreadyPurchased = false;
  if (session?.user?.id) {
    const p = await prisma.purchase.findFirst({
      where: { userId: session.user.id, linkId: link.id },
    });
    alreadyPurchased = !!p;
  }

  const userBalance = session?.user?.id
    ? (await prisma.user.findUnique({ where: { id: session.user.id }, select: { balance: true } }))?.balance || 0
    : 0;

  // Đọc cấu hình hệ thống
  const [freeLinkEnabledRaw, defaultPriceRaw, adLayersRaw, interstitialAdLayersRaw, interstitialAdUrl] = await Promise.all([
    getSetting("FREE_LINK_ENABLED", "true"),
    getSetting("DEFAULT_LINK_PRICE", "5000"),
    getSetting("AD_LAYERS", "[]"),
    getSetting("INTERSTITIAL_AD_LAYERS", "[]"),
    getSetting("INTERSTITIAL_AD_URL", ""),
  ]);

  const freeLinkEnabled = freeLinkEnabledRaw === "true" || freeLinkEnabledRaw === "1";
  const defaultLinkPrice = parseFloat(defaultPriceRaw) || 5000;

  // Lấy tất cả URL bọc link đang bật
  let adLayers: { url: string; enabled: boolean; region: string }[] = [];
  try { adLayers = JSON.parse(adLayersRaw); } catch { adLayers = []; }
  const enabledAdUrls = adLayers
    .filter((l) => l.enabled !== false && (l.enabled as any) !== "false" && l.url && l.url.trim().length > 0)
    .map((l) => l.url.trim());

  // Lấy tất cả URL quảng cáo hình ảnh đang bật
  let interstitialAdLayers: { url: string; enabled: boolean; region: string }[] = [];
  try { interstitialAdLayers = JSON.parse(interstitialAdLayersRaw); } catch { interstitialAdLayers = []; }
  let enabledInterstitialAdUrls = interstitialAdLayers
    .filter((l) => l.enabled !== false && (l.enabled as any) !== "false" && l.url && l.url.trim().length > 0)
    .map((l) => l.url.trim());

  // Fallback nếu chưa có INTERSTITIAL_AD_LAYERS nhưng có INTERSTITIAL_AD_URL
  if (enabledInterstitialAdUrls.length === 0 && interstitialAdUrl.trim()) {
    enabledInterstitialAdUrls = [interstitialAdUrl.trim()];
  }

  // Tăng click
  await prisma.link.update({ where: { slug }, data: { clicks: { increment: 1 } } });

  // Nếu link.price = 0 và có defaultLinkPrice → dùng default
  const effectivePrice = link.price > 0 ? link.price : defaultLinkPrice;

  // Tiêu đề hiển thị (nếu title chứa http/https hoặc rỗng -> dùng "Get link - {slug}")
  const isUrlTitle = !link.title || link.title.startsWith("http://") || link.title.startsWith("https://");
  const displayTitle: string = isUrlTitle ? `Get link - ${link.slug}` : (link.title as string);

  const linkData = {
    id: link.id,
    slug: link.slug,
    title: displayTitle,
    description: link.description || "",
    price: effectivePrice,
    adDuration: link.adDuration,
  };

  return (
    <LinkPageClient
      link={linkData}
      isLoggedIn={!!session?.user}
      alreadyPurchased={alreadyPurchased}
      userBalance={userBalance}
      freeLinkEnabled={freeLinkEnabled}
      adUrls={enabledAdUrls}
      interstitialAdUrls={enabledInterstitialAdUrls}
      interstitialAdUrl={interstitialAdUrl}
    />
  );
}

