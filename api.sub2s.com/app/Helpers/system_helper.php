<?php

use App\Models\UserModel;

if (!function_exists('is_free_enabled')) {
    function is_free_enabled()
    {
        $userModel = new UserModel();
        $user = $userModel->getUser();
        return ($user['enable_free_global'] ?? 1) == 1;
    }
}