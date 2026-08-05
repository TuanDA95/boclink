<?php

namespace App\Controllers;

use App\Models\LicenseModel;

class Key extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        // if (!$key) {
        //     return redirect()->to('/');
        // }

        $model = new LicenseModel();
        $data = $model->where('license_key', $key)->first();

        if (!$data || strtotime($data['expired_at']) < time()) {
            return view('Auth/key', [
                'key' => 'Key không hợp lệ hoặc đã hết hạn'
            ]);
        }

        return view('Auth/key', [
            'key' => $data['license_key']
        ]);
    }
}
