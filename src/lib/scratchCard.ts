import crypto from "crypto";

export interface ScratchCardTelco {
  code: string;
  name: string;
  type: "phone" | "game";
  defaultDiscount: number; // % chiết khấu mặc định
  logo?: string;
}

export const TELCOS: ScratchCardTelco[] = [
  { code: "VIETTEL", name: "Viettel", type: "phone", defaultDiscount: 15 },
  { code: "VINAPHONE", name: "Vinaphone", type: "phone", defaultDiscount: 15 },
  { code: "MOBIFONE", name: "Mobifone", type: "phone", defaultDiscount: 15 },
  { code: "VIETNAMOBILE", name: "Vietnamobile", type: "phone", defaultDiscount: 15 },
  { code: "GMOBILE", name: "Gmobile", type: "phone", defaultDiscount: 15 },
  { code: "ZING", name: "Thẻ Zing", type: "game", defaultDiscount: 16 },
  { code: "GATE", name: "Thẻ Gate", type: "game", defaultDiscount: 16 },
  { code: "GARENA", name: "Thẻ Garena", type: "game", defaultDiscount: 18 },
  { code: "VCOIN", name: "Thẻ Vcoin", type: "game", defaultDiscount: 18 },
];

export const DECLARED_VALUES = [
  20000, 50000, 100000, 200000, 500000, 1000000,
];

export interface ScratchCardSubmitPayload {
  telco: string;
  code: string;
  serial: string;
  declaredValue: number;
  requestId: string;
}

export interface ScratchCardResponse {
  status: number; // 99: Chờ xử lý, 1: Thành công, 2: Sai mệnh giá, 3: Thẻ sai/Đã dùng, 4: Lỗi cổng
  message: string;
  requestId: string;
  amount?: number;
}

/**
 * Tính toán số tiền thực nhận sau khi trừ chiết khấu %
 */
export function calculateRealAmount(declaredValue: number, discountPercent: number): number {
  const discountAmount = (declaredValue * discountPercent) / 100;
  return Math.max(0, declaredValue - discountAmount);
}

/**
 * Gửi thẻ cào lên cổng gạch thẻ (Doithe1s / Doithegiare / CardVIP standard API)
 */
export async function submitScratchCardToGateway(
  payload: ScratchCardSubmitPayload,
  partnerId: string,
  partnerKey: string,
  apiUrl: string
): Promise<ScratchCardResponse> {
  const sign = crypto
    .createHash("md5")
    .update(partnerKey + payload.code + payload.serial)
    .digest("hex");

  const body = {
    partner_id: partnerId,
    request_id: payload.requestId,
    telco: payload.telco,
    code: payload.code,
    serial: payload.serial,
    amount: payload.declaredValue,
    sign: sign,
    command: "charging",
  };

  try {
    const res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });

    if (!res.ok) {
      const errText = await res.text();
      return {
        status: 4,
        message: `Cổng gạch thẻ phản hồi lỗi (${res.status}): ${errText}`,
        requestId: payload.requestId,
      };
    }

    const data = await res.json();
    return {
      status: Number(data.status ?? 99),
      message: data.message || data.msg || "Đã gửi thẻ cào lên hệ thống xử lý",
      requestId: payload.requestId,
      amount: data.amount ? Number(data.amount) : undefined,
    };
  } catch (error: any) {
    return {
      status: 4,
      message: `Lỗi kết nối tới cổng gạch thẻ: ${error.message || error}`,
      requestId: payload.requestId,
    };
  }
}

/**
 * Kiểm tra chữ ký webhook callback từ cổng gạch thẻ
 * MD5(partner_key + code + serial) hoặc MD5(partner_key + request_id)
 */
export function verifyScratchCardCallback(
  code: string,
  serial: string,
  requestId: string,
  sign: string,
  partnerKey: string
): boolean {
  if (!sign) return true;

  const expected1 = crypto
    .createHash("md5")
    .update(partnerKey + code + serial)
    .digest("hex");

  const expected2 = crypto
    .createHash("md5")
    .update(partnerKey + requestId)
    .digest("hex");

  return (
    sign.toLowerCase() === expected1.toLowerCase() ||
    sign.toLowerCase() === expected2.toLowerCase()
  );
}
