<?php
session_start();
require 'functions.php';
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Xóa ngay sau khi lấy
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']); // Xóa ngay sau khi lấy
}
$data = loadKeys();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $assignByIp   = !empty($_POST['assign_by_ip']);
    $deleteOnCopy = !empty($_POST['delete_on_copy']);

    $availableKeys = array_filter(
        array_map('trim', explode("\n", $_POST['available'] ?? ''))
    );

    // ❌ Nếu bật IP mà không có key riêng
    if (($assignByIp || $deleteOnCopy) && empty($availableKeys)) {
        $_SESSION['error'] = "❌ Không thể lưu: Key riêng (1 IP / 1 key) không được để trống khi bật chức năng.";
    } else {

        // ✅ SETTINGS
        $data['settings']['assign_by_ip']   = $assignByIp ? 1 : 0;
        $data['settings']['delete_on_copy'] = $deleteOnCopy ? 1 : 0;

        /**
         * ===== GLOBAL KEYS (CÓ / KHÔNG HẾT HẠN) =====
         */
        $rawGlobalKeys = array_filter(
            array_map('trim', explode("\n", $_POST['global_keys'] ?? ''))
        );

        $expireValue = $_POST['expire_value'] ?? null;
        $expireUnit  = $_POST['expire_unit'] ?? null;

        $expireAt = null;
        if ($expireValue && $expireUnit) {
            $expireAt = parseExpireTime($expireValue, $expireUnit);
        }

        $globalKeys = [];

        foreach ($rawGlobalKeys as $key) {

            $key = trim(explode('|', $key)[0]);

            if ($expireAt) {
                $globalKeys[] = [
                    'key' => $key,
                    'expire_at' => $expireAt
                ];
            } else {
                $globalKeys[] = $key;
            }
        }


        $data['global_keys'] = $globalKeys;


        /**
         * ===== AVAILABLE KEYS =====
         */
        $data['available'] = array_values(array_unique($availableKeys));

        saveKeys($data);
        $_SESSION['msg'] = "✅ Đã cập nhật cấu hình key";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>𝐖𝐄𝐋𝐂𝐎𝐌𝐄 - Panel</title>
    
    <link href="https://key.gmvmoba.com/assets/css/natacode.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Start menu -->
    <style>
    :root { --side-bg: #ffffff; --side-text: #334155; --accent: #3b82f6; }
    body { background: #f8fafc; overflow-x: hidden; }

    /* Sidebar Styles */
    #main-sidebar {
        width: 280px; height: 100vh; position: fixed; left: 0; top: 0;
        background: var(--side-bg); z-index: 1050; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 15px rgba(0,0,0,0.05); border-right: 1px solid #e2e8f0;
    }
    
    #sidebar-overlay {
        display: none; position: fixed; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.4); z-index: 1040; backdrop-filter: blur(2px);
    }

    .nav-custom { padding: 20px; }
    .nav-custom .nav-link {
        display: flex; align-items: center; padding: 12px 15px;
        color: var(--side-text); font-weight: 500; border-radius: 10px; margin-bottom: 5px;
        transition: 0.2s;
    }
    .nav-custom .nav-link:hover { background: #f1f5f9; color: var(--accent); }
    .nav-custom .nav-link i { font-size: 1.2rem; margin-right: 12px; width: 25px; }
    .nav-custom .active { background: #eff6ff; color: var(--accent); }

    /* Header cho Mobile */
    .mobile-header {
        background: #fff; padding: 15px 20px; display: flex; align-items: center;
        position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    @media (max-width: 991.98px) {
        #main-sidebar { margin-left: -280px; }
        #main-sidebar.show { margin-left: 0; }
        #main-sidebar.show + #sidebar-overlay { display: block; }
    }
</style>

<div class="mobile-header d-lg-none">
    <button class="btn btn-light border-0" id="sidebarToggle">
        <i class="bi bi-list fs-3"></i>
    </button>
    <div class="ms-3 fw-bold">𝐖𝐄𝐋𝐂𝐎𝐌𝐄</div>
</div>

<nav id="main-sidebar">
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <a class="text-decoration-none fw-bold text-dark fs-5" href="https://key.gmvmoba.com/admin">
            <i class="bi bi-star-fill text-danger me-2"></i>𝐖𝐄𝐋𝐂𝐎𝐌𝐄        </a>
        <button class="btn d-lg-none p-0" id="sidebarClose"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="nav-custom">
                    <small class="text-uppercase text-muted fw-bold px-3 mb-2 d-block" style="font-size: 0.7rem;">Menu chính</small>
            
            <a class="nav-link " href="https://key.gmvmoba.com/admin">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

                            <a class="nav-link" href="https://key.gmvmoba.com/apiurl"><i class="bi bi-braces"></i> API Quản trị</a>
                <a class="nav-link" href="https://key.gmvmoba.com/admin/link"><i class="bi bi-link-45deg"></i> Quản lý Links</a>
                <a class="nav-link" href="https://key.gmvmoba.com/public/keyapi/admin_keys.php"><i class="bi bi-key"></i> Key API System</a>
                <a class="nav-link" href="/admin/bundle-manager"><i class="bi bi-key"></i>Package Dylib</a>

            
            <hr class="my-3 text-muted">
            <small class="text-uppercase text-muted fw-bold px-3 mb-2 d-block" style="font-size: 0.7rem;">Tài khoản</small>
            
            <!-- <a class="nav-link" href="https://key.gmvmoba.com/settings"><i class="bi bi-gear"></i> Cài đặt</a> -->

                            <div class="bg-light rounded-3 p-2 my-2">
                <a class="nav-link" href="/customer/dashboard"><i class="bi bi-speedometer2"></i>Dashboard Shop</a>

                    <a class="nav-link py-2 text-danger small" href="https://key.gmvmoba.com/Server"><i class="bi bi-controller"></i> Online System</a>
                    <a class="nav-link py-2 text-danger small" href="https://key.gmvmoba.com/admin/manage-users"><i class="bi bi-person-check"></i> Quản lý User</a>
                    <a class="nav-link py-2 text-danger small" href="https://key.gmvmoba.com/admin/create-referral"><i class="bi bi-person-plus"></i> Tạo Referral</a>
                </div>
            
            <a class="nav-link text-danger mt-3" href="https://key.gmvmoba.com/logout"><i class="bi bi-box-arrow-left"></i> Đăng xuất</a>
            
            <div class="mt-4 p-3 bg-light rounded-4 text-center">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
                <div class="small fw-bold mt-1">GMV</div>
            </div>
            </div>
</nav>

<div id="sidebar-overlay"></div><script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const btnToggle = document.getElementById('sidebarToggle');
        const btnClose = document.getElementById('sidebarClose');

        function toggleMenu() {
            sidebar.classList.toggle('show');
        }

        if(btnToggle) btnToggle.onclick = toggleMenu;
        if(btnClose) btnClose.onclick = toggleMenu;
        if(overlay) overlay.onclick = toggleMenu;

        // Tự động đóng khi nhấn vào các liên kết trên mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.onclick = () => {
                if(window.innerWidth < 992) sidebar.classList.remove('show');
            };
        });
    });
