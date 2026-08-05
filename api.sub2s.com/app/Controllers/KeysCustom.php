<?php

namespace App\Controllers;

use App\Models\KeysModel;
use App\Models\UserModel;
use App\Models\HistoryModel;
use CodeIgniter\I18n\Time;

class KeysCustom extends BaseController
{
    protected $model, $user;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->user = $this->userModel->getUser();
        $this->model = new KeysModel();
    }

    public function index() {
        $keyModel = new \App\Models\KeysModel();
        $today = date('Y-m-d');
        if (!$this->user) {
            return redirect()->to('/login');
        }

        $username = is_array($this->user) ? $this->user['username'] : $this->user->username;

        $created_today = $keyModel
            ->where('registrator', $username)
            ->where("DATE(created_at)", $today)
            ->countAllResults();

        $activated_today = $keyModel
            ->where('registrator', $username)
            ->groupStart()
                ->where('devices !=', '')
                ->where('devices IS NOT NULL')
            ->groupEnd()
            ->where("DATE(updated_at)", $today)
            ->countAllResults();

        $data = [
            'title'           => 'Custom Bulk Generate',
            'user'            => $this->user,
            'game_list'       => ['PUBG' => 'ALL'],
            'created_today'   => $created_today,
            'activated_today' => $activated_today,
        ];

        return view('Admin/apiurl', $data);
    }

    public function action() {
        $user = $this->user;
        $prefix   = strtoupper($this->request->getPost('prefix') ?: 'KEYVIP');
        $quantity = (int)$this->request->getPost('quantity') ?: 1;
        $max_dev  = (int)$this->request->getPost('max_devices') ?: 1;
        $game     = $this->request->getPost('game');

        $d_val  = (int)$this->request->getPost('duration_val');
        $d_unit = $this->request->getPost('duration_unit');
        
        // Logic tính giờ và tạo nhãn đính kèm vào Key
        $label = ""; 
        switch ($d_unit) {
            case 'hour':  
                $hours = $d_val; 
                $label = $d_val . "H";
                break;
            case 'day':   
                $hours = $d_val * 24; 
                $label = $d_val . "D";
                break;
            case 'month': 
                $hours = $d_val * 720; 
                $label = $d_val . "M";
                break;
            case 'year':  
                $hours = $d_val * 8760; 
                $label = $d_val . "Y";
                break;
        }

        $generated_keys = [];
        for ($i = 0; $i < $quantity; $i++) {
            $random_str = strtoupper(bin2hex(random_bytes(8))); 
            // Định dạng mới: PREFIX-LABEL-RANDOM (Ví dụ: KEYVIP-6H-A1B2C3D4)
            $full_key   = $prefix . "-" . $label . "-" . $random_str;
            
            $data_insert = [
                'game'         => $game,
                'user_key'     => $full_key,
                'duration'     => $hours,
                'max_devices'  => $max_dev,
                'registrator'  => $user->username,
                'expired_date' => null,
                'status'       => 1
            ];

            $this->model->insert($data_insert);
            $generated_keys[] = $full_key;
        }

        return redirect()->back()->with('msgSuccess', "Đã tạo thành công $quantity Key!")
                                ->with('generated_keys', $generated_keys)
                                ->with('game_name', $game);
    }
}