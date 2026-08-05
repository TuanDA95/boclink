<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$root = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;

if (!file_exists($root . 'vendor/autoload.php')) {
    die("Lỗi: Không tìm thấy vendor/autoload.php tại: " . $root);
}

define('ROOTPATH', $root);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

require ROOTPATH . 'vendor/autoload.php';

if (!file_exists(SYSTEMPATH . 'bootstrap.php')) {
    die("Lỗi: Không tìm thấy SYSTEMPATH/bootstrap.php tại: " . SYSTEMPATH);
}

require SYSTEMPATH . 'bootstrap.php';

use Config\Services;

// 1. Khởi tạo Request/Response
$request  = Services::request();
$response = Services::response();

// 2. Gọi Controller của bạn (LinkBuy)
$controller = new \App\Controllers\LinkBuy();
$controller->initController($request, $response, Services::logger());

// 3. Thực thi hàm callbackCard
$res = $controller->callbackCard();

// 4. Trả về kết quả cho phía đối tác (doithe1s)
if ($res instanceof \CodeIgniter\HTTP\ResponseInterface) {
    $res->send();
} else {
    echo $res;
}