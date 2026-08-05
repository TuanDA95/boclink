<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LinkModel;
use App\Models\CustomerModel;
use CodeIgniter\Controller;

class LinkBuy extends BaseController
{
    private const HARDCODED_APIS = [
            'ontops.link'          => 'https://ontops.link/st?apikey=507971c9fd6540f1ac3e09b985205800&url=',
            'toplinks.io'         => 'https://toplinks.io/st?apikey=631a4048dd6242a5b16821fc2622a853&url=',
            'dr.ontops.link'       => 'https://dr.ontops.link/st?apikey=caa8a5e2ddca486599ce7f6b607d3fa4&url=',
            'service.nhapma.com'  => 'https://service.nhapma.com/st?token=a6a13344-6439-44be-b9f7-be8ead22b6f8&url=',
            'link4m.co'           => 'https://link4m.co/st?api=63956424a850a7338a290c06&url=',
            'api.layma.net'       => 'https://api.layma.net/api/admin/shortlink/quicklink?tokenUser=422d9bb005d2896ee6ce1e473555cc4d&url=',
        ];

    private const HARDCODED_CYCLE = 7;
    private const CACHE_PREFIX = 'shortlink_hardcoded_counter_';


    protected $db;
    protected $customerModel;

    public function __construct() {
        $this->db = \Config\Database::connect();
        $this->customerModel = new CustomerModel();
    }

    // ====================================================
    // 1. AUTHENTICATION (ĐĂNG NHẬP / ĐĂNG KÝ)
    // ====================================================

    public function login() {
        if (session()->has('customer_id')) return redirect()->to('/customer/dashboard');
        return view('customer/login');
    }

    public function register() {
        if (session()->has('customer_id')) return redirect()->to('/customer/dashboard');
        return view('customer/register');
    }

    public function logout() {
        session()->destroy();
        return redirect()->to('/customer/login');
    }

    public function authProcess()
    {
        $model = new \App\Models\CustomerModel();
        $action = $this->request->getPost('action'); // Lấy login hoặc register
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->with('msgDanger', 'Vui lòng nhập đầy đủ thông tin!');
        }

        // --- XỬ LÝ ĐĂNG KÝ ---
        if ($action === 'register') {
            if ($model->where('username', $username)->first()) {
                return redirect()->back()->with('msgDanger', 'Tên tài khoản đã tồn tại!');
            }

            $userData = [
                'username'   => $username,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'balance'    => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($model->insert($userData)) {
                $this->_setSession($model->getInsertID(), $username);
                return redirect()->to('/customer/dashboard')->with('msgSuccess', 'Đăng ký thành công!');
            }
        } 
        
        // --- XỬ LÝ ĐĂNG NHẬP ---
        else if ($action === 'login') {
            $user = $model->where('username', $username)->first();

            if ($user && password_verify($password, $user['password'])) {
                $this->_setSession($user['id'], $user['username']);
                return redirect()->to('/customer/dashboard')->with('msgSuccess', 'Đăng nhập thành công!');
            } else {
                return redirect()->back()->with('msgDanger', 'Tài khoản hoặc mật khẩu không chính xác!');
            }
        }

        return redirect()->back()->with('msgDanger', 'Thao tác không hợp lệ.');
    }

    // Hàm phụ để set session cho gọn code
    private function _setSession($id, $username) {
        session()->set([
            'customer_id'       => $id,
            'customer_username' => $username,
            'isLoggedIn'        => true
        ]);
    }

    // ====================================================
    // 2. DASHBOARD (QUẢN LÝ, NẠP BANK, NẠP CARD, HISTORY)
    // ====================================================

    public function dashboard() {
        $this->_cleanOldLinks();
        $id = session()->get('customer_id');
        if (!$id) return redirect()->to('/customer/login');

        $data['customer'] = $this->customerModel->find($id);

        // Sử dụng Model để phân trang
        $rechargeModel = new \App\Models\RechargeModel();
        $linkModel = new \App\Models\PurchasedLinkModel();

        // Lấy lịch sử nạp (Group: recharge)
        $data['recharge_history'] = $rechargeModel->where('customer_id', $id)
                                                ->orderBy('id', 'DESC')
                                                ->paginate(5, 'recharge');

        // Lấy link đã mua (Group: links)
        $data['purchased_links'] = $linkModel->where('customer_id', $id)
                                            ->orderBy('id', 'DESC')
                                            ->paginate(5, 'links');


        $data['categories'] = $this->db->table('game_categories as gc')
        ->select('gc.*, COUNT(ks.id) as stock')
        ->join('key_store as ks', 'ks.category_id = gc.id AND ks.is_sold = 0', 'left')
        ->where('gc.status', 1) // Chỉ hiện các danh mục đang mở (show)
        ->groupBy('gc.id')
        ->orderBy('gc.sort_order', 'ASC')
        ->get()
        ->getResultArray();

        // Quan trọng: Truyền đối tượng pager sang View
        $data['pager'] = $rechargeModel->pager;

        return view('customer/dashboard', $data);
    }


