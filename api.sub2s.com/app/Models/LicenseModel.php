<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table = 'licenses';
    protected $allowedFields = [
        'license_key',
        'created_at',
        'expired_at',
        'ip_address',
        'created_by_user_id'
    ];

}
