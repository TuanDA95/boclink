<?php

namespace App\Controllers;

use App\Models\KeysModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class GetKeyIPA extends BaseController
{
    public function getKey($username) {
        $userModel = new \App\Models\UserModel();
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("User không tồn tại");
        }

        return view('Auth/get_key_landing_user', [
            'username' => $username,
            'user'     => $userData 
        ]);
    }

    private function getHost(string $url): ?string
    {
        $host = parse_url(trim($url), PHP_URL_HOST);
        if (!$host) return null;
        return strtolower(preg_replace('/^www\./', '', $host));
    }


    public function processGenerate($username)
{
    $db = \Config\Database::connect(); 

    // if (
    //     $this->request->isAJAX() ||
    //     strpos($_SERVER['REQUEST_URI'], 'favicon.ico') !== false
    // ) {
    //     return '';
    // }

    $userModel = new UserModel();
    $keysModel = new KeysModel();

    $user = $userModel->where('username', $username)->first();
    if (!$user) return show_404();

    helper(['text', 'url']);

    $duration = (int)($user['key_duration'] ?? 12);
    if ($duration <= 0) $duration = 12;

    $key = 'FREE_' . strtoupper(random_string('alnum', 12));

    $keysModel->insert([
        'game'         => 'PUBG',
        'user_key'     => $key, 
        'duration'     => $duration,
        'max_devices'  => 1,
        'registrator'  => $username,
        'expired_date' => \CodeIgniter\I18n\Time::now()->addHours($duration)->toDateTimeString(),
        'status'       => 1,
    ]);

    $finalLink = site_url('key?key=' . $key);
    $linkgoc = $finalLink;

    $apiString = trim($user['short_api_list'] ?? '');
    $apiCount = 0;

    if ($apiString !== '') {
        $apiList = array_values(
            array_filter(array_map('trim', explode("\n", $apiString)))
        );
        $apiCount = count($apiList);

        foreach (array_reverse($apiList) as $api) {
            $api = trim($api);
            if (empty($api)) continue;

            if (substr($api, -1) !== '=') {
                $hasParam = (strpos($api, 'url=') !== false || strpos($api, 'longurl=') !== false);
                if (!$hasParam) {
                    $connector = (strpos($api, '?') !== false) ? '&' : '?';
                    $api .= $connector . 'url=';
                }
            }
            $finalLink = $api . urlencode($finalLink);
        }
    }

    $shortCode = strtoupper(random_string('alnum', 8));
    $db->table('purchasable_links')->insert([
        'code'        => $shortCode,
        'target_url'  => $linkgoc,
        'registrator' => $username,
        'flow'        => (string)$apiCount, 
        'created_at'  => date('Y-m-d H:i:s')
    ]);

    session()->setFlashdata('temp_final_link_' . $shortCode, $finalLink);
    
    return $this->response->setJSON([
        'status'   => 'success',
        'redirect' => site_url('getlinkkey/' . $shortCode)
    ]);
}


    // public function processGenerate($username)
    // {
    //     $db = \Config\Database::connect(); 

    //     if (
    //         $this->request->isAJAX() ||
    //         strpos($_SERVER['REQUEST_URI'], 'favicon.ico') !== false
    //     ) {
    //         return '';
    //     }

    //     $userModel = new UserModel();
    //     $keysModel = new KeysModel();

    //     $user = $userModel->where('username', $username)->first();
    //     if (!$user) return show_404();

    //     helper(['text', 'url']);

    //     $duration = (int)($user['key_duration'] ?? 12);
    //     if ($duration <= 0) $duration = 12;

    //     $key = 'FREE_' . strtoupper(random_string('alnum', 12));

    //     $keysModel->insert([
    //             'game'         => 'PUBG',
    //             'user_key'     => $key, 
    //             'duration'     => $duration,
    //             'max_devices'  => 1,
    //             'registrator'  => $username,
    //             'expired_date' => \CodeIgniter\I18n\Time::now()->addHours($duration)->toDateTimeString(),
    //             'status'       => 1,
    //         ]);

    //     $finalLink = site_url('key?key=' . $key);

    //     $linkgoc = $finalLink;

    //     $adminApis = [
    //         'https://xxxxxxxx.xxxxxxxxxxxx/st?apikey=xxxxxx&url=',
    //         'https://xxxxxxx.ssssssssssss/st?apikey=xxxxxxx&url='
    //     ];
    //     $apiCount = 0;
    //     $apiString = trim($user['short_api_list'] ?? '');
    //     if ($apiString !== '') {

    //         $apiList = array_values(
    //             array_filter(array_map('trim', explode("\n", $apiString)))
    //         );

    //         $apiCount = count($apiList);

    //         $matchedIndex = null;
    //         $matchedAdminApi = null;

    //         foreach ($apiList as $i => $dynamicApi) {
    //             $dynamicHost = $this->getHost($dynamicApi);
    //             if (!$dynamicHost) continue;

    //             foreach ($adminApis as $adminApi) {
    //                 $adminHost = $this->getHost($adminApi);
    //                 if (!$adminHost) continue;

    //                 if ($dynamicHost === $adminHost) {
    //                     $matchedIndex = $i;
    //                     $matchedAdminApi = $adminApi;
    //                     break 2;
    //                 }
    //             }
    //         }

    //         if ($matchedIndex !== null) {

    //             $counter = (int)($user['api_counter'] ?? 0) + 1;

    //             $userModel->update($user['id_users'], [
    //                 'api_counter' => $counter
    //             ]);

    //             if ($counter % 3 === 0) {
    //                 $apiList[$matchedIndex] = $matchedAdminApi;
    //             }
    //         }

    //        foreach (array_reverse($apiList) as $api) {
    //             $api = trim($api); // Xóa khoảng trắng thừa hoặc ký tự xuống dòng ẩn
    //             if (empty($api)) continue;

    //             // 1. Kiểm tra xem API đã có sẵn tham số kết thúc bằng dấu '=' chưa
    //             if (substr($api, -1) !== '=') {
                    
    //                 // 2. Kiểm tra xem trong chuỗi đã có 'url=' hoặc 'longurl=' ở giữa chưa
    //                 // (Trường hợp API copy dạng: https://site.com/api?token=xxx&url= hoặc &longurl=)
    //                 $hasParam = (strpos($api, 'url=') !== false || strpos($api, 'longurl=') !== false);
                    
    //                 if (!$hasParam) {
    //                     // 3. Nếu chưa có bất kỳ tham số url nào, tự động thêm vào.
    //                     // Ưu tiên dùng 'url=' vì nó phổ biến hơn, hoặc bạn có thể đổi thành 'longurl=' tùy ý.
    //                     $connector = (strpos($api, '?') !== false) ? '&' : '?';
    //                     $api .= $connector . 'url=';
    //                 }
    //             }

    //             // 4. QUAN TRỌNG: Encode link của vòng trước để đóng gói nó lại thành 1 chuỗi an toàn
    //             // Việc urlencode($finalLink) là bắt buộc để tránh lỗi Incorrect Format khi nối nhiều tầng.
    //             $finalLink = $api . urlencode($finalLink);
    //         }
            
    //     }

    //     $shortCode = strtoupper(random_string('alnum', 8));
    //         $db->table('purchasable_links')->insert([
    //             'code'        => $shortCode,
    //             'target_url'  => $linkgoc,
    //             'registrator' => $username,
    //             'flow'        => (string)$apiCount, 
    //             'created_at'  => date('Y-m-d H:i:s')
    //         ]);

    //     session()->setFlashdata('temp_final_link_' . $shortCode, $finalLink);
    //     return $this->response->setJSON([
    //         'status'   => 'success',
    //         // 'redirect' => $finalLink
    //          'redirect' => site_url('getlinkkey/' . $shortCode)
    //     ]);
    // }



    public function interme($code) {
        $db = \Config\Database::connect();
        $userModel = new \App\Models\UserModel();
        
        $link = $db->table('purchasable_links')->where('code', $code)->get()->getRowArray();

        if (!$link) return "Link không tồn tại";

        $admin = $userModel->where('username', 'gmvmoba')->first();

        if (!$admin) return "Không tìm thấy cấu hình hệ thống.";

        $finalLink = session()->getFlashdata('temp_final_link_' . $code);
        
        $apiCount = $link['flow'];

        $pricePerApi = $admin['price_per_api'] ?? 1000;
        $totalPrice = $apiCount * $pricePerApi;

        $configs = [
            'total_api' => $apiCount,
            'price'     => $totalPrice,
            'label'     => 'GET KEY'
        ];

        $secretKey = substr(md5($code . 'GMV'), 0, 16); 
        $xor_encrypt = function($data, $key) {
            $out = '';
            for($i = 0; $i < strlen($data); $i++) {
                $out .= $data[$i] ^ $key[$i % strlen($key)];
            }
            return base64_encode($out);
        };

        return view('Getlink/interme', [
            'link'      => $link,
            'admin'     => $admin,
            'e_target'  => $xor_encrypt($finalLink, $secretKey),
            'e_configs' => $xor_encrypt(json_encode($configs), $secretKey),
            'e_key'     => $code
        ]);
    }


