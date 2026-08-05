<?php

class LinkExtractorPHP {
    private $cookieJar;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    public function __construct() {
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'cookie_');
    }

    public function __destruct() {
        if (file_exists($this->cookieJar)) {
            unlink($this->cookieJar);
        }
    }

    private function curlGet($url, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return ['body' => $body, 'final_url' => $info['url'], 'http_code' => $info['http_code']];
    }

    private function curlPost($url, $postData, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => array_merge([
                'X-Requested-With: XMLHttpRequest',
                'Content-Type: application/x-www-form-urlencoded',
            ], $headers),
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body, 'http_code' => $code];
    }

    public function extractToken($startUrl = 'https://haxvip365.fun/getkey.php') {
        $step1 = $this->curlGet($startUrl);
        $currentUrl = $step1['final_url'];

        if (strpos($currentUrl, 'linkx.me') === false) {
            throw new Exception('Không redirect đến linkx.me. URL cuối: ' . $currentUrl);
        }

        $html = $step1['body'];

        preg_match('#linkx\.me/([A-Za-z0-9]+)#', $currentUrl, $aliasMatch);
        $alias = $aliasMatch[1] ?? null;
        if (!$alias) {
            throw new Exception('Không lấy được alias từ URL: ' . $currentUrl);
        }

        preg_match('/name="_csrfToken"[^>]*value="([^"]+)"/', $html, $csrfMatch);
        $csrfToken = $csrfMatch[1] ?? null;
        if (!$csrfToken) {
            throw new Exception('Không tìm thấy _csrfToken trong HTML');
        }

        preg_match('/name="ad_form_data"\s+value="([^"]+)"/', $html, $adMatch);
        $adFormData = $adMatch[1] ?? null;
        if (!$adFormData) {
            throw new Exception('Không tìm thấy ad_form_data trong HTML');
        }

        sleep(5);

        $gosl = $this->curlPost(
            "https://linkx.me/links/gosl/?alias={$alias}",
            ['ad_form_data' => $adFormData],
            ["X-CSRF-Token: {$csrfToken}"]
        );

        $json = json_decode($gosl['body'], true);
        if (!$json || empty($json['url'])) {
            throw new Exception('Không lấy được url từ response gosl. Body: ' . $gosl['body']);
        }

        $redirectUrl = $json['url'];

        $result = [
            'success' => false,
            'token' => null,
            'finalUrl' => null,
            'redirectUrl' => $redirectUrl,
        ];

        if (preg_match('/[?&]url=([^&]+)/', $redirectUrl, $urlParamMatch)) {
            $innerUrl = urldecode($urlParamMatch[1]);
            $result['finalUrl'] = $innerUrl;
            $result['success'] = true;

            if (preg_match('/[?&]token=([^&\s"\']+)/', $innerUrl, $tokenMatch)) {
                $result['token'] = urldecode($tokenMatch[1]);
            }
        } else {
            // fallback: token có thể nằm ngay trên redirectUrl
            if (preg_match('/[?&]token=([^&\s"\']+)/', $redirectUrl, $tokenMatch)) {
                $result['token'] = urldecode($tokenMatch[1]);
                $result['finalUrl'] = $redirectUrl;
                $result['success'] = true;
            }
        }

        return $result;
    }
}

// === THỰC THI ===
try {
    $extractor = new LinkExtractorPHP();
    $result = $extractor->extractToken('https://haxvip365.fun/getkey.php');

    if ($result['success']) {
        $shortUrl = $result['finalUrl'];


    } else {
        $shortUrl = null;
    }

} catch (Exception $e) {
    $shortUrl = null;
}



// $api = "https://haxvip365.fun/getkey.php";

// $ch = curl_init();
// curl_setopt_array($ch, [
//     CURLOPT_URL => $api,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_TIMEOUT => 15,
//     CURLOPT_SSL_VERIFYPEER => false,
//     CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
//     // QUAN TRỌNG: Không cho phép tự động chuyển hướng
//     CURLOPT_FOLLOWLOCATION => false, 
//     // Yêu cầu trả về Header để lấy link Location
//     CURLOPT_HEADER => true 
// ]);

// $response = curl_exec($ch);
// $info = curl_getinfo($ch);
// curl_close($ch);

// // 1. Kiểm tra nếu Server trả về mã 301 hoặc 302 (Redirect)
// if ($info['http_code'] == 301 || $info['http_code'] == 302) {
//     if (preg_match('/Location: (.*)/i', $response, $matches)) {
//         $shortUrl = trim($matches[1]);
//     }
// } else {
//     $headerSize = $info['header_size'];
//     $shortUrl = trim(substr($response, $headerSize));
// }

// // Kiểm tra kết quả
// if (!$shortUrl || strlen($shortUrl) < 5) {
//     die("Lỗi: Không tìm thấy link liên hệ admin.");
// }

// $shortUrl = $shortUrl . "?keygmvmoba";





 ?>