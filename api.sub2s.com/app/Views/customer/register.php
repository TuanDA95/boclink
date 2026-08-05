<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1ad5fa;
            /* Safe area cho iPhone X trở lên */
            --safe-top: env(safe-area-inset-top);
            --safe-bottom: env(safe-area-inset-bottom);
        }

        body { 
            margin: 0; padding: 0; 
            font-family: 'Lexend', sans-serif; 
            height: 100dvh; /* Sử dụng dvh để tính toán chính xác chiều cao mobile */
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: #000;
            overflow: hidden;
            position: relative;
        }

        /* Nền tối ưu cho iPhone */
        .bg-container {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1;
            background: url('https://bbmkts.com/uploads/img_698c136c1de743_20541444.png') center/cover no-repeat;
            filter: brightness(0.4);
        }

        /* Hiệu ứng lớp phủ động thay vì cuộn ảnh nặng */
        .bg-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, transparent 0%, black 90%);
            z-index: 2;
        }

        /* Card đăng nhập tối ưu iPhone X */
        .auth-card { 
            position: relative; 
            z-index: 10; 
            background: rgba(255, 255, 255, 0.96); 
            border: 1.5px solid var(--primary-color); 
            border-radius: 28px; 
            padding: 30px 25px; 
            width: 88%; 
            max-width: 360px; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            /* Tránh bị dính vào tai thỏ/đáy màn hình */
            margin-top: var(--safe-top);
            margin-bottom: var(--safe-bottom);
        }

        .auth-card h3 {
            font-size: 1.5rem;
            letter-spacing: 2px;
            margin-bottom: 25px !important;
        }

        /* Fix lỗi Input trên iOS */
        .form-control { 
            background: #fff; 
            border: 1.5px solid #eee; 
            padding: 12px 15px;
            font-size: 16px !important; /* Bắt buộc 16px để không bị zoom tự động */
            border-radius: 14px;
            -webkit-appearance: none; /* Bỏ style mặc định Safari */
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(26, 213, 250, 0.1);
            background: #fff;
        }

        .btn-gaming { 
            background: linear-gradient(135deg, #1ad5fa, #007bff); 
            border: none; 
            font-weight: 700; 
            padding: 14px; 
            width: 100%; 
            color: white; 
            border-radius: 14px; 
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .btn-gaming:active {
            transform: scale(0.96);
            opacity: 0.9;
        }

        .small-link {
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
        }

        /* Fix lỗi bàn phím che mất form trên mobile */
        @media screen and (max-height: 500px) {
            .auth-card { padding: 15px; }
            .auth-card h3 { margin-bottom: 10px !important; }
            .mb-3, .mb-4 { margin-bottom: 8px !important; }
        }
    </style>
</head>
<body>
<div class="bg-container">
        <div class="scrolling-bg"></div>
    </div>
    <div class="auth-card">
        <h3 class="text-center fw-bold mb-4 text-warning">ĐĂNG KÝ MỚI</h3>
       <?php if(session()->getFlashdata('msgSuccess')): ?>
    <div class="alert alert-success small"><?= session()->getFlashdata('msgSuccess') ?></div>
<?php endif; ?>

<?php if(session()->getFlashdata('msgDanger')): ?>
    <div class="alert alert-danger small"><?= session()->getFlashdata('msgDanger') ?></div>
<?php endif; ?>
        <form action="/customer/auth" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="register">
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark ms-1">Tên tài khoản</label>
                <input type="text" name="username"  placeholder="Nhập username"  class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-dark ms-1">Mật khẩu</label>
                <input type="password" name="password" placeholder="••••••••" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-gaming mb-3">TẠO TÀI KHOẢN</button>
            <div class="text-center">
                <a href="/customer/login" class="text-decoration-none text-secondary small">Đã có tài khoản? <span style="color: red; font-weight: 700;">Đăng nhập</span></a>
            </div>
        </form>
    </div>
</body>
</html>