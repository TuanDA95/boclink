<?php

namespace App\Controllers;

use App\Models\HistoryModel;
use App\Models\KeysModel;
use App\Models\UserModel;
use Config\Services;
use CodeIgniter\I18n\Time;


class Keys extends BaseController
{
    protected $userModel, $model, $user;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->user = $this->userModel->getUser();
        $this->model = new KeysModel();
        $this->time = new \CodeIgniter\I18n\Time;

        /* ------- Game ------- */
        $this->game_list = [
            'PUBG' => 'PUBG Mobile'
        ];

        $this->duration = [
             2 => '2 Hours ',
            5 => '5 Hours ',
            24 => '1 Days ',
            72 => '3 Days ',
            168 => '7 Days',
            336 => '14 Days',
            720 => '30 Days',
            1440 => '60 Days',
            9999 => 'x Years',
            
        ];

        $this->price = [
            2 => 0,
            5 => 0,
            24 => 0,
            72 => 0,
            168 => 0,
            336 => 0,
            720 => 0,
            1440 => 0,
            9999 => 0,
        ];
    }

    public function index()
    {
        $model = $this->model;
        $user = $this->user;

        if ($user->level != 1) {
            $keys = $model->where('registrator', $user->username)
                ->findAll();
        } else {
            $keys = $model->findAll();
        }

        $data = [
            'title' => 'Keys',
            'user' => $user,
            'keylist' => $keys,
            'time' => $this->time,
        ];
        return view('Keys/list', $data);
    }

    public function api_get_keys()
    {
        // ? API for DataTable Keys
        $model = $this->model;
        return $model->API_getKeys();
    }
    

    // 1. Reset toàn bộ thiết bị của các Key thuộc quyền quản lý
public function reset_all_devices() {
    $user = (new \App\Models\UserModel())->getUser();
    
    // 1. Reset Database
    $this->model->where('registrator', $user->username)
                ->set(['devices' => null])
                ->update();

    // 2. XÓA TOÀN BỘ FILE TRONG domain.com/data/
    // $dir = ROOTPATH . 'data/';
    // if (is_dir($dir)) {
    //     $files = glob($dir . 'udid_*.txt'); 
    //     foreach ($files as $file) {
    //         if (is_file($file)) {
    //             @unlink($file); 
    //         }
    //     }
    // }

    return $this->response->setJSON(['status' => 'success', 'msg' => 'Đã reset trắng toàn bộ Key và xóa sạch UDID tạm!']);
}