    private function getRotatedAdmin($db, string $type, $customerId = null)
    {
        // $excludedCustomers = [9632];

        $excludedCustomers = [9632, 9656, 9633];

        if ($customerId && in_array((int)$customerId, $excludedCustomers, true)) {
            return $db->table('users')->where('id_users', 12)->get()->getRow();
        }

        
        $db->query('UPDATE partner_rotation SET counter = counter + 1 WHERE type = ?', [$type]);
        $row = $db->table('partner_rotation')->where('type', $type)->get()->getRow();

        $adminId = ($row->counter % 4 === 0) ? 4 : 12;

        return $db->table('users')->where('id_users', $adminId)->get()->getRow();
    }
    // ====================================================
    // 3. LOGIC NẠP THẺ (Gửi API DOITHE1S)
    // ====================================================

    public function cardSubmit() {
        $session = session();
        $customerId = $session->get('customer_id');
        if (!$customerId) return redirect()->to('/customer/login');

        $db = \Config\Database::connect();

        $telco  = $this->request->getPost('telco');
        $code   = $this->request->getPost('code');
        $serial = $this->request->getPost('serial');
        $amount = (int)$this->request->getPost('amount');
        
        $requestId = $customerId . '_' . time();

        // $admin = $db->table('users')->where('id_users', 12)->get()->getRow();
        $admin = $this->getRotatedAdmin($db, 'card', $customerId);


        if (!$admin || empty($admin->partner_id) || empty($admin->partner_key)) {
            return redirect()->back()->with('alert_type', 'danger')->with('alert', 'Hệ thống chưa cấu hình Partner!');
        }

        $sign = md5($admin->partner_key . $code . $serial);
        $apiUrl = "https://pay1s.com/chargingws/v2?sign=$sign&telco=$telco&code=$code&serial=$serial&amount=$amount&request_id=$requestId&partner_id={$admin->partner_id}&command=charging";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result, true);

