<?php

define('KEY_FILE', __DIR__ . '/keys.json');

function loadKeys()
{
    if (!file_exists(KEY_FILE)) {
        $init = [
            'settings' => [
                'assign_by_ip'   => 1,
                'delete_on_copy' => 1
            ],
            'global_keys' => [],
            'available'   => [],
            'assigned'    => [], // ip => key
            'used'        => []  // log key đã copy (optional)
        ];

        file_put_contents(
            KEY_FILE,
            json_encode($init, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    clearstatcache(true, KEY_FILE);

    $data = json_decode(file_get_contents(KEY_FILE), true);

    /* 🛡️ BẢO VỆ TRƯỜNG HỢP FILE BỊ HỎNG */
    if (!is_array($data)) {
        $data = [
            'settings' => [
                'assign_by_ip'   => 1,
                'delete_on_copy' => 1
            ],
            'global_keys' => [],
            'available'   => [],
            'assigned'    => [],
            'used'        => []
        ];
    }

    /* 🛠️ TỰ ĐỘNG BÙ FIELD THIẾU */
    $data['settings']    = $data['settings']    ?? ['assign_by_ip' => 1, 'delete_on_copy' => 1];
    $data['global_keys'] = $data['global_keys'] ?? [];
    $data['available']   = $data['available']   ?? [];
    $data['assigned']    = $data['assigned']    ?? [];
    $data['used']        = $data['used']        ?? [];

    cleanupExpiredGlobalKeys($data);
    saveKeys($data);
    return $data;
}

function saveKeys($data)
{
    clearstatcache(true, KEY_FILE);

    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        die('❌ JSON encode lỗi');
    }

    if (file_put_contents(KEY_FILE, $json, LOCK_EX) === false) {
        die('❌ Không ghi được keys.json');
    }

    clearstatcache(true, KEY_FILE);
}
function cleanupExpiredGlobalKeys(&$data)
{
    if (empty($data['global_keys'])) return;

    $now = time();

    $data['global_keys'] = array_values(array_filter(
        $data['global_keys'],
        function ($item) use ($now) {

            // key cũ → vô hạn
            if (is_string($item)) return true;

            // key có expire
            if (is_array($item) && isset($item['expire_at'])) {
                return $item['expire_at'] > $now;
            }

            return false;
        }
    ));
}

function isBlockedIP($ip) {
    if ($ip === '127.0.0.1' || $ip === '::1') return false;

    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,countryCode");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    if (!$res) return false;

    $data = json_decode($res, true);
    return (isset($data['status']) && $data['status'] === 'success' && $data['countryCode'] !== 'VN');
}
function getKeyForIP($ip, &$isOutOfKey = false)
{
    $data = loadKeys();

    $settings   = $data['settings'] ?? [];
    $available  = $data['available'] ?? [];
    $assigned   = $data['assigned'] ?? [];
    $globalKeys = $data['global_keys'] ?? [];

    if (empty($settings['assign_by_ip'])) {

        if (!empty($globalKeys)) {
            $k = $globalKeys[0];
            return is_array($k) ? $k['key'] : $k;
        }

        if (!empty($available)) {
            return $available[0];
        }

        $isOutOfKey = true;
        return null;
    }

   
    if (isset($assigned[$ip])) {
        $oldKey = $assigned[$ip];

        if (
            in_array($oldKey, $available, true) &&
            count(array_keys($assigned, $oldKey, true)) === 1
        ) {
            return $oldKey;
        }

        unset($assigned[$ip]);
    }

    $usedKeys = array_values($assigned);

    $freeKeys = array_values(
        array_diff($available, $usedKeys)
    );

    if (empty($freeKeys)) {
        $isOutOfKey = true;
        $data['assigned'] = $assigned;
        saveKeys($data);
        return null;
    }

    $newKey = array_shift($freeKeys);

    $assigned[$ip] = $newKey;

    $data['available'] = array_values(
        array_diff($available, [$newKey])
    );

    $data['assigned'] = $assigned;
    saveKeys($data);

    return $newKey;
}


function removeKeyAfterCopy($ip, $key)
{
    $data = loadKeys();

    $settings   = $data['settings'] ?? [];
    $assigned   = $data['assigned'] ?? [];
    $globalKeys = $data['global_keys'] ?? [];

    if (empty($settings['delete_on_copy'])) {
        return false;
    }

    foreach ($data['global_keys'] as $gk) {
        if (is_string($gk) && $gk === $key) return false;
        if (is_array($gk) && $gk['key'] === $key) return false;
    }

    if (empty($settings['assign_by_ip'])) {
        return false;
    }

    if (
        isset($assigned[$ip]) &&
        $assigned[$ip] === $key
    ) {
        unset($assigned[$ip]);
        $data['assigned'] = $assigned;
        saveKeys($data);
        return true;
    }

    return false;
}
function parseExpireTime($value, $unit)
{
    $value = (int)$value;
    if ($value <= 0 || !$unit) return null;

    $unit = strtolower($unit);

    $map = [
        'minute' => 'minute',
        'hour'   => 'hour',
        'day'    => 'day',
        'month'  => 'month',
        'year'   => 'year'
    ];

    if (!isset($map[$unit])) return null;

    return strtotime("+$value {$map[$unit]}");
}


function formatRemainingTime($sec)
{
    if ($sec <= 0) return '❌ Hết hạn';

    $m = floor($sec / 60);
    $h = floor($sec / 3600);
    $d = floor($sec / 86400);

    if ($d > 0) return $d . ' ngày';
    if ($h > 0) return $h . ' giờ';
    if ($m > 0) return $m . ' phút';

    return $sec . ' giây';
}

function logSpamIP($ip)
{
    $file = __DIR__ . '/logs/ip_spam.json';

    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }

    $data = json_decode(file_get_contents($file), true);
    $data[] = [
        'ip' => $ip,
        'time' => time()
    ];

    file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}
