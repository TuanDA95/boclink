<?php
require 'functions.php';

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$isOutOfKey = false;

$key = getKeyForIP($ip, $isOutOfKey);

if ($isOutOfKey || empty($key)) {
    header("Location: /public/keyapi/out_of_key_modal.php");
    exit;
}

$shortUrl = "https://key.gmvmoba.com/api/codekey?key=" . urlencode($key);

// if (isBlockedIP($ip)) {
//     $ipglobal = "https://web.bbmkts.com/st?api=03bdde94119e529cc1c7b0cd8bca428aee16f149" . "&url=" . urlencode($shortUrl);
    
//     $blockedRedirect = "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401" . "&longurl=" . urlencode($ipglobal);
    
//     header("Location: $blockedRedirect");
//     exit;
// }

// $funlink = "https://funlink.io/st?apikey=507971c9fd6540f1ac3e09b985205800" . "&url=" . urlencode($shortUrl);

// $toplink = "https://toplinks.io/st?apikey=631a4048dd6242a5b16821fc2622a853" . "&url=" . urlencode($funlink);

// $redirect = "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401" . "&longurl=" . urlencode($toplink);

// header("Location: $redirect");
// exit;

// require 'functions.php';

// $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
// $isOutOfKey = false;

// $key = getKeyForIP($ip, $isOutOfKey);

// if ($isOutOfKey || empty($key)) {
//     header("Location: /public/keyapi/out_of_key_modal.php");
//     exit;
// }


// $shortUrl = "https://key.gmvmoba.com/api/codekey?key=" . urlencode($key);


// $funlink =
//     "https://funlink.io/st?apikey=507971c9fd6540f1ac3e09b985205800"
//     . "&url=" . urlencode($shortUrl);

// $ipglobal =
//     "https://web.bbmkts.com/st?api=03bdde94119e529cc1c7b0cd8bca428aee16f149"
//     . "&url=" . urlencode($shortUrl);

// $blockedRedirect =
//     "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401"
//     . "&longurl=" . urlencode($ipglobal);

// if (isBlockedIP($ip)) {
//     header("Location: $blockedRedirect");
//     exit;
// }

// $redirect =
//     "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401"
//     . "&longurl=" . urlencode($funlink);

// header("Location: $redirect");
// exit;
