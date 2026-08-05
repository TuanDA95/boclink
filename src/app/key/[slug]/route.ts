import { NextRequest, NextResponse } from "next/server";
import { getAppOrigin } from "@/lib/url-resolver";

export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params;
  const origin = getAppOrigin(req);
  return NextResponse.redirect(`${origin}/l/${slug}`, 301);
}