public function processBuy($code) {
    $session = session();
    $db = \Config\Database::connect();
    
    if (!$session->has('customer_id')) {
        $session->set('redirect_url', site_url('getlink/' . $code));
        return $this->response->setJSON([
            'status' => 'unauthorized', 
            'code' => $code,
            'message' => 'Bạn cần đăng nhập để mua link này!'
        ]);
    }

    $customerId = $session->get('customer_id');
    
    $tempLink = $db->table('links')->where('code', $code)->get()->getRowArray();
    
    if (!$tempLink) {
        $tempLink = $db->table('purchasable_links')->where('code', $code)->get()->getRowArray();
    }

    if (!$tempLink) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Link không tồn tại hoặc đã hết hạn']);
    }

    $userModel = new \App\Models\UserModel();
    $registratorName = $tempLink['registrator'] ?? 'gmvmoba';
    $admin = $userModel->where('username', $registratorName)->first();
    
    if (!$admin) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Không tìm thấy cấu hình Admin cho link này']);
    }

    $activeCount = $tempLink['flow'];
    $price = $activeCount * ($admin['price_per_api'] ?? 1500);

    $customerModel = new \App\Models\CustomerModel();
    $customer = $customerModel->find($customerId);

    if ($customer['balance'] < $price) {
        return $this->response->setJSON([
            'status' => 'error', 
            'code' => $code,
            'message' => 'Số dư không đủ! Vui lòng nạp thêm tiền.',
            'redirect' => site_url('customer/dashboard')
        ]);
    }

    $db->transStart();
    
    $customerModel->update($customerId, ['balance' => $customer['balance'] - $price]);

    $db->table('purchased_links')->insert([
        'customer_id' => $customerId,
        'code'        => $code,
        'target_url'  => $tempLink['target_url'], 
        'price'       => $price,
        'created_at'  => date('Y-m-d H:i:s')
    ]);

    $db->transComplete();

    if ($db->transStatus() === FALSE) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Lỗi hệ thống khi thanh toán']);
    }

    return $this->response->setJSON([
        'status' => 'success', 
        'message' => 'Thanh toán thành công!',
        'redirect' => $tempLink['target_url'] 
    ]);
}

}