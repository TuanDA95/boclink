<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel' ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { 
            --sidebar-width: 260px; 
            --dark-blue: #1e293b;
            --active-bg: #334155;
            --accent-color: #3b82f6;
        }

        body { 
            background-color: #f4f7f6; 
            overflow-x: hidden; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--dark-blue);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
        }
        
        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
        }

        #sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 991.98px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #sidebar.active { margin-left: 0; box-shadow: 10px 0 25px rgba(0,0,0,0.3); }
            #content { margin-left: 0 !important; width: 100% !important; }
            #sidebar.active + #sidebar-overlay { display: block; }
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-link { 
            color: #94a3b8; 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 4px 15px; 
            display: flex;
            align-items: center;
            transition: 0.2s;
            text-decoration: none;
        }
        
        .nav-link:hover { background: var(--active-bg); color: #fff; }
        .nav-link.active { 
            background: var(--active-bg); 
            color: #fff; 
            border-left: 4px solid var(--accent-color);
        }

        .navbar { border-bottom: 1px solid #eee; }
        .stat-card { border: none; border-radius: 15px; transition: 0.3s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .stat-card:hover { transform: translateY(-5px); }
        #sidebar::-webkit-scrollbar { width: 5px; }
        #sidebar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <div class="sidebar-header mb-3">
            <h4 class="fw-bold text-primary mb-0">ADMIN <span class="text-white">PRO</span></h4>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'admin/dashboard') ? 'active' : '' ?>" href="/admin/dashboard">
                    <i class="bi bi-grid-fill me-2"></i> Thống kê
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'admin/customers') ? 'active' : '' ?>" href="/admin/customers">
                    <i class="bi bi-people-fill me-2"></i> Quản lý Users
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'admin/history/banks') ? 'active' : '' ?>" href="/admin/history/banks">
                    <i class="bi bi-bank2 me-2"></i> Lịch sử Bank/USDT
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (uri_string() == 'admin/history/cards') ? 'active' : '' ?>" href="/admin/history/cards">
                    <i class="bi bi-card-list me-2"></i> Lịch sử Thẻ
                </a>
            </li>

            <li class="nav-item mt-4 border-top border-secondary pt-3">
                <a class="nav-link" href="/customer/dashboard">
                    <i class="bi bi-house-door me-2"></i> Home
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link text-danger fw-bold" href="<?= site_url('admin') ?>">
                    <i class="bi bi-house-door me-2"></i> Admin API
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger fw-bold" href="/customer/logout">
                    <i class="bi bi-power me-2"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </nav>

    <div id="sidebar-overlay"></div>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top p-3">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-light border me-3">
                    <i class="bi bi-list"></i>
                </button>
                <span class="fw-bold text-uppercase"><?= $title ?? 'Hệ thống Quản trị' ?></span>
                
                <div class="ms-auto d-flex align-items-center">
                    <span class="badge bg-light text-dark border me-2">Cấp bậc: Admin</span>
                    <i class="bi bi-person-circle fs-4 text-secondary"></i>
                </div>
            </div>
        </nav>

        <div class="container-fluid ">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card bg-primary text-white p-3">
                        <small class="opacity-75">Doanh thu Tổng</small>
                        <h4 class="fw-bold mb-0"><?= number_format($total_rev ?? 0) ?>đ</h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card bg-success text-white p-3">
                        <small class="opacity-75">Tháng này</small>
                        <h4 class="fw-bold mb-0"><?= number_format($month_rev ?? 0) ?>đ</h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card bg-info text-white p-3">
                        <small class="opacity-75">Hôm nay</small>
                        <h4 class="fw-bold mb-0"><?= number_format($today_rev ?? 0) ?>đ</h4>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card bg-dark text-white p-3">
                        <small class="opacity-75">Tổng User</small>
                        <h4 class="fw-bold mb-0"><?= $total_customers ?? 0 ?></h4>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body ">
                    <?php 
                        if (uri_string() == 'admin/dashboard') {
                            echo $this->include('customer/history_links');
                        } else {
                            echo $this->renderSection('admin_content');
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function (e) {
            e.stopPropagation();
            $('#sidebar').toggleClass('active');
        });

        $('#sidebar-overlay').on('click', function () {
            $('#sidebar').removeClass('active');
        });

        if ($(window).width() < 992) {
            $('.nav-link').on('click', function () {
                $('#sidebar').removeClass('active');
            });
        }
    });
</script>

</body>
</html>