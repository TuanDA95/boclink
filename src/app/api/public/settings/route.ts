import { NextResponse } from "next/server";
import { getSetting } from "@/lib/settings";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export async function GET() {
  try {
    const [chatLinkUrl, bankRaw, cardRaw] = await Promise.all([
      getSetting("CHAT_LINK_URL", ""),
      getSetting("BANK_DEPOSIT_ENABLED", "true"),
      getSetting("CARD_DEPOSIT_ENABLED", "true"),
    ]);

    return NextResponse.json({
      chatLinkUrl,
      bankDepositEnabled: bankRaw !== "false" && bankRaw !== "0",
      cardDepositEnabled: cardRaw !== "false" && cardRaw !== "0",
    });
  } catch (err: any) {
    return NextResponse.json({
      chatLinkUrl: "",
      bankDepositEnabled: true,
      cardDepositEnabled: true,
    }, { status: 500 });
  }
}
