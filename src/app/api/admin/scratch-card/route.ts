import { NextRequest, NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { getSetting, saveSettings } from "@/lib/settings";

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

export async function GET() {
  const session = await auth();
  if (!session?.user || session.user.role !== "ADMIN") {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const settings: Record<string, string> = {};
  for (const key of SCRATCH_CARD_KEYS) {
    settings[key] = await getSetting(key, getDefaultValue(key));
  }

  return NextResponse.json({ settings });
}

export async function POST(req: NextRequest) {
  const session = await auth();
  if (!session?.user || session.user.role !== "ADMIN") {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await req.json();
    const settingsToSave: Record<string, string> = {};

    for (const key of SCRATCH_CARD_KEYS) {
      if (body[key] !== undefined) {
        settingsToSave[key] = String(body[key]).trim();
      }
    }

    await saveSettings(settingsToSave);
    return NextResponse.json({ success: true, settings: settingsToSave });
  } catch (error) {
    return NextResponse.json({ error: "Lỗi lưu cấu hình thẻ cào" }, { status: 500 });
  }
}

function getDefaultValue(key: string): string {
  switch (key) {
    case "CARD_PARTNER_ID":
      return process.env.CARD_PARTNER_ID || "";
    case "CARD_PARTNER_KEY":
      return process.env.CARD_PARTNER_KEY || "";
    case "CARD_API_URL":
      return process.env.CARD_API_URL || "https://doithe1s.vn/chargingws/v2";
    case "CARD_SANDBOX":
      return process.env.CARD_SANDBOX || "true";
    case "CARD_DISCOUNT_VIETTEL":
    case "CARD_DISCOUNT_VINAPHONE":
    case "CARD_DISCOUNT_MOBIFONE":
      return "15";
    case "CARD_DISCOUNT_ZING":
    case "CARD_DISCOUNT_GATE":
      return "16";
    case "CARD_DISCOUNT_GARENA":
    case "CARD_DISCOUNT_VCOIN":
      return "18";
    default:
      return "";
  }
}
