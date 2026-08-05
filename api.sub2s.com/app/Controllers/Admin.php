<?php
namespace App\Controllers;
use App\Models\CustomerModel;
use App\Models\RechargeModel;
use App\Models\PurchasedLinkModel;

class Admin extends BaseController {
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        if (session()->get('customer_id') != 1 && session()->get('customer_id') != 9632) {
            header('Location: ' . base_url('/customer/login'));
            exit;
        }
    }

    private function _loadBaseData($title) {
        $db = \Config\Database::connect();
        
        // 1. Đồng bộ múi giờ Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $db->query("SET time_zone = '+07:00'");
    
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');
    
        // 2. Khởi tạo Builder cho bảng nạp tiền (chỉ lấy đơn thành công status = 1)
        $builder = $db->table('recharge_history')
                            ->where('status', 1)
                            ->notLike('code', 'BBZQT')
                            ->groupStart()
                                ->where('admin_id !=', 4)
                                ->orWhere('admin_id IS NULL', null, false)
                            ->groupEnd();
        
        // 3. Tính toán các mốc (Sử dụng clone để không bị cộng dồn điều kiện WHERE)
        $total_rev = (clone $builder)->selectSum('amount')->get()->getRow()->amount ?? 0;
        
        $month_rev = (clone $builder)->where('created_at >=', $monthStart)
                            ->selectSum('amount')->get()->getRow()->amount ?? 0;
                            
        $today_rev = (clone $builder)->where('created_at >=', $todayStart)
                            ->where('created_at <=', $todayEnd)
                            ->selectSum('amount')->get()->getRow()->amount ?? 0;
    
        return [
            'title'           => $title,
            'total_customers' => $db->table('customers')
                       ->where('username !=', 'gmvmoba')
                       ->countAllResults(),
            'total_rev'       => $total_rev,
            'month_rev'       => $month_rev,
            'today_rev'       => $today_rev,
        ];
    }