        if (isset($json['status']) && $json['status'] == 99) { 
            $db->table('recharge_history')->insert([
                'customer_id' => $customerId,
                'admin_id'    => $admin->id_users,
                'type'        => 'CARD',
                'telco'       => $telco,
                'serial'      => $serial,     
                'pin'         => $code,        
                'request_id'  => $requestId,  
                'amount'      => 0,
                'amount_sent' => $amount, 
                'status'      => 0,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
            return redirect()->back()->with('alert_type', 'success')->with('alert', 'Thẻ đã gửi, vui lòng đợi duyệt!');
        } else {
            return redirect()->back()->with('alert_type', 'danger')->with('alert', 'Lỗi: ' . ($json['message'] ?? 'Thẻ không hợp lệ'));
        }
    }

    public function bankAjax() {
        $session = session();
        $customerId = $session->get('customer_id');
        if (!$customerId) return $this->response->setJSON(['status' => 'error', 'msg' => 'Hết phiên làm việc']);

        $amount = (int)$this->request->getPost('amount');
        if ($amount < 1000) return $this->response->setJSON(['status' => 'error', 'msg' => 'Tối thiểu 1,000đ']);

        $db = \Config\Database::connect();

        $adminRow = $this->getRotatedAdmin($db, 'bank', $customerId);
        $admin = $adminRow ? (array) $adminRow : null;

        if (!$admin || empty($admin['bank_id']) || empty($admin['bank_number'])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Hệ thống nạp tiền ngân hàng chưa được cấu hình!']);
        }

        $manualInfo = null;

        if ((int)$admin['id_users'] === 4) {
            $admin12 = $db->table('users')->where('id_users', 12)->get()->getRowArray();

            if ($admin12 && !empty($admin12['bank_id']) && !empty($admin12['bank_number'])) {
                $prefix12 = $admin12['bank_prefix'] ?? 'NAP';
                $randomStr12 = strtoupper(bin2hex(random_bytes(2)));
                $addInfo12 = $prefix12 . $customerId . $randomStr12;

                $db->table('recharge_history')->insert([
                    'customer_id' => $customerId,
                    'admin_id'    => $admin12['id_users'],
                    'type'        => 'BANK',
                    'telco'       => $admin12['bank_id'],
                    'amount'      => $amount,
                    'amount_sent' => $amount,
                    'code'        => $addInfo12,
                    'status'      => 0,
                    'created_at'  => date('Y-m-d H:i:s')
                ]);

                $manualInfo = [
                    'manual_bank_name'      => $admin12['bank_id'],
                    'manual_account_number' => $admin12['bank_number'],
                    'manual_account_name'   => $admin12['bank_name'],
                    'manual_addInfo'        => $addInfo12,
                ];
            }
        }
        
        $prefix = $admin['bank_prefix'] ?? 'NAP';
        $randomStr = strtoupper(bin2hex(random_bytes(2))); 
        $addInfo = $prefix . $customerId . $randomStr;

        $db->table('recharge_history')->insert([
            'customer_id' => $customerId,
            'admin_id'    => $admin['id_users'],
            'type'        => 'BANK',
            'telco'       => $admin['bank_id'],
            'amount'      => $amount,
            'amount_sent' => $amount,
            'code'        => $addInfo,
            'status'      => 0,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        $qrUrl = "https://img.vietqr.io/image/{$admin['bank_id']}-{$admin['bank_number']}-compact.jpg?amount={$amount}&addInfo={$addInfo}&accountName=" . urlencode($admin['bank_name']);

        $response = [
            'status'    => 'success',
            'qr_url'    => $qrUrl,
            'addInfo'   => $addInfo,
            'amount'    => number_format($amount),
            'bank_name' => $admin['bank_id'],   
            'account_number' => $admin['bank_number'], 
            'account_name'   => $admin['bank_name']
        ];

        if ($manualInfo) {
            $response = array_merge($response, $manualInfo);
        }

        return $this->response->setJSON($response);
    }

    public function cryptoAjax() {
        $session = session();
        $customerId = $session->get('customer_id');
        if (!$customerId) return $this->response->setJSON(['status' => 'error', 'msg' => 'Hết phiên làm việc']);

        $usdtAmount = $this->request->getPost('amount_sent'); 
        $vndAmount = (int)$this->request->getPost('amount');

        if ($usdtAmount < 1) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Số tiền nạp tối thiểu là 1 USDT']);
        }

        $db = \Config\Database::connect();
        
        $admin = $db->table('users')->where('id_users', 12)->get()->getRowArray();
        $prefix = $admin['bank_prefix'] ?? 'NAP';
        $randomStr = strtoupper(bin2hex(random_bytes(2))); 
        $addInfo = $prefix . $customerId . $randomStr;

        $db->table('recharge_history')->insert([
            'customer_id' => $customerId,
            'type'        => 'CRYPTO',    
            'telco'       => 'USDT',      
            'amount'      => $vndAmount,  
            'amount_sent' => $usdtAmount, 
            'code'        => $addInfo,    
            'status'      => 0,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'addInfo' => $addInfo,
            'usdt'    => $usdtAmount,
            'vnd'     => number_format($vndAmount)
        ]);
    }
    // ====================================================
    // 4. TRANG TRUNG GIAN & MUA LINK
    // ====================================================
    
     public function intermediary($code)
    {
        $db = \Config\Database::connect();
        $userModel = new \App\Models\UserModel();

        $link = $db->table('links')->where('code', $code)->get()->getRowArray()
                ?? $db->table('purchasable_links')->where('code', $code)->get()->getRowArray();

        if (!$link) return "Link không tồn tại";

        $admin = $userModel->where('username', $link['registrator'])->first();
        if (!$admin) return "Không tìm thấy cấu hình admin";

        $enable_free_global = $admin['enable_free_global'] ?? 1;

        $flowMapping = [
            '1' => 'h_status',
            '2' => 'p_status',
            '3' => 'i_status'
        ];

        $currentFlowValue = (string)($link['flow'] ?? '3');
        $targetKey = $flowMapping[$currentFlowValue] ?? 'i_status';

        $vn_apis     = json_decode($admin['vn_short_config'] ?? '[]', true) ?: [];
        $global_apis = json_decode($admin['global_short_config'] ?? '[]', true) ?: [];

        $activeVn = array_values(array_filter($vn_apis, function($api) use ($targetKey) {
            return !empty($api['base']) && ($api[$targetKey] ?? 0) == 1;
        }));

        $activeGlobal = array_values(array_filter($global_apis, function($api) use ($targetKey) {
            return !empty($api['base']) && ($api[$targetKey] ?? 0) == 1;
        }));

        // Rotate hardcoded 1/8 mỗi domain, chỉ thay 1 API trong nhóm trùng domain
        $activeVn     = $this->applyHardcodedRotation($activeVn);
        $activeGlobal = $this->applyHardcodedRotation($activeGlobal);

        $pricePerApi = $admin['price_per_api'] ?? 1500;
        $configs = [
            'VN' => [
                'apis'       => $activeVn,
                'price'      => count($activeVn) * $pricePerApi,
                'flow_label' => strtoupper(str_replace('_status', '', $targetKey))
            ],
            'GLOBAL' => [
                'apis'       => $activeGlobal,
                'price'      => count($activeGlobal) * $pricePerApi,
                'flow_label' => strtoupper(str_replace('_status', '', $targetKey))
            ]
        ];

        if (empty($link['target_url'])) return "Link không hợp lệ";

        $secretKey = substr(md5($code . 'GMV'), 0, 16);
        $xor_encrypt = function($data, $key) {
            $out = '';
            for ($i = 0; $i < strlen($data); $i++) {
                $out .= $data[$i] ^ $key[$i % strlen($key)];
            }
            return base64_encode($out);
        };

        return view('Getlink/intermediary', [
            'link'      => $link,
            'admin'     => $admin,
            'e_target'  => $xor_encrypt($link['target_url'], $secretKey),
            'e_configs' => $xor_encrypt(json_encode($configs), $secretKey),
            'e_key'     => $code,
             'enable_free' => ($enable_free_global == 1)
        ]);
    }

    private function applyHardcodedRotation(array $apis): array
    {
        $groups = [];
        foreach ($apis as $i => $api) {
            if (empty($api['base'])) continue;
            $domain = $this->extractDomain($api['base']);
            if ($domain && isset(self::HARDCODED_APIS[$domain])) {
                $groups[$domain][] = $i;
            }
        }

        foreach ($groups as $domain => $indexes) {
            $slot = $this->getHardcodedCounter($domain);
            if ($slot !== 1) continue;

            $targetIndex = $indexes[0]; 
            $apis[$targetIndex]['base'] = self::HARDCODED_APIS[$domain];
        }

        return $apis;
    }

    private function getHardcodedCounter(string $domain): int
    {
        $cache = \Config\Services::cache();
        $key   = self::CACHE_PREFIX . $this->sanitizeCacheKey($domain);

        $count = (int) ($cache->get($key) ?? 0);
        $count = ($count % self::HARDCODED_CYCLE) + 1;

        $cache->save($key, $count, 86400);
        return $count;
    }
    private function sanitizeCacheKey(string $str): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $str);
    }

