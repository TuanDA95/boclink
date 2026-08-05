<?php

namespace App\Controllers;

use App\Models\LinkModel;
use App\Models\UserModel;

class LinkController extends BaseController
{

private const API_TOKEN = '0000aiddsuahwksa9999';
private const API_REGISTRATOR = 'ngocbong'; 

public function quicklink()
{
    $token = $this->request->getGet('token');
    $url   = $this->request->getGet('url');

    $result = $this->_createShortlinkByToken($token, $url);

    if (!$result['success']) {
        return $result['message'];
    }

    // return $this->go($result['code']);
    return redirect()->to(site_url('key/' . $result['code']));
}

public function developer()
{
    $token = $this->request->getGet('token');
    $url   = $this->request->getGet('url');

    $result = $this->_createShortlinkByToken($token, $url);

    return $this->response->setJSON(
        $result['success']
            ? [
                'status'    => 'success',
                'short_url' => site_url('key/' . $result['code']),
              ]
            : [
                'status'  => 'error',
                'message' => $result['message'],
              ]
    )->setStatusCode($result['success'] ? 200 : 400);
}

private function _createShortlinkByToken(?string $token, ?string $url): array
{
    if (empty($token) || !hash_equals(self::API_TOKEN, $token)) {
        return ['success' => false, 'message' => 'Token không hợp lệ'];
    }

    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'message' => 'URL không hợp lệ'];
    }

    $model = new LinkModel();
    $code  = substr(md5(uniqid()), 0, 8);

    while ($model->where('code', $code)->first()) {
        $code = substr(md5(uniqid()), 0, 8);
    }

    $model->insert([
        'code'        => $code,
        'target_url'  => $url,
        'flow'        => 3,
        'registrator' => self::API_REGISTRATOR,
    ]);

    return ['success' => true, 'code' => $code];
}

    public function add()
    {
        $model = new LinkModel();
        $userModel = new UserModel();
        $user = $userModel->getUser();

        // Lấy từ khóa tìm kiếm tiền tố
        $search = $this->request->getVar('search');
        
        if ($search) {
            $model->like('code', $search, 'after'); // Tìm các code bắt đầu bằng tiền tố
        }

        $data = [
            'links' => $model->orderBy('id', 'DESC')->paginate(10, 'group1'),
            'pager' => $model->pager,
            'user'  => $user,
            'search' => $search
        ];

        return view('links/add', $data);
    }

public function create()
{
    $url = $this->request->getPost('url');
    $prefix = trim($this->request->getPost('prefix'));
    $flow = $this->request->getPost('flow');
    $flow = (!empty($flow)) ? $flow : 3;

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return redirect()->back()->with('msgDanger', 'URL không hợp lệ!');
    }

    $userModel = new UserModel();
    $user = $userModel->getUser();
    $model = new LinkModel();

    $code = (!empty($prefix)) ? $prefix : substr(md5(uniqid()), 0, 8);

    if ($model->where('code', $code)->first()) {
        return redirect()->back()->with('msgDanger', 'Mã hoặc Tiền tố này đã tồn tại!');
    }

    $model->insert([
        'code'        => $code,
        'target_url'  => $url,
        'flow'        => $flow, // Sử dụng biến flow đã xử lý ở trên
        'registrator' => $user->username ?? 'ngocbong',
    ]);

    return redirect()->to(site_url("admin/link"))->with('msgSuccess', 'Tạo link thành công: ' . $code);
}

public function update($id)
{
    $url = $this->request->getPost('url');
    $flow = $this->request->getPost('flow');
    $flow = (!empty($flow)) ? $flow : 1; // Mặc định là 1 nếu xóa trắng khi edit

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return redirect()->back()->with('error', 'URL không hợp lệ');
    }

    $model = new LinkModel();
    $model->update($id, [
        'target_url' => $url,
        'flow'       => $flow
    ]);

    return redirect()->to('admin/link')->with('success', 'Cập nhật link thành công');
}
    public function edit($id)
    {
        

        $model = new LinkModel();
        $link = $model->find($id);

        if (!$link) {
            return redirect()->to('admin/link')->with('error', 'Link không tồn tại');
        }

        return view('links/edit', [
            'link' => $link
        ]);
    }


// public function go($code)
// {
//     // Khởi tạo trực tiếp Controller chứa hàm intermediary
//     $getlinkController = new \App\Controllers\LinkBuy();
    
//     // Gọi và trả về kết quả từ hàm đó
//     return $getlinkController->intermediary($code);
// }

public function go($code)
{
    $model = new \App\Models\LinkModel();
    $link = $model->where('code', $code)->first();

    if (!$link) {
        return redirect()->to('/')->with('error', 'Link không tồn tại');
    }

    if ($this->request->getGet('gmvmoba') !== null) {
        return redirect()->to($link['target_url']);
    }

    $getlinkController = new \App\Controllers\LinkBuy();
    return $getlinkController->intermediary($code);
}
}
