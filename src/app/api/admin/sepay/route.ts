import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { getSetting, saveSettings } from "@/lib/settings";

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

// GET SePay config (admin)
export async function GET() {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền truy cập" }, { status: 403 });
    }

    const settings: Record<string, string> = {};
    for (const key of SEPAY_SETTING_KEYS) {
      settings[key] = await getSetting(
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

    return NextResponse.json({ settings });
  } catch (err: any) {
    return NextResponse.json({ error: err.message || "Lỗi lấy cấu hình" }, { status: 500 });
  }
}

// POST Save SePay config (admin)
export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (session?.user?.role !== "ADMIN") {
      return NextResponse.json({ error: "Không có quyền truy cập" }, { status: 403 });
    }

    const body = await req.json().catch(() => ({}));
    const settingsToSave: Record<string, string> = {};

    for (const key of SEPAY_SETTING_KEYS) {
      if (body[key] !== undefined && body[key] !== null) {
        settingsToSave[key] = body[key].toString();
      }
    }

    await saveSettings(settingsToSave);

    return NextResponse.json({ success: true, message: "Cấu hình SePay đã được lưu thành công" });
  } catch (err: any) {
    console.error("Lỗi lưu cấu hình SePay:", err);
    return NextResponse.json({ error: err.message || "Lỗi lưu cấu hình vào database" }, { status: 500 });
  }
}
