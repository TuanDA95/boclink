import { prisma } from "@/lib/prisma";

/**
 * Lấy cấu hình từ Database `settings` table, nếu chưa có thì lấy từ process.env, nếu không có nữa thì dùng defaultValue
 */
export async function getSetting(key: string, defaultValue: string = ""): Promise<string> {
  try {
    const setting = await prisma.setting.findUnique({
      where: { key },
    });
    if (setting && setting.value !== undefined && setting.value !== null && setting.value !== "") {
      return setting.value;
    }
  } catch (err) {
    console.error(`[getSetting error for ${key}]:`, err);
  }
  return process.env[key] || defaultValue;
}

/**
 * Lưu nhiều cấu hình vào Database
 */
export async function saveSettings(settingsObj: Record<string, string>): Promise<void> {
  for (const [key, rawValue] of Object.entries(settingsObj)) {
    const value = (rawValue ?? "").toString().trim();
    await prisma.setting.upsert({
      where: { key },
      update: { value },
      create: { key, value },
    });
  }
}