public function historyLinks()
{
    $data = $this->_loadBaseData('Lịch sử mua Link');
    $model = new \App\Models\PurchasedLinkModel();

    $data['links'] = $model->select('purchased_links.*, customers.username')
        ->join('customers', 'customers.id = purchased_links.customer_id', 'left')
        ->orderBy('purchased_links.id', 'DESC')
        ->paginate(3, 'group_links'); 

    $data['pager'] = $model->pager;

    return view('customer/history_links', $data);
}
    public function dashboard() {
        $data = $this->_loadBaseData('Bảng điều khiển');
        $data['links'] = $this->db->table('purchased_links')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();
        return view('customer/Admin', $data);
    }

  public function customers() {
    $data = $this->_loadBaseData('Quản lý User');
    $model = new \App\Models\CustomerModel();
    
    $search = $this->request->getVar('search');
    $model->where('username !=', 'gmvmoba');

    if ($search) {
        $model->like('username', $search);
    }

    $allIds = $model->select('id')->findAll();
    $ids = array_column($allIds, 'id');
    
    shuffle($ids);
    
    $idString = implode(',', $ids);
    
    $data['customers'] = $model->whereIn('id', $ids)
                               ->orderBy("FIELD(id, $idString)")
                               ->paginate(10, 'cust');
    $data['pager'] = $model->pager;
    $data['search'] = $search;
    
    return view('customer/customers', $data);
}

    public function updateBalance() {
        $id = $this->request->getPost('id');
        $amount = (int)$this->request->getPost('amount');
        $type = $this->request->getPost('type');
        
        $customer = $this->db->table('customers')->where('id', $id)->get()->getRow();
        if (!$customer) {
            return redirect()->back()->with('msgDanger', 'Người dùng không tồn tại!');
        }

        $this->db->transStart(); 

        $finalAmount = ($type == 'sub') ? -$amount : $amount;
        $this->db->table('customers')->where('id', $id)->increment('balance', $finalAmount);

        $historyData = [
            'customer_id' => $id,
            'type'        => 'ADMIN', 
            'amount_sent' => $amount,
            'amount'      => $amount,
            'code'        => ($type == 'add' ? 'Cộng tiền bởi Admin' : 'Trừ tiền bởi Admin'),
            'status'      => 1,
            'created_at'  => date('Y-m-d H:i:s')
        ];
        
        if ($type == 'sub') {
            $historyData['amount'] = -$amount;
            $historyData['amount_sent'] = -$amount;
        }

        $this->db->table('recharge_history')->insert($historyData);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return redirect()->back()->with('msgDanger', 'Lỗi hệ thống khi cập nhật số dư!');
        }

        return redirect()->back()->with('msgSuccess', 'Đã ' . ($type == 'add' ? 'cộng' : 'trừ') . ' ' . number_format($amount) . 'đ thành công!');
    }

    public function changePassword() {
        $id = $this->request->getPost('id');
        $new_pass = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        $this->db->table('customers')->where('id', $id)->update(['password' => $new_pass]);
        return redirect()->back()->with('success', 'Đổi mật khẩu thành công!');
    }
    public function historyBanks() {
        $data = $this->_loadBaseData('Lịch sử nạp Bank');
        $model = new RechargeModel();

        $data['banks'] = $model->select('recharge_history.*, customers.username AS customer_username')
            ->join('customers', 'customers.id = recharge_history.customer_id', 'left')
            ->where('recharge_history.type !=', 'CARD')
            ->orderBy('recharge_history.id', 'DESC')
            ->notLike('recharge_history.code', 'BBZQT')
            ->paginate(10, 'bank');

        $data['pager'] = $model->pager;
        return view('customer/history_bank', $data);
    }
    public function historyCards() {
        $data = $this->_loadBaseData('Lịch sử nạp Thẻ');
        $model = new \App\Models\RechargeModel();

        $data['cards'] = $model->select('recharge_history.*, customers.username AS customer_username')
            ->join('customers', 'customers.id = recharge_history.customer_id')
            ->where('recharge_history.type', 'CARD')
            ->groupStart()
                ->where('recharge_history.admin_id !=', 4)
                ->orWhere('recharge_history.admin_id IS NULL', null, false)
            ->groupEnd()
            ->orderBy('recharge_history.id', 'DESC')
            ->paginate(10, 'card');
            
        $data['pager'] = $model->pager;
        return view('customer/history_card', $data);
    }
    public function approveBank($id) {
        $order = $this->db->table('recharge_history')->where('id', $id)->get()->getRow();

        if ($order && $order->status != 1) {
            $this->db->transStart();

            $this->db->table('recharge_history')->where('id', $id)->update(['status' => 1]);

            $this->db->table('customers')->where('id', $order->customer_id)->increment('balance', $order->amount);

            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                return redirect()->back()->with('error', 'Lỗi hệ thống, vui lòng thử lại.');
            }

            return redirect()->back()->with('success', 'Đã phê duyệt và cộng tiền thành công!');
        }

        return redirect()->back()->with('error', 'Đơn hàng đã được xử lý hoặc không hợp lệ.');
    }


  public function gameCategories() {
    $db = \Config\Database::connect();
    $data = $this->_loadBaseData('Quản lý danh mục Key');
    
    $data['categories'] = $db->table('game_categories as gc')
        ->select('gc.*, COUNT(ks.id) as stock_count')
        ->join('key_store as ks', 'ks.category_id = gc.id AND ks.is_sold = 0', 'left')
        ->groupBy('gc.id')
        // ->orderBy('gc.id', 'DESC')
        ->orderBy('gc.sort_order', 'ASC')
        ->get()
        ->getResultArray();

    $perPage = 10;
    $page = (int)($this->request->getVar('page_history') ?? 1);
    if ($page < 1) $page = 1;

    $builder = $db->table('key_store ks')
        ->select('ks.*, gc.title as game_title, c.username as customer_name')
        ->join('game_categories gc', 'gc.id = ks.category_id', 'left') 
        ->join('customers c', 'c.id = ks.customer_id', 'left')       
        ->where('ks.is_sold', 1);

    $totalCount = $builder->countAllResults(false);

    $data['sales_history'] = $builder->orderBy('ks.sold_at', 'DESC')
        ->limit($perPage, ($page - 1) * $perPage)
        ->get()
        ->getResultArray();

    $pager = \Config\Services::pager();
    $data['history_pager'] = $pager->makeLinks($page, $perPage, $totalCount, 'default_full', 0, 'history');

    return view('customer/game_categories', $data);
}

    public function editCategory()
{
    $id = $this->request->getPost('id');
    $model = new \App\Models\CategoryModel(); // Đổi tên theo Model của bạn
    
    $data = [
        'title'  => $this->request->getPost('title'),
        'status' => $this->request->getPost('status'),
        'file_link' => $this->request->getPost('file_link'),
        
    ];

    $file = $this->request->getFile('image');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move('public/uploads', $newName);
        $data['image'] = $newName;
        
        // (Tùy chọn) Xóa ảnh cũ trong folder để tiết kiệm dung lượng
        $oldData = $model->find($id);
        if (file_exists('public/uploads/' . $oldData['image'])) {
            unlink('public/uploads/' . $oldData['image']);
        }
    }

    $model->update($id, $data);
    return redirect()->back()->with('success', 'Đã cập nhật danh mục thành công!');
}

