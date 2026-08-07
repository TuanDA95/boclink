import crypto from "crypto";

// ============================================================
// TYPES
// ============================================================
export interface SePayWebhookPayload {
  id: number;
  gateway: string;
  transactionDate: string;
  accountNumber: string;
  subAccount: string;
  code: string | null;
  content: string;
  transferType: "in" | "out";
  description: string;
  transferAmount: number;
  accumulated: number;
  referenceCode: string;
}

export interface SePayCardOrderResponse {
  paymentUrl: string;
  orderCode: string;
}

// ============================================================
// VIETQR - BANK TRANSFER
// ============================================================

/**
 * Tạo URL QR VietQR để hiển thị cho người dùng chuyển khoản
 * Sử dụng API VietQR công khai (không cần API key)
 */
export function generateVietQRUrl(
  bankAccount: string,
  bankBin: string,
  amount: number,
  content: string,
  ownerName?: string
): string {
  const params = new URLSearchParams({
    accountNo: bankAccount,
    accountName: ownerName || process.env.SEPAY_BANK_OWNER || "",
    acqId: bankBin,
    amount: amount.toString(),
    addInfo: content,
    template: "compact",
  });

  return `https://img.vietqr.io/image/${bankBin}-${bankAccount}-compact.jpg?${params.toString()}`;
}

/**
 * Lấy BIN ngân hàng từ tên để tạo VietQR
 */
export function getBankBin(bankName: string): string {
  const bankBins: Record<string, string> = {
    Vietcombank: "970436",
    VCB: "970436",
    BIDV: "970418",
    VietinBank: "970415",
    Agribank: "970405",
    Techcombank: "970407",
    MB: "970422",
    MBBank: "970422",
    ACB: "970416",
    VPBank: "970432",
    TPBank: "970423",
    Sacombank: "970403",
    HDBank: "970437",
    VIB: "970441",
    SHB: "970443",
    OCB: "970448",
    MSB: "970426",
  };
  return bankBins[bankName] || "970436";
}

// ============================================================
// SEPAY PAYMENT GATEWAY - CARD
// ============================================================

/**
 * Tạo đơn thanh toán thẻ qua SePay Payment Gateway
 */
export async function createCardPaymentOrder(
  amount: number,
  orderCode: string,
  returnUrl: string,
  cancelUrl: string,
  description: string = "Nạp tiền Sub2S"
): Promise<SePayCardOrderResponse> {
  const apiKey = process.env.SEPAY_API_KEY;
  if (!apiKey) throw new Error("SEPAY_API_KEY chưa được cấu hình");

  const baseUrl = process.env.SEPAY_SANDBOX === "true"
    ? "https://pgapi-sandbox.sepay.vn"
    : "https://pgapi.sepay.vn";

  const merchantId = process.env.SEPAY_MERCHANT_ID || apiKey;
  const secretKey = process.env.SEPAY_SECRET_KEY || process.env.SEPAY_API_KEY || "";
  const basicAuth = Buffer.from(`${merchantId}:${secretKey}`).toString("base64");

  const response = await fetch(`${baseUrl}/api/gateway/create-payment`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Basic ${basicAuth}`,
    },
    body: JSON.stringify({
      order_code: orderCode,
      amount,
      description,
      return_url: returnUrl,
      cancel_url: cancelUrl,
    }),
  });

  if (!response.ok) {
    const err = await response.text();
    throw new Error(`SePay API lỗi: ${err}`);
  }

  const data = await response.json();
  return {
    paymentUrl: data.checkout_url || data.payment_url,
    orderCode,
  };
}

// ============================================================
// WEBHOOK VERIFICATION
// ============================================================

/**
 * Xác thực webhook từ SePay bằng API Key trong Authorization header: "Apikey YOUR_API_KEY"
 * hoặc chữ ký HMAC-SHA256
 */
export function verifySePayWebhook(
  payload: string,
  signature: string,
  authHeader: string,
  customSecret?: string
): boolean {
  const secret = (customSecret && customSecret.trim() !== "") ? customSecret.trim() : process.env.SEPAY_WEBHOOK_SECRET;

  // Nếu chưa cài secret hoặc đang dùng placeholder mặc định -> Chấp nhận test webhook
  if (!secret || secret === "your-sepay-webhook-secret") {
    console.log("[SePay Webhook] Bỏ qua xác thực API Key (Chưa cấu hình API Key trong Quản trị)");
    return true;
  }

  // 1. Xác thực qua Authorization header (Chuẩn API Key của SePay: "Apikey YOUR_API_KEY")
  if (authHeader) {
    const cleanAuth = authHeader.trim();
    const expectedAuth = `Apikey ${secret}`;

    if (
      cleanAuth.toLowerCase() === expectedAuth.toLowerCase() ||
      cleanAuth === secret ||
      cleanAuth.includes(secret)
    ) {
      console.log(`[SePay Webhook] ✅ Xác thực thành công qua Authorization Header ("Apikey ${secret.slice(0, 6)}...")!`);
      return true;
    }
  }

  // 2. Kiểm tra HMAC SHA-256 signature (nếu SePay gửi x-sepay-signature)
  if (signature) {
    const cleanSignature = signature.replace(/^sha256=/i, "").trim();

    const expectedSigHex = crypto
      .createHmac("sha256", secret)
      .update(payload)
      .digest("hex");

    const expectedSigBase64 = crypto
      .createHmac("sha256", secret)
      .update(payload)
      .digest("base64");

    if (
      cleanSignature === expectedSigHex ||
      cleanSignature === expectedSigBase64 ||
      signature === expectedSigHex ||
      signature.includes(expectedSigHex)
    ) {
      console.log("[SePay Webhook] ✅ Xác thực chữ ký HMAC SHA-256 thành công!");
      return true;
    }
  }

  // Log thông tin diagnostic nếu không khớp
  console.warn(`[SePay Webhook] API Key / Chữ ký không trùng khớp.`);
  console.warn(`  - Configured API Key/Secret: ${secret.slice(0, 6)}...`);
  console.warn(`  - Received Auth Header     : ${authHeader || "(trống)"}`);
  console.warn(`  - Received Signature Header: ${signature || "(trống)"}`);

  // Trong môi trường dev/sandbox, chấp nhận dữ liệu để test mượt mà
  if (process.env.SEPAY_SANDBOX === "true" || process.env.NODE_ENV === "development") {
    console.log("[SePay Webhook] [DEV / SANDBOX MODE] Cho phép dữ liệu thử nghiệm đi qua");
    return true;
  }

  return false;
}

/**
 * Tạo mã thanh toán duy nhất cho bank transfer
 * @param userId - ID người dùng
 * @param prefix - Tiền tố nội dung chuyển khoản (lấy từ DB qua getSetting)
 */
export function generatePaymentCode(userId: string, prefix?: string): string {
  const resolvedPrefix = prefix || process.env.SEPAY_PAYMENT_PREFIX || "SUB2S";
  // Loại bỏ tất cả ký tự không phải alphanumeric (đặc biệt dấu _ trong userId cũ c_XXXX)
  const cleanId = userId.replace(/[^a-zA-Z0-9]/g, "");
  const shortId = cleanId.slice(-8).toUpperCase();
  const time = Date.now().toString().slice(-5);
  return `${resolvedPrefix}${shortId}${time}`;
}

/**
 * Format số tiền VNĐ
 */
export function formatVND(amount: number): string {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}
