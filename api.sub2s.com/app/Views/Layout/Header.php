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
    <div class="ms-3 fw-bold"><?= BASE_NAME ?></div>
</div>

<nav id="main-sidebar">
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <a class="text-decoration-none fw-bold text-dark fs-5" href="<?= site_url('admin') ?>">
            <i class="bi bi-star-fill text-danger me-2"></i><?= BASE_NAME ?>
        </a>
        <button class="btn d-lg-none p-0" id="sidebarClose"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="nav-custom">
        <?php if (session()->has('userid')) : ?>
            <small class="text-uppercase text-muted fw-bold px-3 mb-2 d-block" style="font-size: 0.7rem;">Menu chính</small>

            <?php if (session('userid') === '4' || session('userid') === '12' ): ?>
                <a class="nav-link" href="<?= site_url('apiurl') ?>"><i class="bi bi-braces"></i> API Quản trị</a>
                <a class="nav-link" href="<?= site_url('admin/link') ?>"><i class="bi bi-link-45deg"></i> Quản lý Links</a>

            <?php else: ?>
                <a class="nav-link" href="<?= site_url('apishorten') ?>"><i class="bi bi-lightning"></i> API Shorten</a>
            <?php endif; ?>

            <hr class="my-3 text-muted">
            <small class="text-uppercase text-muted fw-bold px-3 mb-2 d-block" style="font-size: 0.7rem;">Tài khoản</small>
            
            <!-- <a class="nav-link" href="<?= site_url('settings') ?>"><i class="bi bi-gear"></i> Cài đặt</a> -->

            <?php if (isset($user) && $user->level == 1) : ?>
                <div class="bg-light rounded-3 p-2 my-2">
                <a class="nav-link" href="<?= site_url('admin/dashboard') ?>"><i class="bi bi-speedometer2"></i>Dashboard Shop</a>
                </div>
            <?php endif; ?>

            <a class="nav-link text-danger mt-3" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-left"></i> Đăng xuất</a>
            
            <div class="mt-4 p-3 bg-light rounded-4 text-center">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
                <div class="small fw-bold mt-1"><?= getName($user) ?></div>
            </div>
        <?php endif; ?>
    </div>
</nav>

<div id="sidebar-overlay"></div>
<script>
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
</script>