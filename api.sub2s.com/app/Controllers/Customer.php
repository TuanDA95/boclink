<?php
namespace App\Controllers;

class Customer extends BaseController {

    protected $db;

    public function __construct() {
        // Khởi tạo kết nối database dùng chung cho toàn bộ Class
        $this->db = \Config\Database::connect();
    }

    public function viewGame($id) {
        $cust_id = session()->get('customer_id');
        if (!$cust_id) return redirect()->to('/customer/login');

        // Lấy thông tin khách hàng để hiện số dư trên View
        $data['customer'] = $this->db->table('customers')->where('id', $cust_id)->get()->getRowArray();
        
        // Lấy thông tin Game
        $data['game'] = $this->db->table('game_categories')->where('id', $id)->get()->getRow();
        
        if (!$data['game']) {
            return redirect()->back()->with('msgDanger', 'Sản phẩm không tồn tại!');
        }

        // Gom nhóm các loại thời gian và giá tương ứng
        $data['durations'] = $this->db->table('key_store')
            ->select('duration, price, count(*) as stock')
            ->where(['category_id' => $id, 'is_sold' => 0])
            ->groupBy('duration')
            ->get()->getResultArray();
            
        return view('customer/store_view', $data);
    }

    public function buyKey() {
        $cust_id = session()->get('customer_id');
        if (!$cust_id) return redirect()->to('/customer/login');

        $cat_id = $this->request->getPost('category_id');
        $duration = $this->request->getPost('duration');
        $qty = (int)$this->request->getPost('qty');

        if ($qty <= 0) return redirect()->back()->with('msgDanger', 'Số lượng không hợp lệ!');

        $this->db->transStart();

        // Lấy đúng số lượng và dùng FIFO (mã nào nhập trước bán trước)
        $keys = $this->db->table('key_store')
            ->where([
                'category_id' => $cat_id, 
                'duration'    => $duration, 
                'is_sold'     => 0
            ])
            ->orderBy('id', 'ASC') 
            ->limit($qty)
            ->get()
            ->getResultArray();

        if (count($keys) < $qty) {
            $this->db->transRollback();
            return redirect()->back()->with('msgDanger', 'Kho không đủ key cho yêu cầu này!');
        }

        $price_each = $keys[0]['price'];
        $total_cost = $price_each * $qty;

        $customer = $this->db->table('customers')->where('id', $cust_id)->get()->getRow();
        if ($customer->balance < $total_cost) {
            $this->db->transRollback();
            return redirect()->back()->with('msgDanger', 'Số dư không đủ! Cần thêm ' . number_format($total_cost - $customer->balance) . 'đ');
        }
       
        // 1. Trừ tiền khách hàng
        $this->db->table('customers')->where('id', $cust_id)->decrement('balance', $total_cost);

        // 2. Cập nhật trạng thái Key
        foreach ($keys as $k) {
            $this->db->table('key_store')->where('id', $k['id'])->update([
                'is_sold'     => 1,
                'customer_id' => $cust_id,
                'sold_at'     => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return redirect()->back()->with('msgDanger', 'Giao dịch thất bại, vui lòng thử lại.');
        }

        return redirect()->to(base_url('/historyKeys?cat_id=' . $cat_id))
                         ->with('msgSuccess', 'Mua thành công ' . $qty . ' mã! Xem chi tiết bên dưới.');
    }

 public function historyKeys() 
{
    $cust_id = session()->get('customer_id');
    if (!$cust_id) return redirect()->to('/customer/login');

    $db = \Config\Database::connect();
    $cat_id = $this->request->getVar('cat_id'); // ID game khách chọn

    $data['customer'] = $db->table('customers')->where('id', $cust_id)->get()->getRowArray();
    $data['selected_cat'] = null;

    if ($cat_id) {
        // CHẾ ĐỘ 2: Hiển thị danh sách Key của Game đã chọn
        $data['selected_cat'] = $db->table('game_categories')->where('id', $cat_id)->get()->getRowArray();
        
        $builder = $db->table('key_store ks')
            ->select('ks.*, gc.title as game_title')
            ->join('game_categories gc', 'gc.id = ks.category_id')
            ->where(['ks.customer_id' => $cust_id, 'ks.is_sold' => 1, 'ks.category_id' => $cat_id]);

        $perPage = 10;
        $page = (int)($this->request->getVar('page') ?? 1);
        $total = $builder->countAllResults(false);

        $data['history'] = $builder->orderBy('ks.sold_at', 'DESC')
                                   ->limit($perPage, ($page - 1) * $perPage)
                                   ->get()->getResultArray();

        $pager = \Config\Services::pager();
        $data['pager_links'] = $pager->makeLinks($page, $perPage, $total, 'default_full');
    } else {
        // CHẾ ĐỘ 1: Hiển thị danh sách các Game khách đã từng mua
        $data['categories'] = $db->table('key_store ks')
            ->select('gc.*, count(ks.id) as total_bought')
            ->join('game_categories gc', 'gc.id = ks.category_id')
            ->where(['ks.customer_id' => $cust_id, 'ks.is_sold' => 1])
            ->groupBy('gc.id')
            ->get()->getResultArray();
    }

    return view('customer/history_keys', $data);
}

public function historylogKeys()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();
        
        // 1. Kiểm tra đăng nhập
        $cust_id = session()->get('customer_id');
        if (!$cust_id) return redirect()->to('/customer/login');

        // 2. Lấy tham số cat_id từ URL (?cat_id=1)
        $cat_id = $request->getGet('cat_id');
        
        if (!$cat_id) {
            return redirect()->to('/customer/history')->with('error', 'Vui lòng chọn danh mục!');
        }

        // 3. Lấy thông tin danh mục để hiển thị tiêu đề/hình ảnh
        $category = $db->table('game_categories')
                       ->where('id', $cat_id)
                       ->get()
                       ->getRowArray();

        if (!$category) {
            return redirect()->to('/customer/history')->with('error', 'Danh mục không tồn tại!');
        }

        // 4. Lấy thông tin số dư khách hàng
        $customer = $db->table('customers')->where('id', $cust_id)->get()->getRowArray();

        // 5. Cấu hình phân trang cho danh sách Key
        $perPage = 10;
        $page = (int)($request->getGet('page') ?? 1);

        $builder = $db->table('key_store ks')
            ->select('ks.*, gc.title as game_title')
            ->join('game_categories gc', 'gc.id = ks.category_id')
            ->where([
                'ks.customer_id' => $cust_id, 
                'ks.is_sold'     => 1, 
                'ks.category_id' => $cat_id
            ]);

        // Đếm tổng số bản ghi để làm phân trang
        $total = $builder->countAllResults(false);

        // Lấy dữ liệu thực tế theo trang
        $history = $builder->orderBy('ks.sold_at', 'DESC')
                           ->limit($perPage, ($page - 1) * $perPage)
                           ->get()
                           ->getResultArray();

        // 6. Khởi tạo Pager
        $pager = \Config\Services::pager();
        
        $data = [
            'title'        => 'Lịch sử Key: ' . $category['title'],
            'customer'     => $customer,
            'selected_cat' => $category,
            'history'      => $history,
            'pager_links'  => $pager->makeLinks($page, $perPage, $total, 'default_full'),
        ];

        return view('customer/history_log_key', $data);
    }
}