public function update_all_expiry() {
    $val  = $this->request->getGet('val');
    $unit = $this->request->getGet('unit');
    $user = (new \App\Models\UserModel())->getUser();

    $interval = "INTERVAL $val " . strtoupper($unit);
    
    $sql = "UPDATE keys_code SET expired_date = 
            CASE 
                WHEN expired_date < NOW() OR expired_date IS NULL THEN DATE_ADD(NOW(), $interval)
                ELSE DATE_ADD(expired_date, $interval)
            END 
            WHERE registrator = '{$user->username}'";

    db_connect()->query($sql);

    return $this->response->setJSON(['status' => 'success', 'msg' => "Đã gia hạn thêm $val $unit cho tất cả key!"]);
}
public function update_status()
{
    // Lấy dữ liệu từ AJAX gửi lên
    $id = $this->request->getPost('id_keys');
    $status = $this->request->getPost('status');

    // Kiểm tra xem ID có tồn tại không để tránh crash
    if (!$id) {
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Thiếu ID']);
    }

    // Cách gọi Model trực tiếp không cần 'use' ở đầu trang
    // Thay 'KeyModel' bằng tên Model thực tế của bạn (Ví dụ: KeysModel)
    $keyModel = new \App\Models\KeyModel(); 

    try {
        $keyModel->update($id, ['status' => $status]);
        return $this->response->setJSON(['status' => 'success']);
    } catch (\Exception $e) {
        // Nếu lỗi, nó sẽ trả về tin nhắn lỗi để bạn xem ở tab Network
        return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()], 500);
    }
}
public function delete_expired() {
    $model = $this->model;
    $now = date('Y-m-d H:i:s');

    // CHỈ xóa khi thỏa mãn đồng thời 3 điều kiện:
    // 1. Không phải NULL (đã kích hoạt)
    // 2. Không phải 0000-00-00 (đã kích hoạt)
    // 3. Nhỏ hơn thời gian hiện tại (đã hết hạn)
    $model->where('expired_date IS NOT NULL')
          ->where('expired_date !=', '0000-00-00 00:00:00')
          ->where('expired_date <', $now)
          ->delete();

    return redirect()->back()->with('msgSuccess', 'Đã dọn dẹp các key đã hết hạn sử dụng.');
}
//delete wasted keys
public function deleteUnused(){
    echo  date('Y-m-d H:i:s');
    $model=$this->model;
    $data=$model->where('expired_date ='.null)->delete();
    return redirect()->back()->with('msgSuccess', 'success');
    
}

   public function api_key_reset()
{
    sleep(1);
    $model = $this->model;
    $keys = $this->request->getGet('userkey');
    $reset = $this->request->getGet('reset');
    $db_key = $model->getKeys($keys);

    $rules = [];
    if ($db_key) {
        $user = $this->user;
        if ($db_key->devices && $reset) {
            if ($user->level == 1 || $db_key->registrator == $user->username) {
                
                // 1. Lấy danh sách thiết bị hiện tại
                $old_devices = $db_key->devices; 
                $device_array = explode(',', $old_devices);
                
                // 2. Reset trong Database
                $model->set('devices', NULL)
                      ->where('user_key', $keys)
                      ->update();

                // 3. XÓA FILE TXT TẠI domain.com/data/
                // ROOTPATH thường trỏ đến gốc cài đặt của bạn
                // $dir = ROOTPATH . 'data/'; 
                
                // if (is_dir($dir)) {
                //     $files = glob($dir . 'udid_*.txt'); 
                //     foreach ($files as $file) {
                //         if (is_file($file)) {
                //             $content = trim(file_get_contents($file));
                //             // Nếu nội dung file trùng với UDID vừa reset thì xóa file
                //             if (in_array($content, $device_array)) {
                //                 @unlink($file);
                //             }
                //         }
                //     }
                // }

                $rules = ['reset' => true, 'devices_total' => 0, 'devices_max' => $db_key->max_devices];
            }
        }
    }

    $data = [
        'registered' => $db_key ? true : false,
        'keys' => $keys,
    ];

    $real_response = array_merge($data, $rules);
    return $this->response->setJSON($real_response);
}

    public function edit_key($key = false)
    {
        if ($this->request->getPost()) return $this->edit_key_action();
        $msgDanger = "The user key no longer exists.";
        if ($key) {
            $dKey = $this->model->getKeys($key, 'id_keys');
            $user = $this->user;
            if ($dKey) {
                if ($user->level == 1 or $dKey->registrator == $user->username) {
                    $validation = Services::validation();
                    $data = [
                        'title' => 'Key',
                        'user' => $user,
                        'key' => $dKey,
                        'game_list' => $this->game_list,
                        'time' => $this->time,
                        'key_info' => getDevice($dKey->devices),
                        'messages' => setMessage('Please carefuly edit information'),
                        'validation' => $validation,
                    ];
                    return view('Keys/key_edit', $data);
                } else {
                    $msgDanger = "Restricted to this user key.";
                }
            }
        }
        return redirect()->to('keys')->with('msgDanger', $msgDanger);
    }

    private function edit_key_action()
    {
        $keys = $this->request->getPost('id_keys');
        $user = $this->user;
        $dKey = $this->model->getKeys($keys, 'id_keys');
        $game = implode(",", array_keys($this->game_list));

        if (!$dKey) {
            $msgDanger = "The user key no longer exists~";
        } else {
            if ($user->level == 1 or $dKey->registrator == $user->username) {
                $form_reseller = [
                    'status' => [
                        'label' => 'status',
                        'rules' => 'required|integer|in_list[0,1]',
                        'erros' => [
                            'integer' => 'Invalid {field}.',
                            'in_list' => 'Choose between list.'
                        ]
                    ]
                ];
                $form_admin = [
                    'id_keys' => [
                        'label' => 'keys',
                        'rules' => 'required|is_not_unique[keys_code.id_keys]|numeric',
                        'errors' => [
                            'is_not_unique' => 'Invalid keys.'
                        ],
                    ],
                    'game' => [
                        'label' => 'Games',
                        'rules' => "required|alpha_numeric_space|in_list[$game]",
                        'errors' => [
                            'alpha_numeric_space' => 'Invalid characters.'
                        ],
                    ],
                    'user_key' => [
                        'label' => 'User keys',
                        'rules' => "required|is_unique[keys_code.user_key,user_key,$dKey->user_key]|regex_match[/^[A-Za-z0-9@#._-]+$/]",
                        'errors' => [
                            'is_unique' => '{field} has been taken.'
                        ],
                    ],
                    'duration' => [
                        'label' => 'duration',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid Hours {field}.'
                        ]
                    ],
                    'max_devices' => [
                        'label' => 'devices',
                        'rules' => 'required|numeric|greater_than_equal_to[1]',
                        'errors' => [
                            'greater_than_equal_to' => 'Minimum {field} is invalid.',
                            'numeric' => 'Invalid max of {field}.'
                        ]
                    ],
                    'registrator' => [
                        'label' => 'registrator',
                        'rules' => 'permit_empty|alpha_numeric_space|min_length[4]'
                    ],
                    'expired_date' => [
                        'label' => 'expired',
                        'rules' => 'permit_empty|valid_date[Y-m-d H:i:s]',
                        'errors' => [
                            'valid_date' => 'Invalid {field} date.',
                        ]
                    ],
                    'devices' => [
                        'label' => 'device list',
                        'rules' => 'permit_empty'
                    ]
                ];

                if ($user->level == 1) {
                    // Admin full rules.
                    $form_rules = array_merge($form_reseller, $form_admin);
                    $devices = $this->request->getPost('devices');
                    $max_devices = $this->request->getPost('max_devices');

                    $data_saves = [
                        'game' => $this->request->getPost('game'),
                        'user_key' => $this->request->getPost('user_key'),
                        'duration' => $this->request->getPost('duration'),
                        'max_devices' => $max_devices,
                        'status' => $this->request->getPost('status'),
                        'registrator' => $this->request->getPost('registrator'),
                        'expired_date' => $this->request->getPost('expired_date') ?: NULL,
                        'devices' => setDevice($devices, $max_devices),
                    ];
                } else {
                    // Reseller just status rules, you can set manually later.
                    $form_rules = $form_reseller;
                    $data_saves = ['status' => $this->request->getPost('status')];
                }

                if (!$this->validate($form_rules)) {
                    return redirect()->back()->withInput()->with('msgDanger', 'Failed! Please check the error');
                } else {
                    // * Data Updates
                    $this->model->update($dKey->id_keys, $data_saves);
                    return redirect()->back()->with('msgSuccess', 'User key successfuly updated!');
                }
            } else {
                $msgDanger = "Restricted to this user key~";
            }
        }
        return redirect()->to('keys')->with('msgDanger', $msgDanger);
    }

    public function generate()
    {
        if ($this->request->getPost())
            return $this->generate_action();

        $user = $this->user;
        $validation = Services::validation();

        $message = setMessage("<i class='bi bi-wallet'></i> Total Saldo - ₹ $user->saldo");
        if ($user->saldo <= 0) {
            $message = setMessage("Please top up to your beloved admin.", 'warning');
        }

        $data = [
            'title' => 'Generate',
            'user' => $user,
            'time' => $this->time,
            'game' => $this->game_list,
            'duration' => $this->duration,
            'price' => json_encode($this->price),
            'messages' => $message,
            'validation' => $validation,
        ];
        return view('Keys/generate', $data);
    }
    

    private function generate_action()
    {
    
        $user = $this->user;
        $game = $this->request->getPost('game');
        $maxd = $this->request->getPost('max_devices');
        $drtn = $this->request->getPost('duration');
        $getPrice = getPrice($this->price, $drtn, $maxd);
        
        $loopcount =  $this->request->getPost('loopcount');
        
        if ($loopcount == "1"){
        $loopcount = 6;
        
        }
        
        else if ($loopcount == "2"){
        $loopcount = 11;
  
        }
        
        else if ($loopcount == "3"){
        $loopcount = 51;
  
        }
        else if ($loopcount == "4"){
        $loopcount = 101;
        
        }
        
       
      

          $game_list = implode(",", array_keys($this->game_list));
          $form_rules = [
              'game' => [
                  'label' => 'Games',
                  'rules' => "required|alpha_numeric_space|in_list[$game_list]",
                  'errors' => [
                      'alpha_numeric_space' => 'Invalid characters.'
                  ],
              ],
              'duration' => [
                  'label' => 'duration',
                  'rules' => 'required|numeric|greater_than_equal_to[1]',
                  'errors' => [
                     'greater_than_equal_to' => 'Minimum {field} is invalid.',
                      'numeric' => 'Invalid hour {field}.'
                  ]
              ],
              'max_devices' => [
                  'label' => 'devices',
                  'rules' => 'required|numeric|greater_than_equal_to[1]',
                  'errors' => [
                      'greater_than_equal_to' => 'Minimum {field} is invalid.',
                      'numeric' => 'Invalid max of {field}.'
                  ]
              ],
          ];

          $validation = Services::validation();
          $reduceCheck = ($user->saldo - $getPrice);
          // dd($reduceCheck);
          if ($reduceCheck < 0) {
              $validation->setError('duration', 'Insufficient balance');
              return redirect()->back()->withInput()->with('msgWarning', 'Please top up to your beloved admin.');
          } else {
              if (!$this->validate($form_rules)) {
                  return redirect()->back()->withInput()->with('msgDanger', 'Failed! Please check the error');
              } else {
                
                 //================================================//
                
           
            
    
                //for($i = 1; $i < $loopcount; $i++) {
                
              //}
            
            
                    $license = random_string('alnum',20);
        
                   // echo "$license  <br><br>";
        
                  
               
                  
                  //================================================//
                  
   
                      $msg = "Successfuly Generated.";

                  $expiredDate = Time::now()
                        ->addHours((int)$drtn)
                        ->toDateTimeString();

                    $data_response = [
                        'game'         => $game,
                        'user_key'     => $license,
                        'duration'     => $drtn,
                        'max_devices'  => $maxd,
                        'registrator'  => $user->username,
                        'expired_date' => $expiredDate,
                    ];



                //   $data_response = [
                //       'game' => $game,
                //       'user_key' => $license,
                //       'duration' => $drtn,
                //       'max_devices' => $maxd,
                //       'registrator' => $user->username,
                //   ];

                 // * reseller reduce saldo
                  $idKeys = $this->model->insert($data_response);

                  $this->userModel->update(session('userid'), ['saldo' => $reduceCheck]);

                  $history = new HistoryModel();
                  $history->insert([
                      'keys_id' => $idKeys,
                      'user_do' => $user->username,
                      'info' => "$game|" . substr($license, 0, 5) . "|$drtn|$maxd"
                  ]);

                  $other_response = [
                      'fees' => $getPrice
                  ];

                  session()->setFlashdata(array_merge($data_response, $other_response));
                 
                 
                  return redirect()->back()->with('msgSuccess', $msg);
                
              }
          }
     }
 
}
