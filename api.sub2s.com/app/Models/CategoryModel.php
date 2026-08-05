<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'game_categories'; // Tên bảng trong DB của bạn
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['title', 'image', 'status', 'file_link']; // Các cột được phép sửa/thêm

    // Hàm lấy danh sách kèm số lượng stock (nếu bạn cần dùng ở trang quản lý)
    public function getCategoriesWithStock()
    {
        return $this->select('game_categories.*, COUNT(key_store.id) as stock_count')
                    ->join('key_store', 'key_store.category_id = game_categories.id AND key_store.is_sold = 0', 'left')
                    ->groupBy('game_categories.id')
                    ->findAll();
    }
}