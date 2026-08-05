import { NextResponse } from "next/server";
import { getSetting } from "@/lib/settings";

/**
 * Public endpoint — trả về danh sách ad layers đang bật
 * Dùng bởi trang /l/[slug]/ad để render quảng cáo
 */
export async function GET() {
  try {
    const raw = await getSetting("AD_LAYERS", "[]");
    let layers: any[] = [];
    try { layers = JSON.parse(raw); } catch { layers = []; }

    // Chỉ trả về layers đang bật
    const enabled = layers.filter((l: any) => l.enabled !== false);
    return NextResponse.json({ layers: enabled }, { status: 200 });
  } catch (err: any) {
    return NextResponse.json({ layers: [] }, { status: 200 });
  }
}
