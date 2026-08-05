<?php
require 'functions.php';

$ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$key = trim($_POST['key'] ?? '');

$data = loadKeys();
$settings = $data['settings'] ?? [];

if (!$settings['delete_on_copy']) {
    echo json_encode(['status' => 'skip']);
    exit;
}

/* Không xóa key chung */
if (in_array($key, $data['global_keys'], true)) {
    echo json_encode(['status' => 'global']);
    exit;
}

/* Xóa key riêng */
$data['available'] = array_values(
    array_diff($data['available'], [$key])
);

unset($data['assigned'][$ip]);

saveKeys($data);

echo json_encode(['status' => 'deleted']);
?>