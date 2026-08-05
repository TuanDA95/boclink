<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Khách Hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://fastly.jsdelivr.net/npm/vanilla-lazyload@17.3.2/dist/lazyload.min.js"></script>
    <link href="https://fastly.jsdelivr.net/gh/ngocminhvn/all/snow.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
            --text-main: #2d3436;
            --success-color: #198754;
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
            
            /* Game detail */
            .card-custom {
                padding: 12px !important;
            }
            
            .card-custom .row.g-5 {
                gap: 1rem !important;
            }
            
            .card-custom h2 {
                font-size: 1.2rem !important;
            }
            
            .game-img {
                max-height: 200px !important;
                object-fit: cover;
            }
            
            .duration-label {
                padding: 10px !important;
                font-size: 0.75rem !important;
            }
            
            .duration-label .small {
                font-size: 0.6rem !important;
            }
            
            .duration-label div {
                font-size: 0.8rem !important;
            }
            
            .input-group {
                max-width: 120px !important;
            }
            
            .input-group .btn {
                padding: 4px 8px !important;
                font-size: 0.7rem !important;
            }
            
            .input-group input {
                font-size: 0.7rem !important;
                padding: 4px !important;
            }
            
            .btn-pay {
                font-size: 0.85rem !important;
                padding: 10px !important;
            }
            
            .bg-primary.bg-opacity-10.rounded-4 {
                padding: 10px !important;
            }
            
            .bg-primary.bg-opacity-10.rounded-4 h3 {
                font-size: 1.1rem !important;
            }
            
            .bg-primary.bg-opacity-10.rounded-4 span {
                font-size: 0.8rem !important;
            }
            
            .mt-4.p-3.bg-light.rounded-4 {
                padding: 10px !important;
            }
            
            .mt-4.p-3.bg-light.rounded-4 h6 {
                font-size: 0.75rem !important;
            }
            
            .mt-4.p-3.bg-light.rounded-4 small {
                font-size: 0.65rem !important;
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
            
            .game-img {
                max-height: 300px !important;
                object-fit: cover;
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

        /* Card Styling */
        .card-custom { 
            background: white; 
            border: none; 
            border-radius: 16px; 
            margin-bottom: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .card-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f2f6;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header-custom h6 {
            margin: 0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        /* Input Styling */
        .form-control, .form-select { 
            background: #f8f9fa !important; 
            border: 1px solid #e9ecef !important; 
            color: #2d3436 !important; 
            padding: 10px 15px;
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            background: #fff !important;
        }

        /* Quick Buy */
        .btn-quick-buy { 
            background: #ffc107; 
            color: #000; 
            font-weight: 700; 
            border: none;
            padding: 0 25px;
            border-radius: 0 10px 10px 0;
        }

        .btn-quick-buy:hover { background: #ffca2c; }

        /* Tables */
        .table thead th {
            background: #f8f9fa;
            color: #636e72;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 12px 20px;
            border: none;
        }
        .table tbody td {
            padding: 12px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f2f6;
        }

        /* Status Badges */
        .badge-success-soft { background: rgba(25, 135, 84, 0.1); color: #198754; border: none; }
        .badge-warning-soft { background: rgba(255, 193, 7, 0.1); color: #997404; border: none; }
        
        .qr-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            border: 1px dashed #dee2e6;
        }

        #depositTab {
            border-bottom: none;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 12px;
            display: flex;
            gap: 5px;
        }

        #depositTab .nav-item {
            flex: 1;
        }

        #depositTab .nav-link {
            border: 2px solid #8bacf3;
            color: #6c757d;
            font-weight: 600;
            text-align: center;
            padding: 12px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: transparent;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
        }

        #depositTab .nav-link.active {
            background: #ffffff;
            color: #0d6efd;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        #depositTab .nav-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.5);
            color: #333;
        }

        #depositTab .nav-link i {
            font-size: 18px;
        }

        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            border-radius: 0.25rem;
            gap: 5px;
        }

        .pagination li a {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            color: #0d6efd;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            transition: all 0.2s;
        }

        .pagination li.active a {
            z-index: 3;
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .pagination li a:hover {
            background-color: #e9ecef;
        }

        .img-qr {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 1px solid #eee;
            padding: 5px;
            background: #fff;
            border-radius: 8px;
        }

        .qr-code-side {
            min-width: 180px;
        }

        @media (min-width: 768px) {
            .qr-code-side {
                border-right: 1px dashed #ddd;
                margin-right: 20px;
            }
        }

        .game-img { 
            width: 100%; 
            border-radius: 20px; 
            transition: transform 0.3s ease;
        }
        .game-img:hover { transform: scale(1.02); }
        
        .duration-card {
            cursor: pointer;
            position: relative;
        }
        
        .btn-check:checked + .duration-label {
            background-color: #eef6ff !important;
            border-color: var(--primary-color) !important;
            color: var(--primary-color) !important;
        }
        
        .duration-label {
            border: 2px solid #f1f2f6;
            transition: all 0.2s;
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            display: block;
        }

        .btn-pay {
            background: linear-gradient(45deg, #0d6efd, #0052cc);
            border: none;
            transition: all 0.3s;
        }
        .btn-pay:disabled { opacity: 0.7; cursor: not-allowed; }

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

    <div class="mb-4">
        <a href="<?= base_url('/customer/dashboard') ?>" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> Quay lại cửa hàng
        </a>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card-custom p-3 p-md-5">
                    <div class="row g-4 g-md-5">
                        <div class="col-md-5">
                            <img src="<?= base_url('public/uploads/'.$game->image) ?>" class="game-img shadow">
                        </div>
                        
                        <div class="col-md-7">
                            <h2 class="fw-bold text-dark mb-1"><?= esc($game->title) ?></h2>
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <span class="badge bg-primary bg-opacity-10 text-primary">Sẵn hàng</span>
                            </div>

                            <form id="purchaseForm" action="<?= base_url('store/buy') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="category_id" value="<?= $game->id ?>">

                                <label class="form-label fw-bold mb-3">1. Chọn gói thời gian:</label>
                                <div class="row g-3 mb-4">
                                <?php 
                                function getDurationWeight($str) {
                                    $val = (int)$str; 
                                    $s = mb_strtolower($str, 'UTF-8');

                                    if (strpos($s, 'năm') !== false)   return $val * 24 * 365;
                                    if (strpos($s, 'tháng') !== false) return $val * 24 * 30;
                                    if (strpos($s, 'tuần') !== false)  return $val * 24 * 7;
                                    if (strpos($s, 'ngày') !== false) {
                                        if ($val == 7) return 7 * 24;
                                        return $val * 24;
                                    }
                                    if (strpos($s, 'giờ') !== false)   return $val;
                                    
                                    return $val; 
                                }

                                usort($durations, function($a, $b) {
                                    return getDurationWeight($a['duration']) <=> getDurationWeight($b['duration']);
                                });

                                foreach($durations as $index => $opt): 
                                    $current_label = $opt['duration'];
                                    
                                    $display_text = $current_label;
                                    if (strpos($current_label, '7') !== false && strpos(mb_strtolower($current_label, 'UTF-8'), 'ngày') !== false) {
                                        $display_text = '1 Tuần';
                                    }
                                ?>
                                <div class="col-6 col-sm-4">
                                    <input type="radio" class="btn-check" name="duration" id="opt<?= $index ?>" 
                                        value="<?= $opt['duration'] ?>" data-price="<?= $opt['price'] ?>" 
                                        <?= $index == 0 ? 'checked' : '' ?> onchange="updatePrice()">
                                    
                                    <label class="duration-label fw-bold h-100" for="opt<?= $index ?>">
                                        <div class="small opacity-75 mb-1"><?= $display_text ?></div>
                                        <div><?= number_format($opt['price']) ?>đ</div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                                <label class="form-label fw-bold mb-2">2. Số lượng mã:</label>
                                <div class="mb-4">
                                    <div class="input-group" style="max-width: 160px;">
                                        <button class="btn btn-outline-secondary border-2" type="button" onclick="changeQty(-1)"><i class="bi bi-dash-lg"></i></button>
                                        <input type="number" name="qty" id="buy_qty" class="form-control text-center fw-bold border-2" value="1" min="1" readonly>
                                        <button class="btn btn-outline-secondary border-2" type="button" onclick="changeQty(1)"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                </div>

                                <div class="bg-primary bg-opacity-10 rounded-4 mb-4 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">Tổng tiền cần trả:</span>
                                        <h3 class="fw-bold text-primary mb-0" id="displayPrice">0đ</h3>
                                    </div>
                                </div>

                                <button type="button" onclick="confirmPurchase()" id="btnSubmit" class="btn btn-pay w-100 py-3 rounded-pill text-white fw-bold fs-5 shadow">
                                    <span id="btnText"><i class="bi bi-shield-check me-2"></i>THANH TOÁN NGAY</span>
                                    <span id="btnLoading" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </form>
                             <div class="mt-4 p-3 bg-light rounded-4">
                                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Mô tả sản phẩm</h6>
                                <small class="text-muted">Nhận key ngay sau khi thanh toán. Link tải File/Game sẽ có sau khi mua key.<br>Tham gia nhóm Discord để đọc hướng dẫn cách chơi, cũng như các thông tin cập nhật mới nhất</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (session('customer_id') != 1): ?>
<a href="https://m.me/192498543927268" id="linktelegram" target="_blank" rel="noopener noreferrer">
    <div id="fcta-telegram-tracking" class="fcta-telegram-mess">
        <span id="fcta-telegram-tracking">Liên hệ Admin</span>
    </div>
    <div class="fcta-telegram-vi-tri-nut">
        <div id="fcta-telegram-tracking" class="fcta-telegram-nen-nut">
            <div id="fcta-telegram-tracking" class="fcta-telegram-ben-trong-nut">
                <i class="fab fa-facebook-messenger fa-2x"></i>
            </div>
            <div id="fcta-telegram-tracking" class="fcta-telegram-text"> Chat Ngay </div>
        </div>
    </div>
</a>
<style>
@keyframes zoom{
    0%{
        transform:scale(.5);
        opacity:0
    }
    50%{
        opacity:1
    }
    to{
        opacity:0;
        transform:scale(1)
    }
}
@keyframes lucidgentelegram{
    0% to{
        transform:rotate(-25deg)
    }
    50%{
        transform:rotate(25deg)
    }
}
.jscroll-to-top{
    bottom:100px
}
.fcta-telegram-ben-trong-nut svg path{
    fill:#fff
}
.fcta-telegram-vi-tri-nut{
    position:fixed;
    bottom:24px;
    right:20px;
    z-index:999
}
.fcta-telegram-nen-nut,div.fcta-telegram-mess{
    box-shadow:0 1px 6px rgba(0,0,0,.06),0 2px 32px rgba(0,0,0,.16)
}
.fcta-telegram-nen-nut{
    width:50px;
    height:50px;
    text-align:center;
    color:#fff;
    background:#3a9140;
    border-radius:50%;
    position:relative
}
.fcta-telegram-nen-nut::after,.fcta-telegram-nen-nut::before{
    content:"";
    position:absolute;
    border:1px solid #3a9140;
    background:#3a914080;
    z-index:-1;
    left:-20px;
    right:-20px;
    top:-20px;
    bottom:-20px;
    border-radius:50%;
    animation:zoom 1.9s linear infinite
}
.fcta-telegram-nen-nut::after{
    animation-delay:.4s
}
.fcta-telegram-ben-trong-nut,.fcta-telegram-ben-trong-nut i{
    transition:all 1s
}
.fcta-telegram-ben-trong-nut{
    position:absolute;
    text-align:center;
    width:30%;
    height:46%;
    left:10px;
    bottom:25px;
    line-height:50px;
    font-size:20px;
    opacity:1
}
.fcta-telegram-ben-trong-nut i{
    animation:lucidgentelegram 1s linear infinite
}
.fcta-telegram-nen-nut:hover .fcta-telegram-ben-trong-nut,.fcta-telegram-text{
    opacity:0
}
.fcta-telegram-nen-nut:hover i{
    transform:scale(.5);
    transition:all .5s ease-in
}
.fcta-telegram-text a{
    text-decoration:none;
    color:#fff
}
.fcta-telegram-text{
    position:absolute;
    top:6px;
    text-transform:uppercase;
    font-size:12px;
    font-weight:700;
    transform:scaleX(-1);
    transition:all .5s;
    line-height:1.5
}
.fcta-telegram-nen-nut:hover .fcta-telegram-text{
    transform:scaleX(1);
    opacity:1
}
div.fcta-telegram-mess{
    position:fixed;
    bottom:29px;
    right:58px;
    z-index:99;
    background:#fff;
    padding:7px 25px 7px 15px;
    color:#3a9140;
    border-radius:50px 0 0 50px;
    font-weight:700;
    font-size:15px
}
.fcta-telegram-mess span{
    color:#3a9140!important
}
span#fcta-telegram-tracking{
    font-family:Roboto;
    line-height:1.5
}
.fcta-telegram-text{
    font-family:Roboto
}
</style>
<?php endif; ?>

<!-- Tooltip copy -->
<div class="copy-tooltip" id="copyTooltip"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://shop.binhbun.com/live_chat.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js"></script>

<div class="snowflake"><img height="17px" src="https://i.imgur.com/PNmfqix.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/8kTLV1P.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/PNmfqix.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/8kTLV1P.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/PNmfqix.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/8kTLV1P.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/PNmfqix.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/8kTLV1P.png?rel=uploadfree.pw" alt="hoa" /></div>
<div class="snowflake"><img height="17px" src="https://i.imgur.com/PNmfqix.png?rel=uploadfree.pw" alt="hoa" /></div>

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
        const textarea = document.createElement('textarea');
        textarea.value = username;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showTooltip('Đã copy: ' + username);
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

// Ngăn chặn Double Click gửi form 2 lần
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnSubmit');
    const text = document.getElementById('btnText');
    const loading = document.getElementById('btnLoading');
    
    btn.disabled = true;
    text.classList.add('d-none');
    loading.classList.remove('d-none');
});

