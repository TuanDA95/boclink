<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('dbg', 'Auth::index');
$routes->get('logout', 'Auth::logout');
$routes->get('/', 'User::index');
$routes->match(['get', 'post'], '/', 'LinkBuy::login');
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->match(['get', 'post'], 'register', 'Auth::register');//Server
$routes->get('acc', 'User::acc');



//
$routes->match(['get', 'post'], 'settings', 'User::settings');
$routes->match(['get', 'post'], 'Server', 'User::Server');

//
//testing
$routes->match(['get', 'post'], 'New', 'Home::index');
//$routes->get('server', 'User::server');
//

$routes->group('keys', function ($routes) {
	$routes->match(['get', 'post'], '/', 'Keys::index');
	$routes->match(['get', 'post'], 'generate', 'Keys::generate');
		$routes->match(['get', 'post'], 'deleteUnused', 'Keys::deleteUnused');
	$routes->get('(:num)', 'Keys::edit_key/$1');
	$routes->get('reset', 'Keys::api_key_reset');
	$routes->post('edit', 'Keys::edit_key');
	$routes->match(['get', 'post'], 'api', 'Keys::api_get_keys');
	$routes->match(['get'],'deleteExp','Keys::deleteExpired');
      //  $routes->match(['get'],'deleteUnused','Keys::deleteUnused');
});

$routes->group('admin', ['filter' => 'admin'], function ($routes) {
	$routes->match(['get', 'post'], 'create-referral', 'User::ref_index');
	$routes->match(['get', 'post'], 'manage-users', 'User::manage_users');
	$routes->match(['get', 'post'], 'user/(:num)', 'User::user_edit/$1');
	/* --------------------------- Admin API Grouping -------------------------- */
	$routes->group('api', function ($routes) {
		$routes->match(['get', 'post'], 'users', 'User::api_get_users');
	});
});

$routes->match(['get', 'post'], 'connect', 'Connect::index');

$routes->get('getkey', 'GetKey::index');
$routes->get('(:segment)/getkey', 'GetKey::getKey/$1');
$routes->get('key', 'GetKey::key');
$routes->get('(:segment)/getkeyapp', 'GetKey::getKey/$1');
$routes->add('getkey/processGenerate/(:segment)', 'GetKey::processGenerate/$1');
$routes->group('', ['filter' => 'auth'], function ($routes) {

$routes->get('apiurl', 'ApiShorten::index'); 
$routes->post('apiurl/save', 'ApiShorten::save');
});

$routes->get('getkey', 'nb::index');
$routes->get('(:segment)/getkeyfree', 'nb::getKey/$1');
$routes->get('key', 'nb::key');


$routes->get('(:segment)/getkeyauto', 'GetKeyIPA::getKey/$1');
$routes->add('getkeyauto/processGenerate/(:segment)', 'GetKeyIPA::processGenerate/$1');



$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Trang chỉnh API rút gọn
    $routes->get('apishorten', 'Shorten::index');
    $routes->post('apishorten/save', 'Shorten::save');

});
$routes->get('admin/link', 'LinkController::add');
$routes->post('admin/link/create', 'LinkController::create');
$routes->get('admin/link/edit/(:num)', 'LinkController::edit/$1');
$routes->post('admin/link/update/(:num)', 'LinkController::update/$1');
// Link trung gian (URL đẹp)

$routes->get('key/(:segment)', 'LinkController::go/$1');
$routes->get('st', 'LinkController::quicklink');
$routes->get('devst', 'LinkController::developer');


// $routes->get('api/key', function () {
//     require ROOTPATH . 'public/keyapi/api.php';
// });
$routes->get('api/key', 'GetKey::handleApiKey');
$routes->get('api/keycfm', 'GetKey::handleApiKey2');
$routes->get('api/keycf', 'GetKey::handleApiKey3');
$routes->get('api/fcmobile', 'GetKey::handleApiKey4');
$routes->get('api/keyproxy', 'GetKey::handleApiKey5');

$routes->get('api/codekey', function () {
    require ROOTPATH . 'public/keyapi/index.php';
});
$routes->get('api/nhapcode', function () {
    require ROOTPATH . 'public/keyapi/apinhapcode.php';
});
$routes->get('api/funtop', function () {
    require ROOTPATH . 'public/keyapi/apifuntop.php';
});

$routes->get('udid/config', function () {
    require ROOTPATH . 'public/binhbun.mobileconfig';
});
$routes->get('udid/done', function () {
    require ROOTPATH . 'public/get_udid.php';
});
$routes->get('udid/profile', function () {
    require ROOTPATH . 'public/udid/index.php';
});
$routes->get('udid/api', function () {
    require ROOTPATH . 'public/udid/config.php';
});
$routes->get('udid/api/done', function () {
    require ROOTPATH . 'public/udid/get_udid_by_ip.php';
});
// ADMIN
$routes->group('admin', function ($routes) {
    $routes->get('ios/package', 'PackageController::index');
    $routes->post('ios/package/create', 'PackageController::create');

    $routes->get('ios/key', 'KeyController::index');
    $routes->post('ios/key/create', 'KeyController::create');
});

