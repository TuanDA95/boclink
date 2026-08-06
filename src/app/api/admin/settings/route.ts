import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { getSetting, saveSettings } from "@/lib/settings";

// setting keys chính
export const CORE_SETTING_KEYS = [
  "FREE_LINK_ENABLED",        // "true" | "false"
  "DEFAULT_LINK_PRICE",       // số đồng, VD "5000"
  "AD_LAYERS",                // JSON string của AdLayer[] (Bọc link)
  "INTERSTITIAL_AD_LAYERS",   // JSON string của AdLayer[] (Quảng cáo hình ảnh)
] as const;

export const CORE_SETTING_DEFAULTS: Record<string, string> = {
  FREE_LINK_ENABLED: "true",
  DEFAULT_LINK_PRICE: "5000",
  AD_LAYERS: "[]",
  INTERSTITIAL_AD_LAYERS: "[]",
};

// GET all core settings
export async function GET() {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền truy cập" }, { status: 403 });
    }

    const settings: Record<string, string> = {};
    for (const key of CORE_SETTING_KEYS) {
      settings[key] = await getSetting(key, CORE_SETTING_DEFAULTS[key] ?? "");
    }

    return NextResponse.json({ settings });
  } catch (err: any) {
    return NextResponse.json({ error: err.message || "Lỗi lấy cấu hình" }, { status: 500 });
  }
}

// POST save core settings
export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền truy cập" }, { status: 403 });
    }

    const body = await req.json().catch(() => ({}));
    const settingsToSave: Record<string, string> = {};

    for (const [key, val] of Object.entries(body)) {
      if (val !== undefined && val !== null) {
        settingsToSave[key] = val.toString().trim();
      }
    }

    await saveSettings(settingsToSave);
    return NextResponse.json({ success: true, message: "Đã lưu cấu hình thành công" });
  } catch (err: any) {
    console.error("Lỗi lưu cấu hình:", err);
    return NextResponse.json({ error: err.message || "Lỗi lưu cấu hình" }, { status: 500 });
  }
}

