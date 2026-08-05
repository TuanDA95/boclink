<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['username', 'password', 'balance', 'created_at'];
    protected $returnType       = 'array';
    
    // Hàm tiện ích cộng tiền
    public function deposit($id, $amount) {
        $this->where('id', $id)->increment('balance', $amount);
    }

    // Hàm tiện ích trừ tiền
    public function deduct($id, $amount) {
        $this->where('id', $id)->decrement('balance', $amount);
    }
}