// API cho iOS
$routes->get('api/ios/key', 'Api\KeyApiController::index');
$routes->get('keys/delete_expired', 'Keys::delete_expired');
$routes->get('keys/reset_all_devices', 'Keys::reset_all_devices');
$routes->get('keys/update_all_expiry', 'Keys::update_all_expiry');
$routes->post('keys/update_status', 'Keys::update_status');
$routes->get('keys/custom', 'KeysCustom::index');
$routes->get('admin', 'KeysCustom::index');
$routes->post('keys/generate_custom_action', 'KeysCustom::action');



$routes->group('admin', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('customers', 'Admin::customers');
    $routes->post('customers/update-balance', 'Admin::updateBalance');
    $routes->post('customers/change-password', 'Admin::changePassword');
    
    $routes->get('history/links', 'Admin::historyLinks');
    $routes->get('history/cards', 'Admin::historyCards');
    $routes->get('history/banks', 'Admin::historyBanks');
    $routes->get('approve-bank/(:num)', 'Admin::approveBank/$1');
});

$routes->get('apiurl/get_quick_banks', 'ApiUrl::get_quick_banks');
$routes->post('apiurl/update_quick_banks', 'ApiUrl::update_quick_banks');
// Route cho Khách hàng (Customer)
// Route Trang Trung Gian (Nơi quyết định mua hay vượt link)
$routes->get('getlink/(:any)', 'LinkBuy::intermediary/$1');

$routes->get('getlinkkey/(:any)', 'GetKeyIPA::interme/$1');

// Thử thay đổi dòng này trong Routes.php để kiểm tra
$routes->get('customer/buy/(:segment)', 'LinkBuy::processBuy/$1');
$routes->get('customers/buys/(:segment)', 'GetKeyIPA::processBuy/$1');
// Route Khách Hàng (Auth & Dashboard)
$routes->group('customer', function($routes) {
    $routes->get('login', 'LinkBuy::login');       // Trang đăng nhập
    $routes->get('register', 'LinkBuy::register'); // Trang đăng ký
    $routes->post('auth', 'LinkBuy::authProcess'); // Xử lý đăng nhập/đăng ký
    $routes->get('logout', 'LinkBuy::logout');     // Đăng xuất
    
    $routes->get('dashboard', 'LinkBuy::dashboard'); // Trang quản lý chính (Dashboard)
    
    // $routes->get('buy/(:any)', 'LinkBuy::processBuy/$1');    // Xử lý mua link (AJAX)
    $routes->add('card', 'LinkBuy::cardSubmit');   
    $routes->add('bank', 'LinkBuy::bankAjax');   
    $routes->add('crypto', 'LinkBuy::cryptoAjax');   
});
$routes->group('webhook', function($routes) {
    $routes->match(['get', 'post'], 'card', 'LinkBuy::callbackCard');
    $routes->match(['get', 'post'], 'bank', 'LinkBuy::callbackBank');
});

$routes->get('api/check-bundle/(:segment)', 'DylibManager::checkStatus/$1');

$routes->get('admin/bundle-manager', 'DylibManager::index');
$routes->post('admin/bundle-update', 'DylibManager::update');
$routes->get('admin/bundle-delete/(:num)', 'DylibManager::bundleDelete/$1');
$routes->get('customer/checkPayment/(:any)', 'LinkBuy::checkPayment/$1');


// Admin Routes
$routes->group('admin', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('game-categories', 'Admin::gameCategories');
    $routes->post('add-category', 'Admin::addCategory');
    $routes->post('update-category', 'Admin::updateCategory');
    $routes->get('delete-category/(:num)', 'Admin::deleteCategory/$1');
    
    $routes->get('manage-keys/(:num)', 'Admin::manageKeys/$1');
    $routes->post('add-keys', 'Admin::addKeys');
    $routes->get('delete-key/(:num)', 'Admin::deleteKey/$1');
    $routes->post('update-all-price', 'Admin::updateAllPrice');
    $routes->get('delete-all-keys/(:num)', 'Admin::deleteAllKeys/$1');
    $routes->post('update-single-key', 'Admin::updateSingleKey');
    $routes->post('edit-category', 'Admin::editCategory');
    $routes->post('update-key-price', 'Admin::updateKeyPrice');
    $routes->get('admin/sales-history', 'AdminController::salesHistory');

    $routes->post('update-sort-order', 'Admin::updateSortOrder');

    });

// Customer Routes
$routes->get('store', 'Customer::store');
$routes->get('store/view/(:num)', 'Customer::viewGame/$1');
$routes->post('store/buy', 'Customer::buyKey');
$routes->get('history-keys', 'Customer::historyKeys');
$routes->get('historyKeys', 'Customer::historylogKeys');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
