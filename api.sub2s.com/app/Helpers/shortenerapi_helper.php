<?php

if (!function_exists('shortenapi_url')) {
    function shortenapi_url($api_base, $long_url, $type = 'json', $res_key = 'url') {
        // Tự động thêm &url= hoặc ?url= nếu người dùng quên điền ở cuối link base
        $separator = (parse_url($api_base, PHP_URL_QUERY)) ? '' : ''; 
        $apiUrl = $api_base . urlencode($long_url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Chờ tối đa 15 giây cho mỗi lớp
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) return false;

        if ($type === 'json') {
            $data = json_decode($response, true);
            
            // Xử lý lấy key lồng nhau ví dụ: data.url
            $keys = explode('.', $res_key);
            $result = $data;
            foreach ($keys as $k) {
                if (isset($result[$k])) {
                    $result = $result[$k];
                } else {
                    return false;
                }
            }
            return is_string($result) ? $result : false;
        }

        return trim($response);
    }
}