import { prisma } from "@/lib/prisma";

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
