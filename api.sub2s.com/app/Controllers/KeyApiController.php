<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ApiKeyModel;
use App\Models\PackageModel;

class KeyApiController extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        $token = $this->request->getGet('token');

        $package = (new PackageModel())->where('token', $token)->first();
        if (!$package) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid package token'
            ]);
        }

        $udid = $this->request->getGet('udid');

        if (!$key || !$token) {
            return $this->response->setJSON(['status'=>false,'msg'=>'Missing params']);
        }

        $apiKey = (new ApiKeyModel())->where('api_key',$key)->first();
        if (!$apiKey) {
            return $this->response->setJSON(['status'=>false,'msg'=>'Invalid key']);
        }

        if (strtotime($apiKey['expiry_date']) < time()) {
            return $this->response->setJSON(['status'=>false,'msg'=>'Expired']);
        }

        return $this->response->setJSON([
            'status' => true,
            'key' => $key,
            'udid' => $udid,
            'expiry' => $apiKey['expiry_date'],
            'ip' => $this->request->getIPAddress(),
            'os' => $this->request->getUserAgent()->getPlatform()
        ]);
    }
}
