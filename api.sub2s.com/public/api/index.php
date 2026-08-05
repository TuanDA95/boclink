<?php
$api = "https://solitudepremium.click/ngoc/key.php";

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL            => $api,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,

    CURLOPT_USERAGENT =>
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/137.0 Safari/537.36',

    CURLOPT_REFERER =>
        'https://solitudepremium.click/',
]);

$shortUrl = trim(curl_exec($ch));

if (curl_errno($ch)) {
    throw new \RuntimeException(curl_error($ch));
}

curl_close($ch);

$shortUrl = $shortUrl . "/gmvmoba";

echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo $shortUrl;