public function deleteCategory($id)
{
    $db = \Config\Database::connect();
    
    // 1. Tìm thông tin danh mục để lấy tên file ảnh
    $category = $db->table('game_categories')->where('id', $id)->get()->getRow();
    
    if (!$category) {
        return redirect()->back()->with('error', 'Danh mục không tồn tại.');
    }

    // 2. Xóa ảnh minh họa trong thư mục (nếu có)
    $imagePath = FCPATH . 'public/uploads/' . $category->image;
    if (file_exists($imagePath) && !empty($category->image)) {
        unlink($imagePath);
    }

    // 3. Lấy sort_order của danh mục cần xóa
    $deletedOrder = $category->sort_order;

    // 4. Xóa các Key thuộc danh mục này trước (để tránh lỗi ràng buộc dữ liệu)
    $db->table('key_store')->where('category_id', $id)->delete();

    // 5. Xóa chính danh mục đó
    $db->table('game_categories')->where('id', $id)->delete();

    // 6. Cập nhật lại sort_order cho các danh mục có sort_order > deletedOrder
    $db->table('game_categories')
        ->where('sort_order >', $deletedOrder)
        ->set('sort_order', 'sort_order - 1', false)
        ->update();

    return redirect()->to(base_url('admin/game-categories'))->with('success', 'Đã xóa danh mục và các dữ liệu liên quan.');
}
    public function addCategory() {
            $db = \Config\Database::connect(); // Khởi tạo db

        $title = $this->request->getPost('title');
        $status = $this->request->getPost('status');
        $file = $this->request->getFile('image');
        
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);

        $this->db->table('game_categories')->insert([
            'title' => $title,
            'image' => $newName,
            'status' => $status,
            'file_link' => $this->request->getPost('file_link'),
        ]);

         $insertId = $db->insertID();
    
        $db->table('game_categories')
            ->where('id', $insertId)
            ->update(['sort_order' => $insertId]);


        return redirect()->back()->with('msgSuccess', 'Thêm danh mục thành công!');
    }


    
