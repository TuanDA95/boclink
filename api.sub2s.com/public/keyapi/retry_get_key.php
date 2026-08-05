<?php
require 'functions.php';

header('Content-Type: application/json');

$data = loadKeys();
if (!empty($data['global_keys'])) {
    echo json_encode([
        'success' => true,
        'type'    => 'global'
    ]);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$isOut = false;

$key = getKeyForIP($ip, $isOut);

echo json_encode([
    'success' => (!$isOut && !empty($key)),
    'type'    => 'ip'
]);