</script>     <!-- End of menu -->
    <main>
        <div class="container p-3 py-4 mb-3" style="background-image: url('https://images2.alphacoders.com/993/thumb-1920-993073.jpg')"> 
            <!-- Start content -->
                <style>
/* ===== Layout ===== */
.row.justify-content-center {
    max-width: 900px;
    margin: auto;
}

h2 {
    font-weight: 700;
    margin-bottom: 20px;
    color: #0d6efd;
}

h3 {
    font-weight: 600;
    margin: 18px 0 8px;
    color: #198754;
}

/* ===== Form ===== */
form {
    background: #ffffff;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    border: 1px solid #e5e7eb;
}

/* Checkbox */
label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
}

input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #0d6efd;
}

/* Textarea */
textarea {
    width: 100%;
    min-height: 130px;
    padding: 12px;
    font-family: Consolas, monospace;
    font-size: 14px;
    border-radius: 10px;
    border: 1px solid #ced4da;
    background: #f8fafc;
    resize: vertical;
    transition: .2s;
}

textarea:focus {
    outline: none;
    border-color: #0d6efd;
    background: #ffffff;
    box-shadow: 0 0 0 2px rgba(13,110,253,.15);
}

/* Button Save */
button {
    background: linear-gradient(135deg,#0d6efd,#0b5ed7);
    border: none;
    color: #fff;
    padding: 10px 22px;
    font-weight: 600;
    border-radius: 999px;
    cursor: pointer;
    transition: .25s;
}

button:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(13,110,253,.35);
}

/* Alert message */
p[style*="color:green"] {
    background: #e9fbe9;
    color: #198754 !important;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 600;
    border: 1px solid #b7ebc6;
}

/* Table */
.table {
    margin-top: 15px;
    border-radius: 14px;
    overflow: hidden;
}

/* API link box */
.mb-3 {
    margin-top: 25px;
}

.form-label {
    font-weight: 600;
    margin-bottom: 6px;
    display: inline-block;
}

.input-group {
    display: flex;
    gap: 6px;
}

