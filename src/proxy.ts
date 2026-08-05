import { getToken } from "next-auth/jwt";
import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

export async function proxy(req: NextRequest) {
  const secret = process.env.AUTH_SECRET || process.env.NEXTAUTH_SECRET;
  const isHttps = req.url.startsWith("https://") || req.headers.get("x-forwarded-proto") === "https";

  let token = await getToken({
    req,
    secret,
    secureCookie: isHttps,
  });

  // Fallback cho môi trường Nginx SSL Reverse Proxy
  if (!token) {
    token = await getToken({
      req,
      secret,
      secureCookie: true,
    });
  }

  const { nextUrl } = req;
  const isLoggedIn = !!token;
  const isAdmin = token?.role === "ADMIN";

  // Protect /admin routes - only ADMIN role
  if (nextUrl.pathname.startsWith("/admin")) {
    if (!isLoggedIn) {
      return NextResponse.redirect(new URL("/login?callbackUrl=/admin", nextUrl));
    }
    if (!isAdmin) {
      return NextResponse.redirect(new URL("/", nextUrl));
    }
  }

  // Protect /deposit routes - requires login
  if (nextUrl.pathname.startsWith("/deposit")) {
    if (!isLoggedIn) {
      return NextResponse.redirect(
        new URL(`/login?callbackUrl=${nextUrl.pathname}`, nextUrl)
      );
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/admin/:path*",
    "/deposit/:path*",
  ],
};
