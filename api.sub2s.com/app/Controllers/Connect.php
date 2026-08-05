<?php

namespace App\Controllers;

use App\Models\KeysModel;

class Connect extends BaseController
{
    protected $model, $game, $uKey, $sDev;

    public function __construct()
    {
        include('conn.php');
        
        $sql1 ="select * from onoff where id=11";
        $result1 = mysqli_query($conn, $sql1);
        $userDetails1 = mysqli_fetch_assoc($result1);
        
        $this->model = new KeysModel();
        
        if($userDetails1['status'] == 'on'){
        
        $this->maintenance = false;
        
        }
        if($userDetails1['status'] == 'off'){
        
        $this->maintenance = true;
        
        }
        
        
        $this->staticWords = "Vm8Lk7Uj2JmsjCPVPVjrLa7zgfx3uz9E";
    }

    public function index()
    {
        if ($this->request->getPost()) {
            return $this->index_post();
        } else {
            $nata = [
                "web_info" => [
                    "_client" => BASE_NAME,
                    "license" => "Qp5KSGTquetnUkjX6UVBAURH8hTkZuLM",
                    "version" => "1.0.0",
                ],
                "web__dev" => [
                    "author" => "Vishesh",
                    "website" => "https://gmvmoba.com"
                ],
            ];
            
   /*         return "
<body>
<style>

.wrapper {
    height: 800px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000000; 
}
.txt {
    color: #ffffff;
    background:#000000;
    font-size:200px;
    font-weight: bold;
    font-family: Arial;
    text-transform: uppercase;
}
.neon wrapper {
    display: inline-flex;
}
.txt::before {
    content: 'hey';
    position: absolute;
    mix-blend-mode: difference;
}
.txt::before {
    content: 'hey';
    position: absolute;
    mix-blend-mode: difference;
    filter: blur(3px);
}
.neon-wrapper {
    display:inline-flex;
    filter: brightness(200%);
}
.gradient{
    background: linear-gradient(114.5793141156962deg, rgba(6, 227, 250,1) 4.927083333333334%,rgba(229, 151, 64,1) 97.84374999999999%);
    position: absolute;
    top: 0;
    left:0;
    width: 100%;
    height:100%;
    mix-blend-mode: multiply;
}  
.dodge {
    background: radial-gradient(circle,white,black 35%) center / 25% 25%;
    position: absolute;
    top:-100%;
    left:-100%;
    right:0;
    bottom:0;  
    mix-blend-mode: color-dodge;
    animation: dodge-area 3s linear infinite;
}
@keyframes dodge-area {
    to {
        transform: translate(50%,50%);
    }

}
.neon-wrapper {
    display:inline-flex;
    filter: brightness(200%);
    overflow: hidden;
}

</style>




    <div class='navbar'><span>neon text effect</div><pan>
<div class='wrapper'>
    
    <div class='neon-wrapper'>
        <span class='txt'>hey</span>
        <span class='gradient'></span>
        <span class='dodge'></span>
    </div>
   </div>
</body>";*/

            return "<h1><strong><center><font size='10' color='red' face='arial'><marquee direction='right' scrollamount='15'> Hello <br> Hello !</marquee></font></center></strong></h1>"; //$this->response->setJSON($nata); 
        }
    }

