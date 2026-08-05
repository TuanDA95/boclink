<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<style>
    .login-container {
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-card {
        background: #ffffff;
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        overflow: hidden;
        width: 100%;
        max-width: 400px;
        transition: transform 0.3s ease;
    }

    .login-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }

    .login-header i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #3b82f6;
    }

    .login-body {
        padding: 35px;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .form-control-custom {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .form-control-custom:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .btn-login {
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
        margin-top: 10px;
    }

    .btn-login:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .footer-text {
        text-align: center;
        margin-top: 25px;
        font-size: 0.85rem;
    }

    .footer-text a {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 600;
    }

    .footer-text a:hover {
        text-decoration: underline;
    }

    .admin-contact {
        background: #fef2f2;
        color: #991b1b;
        padding: 10px;
        border-radius: 10px;
        display: block;
        margin-bottom: 15px;
        text-decoration: none;
        transition: 0.2s;
    }

    .admin-contact:hover {
        background: #fee2e2;
    }
</style>

<div class="login-container">
    <div class="login-card shadow-lg">
        <div class="login-header">
            <!-- <i class="bi bi-shield-lock-fill"></i> -->
            <h3 class="fw-bold mb-1">Đăng nhập</h3>
            <!-- <p class="mb-0 opacity-75 small">Chào mừng bạn trở lại hệ thống</p> -->
        </div>

        <div class="login-body">
            
            <?= form_open() ?>
                <div class="mb-3">
                    <label class="form-label">Tên tài khoản</label>
                    <input type="text" name="username" class="form-control form-control-custom" placeholder="Nhập username..." required minlength="4">
                    <?php if ($validation->hasError('username')) : ?>
                        <small class="text-danger small mt-1 d-block"><?= $validation->getError('username') ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control form-control-custom" placeholder="Nhập mật khẩu..." required minlength="6">
                    <?php if ($validation->hasError('password')) : ?>
                        <small class="text-danger small mt-1 d-block"><?= $validation->getError('password') ?></small>
                    <?php endif; ?>
                </div>

                <input type="hidden" name="ip" value="<?= esc($_SERVER['HTTP_USER_AGENT']) ?>">

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" name="stay_log" id="stay_log" value="yes">
                    <label class="form-check-label small text-muted" for="stay_log" data-bs-toggle="tooltip" title="Duy trì phiên đăng nhập lâu hơn">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i> ĐĂNG NHẬP
                </button>
            <?= form_close() ?>

            <div class="footer-text">
                
                <p class="text-muted">
                    Chưa có tài khoản? <a href="<?= site_url('login') ?>">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Khởi tạo tooltip của Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?= $this->endSection() ?>