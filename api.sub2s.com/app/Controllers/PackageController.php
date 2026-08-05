<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PackageModel;

class PackageController extends BaseController
{
    public function index()
    {
        return view('Admin/package');
    }

    // TẠO PACKAGE → SINH TOKEN 1 LẦN
    public function create()
    {
        $model = new PackageModel();

        $model->insert([
            'name'    => $this->request->getPost('name'),
            'version' => $this->request->getPost('version'),
            'token'   => bin2hex(random_bytes(32)), // 👈 AUTO TOKEN
        ]);

        return redirect()->back();
    }

    // UPDATE PACKAGE → KHÔNG ĐỘNG TOKEN
    public function update($id)
    {
        $model = new PackageModel();

        $model->update($id, [
            'name'    => $this->request->getPost('name'),
            'version' => $this->request->getPost('version'),
            // ❌ KHÔNG update token
        ]);

        return redirect()->back();
    }

    // DELETE → tạo mới sẽ có token mới
    public function delete($id)
    {
        (new PackageModel())->delete($id);
        return redirect()->back();
    }
}