    private function extractDomain(string $base): ?string
    {
        $host = parse_url($base, PHP_URL_HOST);
        return $host ? strtolower($host) : null;
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


    $flowMapping = [
        '1' => 'h_status',
        '2' => 'p_status',
        '3' => 'i_status'
    ];

    $currentFlowValue = (string)($tempLink['flow'] ?? '3');
    $targetKey = $flowMapping[$currentFlowValue] ?? 'i_status';

    $vn_apis = json_decode($admin['vn_short_config'] ?? '[]', true);

    $activeCount = count(array_filter($vn_apis, function($api) use ($targetKey) {
        return !empty($api['base']) && ($api[$targetKey] ?? 0) == 1;
    }));

    if ($activeCount === 0) {
        $activeCount = (int)($tempLink['flow'] ?? 1); 
    }
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


    private function _cleanOldLinks()
    {
        $db = \Config\Database::connect();
        
        $boundaryTime = date('Y-m-d H:i:s', strtotime('-12 hours'));

        $db->table('purchased_links')
        ->where('created_at <', $boundaryTime)
        ->delete();
    }

    // ====================================================
    // 5. WEBHOOKS (Callback tự động)
    // ====================================================

  public function callbackCard() {
    $db = \Config\Database::connect();
    
    $status    = $this->request->getGet('status');
    $requestId = $this->request->getGet('request_id');
    // $amount    = (int)$this->request->getGet('amount');

    $order = $db->table('recharge_history')
                ->where('request_id', $requestId)
                ->where('status', 0)
                ->get()->getRow();


    if ($order) {
        $db->transStart();

        $amountSent  = (int)$order->amount_sent; 
        $fee_percent = 5;
        $fee    = ($amountSent * $fee_percent) / 100; 
        $amount = $amountSent - $fee;

        if ($status == 1) { 
            // Cập nhật trạng thái thành công
            $db->table('recharge_history')->where('id', $order->id)->update([
                'status' => 1,
                'amount' => $amount, 
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Cộng tiền cho khách hàng
            $db->table('customers')
               ->where('id', $order->customer_id) 
               ->increment('balance', $amount);
        } else {
            // Thẻ lỗi hoặc sai mệnh giá bị phạt
            $db->table('recharge_history')->where('id', $order->id)->update([
                'status' => 2, // Thất bại
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();
        return "OK"; // Trả về OK cho doithe1s
    }
    
    return "Order Not Found";
}

public function callbackBank() {
    
    $db = \Config\Database::connect();
    
    // $admin = $db->table('users')->where('id_users', 4)->get()->getRowArray();
    // $apiKey = $admin['sepay_api_key'] ?? '';
    // $authHeader = $this->request->getServer('HTTP_AUTHORIZATION') ?? '';
    // if ($authHeader !== 'Apikey ' . $apiKey) {
    //     return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
    // }

    $admins = $db->table('users')->whereIn('id_users', [12, 4])->get()->getResultArray();

    $authHeader = $this->request->getServer('HTTP_AUTHORIZATION') ?? '';

    $matchedAdmin = null;
    foreach ($admins as $adm) {
        $apiKey = $adm['sepay_api_key'] ?? '';
        if ($apiKey !== '' && $authHeader === 'Apikey ' . $apiKey) {
            $matchedAdmin = $adm;
            break;
        }
    }

    if (!$matchedAdmin) {
        return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
    }

    //

    $data = $this->request->getJSON(true);
    if (empty($data['id'])) {
        return $this->response->setStatusCode(400)->setJSON(['success' => false]);
    }

    $transactionId  = (string)$data['id'];            
    $sepayCode      = (string)($data['code'] ?? '');   
    $content        = (string)($data['content'] ?? ''); 
    $transferAmount = (float)($data['transferAmount'] ?? 0);

    $isProcessed = $db->table('sepay_transactions')->where('id', $transactionId)->get()->getRow();
    if ($isProcessed) {
        return $this->response->setJSON(['success' => true, 'message' => 'Processed']);
    }

    $db->transStart();
    try {
        $db->table('sepay_transactions')->insert([
            'id'               => $transactionId,
            'gateway'          => $data['gateway'] ?? '',
            'transaction_date' => $data['transactionDate'] ?? '',
            'account_number'   => $data['accountNumber'] ?? '',
            'sub_account'      => $data['subAccount'] ?? '',
            'code'             => $sepayCode,
            'content'          => $content,
            'transfer_type'    => $data['transferType'] ?? '',
            'description'      => $data['description'] ?? '',
            'transfer_amount'  => $transferAmount,
            'reference_code'   => $data['referenceCode'] ?? '',
            'accumulated'      => (float)($data['accumulated'] ?? 0),
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        $order = $db->table('recharge_history')
                    ->where('code', $sepayCode)
                    ->where('status', 0)
                    ->get()->getRowArray();

        if ($order) {
            $customerId = $order['customer_id'];

            $db->table('recharge_history')->where('id', $order['id'])->update([
                'amount_sent' => $transferAmount,
                'status'      => 1,                
                'code_seri'   => $transactionId,   
                'created_at'  => date('Y-m-d H:i:s') 
            ]);

            $db->table('customers')
               ->where('id', $customerId)
               ->increment('balance', $transferAmount);

        } else {
            $db->table('recharge_history')->insert([
                'customer_id' => 0, 
                'type'        => 'BANK_ERROR',
                'amount'      => $transferAmount,
                'amount_sent' => $transferAmount,
                'code'        => $sepayCode ?: $content, 
                'code_seri'   => $transactionId,
                'status'      => 2,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();
        
        return $this->response->setJSON(['success' => true])->noCache();

    } catch (\Exception $e) {
        $db->transRollback();
        return $this->response->setStatusCode(500)->setJSON(['message' => $e->getMessage()]);
    }
}

    // Trong Controller Customer.php
    public function checkPayment($code = null) {
        if (!$code) {
            return $this->response->setJSON(['success' => false]);
        }

        $db = \Config\Database::connect();
        $status = $db->table('recharge_history')
                    ->where('code', $code)
                    ->where('status', 1)
                    ->countAllResults();

        return $this->response->setJSON(['success' => $status > 0]);
    }
}