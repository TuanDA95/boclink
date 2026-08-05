<?php

/**
 * KHAI BÁO HẰNG SỐ ĐƯỜNG DẪN
 * Sử dụng WRITEPATH của CodeIgniter để lưu vào thư mục writable/uploads/
 */
if (!defined('KEY_FILE')) {
    define('KEY_FILE', WRITEPATH . 'uploads/keys.json');
}

function loadKeys()
{
    // Đảm bảo thư mục tồn tại
    if (!is_dir(WRITEPATH . 'uploads')) {
        mkdir(WRITEPATH . 'uploads', 0775, true);
    }

    if (!file_exists(KEY_FILE)) {
        $init = [
            'settings'    => ['assign_by_ip' => 1, 'delete_on_copy' => 1],
            'global_keys' => [],
            'available'   => [],
            'assigned'    => [], 
            'used'        => []
        ];

        file_put_contents(
            KEY_FILE,
            json_encode($init, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    clearstatcache(true, KEY_FILE);
    $content = file_get_contents(KEY_FILE);
    $data = json_decode($content, true);

    // BẢO VỆ TRƯỜNG HỢP FILE TRỐNG HOẶC HỎNG
    if (!is_array($data)) {
        $data = [
            'settings'    => ['assign_by_ip' => 1, 'delete_on_copy' => 1],
            'global_keys' => [],
            'available'   => [],
            'assigned'    => [],
            'used'        => []
        ];
    }

    // Tự động bù các field thiếu để tránh lỗi Undefined Index
    $data['settings']    = $data['settings'] ?? ['assign_by_ip' => 1, 'delete_on_copy' => 1];
    $data['global_keys'] = $data['global_keys'] ?? [];
    $data['available']   = $data['available'] ?? [];
    $data['assigned']    = $data['assigned'] ?? [];
    $data['used']        = $data['used'] ?? [];

    cleanupExpiredGlobalKeys($data);
    
    // Không nên gọi saveKeys($data) ngay trong loadKeys để tránh tạo vòng lặp ghi file liên tục
    return $data;
}

function saveKeys($data)
{
    // Chuyển đổi mảng 'assigned' sang object nếu trống để JSON ra {} thay vì []
    if (empty($data['assigned'])) {
        $data['assigned'] = (object)[];
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        log_message('error', '❌ JSON encode lỗi: ' . json_last_error_msg());
        return false;
    }

    return file_put_contents(KEY_FILE, $json, LOCK_EX);
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
echo "<pre>"; 
print_r($data['available']); // Kiểm tra xem kho còn key không
die();
    $isOutOfKey = false;

    $settings   = $data['settings'] ?? [];
    $available  = $data['available'] ?? [];
    $assigned   = $data['assigned'] ?? [];
    $globalKeys = $data['global_keys'] ?? [];

    // 1. Nếu không bật gán theo IP
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

    // 2. Nếu IP đã được gán key rồi
    if (isset($assigned[$ip])) {
        $oldKey = $assigned[$ip];
        // Kiểm tra xem key này còn trong kho không
        if (in_array($oldKey, $available, true)) {
            return $oldKey;
        }
        unset($assigned[$ip]);
    }

    // 3. Tìm key tự do (available nhưng chưa bị assigned cho ai)
    $usedKeysInAssigned = array_values($assigned);
    $freeKeys = array_values(array_diff($available, $usedKeysInAssigned));

    if (empty($freeKeys)) {
        $isOutOfKey = true;
        $data['assigned'] = $assigned;
        saveKeys($data);
        return null;
    }

    // 4. Cấp key mới
    $newKey = array_shift($freeKeys);
    $assigned[$ip] = $newKey;

    // QUAN TRỌNG: Nếu settings['delete_on_copy'] = 1, chúng ta XÓA khỏi available luôn
    if (!empty($settings['delete_on_copy'])) {
        $data['available'] = array_values(array_diff($available, [$newKey]));
    }

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
