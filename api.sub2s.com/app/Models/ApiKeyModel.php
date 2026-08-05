<?php
namespace App\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table = 'api_keys';
    protected $allowedFields = [
        'package_id','api_key','udid','expiry_date','ip_address','status'
    ];
}
