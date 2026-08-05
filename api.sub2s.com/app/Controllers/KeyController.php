<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ApiKeyModel;

class KeyController extends BaseController
{
    public function index()
    {
        return view('Admin/key');
    }

    public function create()
    {
        $key = bin2hex(random_bytes(16));

        (new ApiKeyModel())->insert([
            'package_id' => $this->request->getPost('package_id'),
            'api_key' => $key,
            'expiry_date' => date('Y-m-d H:i:s', strtotime('+'.$this->request->getPost('days').' days'))
        ]);

        return redirect()->back()->with('key', $key);
    }
}
