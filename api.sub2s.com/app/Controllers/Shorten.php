<?php

namespace App\Controllers;

use App\Models\UserModel;

class Shorten extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->getUser();
        return view('Admin/apishorten', ['user' => $user]);

    }

   public function save()
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->getUser();
        
        if (!$user) {
            return redirect()->back()->with('msgDanger', 'Người dùng không tồn tại');
        }

        $apiList = $this->request->getPost('short_api_list');
        $duration = $this->request->getPost('key_duration');
        $discord_link = $this->request->getPost('discord_link');
        $buy_key_link = $this->request->getPost('buy_key_link');

        $data = [
            'short_api_list' => $apiList,
            'discord_link'        => $discord_link,
            'buy_key_link'        => $buy_key_link,
            'key_duration'   => (int)$duration > 0 ? (int)$duration : 12, 
        ];

        if (trim($apiList) === '') {
            $data['short_api_list'] = null;
            $userModel->update($user->id_users, $data);
            return redirect()->back()->with('msgSuccess', 'Đã xóa cấu hình API và cập nhật thời gian');
        }

        $userModel->update($user->id_users, $data);

        return redirect()->back()->with('msgSuccess', 'Đã lưu cấu hình bọc link và thời hạn key');
    }

}
