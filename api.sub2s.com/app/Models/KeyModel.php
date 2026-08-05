<?php

namespace App\Models;

use CodeIgniter\Model;

class KeyModel extends Model
{
    protected $table = 'license_keys';
    protected $primaryKey = 'id';
    protected $allowedFields = ['key_value', 'package_token', 'udid', 'expiry_date', 'status'];
}