<?php

namespace App\Models;

use CodeIgniter\Model;

class RechargeModel extends Model
{
    protected $table      = 'recharge_history';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id', 'type', 'telco', 'amount', 
        'amount_sent', 'code', 'status', 'created_at', 'serial', 'pin', 'request_id', 'updated_at'
    ];
}