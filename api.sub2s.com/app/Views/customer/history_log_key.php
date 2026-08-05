<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Mua Key - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
            --text-main: #2d3436;
        }

        body { 
            background: #f0f2f5; 
            color: var(--text-main); 
            font-family: 'Lexend', sans-serif; 
            padding-top: 20px; 
            padding-bottom: 50px;
        }

        /* Balance Header */
        .balance-header {
            background: white;
            padding: 12px 15px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        /* Fix mobile */
        @media (max-width: 576px) {
            .balance-header {
                padding: 10px 12px !important;
                flex-wrap: nowrap !important;
                gap: 6px !important;
            }
            
            .balance-header .d-flex.align-items-center.gap-3 {
                gap: 6px !important;
                min-width: 0;
                flex: 1;
            }
            
            .balance-header .bg-primary.bg-opacity-10.p-2.rounded-circle {
                padding: 5px !important;
            }
            
            .balance-header .bg-primary.bg-opacity-10.p-2.rounded-circle i {
                font-size: 1rem !important;
            }
            
            /* Tên khách hàng - click copy */
            .customer-name {
                font-size: 0.8rem !important;
                max-width: 90px;
                display: inline-block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                cursor: pointer;
                transition: color 0.2s;
            }
            
            .customer-name:hover {
                color: var(--primary-color) !important;
            }
            
            .customer-name:active {
                color: #0a58ca !important;
            }
            
            .customer-name small {
                font-size: 0.6rem !important;
            }
            
            /* Balance */
            .balance-amount {
                font-size: 0.85rem !important;
            }
            
            .balance-amount .small {
                font-size: 0.45rem !important;
            }
            
            /* Buttons */
            .balance-header .btn-sm {
                font-size: 0.6rem !important;
                padding: 3px 6px !important;
                border-radius: 20px !important;
            }
            
            .balance-header .btn-sm i {
                font-size: 0.7rem !important;
            }
            
            .balance-header .btn-sm .d-none {
                display: none !important;
            }
            
            /* Logout button */
            .btn-outline-danger.rounded-circle.logout-btn {
                width: 28px !important;
                height: 28px !important;
                padding: 3px !important;
            }
            
            .btn-outline-danger.rounded-circle.logout-btn i {
                font-size: 0.7rem !important;
            }
            
            /* Card header */
            .card .row .d-flex {
                flex-wrap: wrap !important;
                gap: 8px !important;
            }
            
            .card .row .d-flex .d-flex.align-items-center.gap-3 {
                flex-wrap: wrap !important;
            }
            
            .card .row h4 {
                font-size: 1rem !important;
            }
            
            .card .row p {
                font-size: 0.7rem !important;
            }
            
            /* Table */
            .table thead th {
                font-size: 0.55rem !important;
                padding: 6px 8px !important;
            }
            
            .table tbody td {
                padding: 6px 8px !important;
                font-size: 0.7rem !important;
            }
            
            .input-group-sm {
                min-width: 120px !important;
            }
            
            .input-group-sm .form-control {
                font-size: 0.55rem !important;
                padding: 3px 5px !important;
            }
            
            .input-group-sm .btn {
                padding: 3px 6px !important;
                font-size: 0.55rem !important;
            }
            
            .badge {
                font-size: 0.55rem !important;
                padding: 4px 8px !important;
            }
            
            h4.fw-bold {
                font-size: 1rem !important;
            }
            
            .breadcrumb {
                font-size: 0.75rem !important;
            }
        }

        /* Tablet */
        @media (min-width: 577px) and (max-width: 768px) {
            .balance-header {
                padding: 12px 18px;
            }
            
            .customer-name {
                max-width: 150px;
                cursor: pointer;
            }
            
            .balance-header .btn-sm {
                font-size: 0.65rem !important;
                padding: 4px 8px !important;
            }
            
            .balance-header .btn-sm .d-none {
                display: none !important;
            }
        }

        /* Desktop */
        @media (min-width: 769px) {
            .customer-name {
                cursor: pointer;
                transition: color 0.2s;
            }
            
            .customer-name:hover {
                color: var(--primary-color) !important;
            }
            
            .customer-name:active {
                color: #0a58ca !important;
            }
        }

        /* Custom Card */
        .card-custom { 
            background: white; 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        /* Tabs Styling */
        .nav-pills-custom {
            background: #eee;
            padding: 5px;
            border-radius: 12px;
            display: inline-flex;
            margin-bottom: 20px;
        }
        .nav-pills-custom .nav-link {
            color: #636e72;
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        .nav-pills-custom .nav-link.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Table Styling */
        .table thead th {
            background: #f8f9fa;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 15px 20px;
        }
        .table tbody td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f2f6;
        }

        .key-box {
            background: #f8f9fa;
            border: 1px dashed #ced4da;
            padding: 5px 10px;
            border-radius: 8px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #2d3436;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 180px;
        }

        .badge-success-soft {
            background: #e6fcf5;
            color: #0ca678;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
        }

        .pagination li {
            list-style: none;
        }

        .pagination li a, .pagination li span {
            padding: 8px 16px;
            border-radius: 10px;
            background: #fff;
            color: #636e72;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }

        .pagination li.active span, 
        .pagination li.active a {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        }

        .pagination li a:hover:not(.active) {
            background: #f8f9fa;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .border-dashed { 
            border-style: dashed !important; 
        }
        
        .font-monospace { 
            font-family: 'Courier New', Courier, monospace !important; 
            font-size: 0.95rem; 
        }
        
        .table tbody tr:hover { 
            background-color: rgba(13, 110, 253, 0.02); 
        }
        
        .breadcrumb-item a { 
            color: var(--bs-gray-600); 
        }

        /* Copy tooltip */
        .copy-tooltip {
            position: fixed;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            pointer-events: none;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }
        
        .copy-tooltip.show {
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Balance Header -->
    <div class="balance-header d-flex justify-content-between align-items-center p-3 bg-white shadow-sm rounded-4">
        <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1;">
            <div class="bg-primary bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                <i class="bi bi-person-circle text-primary fs-4"></i>
            </div>
            <div style="min-width: 0;">
                <!-- Tên khách hàng - Click để copy -->
                <div class="customer-name" 
                     onclick="copyUsername('<?= esc(session('customer_name') ?? $customer['username'] ?? 'Khách hàng') ?>')"
                     title="Click để copy tên">
                    <!-- <small class="text-muted d-block" style="font-size: 0.7rem;">Xin chào,</small> -->
                    <b><?= esc(session('customer_name') ?? $customer['username'] ?? 'Khách hàng') ?></b>
                    <i class="bi bi-clipboard ms-1" style="font-size: 0.5rem; opacity: 0.4;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-0 balance-amount">
                    <?= number_format($customer['balance'] ?? 0) ?> 
                    <span class="small opacity-75 text-uppercase">VNĐ</span>
                </h5>
            </div>
        </div>

        <div class="d-flex gap-1 gap-sm-2 align-items-center flex-shrink-0">
            <?php if (session('customer_id') == 1): ?>
                <a href="<?= site_url('admin') ?>" class="btn btn-primary btn-sm rounded-pill px-2 px-sm-3 shadow-sm">
                    <i class="bi bi-key-fill"></i> <span class="d-none d-sm-inline">Admin Key</span>
                </a>
                <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-dark btn-sm rounded-pill px-2 px-sm-3 shadow-sm">
                    <i class="bi bi-speedometer2"></i> <span class="d-none d-sm-inline">Admin</span>
                </a>
                <a href="<?= site_url('history-keys') ?>" class="btn btn-light btn-sm rounded-pill px-2 px-sm-3 border shadow-sm" title="Lịch sử đơn hàng">
                    <i class="bi bi-clock-history text-success"></i> <span class="d-none d-sm-inline">Đơn hàng</span>
                </a>
            <?php else: ?>
                <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-light btn-sm rounded-pill px-2 px-sm-3 border shadow-sm" title="Trang chủ">
                    <i class="bi bi-house-door-fill text-primary"></i> <span class="d-none d-sm-inline">Trang chủ</span>
                </a>
                <a href="<?= site_url('history-keys') ?>" class="btn btn-light btn-sm rounded-pill px-2 px-sm-3 border shadow-sm" title="Lịch sử đơn hàng">
                    <i class="bi bi-clock-history text-success"></i> <span class="d-none d-sm-inline">Đơn hàng</span>
                </a>
            <?php endif; ?>
            <!-- Logout - KHÔNG có onclick confirm -->
            <!-- <a href="<?= site_url('customer/logout') ?>" 
               class="btn btn-outline-danger btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 logout-btn" 
               style="width: 35px; height: 35px;" 
               title="Đăng xuất">
                <i class="bi bi-box-arrow-right"></i>
            </a> -->
        </div>
    </div>

    <h4 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử mua Key</h4>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('customer/dashboard') ?>" class="text-decoration-none">Cửa hàng</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('history-keys') ?>" class="text-decoration-none">Lịch sử mua</a></li>
                <li class="breadcrumb-item active fw-bold text-dark"><?= esc($selected_cat['title']) ?></li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="row g-0 align-items-center">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <h4 class="fw-bold mb-1 text-primary"><?= esc($selected_cat['title']) ?></h4>
                                <!-- <p class="text-muted mb-0 small">Bạn đang xem danh sách các mã Key đã mua cho sản phẩm này.</p> -->
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2 mt-md-0">
                            <?php if (!empty($selected_cat['file_link'])): ?>
                                <a href="<?= esc($selected_cat['file_link']) ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> <span class="d-none d-sm-inline">Tải Game <i class="bi bi-download"></i></span>
                                    <span class="d-inline d-sm-none">Tải Game <i class="bi bi-download"></i></span>
                                </a>
                            <?php endif; ?>

                            <!-- <span class="badge bg-dark align-items-center gap-2 flex-wrap mt-2 mt-md-0">Tổng: <?= count($history) ?></span> -->
                            
                            <!-- <a href="<?= site_url('history-keys') ?>" class="btn btn-light btn-sm rounded-circle border shadow-sm" title="Quay lại">
                                <i class="bi bi-arrow-left"></i>
                            </a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4 py-3 border-0">Mã Key (Kích hoạt)</th>
                            <th class="py-3 border-0">Thời hạn</th>
                            <th class="py-3 border-0">Giá mua</th>
                            <th class="py-3 border-0 text-end pe-4">Ngày giờ mua</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="input-group input-group-sm" style="min-width: 200px;">
                                    <input type="text" class="form-control fw-bold font-monospace bg-light border-dashed" 
                                           value="<?= esc($h['key_code']) ?>" readonly id="key_<?= $h['id'] ?>">
                                    <button class="btn btn-primary" onclick="copyKey('<?= $h['id'] ?>', '<?= esc($h['key_code']) ?>')">
                                        <i class="bi bi-clipboard-check"></i> <span class="d-none d-sm-inline">Copy</span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">
                                    <i class="bi bi-clock-history me-1"></i> <?= esc($h['duration']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger"><?= number_format($h['price']) ?>đ</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="small fw-bold text-dark"><?= date('H:i:s', strtotime($h['sold_at'])) ?></div>
                                <div class="text-muted small"><?= date('d/m/Y', strtotime($h['sold_at'])) ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Bạn chưa mua Key nào trong danh mục này.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            <?= $pager_links ?>
        </div>
    </div>
</div>

<!-- Tooltip copy -->
<div class="copy-tooltip" id="copyTooltip"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logout với SweetAlert2 - KHÔNG confirm double
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Xác nhận đăng xuất',
                text: 'Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đăng xuất',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = this.href;
                }
            });
        });
    }
});

// Copy username khi click
function copyUsername(username) {
    navigator.clipboard.writeText(username).then(function() {
        showTooltip('Đã copy: ' + username);
    }).catch(function() {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = username;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showTooltip('Đã copy: ' + username);
    });
}

// Copy key
function copyKey(id, text) {
    navigator.clipboard.writeText(text).then(() => {
        showTooltip('Đã copy: ' + text);
        // Hoặc dùng SweetAlert2 toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Đã sao chép mã Key'
        });
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showTooltip('Đã copy: ' + text);
    });
}

// Hiển thị tooltip
function showTooltip(message) {
    const tooltip = document.getElementById('copyTooltip');
    tooltip.textContent = message;
    tooltip.classList.add('show');
    
    clearTimeout(tooltip.timeout);
    tooltip.timeout = setTimeout(function() {
        tooltip.classList.remove('show');
    }, 1500);
}
</script>

</body>
</html>