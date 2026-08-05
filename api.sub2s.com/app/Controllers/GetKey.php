<?php
namespace App\Controllers;

use App\Models\KeysModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class GetKey extends BaseController
{
    public function getKey($username) {
        $userModel = new \App\Models\UserModel();
        // Lấy dữ liệu user từ database theo username trên URL
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("User không tồn tại");
        }

        // Truyền BIẾN $user sang VIEW
        return view('Auth/get_key_landing', [
            'username' => $username,
            'user'     => $userData // Biến này cực kỳ quan trọng
        ]);
    }


    public function handleApiKey() {
        include ROOTPATH . 'public/keyapi/api.php'; 
        $username = 'gmvmoba'; 

        if (!empty($shortUrl)) {
            session()->set('pending_api_url', $shortUrl);
            session()->set('origin_flow', 1);
        }

        $userModel = new \App\Models\UserModel();
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Admin không tồn tại");
        }

        return view('Auth/get_key_landing', [
            'username' => $username,
            'user'     => $userData
        ]);
    }


    public function handleApiKey2() {
        include ROOTPATH . 'public/keycfm/api.php'; 
        $username = 'gmvmoba'; 

        if (!empty($shortUrl)) {
            session()->set('pending_api_url', $shortUrl);
            session()->set('origin_flow', 1);
        }

        $userModel = new \App\Models\UserModel();
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Admin không tồn tại");
        }

        return view('Auth/get_key_landing', [
            'username' => $username,
            'user'     => $userData
        ]);
    }


    public function handleApiKey3() {
        include ROOTPATH . 'public/keycf/api.php'; 
        $username = 'gmvmoba'; 

        if (!empty($shortUrl)) {
            session()->set('pending_api_url', $shortUrl);
            session()->set('origin_flow', 2);
        }

        $userModel = new \App\Models\UserModel();
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Admin không tồn tại");
        }

        return view('Auth/get_key_landing', [
            'username' => $username,
            'user'     => $userData
        ]);
    }

    public function handleApiKey4() {
        include ROOTPATH . 'public/fcmobile/api.php'; 
        $username = 'gmvmoba'; 

        if (!empty($shortUrl)) {
            session()->set('pending_api_url', $shortUrl);
            session()->set('origin_flow', 2);
        }

        $userModel = new \App\Models\UserModel();
        $userData = $userModel->where('username', $username)->first();

        if (!$userData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Admin không tồn tại");
        }

        return view('Auth/get_key_landing', [
            'username' => $username,
            'user'     => $userData
        ]);
    }


        public function handleApiKey5() {
            include ROOTPATH . 'public/keyproxy/api.php'; 
            $username = 'gmvmoba'; 

            if (!empty($shortUrl)) {
                session()->set('pending_api_url', $shortUrl);
                session()->set('origin_flow', 2);
            }

            $userModel = new \App\Models\UserModel();
            $userData = $userModel->where('username', $username)->first();

            if (!$userData) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Admin không tồn tại");
            }

            return view('Auth/get_key_landing', [
                'username' => $username,
                'user'     => $userData
            ]);
        }

    public function processGenerate($username) {
        $db = \Config\Database::connect(); 
        helper(['text']); 
        
        $userModel = new \App\Models\UserModel();
        $keysModel = new \App\Models\KeysModel(); 

        $user = $userModel->where('username', $username)->first();
        if (!$user) return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);

        $passedUrl = session()->get('pending_api_url');
        $duration = (int)($user['key_duration'] ?? 12);
        
        $finalTargetUrl = "";
        $generatedKey = "";

        if (!empty($passedUrl)) {
            $finalTargetUrl = $passedUrl;  
            $flow = (int)(session()->get('origin_flow') ?? 2); 
        } else {
            $generatedKey = 'FREE_' . strtoupper(random_string('alnum', 12));
            $finalTargetUrl = site_url('key?key=' . $generatedKey);
            $flow = 2;

            $keysModel->insert([
                'game'         => 'PUBG',
                'user_key'     => $generatedKey, 
                'duration'     => $duration,
                'max_devices'  => 1,
                'registrator'  => $username,
                'expired_date' => \CodeIgniter\I18n\Time::now()->addHours($duration)->toDateTimeString(),
                'status'       => 1,
            ]);
        }

        $existingLink = $db->table('purchasable_links')
                            ->where('target_url', $finalTargetUrl)
                            ->where('registrator', $username)
                            ->where('flow', $flow) 
                            ->orderBy('created_at', 'DESC')
                            ->get()
                            ->getRow();

        if ($existingLink) {
            session()->remove('pending_api_url'); 
            return $this->response->setJSON([
                'status'   => 'success', 
                'message'  => 'Reusing existing link',
                'redirect' => site_url('getlink/' . $existingLink->code)
            ]);
        }

        $shortCode = strtoupper(random_string('alnum', 8));
        $db->table('purchasable_links')->insert([
            'code'        => $shortCode,
            'target_url'  => $finalTargetUrl,
            'registrator' => $username,
            'flow'        => $flow, 
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        session()->remove('pending_api_url'); 

        return $this->response->setJSON([
            'status'   => 'success', 
            'redirect' => site_url('getlink/' . $shortCode)
        ]);
    }

//    public function processGenerate($username) {
//         // 1. KHỞI TẠO DATABASE (Khắc phục lỗi 500)
//         $db = \Config\Database::connect(); 
//         helper(['text']); 
        
//         $userModel = new \App\Models\UserModel();
//         $keysModel = new \App\Models\KeysModel();
//         $user = $userModel->where('username', $username)->first();

//         if (!$user) {
//             return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
//         }

//         $countryCode = $this->request->getGet('country') ?? 'VN';

//         // 2. TẠO KEY (Logic cũ của bạn)
//         $duration = $user['key_duration'] ?? 24;
//         $key = 'FREE_' . strtoupper(random_string('alnum', 12));
        
//         $keysModel->insert([
//             'game'         => 'PUBG',
//             'user_key'     => $key,
//             'duration'     => $duration,
//             'max_devices'  => 1,
//             'registrator'  => $username,
//             'expired_date' => \CodeIgniter\I18n\Time::now()->addHours($duration)->toDateTimeString(),
//             'status'       => 1,
//         ]);

//         // 3. ĐỊNH NGHĨA LINK GỐC (Chưa bọc qua API)
//         $currentUrl = site_url('key?key=' . $key);

//         // 4. SINH MÃ CODE NGẪU NHIÊN VÀ LƯU VÀO BẢNG purchasable_links
//         $shortCode = strtoupper(random_string('alnum', 8));
//         $db->table('purchasable_links')->insert([
//             'code'        => $shortCode,
//             'target_url'  => $currentUrl, // Đây là URL gốc chưa rút gọn
//             'registrator' => $username,
//             'created_at'  => date('Y-m-d H:i:s')
//         ]);

//         // 5. TRẢ VỀ REDIRECT ĐẾN TRANG TRUNG GIAN
//         return $this->response->setJSON([
//             'status'   => 'success', 
//             'redirect' => site_url('getlink/' . $shortCode), // Chuyển đến intermediary
//             'country'  => $countryCode 
//         ]);
//     }

    // public function processGenerate($username) {
    //     helper(['text']); 
        
    //     $userModel = new \App\Models\UserModel();
    //     $keysModel = new \App\Models\KeysModel();
    //     $user = $userModel->where('username', $username)->first();

    //     if (!$user) {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
    //     }

    //     $countryCode = $this->request->getGet('country') ?? 'VN';

    //     // 1. Tạo Key và lưu DB
    //     $duration = $user['key_duration'] ?? 24;
    //     $key = 'FREE_' . strtoupper(random_string('alnum', 12));
        
    //     $keysModel->insert([
    //         'game'         => 'PUBG',
    //         'user_key'     => $key,
    //         'duration'     => $duration,
    //         'max_devices'  => 1,
    //         'registrator'  => $username,
    //         'expired_date' => \CodeIgniter\I18n\Time::now()->addHours($duration)->toDateTimeString(),
    //         'status'       => 1,
    //     ]);

    //     // 2. Link đích cuối cùng (Deepest Target)
    //     $currentUrl = site_url('key?key=' . $key);

    //     // 3. Lấy cấu hình
    //     $configField = ($countryCode === 'VN') ? 'vn_short_config' : 'global_short_config';
    //     $apiConfigs = json_decode($user[$configField] ?? '[]', true);

    //     // 4. THỰC HIỆN BỌC LINK (QUICKLINK CHAINING)
    //     if (!empty($apiConfigs) && is_array($apiConfigs)) {
    //         /** * ĐẢO NGƯỢC MẢNG: 
    //          * Lớp nhập thứ 1 sẽ được bọc CUỐI CÙNG (nằm ngoài cùng)
    //          * Lớp nhập cuối cùng sẽ được bọc ĐẦU TIÊN (nằm sát link gốc nhất)
    //          */
    //         $reversedConfigs = array_reverse($apiConfigs);

    //         foreach ($reversedConfigs as $conf) {
    //             // CHỈ BỌC LINK NẾU BASE KHÔNG RỖNG VÀ STATUS LÀ BẬT (1)
    //             if (empty($conf['base']) || (isset($conf['status']) && $conf['status'] == '0')) {
    //                 continue; 
    //             }

    //             $currentUrl = $conf['base'] . urlencode($currentUrl);
    //         }
    //     }

    //     return $this->response->setJSON([
    //         'status'   => 'success', 
    //         'redirect' => $currentUrl, // Link đã bọc theo đúng thứ tự hiển thị 1 -> 2 -> 3
    //         'country'  => $countryCode 
    //     ]);
    // }
    public function key()
    {
        // $redirectUrl = 'https://key.gmvmoba.com/gmvmoba/getkey';

        $keyValue = $this->request->getGet('key');
        if (!$keyValue) {
            return view('Auth/key', [
                'key'      => $keyValue,
                'username' => 'GMV MOBA',
                'seconds'  => 2,
                // 'redirect' => $redirectUrl,
                'isError'  => true,
            ]);
        }

        $keysModel = new KeysModel();
        $keyData = $keysModel->where('user_key', $keyValue)->first();

        if (!$keyData) {
            return view('Auth/key', [
                'key'      => $keyValue,
                'username' => 'GMV MOBA',
                'seconds'  => 1,
                // 'redirect' => '',
                'isError'  => true,
            ]);
        }

        $expired = Time::parse($keyData['expired_date']);
        $seconds = $expired->getTimestamp() - Time::now()->getTimestamp();

        return view('Auth/key', [
            'key'      => $keyValue,
            'username' => $keyData['registrator'],
            'seconds'  => max(0, $seconds),
            'isError'  => false,
        ]);
    }
}