.form-control {
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    font-size: 14px;
    background: #f8fafc;
}

.form-control:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,.15);
}

.btn-outline-secondary {
    border-radius: 10px;
    font-weight: 600;
    transition: .2s;
    color: #fff;
}

.btn-outline-secondary:hover {
    background: #0d6efd;
    color: #fff;
}

/* Responsive */
@media (max-width: 600px) {
    form {
        padding: 18px;
    }

    textarea {
        min-height: 110px;
    }
}
</style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>API KEY</title>
    
    <link href="https://key.gmvmoba.com/assets/css/natacode.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Start menu -->
    <header>
    
    <style>
.switch-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding:12px 14px;
    background:#0b1220;
    border-radius:10px;
    border:1px solid rgba(0,212,255,.3);
    margin-bottom:20px;
    color:#e5e7eb;
    font-weight:600;
}

.switch{
    position:relative;
    width:52px;
    height:28px;
}

.switch input{
    opacity:0;
    width:0;
    height:0;
}

.slider{
    position:absolute;
    inset:0;
    cursor:pointer;
    background:#1e293b;
    border-radius:999px;
    transition:.3s;
}

.slider:before{
    content:"";
    position:absolute;
    height:22px;
    width:22px;
    left:3px;
    top:3px;
    background:white;
    border-radius:50%;
    transition:.3s;
}

.switch input:checked + .slider{
    background:linear-gradient(135deg,#00d4ff,#6c63ff);
}

.switch input:checked + .slider:before{
    transform:translateX(24px);
}
/* ===== Section title ===== */
.section-title {
    font-weight: 700;
    margin: 18px 0 12px;
    color: #198754;
}

/* ===== Expire input row ===== */
.expire-box {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 14px;
}

.expire-box input,
.expire-box select {
    height: 38px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    background: #f8fafc;
    font-size: 14px;
}

.expire-box input {
    width: 80px;
    text-align: center;
}

.expire-box input:focus,
.expire-box select:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,.15);
}

/* ===== Global key list ===== */
.global-key-list {
    margin-top: 10px;
    font-size: 14px;
}

/* Each key row */
.key-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    margin-bottom: 6px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    font-family: Consolas, monospace;
}

/* Expire text */
.key-item span {
    font-weight: 600;
    white-space: nowrap;
}

