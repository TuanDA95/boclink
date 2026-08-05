<?php
$api = "https://hackergammer.online/GETKEY/Ngocbong2026&type=com.vnggames.cfl.crossfirelegends";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false
]);

$shortUrl = trim(curl_exec($ch));
curl_close($ch);

if (!$shortUrl) {
    die("Lỗi lấy link key, vui lòng liên hệ Admin ");
}

$shortUrl = $shortUrl . "?keygmvmoba";
// $funlink =
//     "https://funlink.io/st"
//     . "?apikey=507971c9fd6540f1ac3e09b985205800"
//     . "&url=" . urlencode($shortUrl) . "?keygmvmoba";

    
// $ipglobal =
//     "https://web.bbmkts.com/st"
//     . "?api=03bdde94119e529cc1c7b0cd8bca428aee16f149"
//     . "&url=" . urlencode($shortUrl) . "?keygmvmoba";

// $blockedRedirect = "https://shrtfly.com/st"
//     . "?api=bafebf3d7496b49cd58a9e16b3cd3346" . "&type=1"
//     . "&url=" . urlencode($ipglobal);

// $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// function isBlockedIP($ip) {
//    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,countryCode");
//     curl_setopt_array($ch, [
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_TIMEOUT => 3,
//     ]);
//     $res = curl_exec($ch);
//     curl_close($ch);

//     if (!$res) return false;

//     $data = json_decode($res, true);

//     if (!isset($data['status']) || $data['status'] !== 'success') return false;

//     return $data['countryCode'] !== 'VN';
// }

// if (isBlockedIP($ip)) {
//     header("Location: $blockedRedirect");
//     exit;
// }

// $redirect =
//     "https://bbmkts.com/ql"
//     . "?token=1a471588d7365b07a2d71401"
//     . "&longurl=" . urlencode($funlink);

// header("Location: $redirect");
// exit;
?>