public function updateSortOrder()
{
    $db = \Config\Database::connect();
    
    // Lấy dữ liệu từ form
    $sortOrders = $this->request->getPost('sort_order');
    
    // Debug: kiểm tra dữ liệu nhận được
    log_message('debug', 'Sort Order Data: ' . print_r($sortOrders, true));
    
    if (empty($sortOrders) || !is_array($sortOrders)) {
        return redirect()->back()->with('error', 'Không có dữ liệu cập nhật!');
    }
    
    try {
        $db->transStart();
        $updated = 0;
        
        foreach ($sortOrders as $id => $order) {
            $id = (int) $id;
            $order = (int) $order;
            
            if ($id > 0 && $order > 0) {
                $result = $db->table('game_categories')
                    ->where('id', $id)
                    ->update(['sort_order' => $order]);
                
                if ($result) {
                    $updated++;
                }
            }
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Cập nhật thất bại!');
        }
        
        return redirect()->back()->with('success', "Đã cập nhật thứ tự cho {$updated} danh mục!");
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
    }
}
    public function manageKeys($cat_id) {
        $data = $this->_loadBaseData('Quản lý Key');
        $data['category'] = $this->db->table('game_categories')->where('id', $cat_id)->get()->getRow();
        $data['keys'] = $this->db->table('key_store')->where('category_id', $cat_id)->where('is_sold', 0)->orderBy('id', 'DESC')->get()->getResultArray();
        return view('customer/manage_keys', $data);
    }


    // Sửa giá hàng loạt theo loại thời gian
public function updateKeyPrice() {
    $cat_id = $this->request->getPost('category_id');
    $duration = $this->request->getPost('duration');
    $new_price = $this->request->getPost('price');

    $this->db->table('key_store')
             ->where(['category_id' => $cat_id, 'duration' => $duration, 'is_sold' => 0])
             ->update(['price' => $new_price]);

    return redirect()->back()->with('msgSuccess', "Đã cập nhật giá mới cho tất cả key loại: $duration");
}

// Xóa key đơn lẻ
public function deleteKey($id) {
    $this->db->table('key_store')->where('id', $id)->delete();
    return redirect()->back()->with('msgSuccess', 'Đã xóa Key thành công!');
}

// Xóa tất cả key chưa bán của một danh mục
public function deleteAllKeys($cat_id) {
    $this->db->table('key_store')
             ->where(['category_id' => $cat_id, 'is_sold' => 0])
             ->delete();
             
    return redirect()->back()->with('msgSuccess', 'Đã dọn dẹp toàn bộ kho key!');
}

// Cập nhật thông tin của một key đơn lẻ
public function updateSingleKey() {
    $id = $this->request->getPost('key_id');
    $time_val = $this->request->getPost('time_val');
    $time_unit = $this->request->getPost('time_unit');
    
    // Ghép chuỗi duration tương tự như lúc thêm mới
    $duration = ($time_unit == 'Vĩnh viễn') ? $time_unit : "$time_val $time_unit";

    $data = [
        'key_code' => trim($this->request->getPost('key_code')),
        'duration' => $duration,
        'price'    => $this->request->getPost('price')
    ];

    $this->db->table('key_store')->where('id', $id)->update($data);
    
    return redirect()->back()->with('msgSuccess', 'Đã cập nhật thông tin key thành công!');
}

public function addKeys() {
    $cat_id = $this->request->getPost('category_id');
    $time_val = $this->request->getPost('time_val');
    $time_unit = $this->request->getPost('time_unit');
    $price = $this->request->getPost('price');
    $keys_raw = $this->request->getPost('keys_list');

    // Nếu là Vĩnh viễn thì không cần con số
    $duration = ($time_unit == 'Vĩnh viễn') ? $time_unit : "$time_val $time_unit";
    
    $keys_array = explode("\n", str_replace("\r", "", $keys_raw));
    $batch_data = [];
    
    foreach ($keys_array as $k) {
        $k = trim($k);
        if (!empty($k)) {
            $batch_data[] = [
                'category_id' => $cat_id,
                'duration'    => $duration,
                'key_code'    => $k,
                'price'       => $price,
                'is_sold'     => 0
            ];
        }
    }
    
    if (!empty($batch_data)) {
        $this->db->table('key_store')->insertBatch($batch_data);
    }
    return redirect()->back()->with('msgSuccess', 'Đã thêm thành công!');
}
}