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


$toplink =
    "https://toplinks.io/st?apikey=1a15e98521504f8eada0014117ad6ac6"
    . "&url=" . urlencode($shortUrl);

    
$toplink1 =
    "https://toplinks.io/st?apikey=631a4048dd6242a5b16821fc2622a853"
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

$counterFile = __DIR__ . '/toplink_turn.txt';

if (!file_exists($counterFile)) {
    file_put_contents($counterFile, '0');
}

$turn = (int) file_get_contents($counterFile);
$turn++;

file_put_contents($counterFile, (string)$turn);

$chosenToplink = ($turn % 4 === 0) ? $toplink1 : $toplink;


$redirect =
    "https://nhapcode1s.com/st?api=0943af4e23ccbae8f55a2688"
    . "&url=" . urlencode($chosenToplink);

header("Location: $redirect");
exit;