    public function index_post()
    {
        $isMT = $this->maintenance;
        $game = $this->request->getPost('game');
        $uKey = $this->request->getPost('user_key');
        $sDev = $this->request->getPost('serial');

       /* $form_rules = [
            'game' => 'required|alpha_dash',
            'user_key' => 'required|alpha_numeric|min_length[1]|max_length[36]',
            'serial' => 'required|alpha_dash'
        ];

        if (!$this->validate($form_rules)) {
            $data = [
                'status' => false,
                'reason' => "Bad Parameter",
            ];
            return $this->response->setJSON($data);
        } */

        if ($isMT) {
            
            include('conn.php');
        
            $sql1 ="select * from onoff where id=11";
            $result1 = mysqli_query($conn, $sql1);
            $userDetails1 = mysqli_fetch_assoc($result1);
        
            
            $data = [
                'status' => false,
                'reason' => $userDetails1['myinput']
            ];
        } else {
            if (!$game or !$uKey or !$sDev) {
                $data = [
                    'status' => false,
                    'reason' => 'THAM SỐ KHÔNG HỢP LỆ'
                ];
            } else {
                $time = new \CodeIgniter\I18n\Time;
                $model = $this->model;
                $findKey = $model
                    ->getKeysGame(['user_key' => $uKey, 'game' => $game]);

                if ($findKey) {
                    if ($findKey->status != 1) {
                        $data = [
                            'status' => false,
                            'reason' => "Bản hack này đã lỗi thời\nVui lòng truy cập website: https://gmvmoba.com để tải hack mới nhất"
                        ];
                    } else {
                        $id_keys = $findKey->id_keys;
                        $duration = $findKey->duration;
                        $expired = $findKey->expired_date;
                        $max_dev = $findKey->max_devices;
                        $devices = $findKey->devices;
    
                        // function checkDevicesAdd($serial, $devices, $max_dev)
                        // {
                        //     $lsDevice = explode(",", $devices);
                        //     $cDevices = isset($devices) ? count($lsDevice) : 0;
                        //     $serialOn = in_array($serial, $lsDevice);
    
                        //     if ($serialOn) {
                        //         return true;
                        //     } else {
                        //         if ($cDevices < $max_dev) {
                        //             array_push($lsDevice, $serial);
                        //             $setDevice = reduce_multiples(implode(",", $lsDevice), ",", true);
                        //             return ['devices' => $setDevice];
                        //         } else {
                        //             // ! false - devices max
                        //             return false;
                        //         }
                        //     }
                        // }

                        $current_devices = count(
                            array_filter(explode(",", (string)$devices))
                        );

                        function checkDevicesAdd($serial, $devices, $max_dev)
                        {
                            $serialClean = str_replace('-', '', (string)$serial);

                            $devicesClean = str_replace('-', '', (string)$devices);
                            
                            $lsDevice = array_filter(explode(",", $devicesClean));
                            $lsDevice = array_map('trim', $lsDevice);

                            $serialOn = in_array($serialClean, $lsDevice);

                            if ($serialOn) {
                                return true; 
                            } else {
                                if (count($lsDevice) < $max_dev) {
                                    $lsRaw = array_filter(explode(",", (string)$devices));
                                    array_push($lsRaw, $serialClean);
                                    $setDevice = implode(",", $lsRaw);
                                    return ['devices' => $setDevice];
                                } else {
                                    return false;
                                }
                            }
                        }
    
                        if (!$expired) {
                            $setExpired = $time::now()->addHours($duration);
                            $model->update($id_keys, ['expired_date' => $setExpired]);
                            $data['status'] = true;
                        } else {
                            if ($time::now()->isBefore($expired)) {
                                $data['status'] = true;
                            } else {
                                $data = [
                                    'status' => false,
                                    'reason' => "KEY HẾT HẠN\nVui lòng Get Key mới"
                                ];
                            }
                        }

                        if ($data['status']) {
                            
                            include('conn.php');
        
                            $sql2 ="select * from modname where id=1";
                            $result2 = mysqli_query($conn, $sql2);
                            $userDetails2 = mysqli_fetch_assoc($result2);
                            
                            $sql3 ="select * from _ftext where id=1";
                            $result3 = mysqli_query($conn, $sql3);
                            $userDetails3 = mysqli_fetch_assoc($result3);
        
        
                            
                            $devicesAdd = checkDevicesAdd($sDev, $devices, $max_dev);
                            if ($devicesAdd) {
                                if (is_array($devicesAdd)) {
                                    $model->update($id_keys, $devicesAdd);
                                }
                                $real = "$game-$uKey-$sDev-$this->staticWords";
                                $expiry = $findKey->expired_date;
                                if ($expiry == null) {
                                    $expiry = $time::now()->addHours($duration);
                                }

                                if ($expiry instanceof \CodeIgniter\I18n\Time) {
                                    $expiry = $expiry->toDateTimeString();
                                }                  
                                $data = [
                                'status' => true,
                                'data' => [
                                // 'real' => $real,
                                'modname' => $userDetails2['modname'],
                                'mod_status' => $userDetails3['_status'],
                                'credit' => $userDetails3['_ftext'],
                                'token' => md5($real),
                                'device'=> $max_dev,
                                'EXP2' => $expiry,
                                'EXP' => $expiry,
                                'rng' => $time->getTimestamp(),
                                'device_used' => $current_devices
                                ],
                                ];

                            } else {
                                $data = [
                                    'status' => false,
                                    'reason' => "KEY ĐÃ SỬ DỤNG CHO THIẾT BỊ KHÁC\nVui lòng Get Key mới để sử dụng trên thiết bị này"
                                ];
                            }
                        }
                    }
                } else {
                    $data = [
                        'status' => false,
                        'reason' => "KEY KHÔNG HỢP LỆ\nVui lòng Get Key mới"
                    ];
                }
            }
        }
        return $this->response->setJSON($data);
    }
}
