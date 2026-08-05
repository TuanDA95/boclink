<?php

namespace App\Controllers;
use App\Models\BundleModel;
use App\Models\UserModel;

class DylibManager extends BaseController
{
public function checkStatus($username = null) {
    if (!$username) return $this->response->setJSON(['error' => 'Missing username']);

    $bid = $this->request->getGet('bid') ?: 'unknown';
    $name = $this->request->getGet('name') ?: 'Unknown Game';
    $clientVer = $this->request->getGet('ver') ?: '1.0.0';
    $udid = $this->request->getGet('udid') ?: 'unknown';

    $udidClean = str_replace('-', '', (string)$udid);
    $ip = $this->request->getIPAddress();

    if ($udidClean && strtoupper($udidClean) !== 'UNKNOWN') {
        $filePath = FCPATH . "data/udid_$ip.txt";
        file_put_contents($filePath, $udidClean);
    }

    $db = \Config\Database::connect();

    if ($udid && strtoupper($udid) !== 'UNKNOWN') {
        $db->table('device_daily_stats')->replace([
            'username'    => $username,
            'bundle_id'   => $bid,
            'udid'        => $udid,
            'active_date' => date('Y-m-d'),
            'last_active' => date('Y-m-d H:i:s')
        ]);
    }
    $builder = $db->table('bundle_configs');

    $config = $builder->where('username', $username)
                      ->where('bundle_id', $bid)
                      ->groupStart()
                        ->where('version', $clientVer)
                        ->orWhere('version', $clientVer . '.close')
                      ->groupEnd()
                      ->get()->getRow();

    if (!$config) {
        $oldRef = $db->table('bundle_configs')
                     ->where(['bundle_id' => $bid, 'username' => $username])
                     ->orderBy('id', 'DESC')
                     ->get()->getRow();

        $newMsg = $oldRef ? $oldRef->message : "Bấm vào Cập nhật để tải xuống phiên bản mới nhất";
        $newLink = $oldRef ? $oldRef->update_link : "https://discord.com/invite/wY6xFY6BzK";

        $newConfigData = [
            'username'         => $username,
            'bundle_id'        => $bid,
            'game_name'        => $name,
            'version'          => $clientVer, 
            'last_app_version' => $clientVer,
            'status'           => 1,
            'update_link'      => $newLink,
            'message'          => $newMsg,
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        $builder->insert($newConfigData);
        
        return $this->response->setJSON([
            'force_update' => false,
            'update_link'  => $newLink,
            'update_msg'   => $newMsg,
            'server_ver'   => $clientVer
        ]);
    }

    $isClosed = ($config->status == 0) || (strpos($config->version, '.close') !== false);

    $builder->where('id', $config->id)->update([
        'updated_at' => date('Y-m-d H:i:s'),
        'last_app_version' => $clientVer
    ]);

    return $this->response
        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
        ->setJSON([
            'force_update' => $isClosed,
            'require_key'  => (int)($config->require_key ?? 1),
            'update_link'  => $config->update_link,
            'update_msg'   => $config->message,
            'server_ver'   => $config->version 
        ]);
}

    public function update() {
        $id = $this->request->getPost('id');
        if (!$id) return redirect()->back()->with('error', 'Thiếu ID!');

        $bundleModel = new BundleModel();
        $status = (int)$this->request->getPost('status');
        $version = $this->request->getPost('version');

        if ($status === 0) {
            if (strpos($version, '.close') === false) {
                $version = $version . ".close";
            }
        } else {
            $version = str_replace('.close', '', $version);
        }

        $data = [
            'status'      => $status,
            'version'     => $version,
            'require_key' => (int)$this->request->getPost('require_key'),
            'update_link' => $this->request->getPost('update_link'),
            'message'     => $this->request->getPost('message'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        if ($bundleModel->update($id, $data)) {
            return redirect()->back()->with('success', 'Đồng bộ thành công!');
        }
        return redirect()->back()->with('error', 'Lỗi database');
    }


public function index()
{
    $userModel = new UserModel();
    $user = $userModel->getUser();
    if (!$user) return redirect()->to(site_url('login'));

    $bundleModel = new BundleModel();
    $db = \Config\Database::connect();

    $userBundles = $bundleModel->where('username', $user->username)->findAll();
    
    if (!empty($userBundles)) {
        $today = date('Y-m-d');
        
        foreach ($userBundles as $bundle) {
            // Kiểm tra số lượng thiết bị hoạt động trong ngày hôm nay của bundle_id này
            $count = $db->table('device_daily_stats')
                        ->where('username', $user->username)
                        ->where('bundle_id', $bundle['bundle_id'])
                        ->where('active_date', $today)
                        ->countAllResults();
            
            // Nếu không có thiết bị nào (count == 0), tiến hành xóa
            if ($count === 0) {
                $bundleModel->delete($bundle['id']);
            }
        }
    }
    
    // Lấy từ khóa tìm kiếm từ request
    $search = $this->request->getGet('search');

    // Khởi tạo builder
    $builder = $bundleModel->where('username', $user->username);

    // Nếu có từ khóa, thêm điều kiện lọc theo bundle_id hoặc game_name
    if (!empty($search)) {
        $builder->groupStart()
                ->like('bundle_id', $search)
                ->orLike('game_name', $search)
                ->groupEnd();
    }

    $list_configs = $builder->orderBy('id', 'DESC')->paginate(10); 

    $stats = $db->table('device_daily_stats')
                ->select('bundle_id, COUNT(udid) as total')
                ->where('username', $user->username)
                ->where('active_date', date('Y-m-d'))
                ->groupBy('bundle_id')
                ->get()->getResultArray();
    
    $active_counts = array_column($stats, 'total', 'bundle_id');

    $data = [
        'title'         => 'Quản lý Dylib',
        'user'          => $user,
        'list_configs'  => $list_configs,
        'active_counts' => $active_counts,
        'pager'         => $bundleModel->pager,
        'search'        => $search // Truyền từ khóa ra view để hiển thị lại vào ô input
    ];

    return view('Admin/dylib', $data);
}


public function bundleDelete($id)
{
    $userModel = new UserModel();
    $user = $userModel->getUser();
    if (!$user) return redirect()->to(site_url('login'));

    $bundleModel = new BundleModel();
    $config = $bundleModel->find($id);

    // Bảo mật: Chỉ cho phép xóa nếu đúng chủ sở hữu
    if ($config && $config['username'] === $user->username) {
        $bundleModel->delete($id);
        return redirect()->back()->with('success', 'Đã xóa cấu hình thành công!');
    }

    return redirect()->back()->with('error', 'Không tìm thấy mục cần xóa hoặc bạn không có quyền.');
}
}