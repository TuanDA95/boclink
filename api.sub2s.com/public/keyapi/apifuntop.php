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


$funlink =
    "https://funlink.io/st?apikey=2b777f760eb34ed881d91548fe4fdcec"
    . "&url=" . urlencode($shortUrl);

$funlink1 =
    "https://funlink.io/st?apikey=507971c9fd6540f1ac3e09b985205800"
    . "&url=" . urlencode($shortUrl);

$ipglobal =
    "https://web.bbmkts.com/st?api=03bdde94119e529cc1c7b0cd8bca428aee16f149"
    . "&url=" . urlencode($shortUrl);

$blockedRedirect =
    "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401"
    . "&longurl=" . urlencode($ipglobal);

if (isBlockedIP($ip)) {
    header("Location: $blockedRedirect");
    exit;
}

$redirect =
    "https://toplinks.io/st?apikey=1a15e98521504f8eada0014117ad6ac6"
    . "&url=" . urlencode($funlink);

$redirect1 =
    "https://toplinks.io/st?apikey=631a4048dd6242a5b16821fc2622a853"
    . "&url=" . urlencode($funlink1);

$turnFile = __DIR__ . '/funlink_turn.txt';

if (!file_exists($turnFile)) {
    file_put_contents($turnFile, '0');
}

$turn = (int) file_get_contents($turnFile);
$turn++;

if ($turn >= 4) {
    $finalRedirect = $redirect1;
    $turn = 0; 
} else {
    $finalRedirect = $redirect;
}

file_put_contents($turnFile, $turn);

header("Location: $finalRedirect");
exit;

exit;
