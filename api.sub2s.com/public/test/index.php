<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'https://vipmodpro.com');
define('COOKIE_FILE', __DIR__ . '/cookie.txt');

/* =========================
   CURL CORE
========================= */
function curlRequest($url, $method = 'GET', $data = null, $headers = [], $follow = true) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => COOKIE_FILE,
        CURLOPT_COOKIEFILE => COOKIE_FILE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_ENCODING => '',
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/147.0.0.0 Safari/537.36'
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);

    if (curl_error($ch)) {
        echo "<b>CURL ERROR:</b> " . curl_error($ch) . "<br>";
    }

    curl_close($ch);
    return $response;
}

/* =========================
   GET XSRF TOKEN
========================= */
function getXsrfToken() {
    if (!file_exists(COOKIE_FILE)) return '';

    $cookie = file_get_contents(COOKIE_FILE);
    preg_match('/XSRF-TOKEN\s+([^\s]+)/', $cookie, $m);

    return isset($m[1]) ? urldecode($m[1]) : '';
}

/* =========================
   HEADER CHROME
========================= */
function headersChrome($xsrf = '', $referer = '/signin') {
    return [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8',
        'Content-Type: application/json',

        'sec-ch-ua: "Google Chrome";v="147", "Not.A/Brand";v="8", "Chromium";v="147"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "Android"',

        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty',

        'X-Requested-With: XMLHttpRequest',
        'X-XSRF-TOKEN: ' . $xsrf,

        'Origin: https://vipmodpro.com',
        'Referer: https://vipmodpro.com' . $referer
    ];
}

/* =========================
   INIT COOKIE FILE
========================= */
if (!file_exists(COOKIE_FILE)) {
    file_put_contents(COOKIE_FILE, '');
}

/* =========================
   STEP 1: GET CSRF
========================= */
// echo "<h3>STEP 1: CSRF</h3>";
curlRequest(BASE_URL . '/sanctum/csrf-cookie', 'GET', null, headersChrome());

$xsrf = getXsrfToken();
// echo "Token: " . htmlspecialchars($xsrf) . "<br>";

if (!$xsrf) {
    die("<b style='color:red'>Không lấy được XSRF token</b>");
}

/* =========================
   STEP 2: LOGIN (NO REDIRECT)
========================= */
// echo "<h3>STEP 2: LOGIN</h3>";

$login = curlRequest(BASE_URL . '/login', 'POST', [
    'email' => 'vinacff@gmail.com',
    'password' => 'Cccccc@1',
    'remember' => true
], headersChrome($xsrf, '/signin'), false); // ❗ không follow redirect

// echo "Login done<br>";

/* =========================
   STEP 3: CHECK USER
========================= */
// echo "<h3>STEP 3: USER</h3>";

$xsrf = getXsrfToken();

$user = curlRequest(BASE_URL . '/api/user', 'GET', null, headersChrome($xsrf));
// echo "<pre>$user</pre>";

$dataUser = json_decode($user, true);

if (!isset($dataUser['id'])) {
    die("<b style='color:red'>Login thất bại</b>");
}

// echo "<b style='color:green'>Login OK: " . $dataUser['email'] . "</b><br>";

/* =========================
   STEP 4: CREATE KEY
========================= */
// echo "<h3>STEP 4: CREATE KEY</h3>";

$xsrf = getXsrfToken();

$prefix = "";

$response = curlRequest(BASE_URL . '/api/seller/keys/bulk', 'POST', [
    "game_id" => 5,
    "duration_value" => 12,
    "duration_type" => "hour",
    "device_limit" => 1,
    "status" => "active",
    "is_vip" => true,
    "quantity" => 1,
    "key_prefix" => $prefix
], headersChrome($xsrf, '/seller/keys'));

// echo "<pre>$response</pre>";

$data = json_decode($response, true);

/* =========================
   RESULT
========================= */
// echo "<h2>KẾT QUẢ</h2>";

// if (isset($data['data'][0]['key'])) {
//     echo "<div style='font-size:24px;color:green;font-weight:bold'>";
//     echo $data['data'][0]['key'];
//     echo "</div>";
// } else {
//     echo "<b style='color:red'>Tạo key thất bại</b>";
// }

if (isset($data['data'][0]['key'])) {
    $key = $data['data'][0]['key'];

    header("Location: https://gmvmoba.com/keygame/index.html?key=" . urlencode($key));
    exit;
} else {
    echo "<b style='color:red'>Tạo key free thất bại vui lòng liên hệ admin zalo: 0965870531
    <br>
    Hoặc mua key tại <a href='https://key.gmvmoba.com'> Shop key Auto 24/7</a></b>";
}
?>