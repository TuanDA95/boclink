<?php

namespace App\Models;
use CodeIgniter\Model;

class BundleModel extends Model {
    protected $table            = 'bundle_configs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['username', 'bundle_id', 'game_name', 'status', 'update_link', 'message', 'version', 'require_key'];
    protected $useTimestamps    = true; // Bật để dùng updated_at
}