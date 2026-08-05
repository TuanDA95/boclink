<?php
header('Content-Type: application/json');

$ip = $_SERVER['REMOTE_ADDR'];
$file = "data/udid_$ip.txt";

if (file_exists($file)) {
    $udid = trim(file_get_contents($file));
    $clean_udid = str_replace('-', '', $udid);
    
    echo json_encode([
        "status" => true, 
        "udid" => $clean_udid
    ]);
} else {
    echo json_encode(["status" => false]);
}
?>