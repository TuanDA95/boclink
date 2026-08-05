<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchasedLinkModel extends Model
{
    protected $table      = 'purchased_links';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'customer_id', 'code', 'target_url', 'price', 'created_at'
    ];
}