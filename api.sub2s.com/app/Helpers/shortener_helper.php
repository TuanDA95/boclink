<?php
if (!function_exists('shorten_url')) {

    /**
     * Gọi API rút gọn link từ user config
     *
     * @param string $apiBase      Link API rút gọn, ví dụ https://bbmkts.com/dapi?token=XXX&longurl=
     * @param string $longUrl      Link cần rút gọn
     * @param string $responseType 'json' hoặc 'text'
     * @param string $responseKey  Tên key trả về trong JSON, ví dụ 'url', 'bbmktsUrl'
     * @return string|false        Link rút gọn hoặc false nếu lỗi
     */
    function shorten_url($apiBase, $longUrl, $responseType = 'json', $responseKey = 'url') {
        $url = $apiBase . urlencode($longUrl);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) return false;

        if ($responseType === 'json') {
            $data = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data[$responseKey])) {
                return $data[$responseKey];
            } else {
                return false;
            }
        }

        // Nếu text thì trả luôn
        return trim($result);
    }

}
