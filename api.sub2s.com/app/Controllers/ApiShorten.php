<?php

namespace App\Controllers;

use App\Models\UserModel;

class ApiShorten extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $user = $this->userModel->getUser();
        return view('Admin/apiurl', ['user' => $user]);
    }

    public function save()
    {
        // Lấy thông tin user từ Model (đảm bảo hàm getUser() trả về object có chứa id_users)
        $user = $this->userModel->getUser();
        
        if (!$user || !isset($user->id_users)) {
            return redirect()->back()->with('msgDanger', 'Không tìm thấy ID người dùng để cập nhật.');
        }

        // Thu thập dữ liệu từ POST
        $vn_apis     = $this->request->getPost('vn_apis') ?? [];
        $global_apis = $this->request->getPost('global_apis') ?? [];
        $duration    = $this->request->getPost('key_duration');
        $discord_link = $this->request->getPost('discord_link');
        $buy_key_link = $this->request->getPost('buy_key_link');
        $partner_id   = $this->request->getPost('partner_id');
        $price_per_api   = $this->request->getPost('price_per_api');
        $partner_key  = $this->request->getPost('partner_key');
        $sepay_api_key = $this->request->getPost('sepay_api_key');
        $bank_id       = $this->request->getPost('bank_id');
        $bank_number   = $this->request->getPost('bank_number');
        $bank_name    = $this->request->getPost('bank_name');
        $bank_prefix  = $this->request->getPost('bank_prefix');

        $enable_free_global = $this->request->getPost('enable_free_global') ? 1 : 0;

        // Lọc bỏ các dòng trống (người dùng thêm hàng nhưng không nhập link)
        $vn_filtered     = array_values(array_filter($vn_apis, fn($v) => !empty($v['base'])));
        $global_filtered = array_values(array_filter($global_apis, fn($v) => !empty($v['base'])));

        // Chuẩn bị mảng dữ liệu để Update
        $data = [
            'key_duration'        => (int)($duration ?? 24),
            // 'vn_short_config'     => json_encode($vn_filtered),
            // 'global_short_config' => json_encode($global_filtered),
            'partner_id'    =>  $partner_id,
            'partner_key'   => $partner_key,
            'sepay_api_key' => $sepay_api_key,
            'price_per_api' => $price_per_api,
            'bank_id'       => $bank_id,
            'bank_number'   => $bank_number,
            'bank_name'     => $bank_name,
            'bank_prefix'   => $bank_prefix,
            'vn_short_config'     => json_encode($vn_apis), // Không lọc array_filter ở đây để giữ lại các link bị tắt
            'global_short_config' => json_encode($global_apis),
            'discord_link'        => $discord_link,
            'buy_key_link'        => $buy_key_link,
            'enable_free_global'  => $enable_free_global,
        ];

        // Thực hiện Update vào bảng 'users' theo id_users
        if ($this->userModel->update($user->id_users, $data)) {
            return redirect()->back()->with('msgSuccess', 'Đã lưu cấu hình đa lớp thành công!');
        } else {
            $errors = $this->userModel->errors();
            return redirect()->back()->with('msgDanger', 'Lỗi lưu dữ liệu: ' . json_encode($errors));
        }
    }
}