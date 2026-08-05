<?php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);

require ROOTPATH . 'vendor/autoload.php';
require SYSTEMPATH . 'bootstrap.php';

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Services;

// 1. Tạo request / response
$request  = Services::request();
$response = Services::response();

// 2. Khởi tạo controller ĐÚNG CHUẨN
$controller = new \App\Controllers\LinkBuy();
$controller->initController($request, $response, Services::logger());

// 3. Gọi webhook
$response = $controller->callbackBank();

// 4. Send
$response->send();