function changeQty(val) {
    let input = document.getElementById('buy_qty');
    let current = parseInt(input.value);
    if (current + val >= 1 && current + val <= 50) {
        input.value = current + val;
        updatePrice();
    }
}

function updatePrice() {
    const selected = document.querySelector('input[name="duration"]:checked');
    const qty = parseInt(document.getElementById('buy_qty').value) || 1;
    const priceEach = selected ? selected.getAttribute('data-price') : 0;
    const total = priceEach * qty;
    document.getElementById('displayPrice').innerText = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
}

window.onload = updatePrice;

function confirmPurchase() {
    const selected = document.querySelector('input[name="duration"]:checked');
    const qty = document.getElementById('buy_qty').value;
    const gameTitle = "<?= esc($game->title) ?>";
    
    if (!selected) {
        Swal.fire('Lỗi', 'Vui lòng chọn gói thời gian!', 'error');
        return;
    }

    const durationText = selected.nextElementSibling.innerText.trim();
    const priceEach = selected.getAttribute('data-price');
    const totalFormat = new Intl.NumberFormat('vi-VN').format(priceEach * qty) + 'đ';

    Swal.fire({
        title: 'Xác nhận thanh toán?',
        html: `
            <div class="text-start p-3 bg-light rounded-3">
                <p class="mb-2"><b>Sản phẩm:</b> ${gameTitle}</p>
                <p class="mb-2"><b>Gói:</b> ${durationText}</p>
                <p class="mb-2"><b>Số lượng:</b> ${qty} Key</p>
                <hr>
                <p class="mb-0 text-danger fs-5 fw-bold text-center">Tổng: ${totalFormat}</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Đồng ý, mua ngay!',
        cancelButtonText: 'Hủy bỏ',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = document.getElementById('btnSubmit');
            const text = document.getElementById('btnText');
            const loading = document.getElementById('btnLoading');
            
            btn.disabled = true;
            text.classList.add('d-none');
            loading.classList.remove('d-none');

            document.getElementById('purchaseForm').submit();
        }
    });
}
</script>
</body>
</html>