/* Infinite key */
.key-item.infinite {
    background: linear-gradient(135deg,#e9fbe9,#f6fffa);
    border-color: #b7ebc6;
    color: #198754;
}

/* Limited key */
.key-item.limited {
    background: linear-gradient(135deg,#fff7e6,#fff1b8);
    border-color: #ffe58f;
    color: #ad6800;
}

/* Mobile */
@media (max-width: 600px) {
    .expire-box {
        flex-direction: column;
        align-items: stretch;
    }

    .expire-box input,
    .expire-box select {
        width: 100%;
    }

    .key-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}

</style>

</head>
<body>
<div class="row justify-content-center pt-3">
<!--  
<a class="btn btn-refresh" href="/keys/custom">
            🔄 Home
        </a>
    <hr> -->
<table class="table table-bordered">
    <h2>⚙ Cấu hình hệ thống key</h2>

<?= isset($error) ? "<p style='color:red;font-weight:600'>$error</p>" : '' ?>
<?= isset($msg) ? "<p style='color:green;font-weight:600'>$msg</p>" : '' ?>


<form method="post">

<input type="hidden" name="assign_by_ip" id="assign_by_ip">
<input type="hidden" name="delete_on_copy" id="delete_on_copy">
<h3 class="section-title">🌍 Key dùng chung</h3>

<div class="expire-box">
    <input type="number" name="expire_value" id="duration_value" min="1" placeholder="...">

    <select name="expire_unit" id="duration_unit">
        <option value="">Vô hạn</option>
        <option value="minute">Phút</option>
        <option value="hour">Giờ</option>
        <option value="day">Ngày</option>
        <option value="month">Tháng</option>
        <option value="year">Năm</option>
    </select>
</div>

<div class="global-key-list">
<?php foreach ($data['global_keys'] as $k): ?>
    <?php if (is_string($k)): ?>
        <div class="key-item infinite">
            🔑 <?= htmlspecialchars($k) ?> <span>♾ Vô hạn</span>
        </div>
    <?php else: ?>
        <?php 
            $remainingSeconds = $k['expire_at'] - time(); 
            
            // Tính toán giờ và phút còn lại
            $hours = floor($remainingSeconds / 3600);
            $minutes = floor(($remainingSeconds % 3600) / 60);
            
            // Định dạng chuỗi hiển thị
            $timeString = "";
            if ($remainingSeconds <= 0) {
                $timeString = "❌ Đã hết hạn";
            } else {
                if ($hours > 0) $timeString .= $hours . " giờ ";
                $timeString .= $minutes . " phút";
            }
        ?>
        <div class="key-item limited">
            🔑 <?= htmlspecialchars($k['key']) ?>
            <span>⏳ còn <?= $timeString ?></span>
        </div>
    <?php endif ?>
<?php endforeach ?>
</div>


<textarea name="global_keys" id="global_keys"><?php
foreach ($data['global_keys'] as $k) {
    echo is_string($k) ? $k : $k['key'];
    echo "\n";
}
?></textarea>






<h3>🔐 Key riêng (1 IP / 1 key)</h3>
<div class="switch-row">
    <span>🔒 Gán Key theo IP & xóa sau khi copy</span>

    <label class="switch">
        <input type="checkbox" id="key_mode_toggle"
            <?= !empty($data['settings']['assign_by_ip']) ? 'checked' : '' ?>>
        <span class="slider"></span>
    </label>
</div>

<textarea name="available"><?= implode("\n", $data['available'] ?? []) ?></textarea>

<br><br>
<button>Lưu</button>
</form>

<div class="mb-3">
    <label class="form-label">Link api KEY</label>
    <div class="input-group">
        <input type="text" class="form-control" value="https://key.gmvmoba.com/api/key" readonly>
        <button class="btn-outline-secondary" type="button" onclick="copyInput(this)">Copy</button>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Link api Nhapcode</label>
    <div class="input-group">
        <input type="text" class="form-control" value="https://key.gmvmoba.com/api/nhapcode" readonly>
        <button class="btn-outline-secondary" type="button" onclick="copyInput(this)">Copy</button>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Link api Toplink</label>
    <div class="input-group">
        <input type="text" class="form-control" value="https://key.gmvmoba.com/api/funtop" readonly>
        <button class="btn-outline-secondary" type="button" onclick="copyInput(this)">Copy</button>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', () => {
    const value = document.getElementById('duration_value').value;
    const unit  = document.getElementById('duration_unit').value;

    if (!value || !unit) return;

    const textarea = document.getElementById('global_keys');
    const lines = textarea.value.split('\n').map(l => l.trim()).filter(Boolean);

    textarea.value = lines.map(k => {
        if (k.includes('|')) return k;
        return `${k}|${value} ${unit}`;
    }).join('\n');
});

</script>



<script>
function copyInput(btn) {
    const input = btn.previousElementSibling;
    input.select();
    input.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(input.value)
        .then(() => alert("✅ Copied: " + input.value))
        .catch(() => alert("❌ Copy failed!"));
}

</script>
    </thead>
    <tbody>
        
            </tbody>
</table>
<script>
document.querySelector('form').addEventListener('submit', function (e) {
    const assign = document.querySelector('[name="assign_by_ip"]').checked;
    const del    = document.querySelector('[name="delete_on_copy"]').checked;
    const keys   = document.querySelector('[name="available"]').value.trim();

    if ((assign || del) && keys === '') {
        alert('❌ Key riêng (1 IP / 1 key) không được để trống khi bật chức năng!');
        e.preventDefault();
    }
});
</script>
<script>
const toggle = document.getElementById('key_mode_toggle');
const assign = document.getElementById('assign_by_ip');
const del    = document.getElementById('delete_on_copy');

function syncToggle(){
    const val = toggle.checked ? 1 : 0;
    assign.value = val;
    del.value    = val;
}

// init khi load trang
syncToggle();

// khi click toggle
toggle.addEventListener('change', syncToggle);

// chặn submit nếu bật mà key trống
document.querySelector('form').addEventListener('submit', e => {
    if (toggle.checked) {
        const keys = document.querySelector('[name="available"]').value.trim();
        if (!keys) {
            alert('❌ Không thể bật chế độ IP khi chưa có key riêng!');
            e.preventDefault();
        }
    }
});
</script>

</div>
</div>
</div>
</div>
<script>
function copyLink(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('✅ Đã copy link!');
        });
    } else {
        // fallback cho trình duyệt cũ
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        alert('✅ Đã copy link!');
    }
}
</script>


            <!-- End of content -->
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.0/sweetalert2.all.min.js" integrity="sha512-0UUEaq/z58JSHpPgPv8bvdhHFRswZzxJUT9y+Kld5janc9EWgGEVGfWV1hXvIvAJ8MmsR5d4XV9lsuA90xXqUQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://key.gmvmoba.com/assets/js/natacode.js" type="text/javascript"></script>
    
</body>

</html>
