<?php
namespace App\Controllers;

use App\Models\KeysModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class nb extends BaseController
{
    // private function isBlockedIP($ip)
    // {
    //     // $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,countryCode,proxy,hosting");
    //     $ch = curl_init("http://ip-api.com/json/{$ip}?fields=status,countryCode");
    //     curl_setopt_array($ch, [
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_TIMEOUT => 3,
    //     ]);
    //     $res = curl_exec($ch);
    //     curl_close($ch);

    //     if (!$res) return false;

    //     $data = json_decode($res, true);
    //     if (!isset($data['status']) || $data['status'] !== 'success') return false;

    //     return $data['countryCode'] !== 'VN';
    // }


    public function getKey($username)
    {
        helper('ShortenHelper');
        $userModel = new UserModel();
        $keysModel = new KeysModel();

        $user = $userModel->where('username', $username)->first();
        if (!$user) {
            return show_404();
        }

        helper(['text', 'shortener']);

        // ===== TẠO KEY =====
        $random = strtoupper(random_string('alnum', 12));
        $key = 'KEY_' . $random;

        $expired = Time::now()->addHours(12);

        $keysModel->insert([
            'game'         => 'PUBG',
            'user_key'     => $key,
            'duration'     => 12,
            'max_devices'  => 1,
            'registrator'  => $username,
            'expired_date' => $expired->toDateTimeString(),
            'status'       => 1,
        ]);

        // ===== LINK GỐC =====
        $longUrl = site_url('key?key=' . $key);
        $finalUrl = $longUrl;

        $funlink = "https://bbmkts.com/ql?token=1a471588d7365b07a2d71401&longurl=" . $finalUrl ;

        // $ip = $this->request->getServer('REMOTE_ADDR') ?? '0.0.0.0';
        // if ($this->isBlockedIP($ip)) {
        //     $blockedRedirect = "https://web.bbmkts.com/st?api=03bdde94119e529cc1c7b0cd8bca428aee16f149&url=" . $finalUrl;
        //     return redirect()->to($blockedRedirect);
        // }
        // ===== RÚT GỌN LINK (NẾU USER CÓ CẤU HÌNH) =====
        if (!empty($user['short_api_base'])) {
            $short = shorten_url(
                $user['short_api_base'],
                $funlink,
                $user['short_response_type'] ?? 'json',
                $user['short_response_key'] ?? 'url'
            );

            if ($short) {
                $linkend = $short;
            }
        }

        return redirect()->to($linkend);
    }

    public function key()
    {
        // $redirectUrl = 'https://key.gmvmoba.com/gmvmoba/getkey';

        $keyValue = $this->request->getGet('key');
        if (!$keyValue) {
            return view('Auth/key', [
                'key'      => $keyValue,
                'username' => 'GMV MOBA',
                'seconds'  => 2,
                // 'redirect' => $redirectUrl,
                'isError'  => true,
            ]);
        }

        $keysModel = new KeysModel();
        $keyData = $keysModel->where('user_key', $keyValue)->first();

        if (!$keyData) {
            return view('Auth/key', [
                'key'      => $keyValue,
                'username' => 'GMV MOBA',
                'seconds'  => 1,
                // 'redirect' => '',
                'isError'  => true,
            ]);
        }

        $expired = Time::parse($keyData['expired_date']);
        $seconds = $expired->getTimestamp() - Time::now()->getTimestamp();

        return view('Auth/key', [
            'key'      => $keyValue,
            'username' => $keyData['registrator'],
            'seconds'  => max(0, $seconds),
            'isError'  => false,
        ]);
    }
}
