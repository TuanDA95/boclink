<?php

namespace App\Controllers;

use App\Models\UserModel;

class ApiUrl extends BaseController
{


// Hàm lấy dữ liệu từ file JSON gửi ra giao diện
public function get_quick_banks()
{
    $filePath = FCPATH . 'banks_list.json'; // Đường dẫn đến file ở thư mục gốc
    $data = file_exists($filePath) ? file_get_contents($filePath) : '[]';
    return $this->response->setJSON(json_decode($data));
}

// Hàm lưu dữ liệu từ giao diện vào file JSON
public function update_quick_banks()
{
    $jsonData = $this->request->getPost('quick_banks_json');
    $filePath = FCPATH . 'banks_list.json';

    // Ghi đè nội dung mới vào file
    if (file_put_contents($filePath, $jsonData)) {
        return $this->response->setJSON([
            'status' => 'success',
            'token'  => csrf_hash()
        ]);
    }

    return $this->response->setJSON(['status' => 'error'], 500);
}

}