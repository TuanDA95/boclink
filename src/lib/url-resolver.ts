import { prisma } from "@/lib/prisma";
import { getSetting } from "@/lib/settings";

/**
 * Tự động unwrap / gỡ bỏ các lớp link bọc nội bộ (bọc lần 2, 3...)
 * Ví dụ: nếu url là "https://boclink.com/l/abc" hoặc "https://boclink.com/st?token=xxx&url=https://target.com"
 * Hàm sẽ truy vấn DB hoặc parse tham số để trả về link gốc thực sự ("https://target.com").
 */
export async function resolveOriginalUrl(url: string, maxDepth = 5): Promise<string> {
  let currentUrl = url.trim();
  let depth = 0;

  while (depth < maxDepth) {
    depth++;
    let resolvedNext: string | null = null;

    try {
      let slug: string | null = null;

      if (currentUrl.startsWith("/l/")) {
        slug = currentUrl.replace("/l/", "").split("?")[0].split("#")[0];
      } else {
        const parsed = new URL(currentUrl);
        // Trường hợp /st?token=...&url=... hoặc /api/devst
        if (
          (parsed.pathname === "/st" || parsed.pathname === "/api/devst") &&
          parsed.searchParams.has("url")
        ) {
          const innerUrl = parsed.searchParams.get("url");
          if (innerUrl && innerUrl !== currentUrl) {
            resolvedNext = innerUrl;
          }
        }
        // Trường hợp /l/[slug]
        if (!resolvedNext) {
          const match = parsed.pathname.match(/^\/l\/([a-zA-Z0-9_-]+)/);
          if (match && match[1]) {
            slug = match[1];
          }
        }
      }

      if (slug) {
        const targetLink = await prisma.link.findUnique({
          where: { slug },
          select: { originalUrl: true },
        });
        if (targetLink && targetLink.originalUrl && targetLink.originalUrl !== currentUrl) {
          resolvedNext = targetLink.originalUrl;
        }
      }
    } catch {
      break;
    }

    if (resolvedNext) {
      currentUrl = resolvedNext.trim();
    } else {
      break;
    }
  }

  return currentUrl;
}

/**
 * Xử lý ghép link bọc chuẩn xác (tránh lỗi lặp &url=&url= khi adUrl chứa sẵn url=)
 */
export function buildWrappedUrl(adUrl: string, targetUrl: string): string {
  const trimmed = adUrl.trim();
  if (!trimmed) return targetUrl;

  const encodedTarget = encodeURIComponent(targetUrl);

  if (trimmed.endsWith("=")) {
    return trimmed + encodedTarget;
  }

  if (trimmed.endsWith("&") || trimmed.endsWith("?")) {
    return `${trimmed}url=${encodedTarget}`;
  }

  if (trimmed.includes("?")) {
    return `${trimmed}&url=${encodedTarget}`;
  }

  return `${trimmed}${trimmed.endsWith("/") ? "?url=" : "/?url="}${encodedTarget}`;
}

/**
 * Lấy lớp quảng cáo (link bọc) đầu tiên đang bật trong cài đặt hệ thống (AD_LAYERS)
 */
export async function getFirstAdUrl(): Promise<string | null> {
  try {
    const raw = await getSetting("AD_LAYERS", "[]");
    const layers = JSON.parse(raw);
    if (Array.isArray(layers)) {
      const active = layers.find(
        (l: any) => l.enabled !== false && String(l.enabled) !== "false" && l.url && l.url.trim().length > 0
      );
      if (active) return active.url.trim();
    }
  } catch {
    // Fallback
  }
  return null;
}

/**
 * Xác định chính xác domain/origin của ứng dụng (ưu tiên env sản phẩm -> X-Forwarded-Host -> host -> fallback)
 */
export function getAppOrigin(req: any): string {
  // 1. Ưu tiên lấy từ biến môi trường của hệ thống/sản phẩm
  const envDomain =
    process.env.NEXT_PUBLIC_APP_URL ||
    process.env.NEXTAUTH_URL ||
    process.env.APP_URL;

  if (envDomain && envDomain.trim().length > 0) {
    let domain = envDomain.trim();
    if (!domain.startsWith("http://") && !domain.startsWith("https://")) {
      domain = `https://${domain}`;
    }
    // Nếu biến môi trường là domain thật (không phải localhost), dùng luôn
    if (!domain.includes("localhost") && !domain.includes("127.0.0.1")) {
      return domain;
    }
  }

  // 2. Lấy từ headers của HTTP request (hỗ trợ Reverse Proxy / Nginx / Cloudflare)
  try {
    const headers = req?.headers;
    if (headers?.get) {
      const xHost = headers.get("x-forwarded-host");
      const rawHost = xHost || headers.get("host");
      const host = rawHost ? rawHost.split(",")[0].trim() : null;
      const proto = headers.get("x-forwarded-proto") || (req.url?.startsWith("https") ? "https" : "http");

      if (host && !host.includes("localhost") && !host.includes("127.0.0.1")) {
        return `${proto}://${host}`;
      }
    }
  } catch {}

  // 3. Fallback envDomain kể cả khi là localhost
  if (envDomain && envDomain.trim().length > 0) {
    let domain = envDomain.trim();
    if (!domain.startsWith("http://") && !domain.startsWith("https://")) {
      domain = `http://${domain}`;
    }
    return domain;
  }

  // 4. Fallback request URL origin
  try {
    return new URL(req.url).origin;
  } catch {
    return "http://localhost:3000";
  }
}


/**
 * Trích xuất tham số url từ req.url (kể cả khi URL gốc chứa ký tự & chưa mã hóa)
 */
export function extractTargetUrlFromReq(reqUrl: string): string | null {
  try {
    const urlObj = new URL(reqUrl);
    const rawUrl = urlObj.searchParams.get("url");
    if (!rawUrl) return null;

    const queryStr = reqUrl.slice(reqUrl.indexOf("?") + 1);
    const urlIdx = queryStr.indexOf("url=");
    if (urlIdx !== -1) {
      let paramVal = queryStr.slice(urlIdx + 4);
      const nextSpecial = paramVal.search(/&(token|code)=/);
      if (nextSpecial !== -1) {
        paramVal = paramVal.slice(0, nextSpecial);
      }
      try {
        const decoded = decodeURIComponent(paramVal);
        new URL(decoded);
        return decoded;
      } catch {
        try {
          new URL(paramVal);
          return paramVal;
        } catch {
          // Fallback
        }
      }
    }
    return rawUrl;
  } catch {
    return null;
  }
}

