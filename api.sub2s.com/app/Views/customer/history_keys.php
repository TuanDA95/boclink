<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Mua Key - Dashboard</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        /* Fix mobile - kích thước nhỏ hơn */
        @media (max-width: 576px) {
            .balance-header {
                padding: 10px 12px;
                flex-wrap: nowrap !important;
                gap: 8px;
            }
            
            .balance-header .d-flex.align-items-center.gap-3 {
                gap: 8px !important;
                min-width: 0;
                flex: 1;
            }
            
            .balance-header .bg-primary.bg-opacity-10.p-2.rounded-circle {
                padding: 6px !important;
            }
            
            .balance-header .bg-primary.bg-opacity-10.p-2.rounded-circle i {
                font-size: 1.1rem !important;
            }
            
            /* Tên khách hàng - có thể click copy */
            .customer-name {
                font-size: 0.85rem !important;
                max-width: 100px;
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
            
            /* Balance */
            .balance-amount {
                font-size: 0.9rem !important;
            }
            
            .balance-amount .small {
                font-size: 0.5rem !important;
            }
            
            /* Buttons */
            .balance-header .btn-sm {
                font-size: 0.65rem !important;
                padding: 4px 8px !important;
                border-radius: 20px !important;
            }
            
            .balance-header .btn-sm i {
                font-size: 0.8rem !important;
            }
            
            .balance-header .btn-sm .d-none {
                display: none !important;
            }
            
            /* Logout button */
            .btn-outline-danger.rounded-circle {
                width: 30px !important;
                height: 30px !important;
                padding: 4px !important;
            }
            
            .btn-outline-danger.rounded-circle i {
                font-size: 0.8rem !important;
            }
            
            /* Game cards */
            .col-6 {
                padding: 6px !important;
            }
            
            .card-game-history .card-body {
                padding: 10px !important;
            }
            
            .card-game-history h6 {
                font-size: 0.8rem !important;
            }
            
            .card-game-history .badge {
                font-size: 0.6rem !important;
                padding: 4px 8px !important;
            }
            
            .card-game-history .btn-sm {
                font-size: 0.6rem !important;
                padding: 4px 8px !important;
            }
            
            /* Table */
            .table thead th {
                font-size: 0.6rem !important;
                padding: 8px 10px !important;
            }
            
            .table tbody td {
                padding: 8px 10px !important;
                font-size: 0.75rem !important;
            }
            
            .input-group-sm {
                min-width: 140px !important;
            }
            
            .input-group-sm .form-control {
                font-size: 0.6rem !important;
                padding: 4px 6px !important;
            }
            
            .input-group-sm .btn {
                padding: 4px 8px !important;
                font-size: 0.6rem !important;
            }
            
            .badge {
                font-size: 0.6rem !important;
            }
            
            h4.fw-bold {
                font-size: 1.1rem !important;
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
                font-size: 0.7rem !important;
                padding: 4px 10px !important;
            }
            
            .balance-header .btn-sm .d-none {
                display: none !important;
            }
        }

        /* Desktop - tên luôn hiển thị đầy đủ */
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

        .card-game-history {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-game-history:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .border-dashed {
            border-style: dashed !important;
            background: #f8f9fa !important;
        }
        
        /* Tooltip copy */
        .copy-tooltip {
            position: fixed;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            pointer-events: none;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .copy-tooltip.show {
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="balance-header d-flex justify-content-between align-items-center p-3 bg-white shadow-sm rounded-4">
        <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1;">
            <div class="bg-primary bg-opacity-10 p-2 rounded-circle flex-shrink-0">
                <i class="bi bi-person-circle text-primary fs-4"></i>
            </div>
            <div style="min-width: 0;">
                <!-- Tên khách hàng - Click để copy -->
                <div class="customer-name" 
                     onclick="copyUsername('<?= esc($customer['username'] ?? 'Khách hàng') ?>')"
                     title="Click để copy tên">
                    <b><?= esc($customer['username'] ?? 'Khách hàng') ?></b>
                    <i class="bi bi-clipboard ms-1" style="font-size: 0.6rem; opacity: 0.5;"></i>
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
            <!-- <a href="<?= site_url('customer/logout') ?>" 
   class="btn btn-outline-danger btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 logout-btn" 
   style="width: 35px; height: 35px;" 
   title="Đăng xuất">
    <i class="bi bi-box-arrow-right"></i>
</a> -->
        </div>
    </div>

    <h4 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử mua Key</h4>

    <?php if (!$selected_cat): ?>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-game-history position-relative">
                        <a href="<?= base_url('historyKeys?cat_id=' . $cat['id']) ?>" class="text-decoration-none">
                            <div class="card-body text-center">
                                <h6 class="fw-bold text-dark mb-1 text-truncate"><?= esc($cat['title']) ?></h6>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                                    <?= $cat['total_bought'] ?> Key đã mua
                                </span>
                            </div>
                        </a>
                        <div class="px-3 pb-3">
                            <a href="<?= base_url('historyKeys?cat_id=' . $cat['id']) ?>" class="btn btn-outline-success btn-sm w-100 rounded-pill" style="font-size: 0.85rem; font-weight: 700">
                                <i class="bi bi-key"></i> Xem key
                            </a>
                        </div>
                        <?php if (!empty($cat['file_link'])): ?>
                            <div class="px-3 pb-3">
                                <a href="<?= esc($cat['file_link']) ?>" target="_blank" class="btn btn-outline-success btn-sm w-100 rounded-pill" style="font-size: 0.85rem; font-weight: 700">
                                    <i class="bi bi-download"></i> Tải Game
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">Bạn chưa mua sản phẩm nào.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card-custom shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Mã Key</th>
                            <th>Thời hạn</th>
                            <th>Giá tiền</th>
                            <th class="text-end pe-4">Thời gian mua</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="input-group input-group-sm" style="min-width: 200px;">
                                        <input type="text" class="form-control fw-bold border-dashed" value="<?= esc($h['key_code']) ?>" readonly id="key<?= $h['id'] ?>">
                                        <button class="btn btn-dark" onclick="copyKey('<?= $h['id'] ?>', '<?= esc($h['key_code']) ?>')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td><span class="badge bg-info bg-opacity-10 text-info border-info"><?= esc($h['duration']) ?></span></td>
                                <td class="fw-bold text-danger">-<?= number_format($h['price']) ?>đ</td>
                                <td class="text-end pe-4 small text-muted"><?= date('H:i d/m/Y', strtotime($h['sold_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            <?= $pager_links ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tooltip copy -->
<div class="copy-tooltip" id="copyTooltip"></div>

<script>
// Copy username khi click
function copyUsername(username) {
    // Copy vào clipboard
    navigator.clipboard.writeText(username).then(function() {
        showTooltip('Đã copy: ' + username);
    }).catch(function() {
        // Fallback cho trình duyệt cũ
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
function copyKey(id, key) {
    const input = document.getElementById('key' + id);
    if (input) {
        input.select();
        navigator.clipboard.writeText(key).then(function() {
            showTooltip('Đã copy key: ' + key);
        }).catch(function() {
            document.execCommand('copy');
            showTooltip('Đã copy key: ' + key);
        });
    } else {
        // Fallback
        navigator.clipboard.writeText(key).then(function() {
            showTooltip('Đã copy key: ' + key);
        }).catch(function() {
            const textarea = document.createElement('textarea');
            textarea.value = key;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showTooltip('Đã copy key: ' + key);
        });
    }
}

// Hiển thị tooltip
function showTooltip(message) {
    const tooltip = document.getElementById('copyTooltip');
    tooltip.textContent = message;
    tooltip.classList.add('show');
    
    // Đặt vị trí ở giữa màn hình
    tooltip.style.left = '50%';
    tooltip.style.top = '50%';
    tooltip.style.transform = 'translate(-50%, -50%)';
    
    // Tự động ẩn sau 1.5 giây
    clearTimeout(tooltip.timeout);
    tooltip.timeout = setTimeout(function() {
        tooltip.classList.remove('show');
    }, 1500);
}

// SweetAlert2 confirm logout
document.querySelector('a[href="<?= site_url('customer/logout') ?>"]')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: 'Bạn sẽ đăng xuất khỏi hệ thống!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Đăng xuất',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = this.href;
        }
    });
});
</script>

</body>
</html>