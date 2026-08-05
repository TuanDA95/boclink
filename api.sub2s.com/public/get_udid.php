<?php
ob_start(); 
$data = file_get_contents('php://input');

// Trích xuất phần XML từ dữ liệu máy khách gửi về
$plistBegin = '<?xml version="1.0"';
$plistEnd   = '</plist>';

$pos1 = strpos($data, $plistBegin);
$pos2 = strpos($data, $plistEnd);

if ($pos1 !== false && $pos2 !== false) {
    $data2 = substr($data, $pos1, $pos2 - $pos1 + strlen($plistEnd));

    $xml = xml_parser_create();
    xml_parse_into_struct($xml, $data2, $vs);
    xml_parser_free($xml);

    $UDID = "";
    $DEVICE_PRODUCT = "";
    $DEVICE_VERSION = "";
    $DEVICE_NAME = "";

    // Làm sạch mảng XML để lấy giá trị key/string
    $arrayCleaned = array();
    foreach($vs as $v){
        if($v['type'] == 'complete'){
            $arrayCleaned[] = $v;
        }
    }

    foreach($arrayCleaned as $index => $elem){
        if (isset($elem['value'])) {
            switch ($elem['value']) {
                case "UDID":
                    $UDID = $arrayCleaned[$index + 1]['value'] ?? "";
                    break;
                case "PRODUCT":
                    $DEVICE_PRODUCT = $arrayCleaned[$index + 1]['value'] ?? "";
                    break;
                case "VERSION":
                    $DEVICE_VERSION = $arrayCleaned[$index + 1]['value'] ?? "";
                    break;
                case "DEVICE_NAME":
                    $DEVICE_NAME = $arrayCleaned[$index + 1]['value'] ?? "";
                    break;
            }
        }
    }

    // Lấy IP thật (Quan trọng để Dylib check chính xác)
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (!empty($UDID)) {
        // Lưu UDID vào folder data
        if (!is_dir('../data')) {
            mkdir('../data', 0755, true);
        }
        
        // Ghi đè file theo IP. Dùng LOCK_EX để tránh xung đột file khi nhiều máy cùng truy cập
        file_put_contents("../data/udid_$ip.txt", trim($UDID), LOCK_EX);

        // Chuẩn bị tham số để chuyển hướng về trang hoàn tất
        $params = http_build_query([
            'UDID' => $UDID,
            'DEVICE_PRODUCT' => $DEVICE_PRODUCT,
            'DEVICE_VERSION' => $DEVICE_VERSION,
            'DEVICE_NAME' => $DEVICE_NAME
        ]);

        ob_clean();
        // Trả về Header 301/303 để trình duyệt Safari chuyển hướng về trang kết quả
        header("Location: /public/udid/index.php?" . $params, true, 301);
        exit;
    }
}

// Nếu không lấy được dữ liệu
header("HTTP/1.1 400 Bad Request");
echo "Lỗi trích xuất thông tin thiết bị.";
exit;