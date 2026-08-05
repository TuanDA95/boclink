<?php

function appendKey($url, $key = "keygmvmoba") {
    $connector = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $connector . $key;
}

/**
 * 🔥 Bóc nhiều tầng shortlink + lấy domain cuối
 */
function extractFinalMeostarcheatUrl($url) {
    // Bóc tối đa 6 tầng redirect
    for ($i = 0; $i < 6; $i++) {
        // Decode dần
        $decoded = urldecode($url);
        if ($decoded !== $url) {
            $url = $decoded;
        }

        $parts = parse_url($url);
        if (!empty($parts['host']) && strpos($parts['host'], 'meostarcheat.com') !== false) {
            // ✅ Đã tới URL cần lấy
            return $url;
        }

        // Nếu còn nằm trong query thì bóc tiếp
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $params);

            if (!empty($params['url'])) {
                $url = $params['url'];
                continue;
            }

            if (!empty($params['link'])) {
                $url = $params['link'];
                continue;
            }
        }

        break;
    }

    return null;
}


$firstRedirect = null;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0',
    CURLOPT_COOKIEJAR => 'cookie.txt',
    CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$firstRedirect) {
        if (stripos($header, 'Location:') === 0 && !$firstRedirect) {
            $firstRedirect = trim(substr($header, 9));
        }
        return strlen($header);
    }
]);

$url1 = "https://meostarcheat.com/public/GETKEY/PhanHongNgocFiFa.php";

$postData = [
    'game'       => 'Discord: @ngocbonggaming',
    'shortlink'  => 'Mod Map Liên Quân',
    'btn'        => ''
];

curl_setopt($ch, CURLOPT_URL, $url1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_exec($ch);

/**
 * Có redirect → dùng redirect
 * Không có → dùng url1
 */
$targetUrl = $firstRedirect ?: $url1;

/**
 * Thêm key
 */
$targetUrl = appendKey($targetUrl);

/**
 * Follow tiếp để lấy URL cuối
 */
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_POST => false,
    CURLOPT_HTTPGET => true,
    CURLOPT_FOLLOWLOCATION => true
]);

curl_exec($ch);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

/**
 * =========================
 * ✅ OUTPUT CUỐI
 * =========================
 */
function isOnlyNhapcode1s($url) {
    $host = parse_url($url, PHP_URL_HOST);
    return $host && stripos($host, 'nhapcode1s.com') !== false;
}
$meostarcheatUrl = extractFinalMeostarcheatUrl($finalUrl);

/**
 * =========================
 * ✅ LOGIC QUYẾT ĐỊNH
 * =========================
 */

// ✅ Ưu tiên meostarcheat nếu bóc được
if ($meostarcheatUrl) {

    $shortUrl = $meostarcheatUrl;

// ❌ Không thấy meostarcheat
// ✅ Domain cuối là nhapcode1s.com
} elseif (isOnlyNhapcode1s($finalUrl)) {

    $shortUrl2 = $finalUrl . "?keygmvmoba";
    $shortUrl  = $shortUrl2;

// ❌ Trường hợp khác → lỗi
} else {

    $shortUrl = "Lỗi: liên hệ admin.";
}

/**
 * =========================
 * ✅ OUTPUT
 * =========================
 */
// echo $shortUrl;


//  echo "URL dùng ban đầu: $targetUrl\n";
//  echo "URL cuối cùng: $finalUrl\n";
 
//  if ($meostarcheatUrl) {
//      echo "URL: $meostarcheatUrl\n";
//  }
 
