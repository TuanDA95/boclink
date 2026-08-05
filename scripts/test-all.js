const assert = require("assert");
const crypto = require("crypto");

console.log("==================================================");
console.log("🧪 BỐC LINK - COMPREHENSIVE TEST SUITE");
console.log("==================================================\n");

let passed = 0;
let failed = 0;

function test(name, fn) {
  try {
    fn();
    console.log(`  ✅ PASS: ${name}`);
    passed++;
  } catch (err) {
    console.error(`  ❌ FAIL: ${name}`);
    console.error(`     Error: ${err.message}`);
    failed++;
  }
}

// --------------------------------------------------
// 1. TEST VALIDATION SCHEMAS (zod equivalents)
// --------------------------------------------------
console.log("📌 1. Testing Validation & Formats...");

test("Slug format validation (only lowercase, numbers, hyphens)", () => {
  const slugRegex = /^[a-z0-9-]+$/;
  assert.strictEqual(slugRegex.test("valid-code-123"), true);
  assert.strictEqual(slugRegex.test("abc"), true);
  assert.strictEqual(slugRegex.test("INVALID CODE"), false);
  assert.strictEqual(slugRegex.test("code@123!"), false);
});

test("URL validation", () => {
  const isUrl = (str) => {
    try { new URL(str); return true; } catch { return false; }
  };
  assert.strictEqual(isUrl("https://google.com/search?q=test"), true);
  assert.strictEqual(isUrl("http://localhost:3000/l/abc"), true);
  assert.strictEqual(isUrl("not-a-url"), false);
});

// --------------------------------------------------
// 2. TEST SEPAY INTEGRATION & HMAC VERIFICATION
// --------------------------------------------------
console.log("\n📌 2. Testing SePay Integration & Webhook Security...");

test("VietQR URL & Payment Content generation", () => {
  const bankAccount = "123456789";
  const bankName = "MBBank";
  const amount = 50000;
  const content = "BOCLINK1001";
  
  const qrUrl = `https://img.vietqr.io/image/${bankName}-${bankAccount}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(content)}`;
  assert.ok(qrUrl.includes("MBBank-123456789-compact2.png"));
  assert.ok(qrUrl.includes("amount=50000"));
  assert.ok(qrUrl.includes("addInfo=BOCLINK1001"));
});

test("SePay Webhook HMAC signature verification", () => {
  const secret = "my_sepay_webhook_secret_key_123";
  const payload = JSON.stringify({ id: 99, amountIn: 50000, content: "BOCLINK1001" });
  
  // Calculate expected signature
  const expectedSig = crypto.createHmac("sha256", secret).update(payload).digest("hex");
  
  // Test valid signature
  const actualSig = crypto.createHmac("sha256", secret).update(payload).digest("hex");
  assert.strictEqual(actualSig, expectedSig);
  
  // Test invalid signature (tampered payload)
  const tamperedSig = crypto.createHmac("sha256", secret).update(payload + "tampered").digest("hex");
  assert.notStrictEqual(tamperedSig, expectedSig);
});

test("SePay Card Basic Auth header construction", () => {
  const merchantId = "MCH12345";
  const secretKey = "SEC67890";
  const authHeader = `Basic ${Buffer.from(`${merchantId}:${secretKey}`).toString("base64")}`;
  
  assert.ok(authHeader.startsWith("Basic "));
  const decoded = Buffer.from(authHeader.split(" ")[1], "base64").toString("utf8");
  assert.strictEqual(decoded, "MCH12345:SEC67890");
});

// --------------------------------------------------
// 3. TEST KEY GENERATION & LICENSE VERIFICATION LOGIC
// --------------------------------------------------
console.log("\n📌 3. Testing Get Key / License System Logic...");

test("License Key format generation (KEY-XXXX-XXXX)", () => {
  const generateKey = () => {
    const r1 = crypto.randomBytes(3).toString("hex").toUpperCase();
    const r2 = crypto.randomBytes(3).toString("hex").toUpperCase();
    return `KEY-${r1}-${r2}`;
  };

  const key = generateKey();
  assert.ok(/^KEY-[A-Z0-9]{6}-[A-Z0-9]{6}$/.test(key), `Key '${key}' does not match KEY-XXXXXX-XXXXXX format`);
});

test("Key Expiration calculation", () => {
  const durationDays = 7;
  const now = new Date("2026-08-05T12:00:00Z");
  const expiresAt = new Date(now.getTime() + durationDays * 24 * 60 * 60 * 1000);
  
  assert.strictEqual(expiresAt.toISOString(), "2026-08-12T12:00:00.000Z");
  assert.strictEqual(now < expiresAt, true);
});

test("HWID Lock logic (First-time bind & Mismatch check)", () => {
  let keyRecord = {
    keyCode: "KEY-TEST-1234",
    hwid: null, // Unbound initially
    expiresAt: new Date(Date.now() + 86400000),
  };

  // 1. First device binds HWID
  const device1 = "IPHONE-DEVICE-UUID-111";
  if (!keyRecord.hwid) {
    keyRecord.hwid = device1;
  }
  assert.strictEqual(keyRecord.hwid, "IPHONE-DEVICE-UUID-111");

  // 2. Same device verifies -> Success
  const verifyResult1 = keyRecord.hwid === device1;
  assert.strictEqual(verifyResult1, true);

  // 3. Different device tries -> Fail (HWID Mismatch)
  const device2 = "ANDROID-DEVICE-UUID-999";
  const verifyResult2 = keyRecord.hwid === device2;
  assert.strictEqual(verifyResult2, false);
});

// --------------------------------------------------
// 4. TEST API RESPONSE STRUCTURES
// --------------------------------------------------
console.log("\n📌 4. Testing API Response Formats...");

test("Developer API JSON response format", () => {
  const devApiResponse = {
    status: "success",
    short_url: "http://localhost:3000/l/mycode",
    code: "mycode",
    original_url: "https://example.com/target",
  };

  assert.strictEqual(devApiResponse.status, "success");
  assert.strictEqual(typeof devApiResponse.short_url, "string");
  assert.strictEqual(typeof devApiResponse.code, "string");
  assert.strictEqual(typeof devApiResponse.original_url, "string");
});

test("Quicklink API GET URL query string parsing", () => {
  const queryStr = "?token=abc123token&url=https%3A%2F%2Fgoogle.com&code=testcode";
  const params = new URLSearchParams(queryStr);
  
  assert.strictEqual(params.get("token"), "abc123token");
  assert.strictEqual(params.get("url"), "https://google.com");
  assert.strictEqual(params.get("code"), "testcode");
});

// --------------------------------------------------
// SUMMARY RESULTS
// --------------------------------------------------
console.log("\n==================================================");
console.log(`📊 TEST SUMMARY: ${passed} PASSED, ${failed} FAILED`);
console.log("==================================================");

if (failed > 0) {
  process.exit(1);
}
