import { NextResponse } from "next/server";
import { getSetting } from "@/lib/settings";

export const dynamic = "force-dynamic";
export const revalidate = 0;

export async function GET() {
  try {
    const chatLinkUrl = await getSetting("CHAT_LINK_URL", "");
    return NextResponse.json({ chatLinkUrl });
  } catch (err: any) {
    return NextResponse.json({ chatLinkUrl: "" }, { status: 500 });
  }
}
