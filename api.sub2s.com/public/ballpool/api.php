<?php
$target_url = "https://kissmodkey.online/Getkey.php?admin=dodainam";
$game_value = "8BALLPOOL"; 

$post_fields = [
    'game'   => $game_value,
    'action' => 'Nhận key miễn phí'
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL            => $target_url,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($post_fields),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
    CURLOPT_REFERER        => $target_url,
    CURLOPT_FOLLOWLOCATION => true,
]);

$cookie_file = tempnam(sys_get_temp_dir(), 'cookie');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

$result    = curl_exec($ch);
$final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

/**
 * 🔥 ƯU TIÊN BẮT window.location.href TRONG HTML
 */
if (preg_match('/window\.location\.href\s*=\s*[\'"]([^\'"]+)[\'"]/', $result, $matches)) {
    $final_url = $matches[1];
}

/**
 * 🔥 TRÍCH XUẤT URL ENCODE (KHÔNG DECODE)
 */
$encodedUrl = null;

if ($final_url) {
    $query = parse_url($final_url, PHP_URL_QUERY);
    if ($query) {
        parse_str($query, $params);

        if (!empty($params['url'])) {
            $encodedUrl = $params['url'];
        } elseif (!empty($params['link'])) {
            $encodedUrl = $params['link'];
        }
    }
}

/**
 * OUTPUT
 */
if ($encodedUrl) {
    echo $encodedUrl;
} else {
    echo "Lỗi: liên hệ admin.";
}

curl_close($ch);
@unlink($cookie_file);
?>
