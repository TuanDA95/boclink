<?php

namespace App\Models;

use CodeIgniter\Model;
use \Hermawan\DataTables\DataTable;

class KeysModel extends Model
{
    protected $table      = 'keys_code';
    protected $primaryKey = 'id_keys';
    protected $allowedFields = ['game', 'user_key', 'duration', 'expired_date', 'max_devices', 'devices', 'status', 'registrator'];

    protected $useTimestamps = true;

    public function getKeys($key = false, $where = 'user_key')
    {
        return $this->where($where, $key)
            ->get()
            ->getRowObject();
    }

    public function getKeysGame($where)
    {
        return $this->where($where)
            ->get()
            ->getRowObject();
    }

    public function API_getKeys()
{
    $connect = db_connect();
    $builder = $connect->table($this->table);

    $userModel = new UserModel();
    $user = $userModel->getUser();
    if ($user->level != 1) {
        $builder->where('registrator', $user->username);
    }

    // Lấy expired_date thô để xử lý
    $builder->select('keys_code.id_keys as id, game, user_key, duration, expired_date as expired, max_devices, devices, status, registrator');

    return DataTable::of($builder)
        ->setSearchableColumns(['id_keys', 'game', 'user_key', 'duration', 'expired_date', 'max_devices', 'devices', 'registrator'])
        ->format('status', function ($value) {
            return ($value ? "Active" : "Inactive");
        })
        ->format('duration', function ($value) {
            return "$value Hours";
        })
        ->format('devices', function ($value) {
            if ($value) {
                // Sửa lỗi explode nếu dữ liệu trống
                $clean_val = reduce_multiples($value, ",", true);
                $e = $clean_val ? explode(',', $clean_val) : [];
                return count($e);
            }
            return 0;
        })
        ->format('expired', function ($value) {
            if (!$value || $value == '0000-00-00 00:00:00') {
                return ''; 
            }
            
            // Sử dụng helper Time của CI4 để ép kiểu về múi giờ VN và định dạng số
            try {
                $time = \CodeIgniter\I18n\Time::parse($value, 'Asia/Ho_Chi_Minh');
                // Trả về định dạng: 26/01/2026 14:24
                return $time->format('d/m/Y - H:i');
            } catch (\Exception $e) {
                return $value; // Nếu lỗi thì trả về chuỗi gốc để không mất dữ liệu
            }
        })
        ->toJson(true);
}
}
