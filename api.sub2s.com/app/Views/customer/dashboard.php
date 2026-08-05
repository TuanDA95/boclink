
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Website</title>
    <meta name="robots" content="noindex, nofollow">
      <meta name="description" content="...">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        /* Top Bar Balance */
        .balance-header {
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
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
        /* Container của Tab */
#depositTab {
    border-bottom: none;
    background: #f8f9fa;
    padding: 5px;
    border-radius: 12px;
    display: flex;
    gap: 5px;
}

/* Kiểu dáng chung cho các nút tab */
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

/* Hiệu ứng khi Tab được kích hoạt (Active) */
#depositTab .nav-link.active {
    background: #ffffff;
    color: #0d6efd; /* Màu xanh chủ đạo */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

/* Hiệu ứng hover cho các tab chưa active */
#depositTab .nav-link:not(.active):hover {
    background: rgba(255, 255, 255, 0.5);
    color: #333;
}

/* Icon (nếu bạn thêm vào) */
#depositTab .nav-link i {
    font-size: 18px;
}
/* Làm đẹp thanh phân trang */
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
    </style>
    <style>
    .img-qr {
        width: 150px; /* Thu nhỏ một chút để cân đối hơn khi nằm ngang */
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
            border-right: 1px dashed #ddd; /* Đường kẻ ngăn cách giữa ảnh và chữ */
            margin-right: 20px;
        }
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

        #purchaseSection {
    transition: all 0.3s ease;
}
#purchaseSection.highlight {
    background: rgba(13, 110, 253, 0.05);
    border-radius: 12px;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
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
            <?php if (session('customer_id') == 1 || session('customer_id') == 9632): ?>
                <a href="<?= site_url('admin') ?>" class="btn btn-primary btn-sm rounded-pill px-2 px-sm-3 shadow-sm">
                    <i class="bi bi-key-fill"></i> <span class="d-none d-sm-inline">Admin Key</span>
                </a>
                <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-dark btn-sm rounded-pill px-2 px-sm-3 shadow-sm">
                    <i class="bi bi-speedometer2"></i> <span class="d-none d-sm-inline">Admin</span>
                </a>
            <?php else: ?>
                <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-light btn-sm rounded-pill px-2 px-sm-3 border shadow-sm" title="Trang chủ">
                    <i class="bi bi-house-door-fill text-primary"></i> <span class="d-none d-sm-inline">Trang chủ</span>
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

    <div class="row">
        <div class="col-lg-5">
            <div class="card-custom">
<div class="card-header-custom d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle-fill text-primary"></i>
        <h6 class="mb-0">Nạp Tiền - Auto</h6>
    </div>
    <!-- <button onclick="scrollToPurchase()" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="bi bi-cart-plus me-1"></i> Mua Key
    </button> -->
</div>

            <div class="p-3">
                <?php if(isset($_SESSION['alert'])): ?>
                    <div class="alert alert-<?= $_SESSION['alert_type'] ?> small p-2">
                        <?= $_SESSION['alert']; unset($_SESSION['alert']); unset($_SESSION['alert_type']); ?>
                    </div>
                <?php endif; ?>

               <ul class="nav nav-tabs mb-3" id="depositTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bank">
                            <i class="bi bi-bank"></i> Ngân Hàng
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#card">
                            <i class="bi bi-phone"></i> Thẻ Cào
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usdt">
                            <i class="bi bi-currency-bitcoin"></i> Crypto (USDT)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="bank">
                        <div class="bank-container shadow-sm border rounded p-3 bg-white" style="max-width: 500px; margin: auto;">
                            <div id="bank-input-step">
                                <div class="text-center mb-3">
                                    <label class="fw-bold mb-2">Nhập số tiền cần nạp (VNĐ)</label>
                                    <div id="preview_money" class="fs-4 fw-bold text-primary mb-2"></div>
                                    <input type="number" id="bank_amount" class="form-control form-control-lg text-center border-primary" placeholder="Ví dụ: 50000">
                                </div>
                                <button type="button" onclick="generateQR(this)" class="btn btn-danger w-100 py-3 fw-bold shadow-sm">
                                    <i class="bi bi-qr-code-scan"></i>THANH TOÁN
                                </button>

                                <div style="text-align: center"><hr>
                                            <P>Hỗ trợ tất cả app ngân hàng, ví điện tử thanh toán <br> duyệt bank tự động 10-30s</P>
                                        </div>
                            </div>

                            <div id="bank-qr-step" style="display: none;" class="animate__animated animate__fadeIn">
                                <div class="text-center mb-3">
                                    <img id="qr_img" src="" class="img-fluid border rounded p-2 bg-white mb-2" style="max-width: 300px;">
                                    <p class="small text-muted italic mb-0">Quét mã QR thanh toán <br>hệ thống thanh toán duyệt tự động</p>
                                </div>

                                <div class="bank-details-list">
                                    <div class="p-2 border-bottom d-flex justify-content-between">
                                        <span class="text-muted small">Ngân hàng</span>
                                        <b id="info_bank_name" class="text-dark">---</b>
                                    </div>

                                    <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light-hover cursor-pointer" onclick="copyText('info_account_number', 'Số tài khoản')">
                                    
                                            <span class="text-muted d-block small">Số tài khoản</span>
                                            <b id="info_account_number"  class="text-dark uppercase">---</b>
                                    </div>

                                    <!-- <div class="p-2 border-bottom d-flex justify-content-between">
                                        <span class="text-muted small">Chủ tài khoản</span>
                                        <b id="info_account_name" class="text-dark uppercase">---</b>
                                    </div> -->

                                    <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light-hover cursor-pointer" onclick="copyText('info_amount_raw', 'Số tiền')">
                                    
                                            <span class="text-muted d-block small">Số tiền nạp</span>
                                            <b id="info_amount" class="text-primary">---</b>
                                            <span id="info_amount_raw" style="display:none"></span> 
                                    </div>


                                    <div class="p-2 border-bottom d-flex justify-content-between align-items-center bg-light-hover cursor-pointer" onclick="copyText('info_addInfo', 'Nội dung')">
                                        
                                        <span class="text-muted small">Nội dung</span>
                                            <b id="info_addInfo" class="text-danger">---</b>
                                    </div>
                                </div>
                                <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm w-100 mt-3">Quay lại</button>
                            </div>
                        </div>
                    </div>

                    <style>
                        .cursor-pointer { cursor: pointer; transition: 0.2s; }
                        .cursor-pointer:hover { background-color: #f8f9fa; }
                        .bg-light-hover:active { background-color: #e9ecef; }
                        .uppercase { text-transform: uppercase; }
                    </style>

                        <div class="tab-pane fade" id="card">
                             <div style="text-align: center">
                             <P>Thẻ Card có thể mất phí 10-15% tùy từng thời điểm</P> </div>
                            <form action="<?= site_url('customer/card') ?>" method="post">
                                <?= csrf_field() ?>
                                <select name="telco" class="form-select mb-2" required>
                                    <option value="">--- Chọn loại thẻ ---</option>
                                    <option value="VIETTEL">Viettel</option>
                                    <option value="VINAPHONE">Vinaphone</option>
                                    <option value="MOBIFONE">Mobifone</option>
                                    <option value="GATE">Gate</option>
                                    <option value="VCOIN">Vcoin</option>
                                    <option value="APPOTA">Appota</option>
                                    <option value="SCOIN">Scoin</option>
                                    <option value="ZING">Zing</option>
                                    <option value="GARENA">Garena</option>
                                    <option value="VNMOBI">Vietnamobile</option>
                                </select>
                                
                                <select name="amount" class="form-select mb-2" required>
                                    <option value="">--- Chọn mệnh giá ---</option>
                                    <?php 
                                    $amounts = [20000, 30000, 50000, 100000, 200000, 300000, 500000, 1000000, 2000000, 3000000, 5000000, 10000000];
                                    foreach($amounts as $val) {
                                        echo "<option value='$val'>".number_format($val)."đ</option>";
                                    }
                                    ?>
                                </select>
                                
                                <input type="text" name="serial" class="form-control mb-2" placeholder="Số Serial" required>
                                <input type="text" name="code" class="form-control mb-3" placeholder="Mã Thẻ (Pin)" required>
                                
                                <button type="submit" name="napthe" class="btn btn-primary w-100 fw-bold">
                                    <i class="bi bi-send-check"></i> GỬI THẺ NGAY
                                </button>
                                <div style="text-align: center"><hr>
                            <P>Check kỹ thông tin trước khi gửi thẻ<br> duyệt thẻ tự động 10-30s</P>
                        
                        </div>
                        </form>
                    </div>



                    <div class="tab-pane fade" id="usdt">
                            <div class="crypto-container shadow-sm border rounded p-3 bg-white">
                                <div id="crypto-input-step">
                                    <div class="text-center mb-3">
                                        <label class="fw-bold mb-2">Nhập số lượng USDT muốn nạp</label>
                                        <div id="preview_vnd_crypto" class="fs-5 fw-bold text-success mb-2">0 VNĐ</div>
                                        <div class="input-group mb-3">
                                            <input type="number" id="crypto_amount" class="form-control form-control-lg text-center" placeholder="Ví dụ: 10" min="1" step="0.1">
                                            <span class="input-group-text fw-bold">USDT</span>
                                        </div>
                                        <small class="text-muted">Tỷ giá: 1 USDT ≈ <span id="usdt_rate">...</span> VNĐ</small>
                                    </div>
                                    <button type="button" onclick="showCryptoDetails()" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                                        <i class="bi bi-arrow-right-circle"></i> TIẾP TỤC
                                    </button>
                                </div>

                                <div id="crypto-qr-step" style="display: none;">
                                    <div class="bank-info mb-4 p-3 border rounded d-flex flex-column flex-md-row align-items-center">
                                        <div class="qr-code-side text-center pe-md-4">
                                            <img class="img-qr" src="https://bbmkts.com/uploads/img_6a62ebd1df0ae6_90913111.jpg" alt="QR TRC20"/>
                                            <div class="mt-2 small text-nowrap"><strong>Mạng: Tron (TRC20)</strong></div>
                                        </div>
                                        <div class="info-side flex-grow-1 w-100 mt-3 mt-md-0">
                                            <label class="small fw-bold">Địa chỉ ví TRC20:</label>
                                            <div class="input-group input-group-sm mb-2">
                                                <input class="form-control bg-light" id="trc20" value="TMqneHo3Tki4qT9FibYkbcnRNYhgnRg1Fm" readonly>
                                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('trc20')">Copy</button>
                                            </div>
                                            <div class="alert alert-warning p-2 mb-0 small" style="font-size: 11px;">
                                                Lưu ý: Chỉ gửi USDT mạng TRC20 vào địa chỉ này.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bank-info mb-4 p-3 border rounded d-flex flex-column flex-md-row align-items-center">
                                        <div class="qr-code-side text-center pe-md-4">
                                            <img class="img-qr" src="https://bbmkts.com/uploads/img_6a62eb98986360_58511549.jpg" alt="QR BEP20"/>
                                            <div class="mt-2 small text-nowrap"><strong>Mạng: BSC (BEP20)</strong></div>
                                        </div>
                                        <div class="info-side flex-grow-1 w-100 mt-3 mt-md-0">
                                            <label class="small fw-bold">Địa chỉ ví BEP20:</label>
                                            <div class="input-group input-group-sm mb-2">
                                                <input class="form-control bg-light" id="bep20" value="0xc494d859223286c1c186c2e8a5dccde3e963a1d3" readonly>
                                                <button class="btn btn-outline-secondary" onclick="copyToClipboard('bep20')">Copy</button>
                                            </div>
                                            <div class="alert alert-warning p-2 mb-0 small" style="font-size: 11px;">
                                                Lưu ý: Chỉ gửi USDT mạng BEP20 (BSC).
                                            </div>
                                        </div>
                                    </div>

                                    <button onclick="location.reload()" class="btn btn-secondary w-100 mt-3">Quay lại</button>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>
        </div>

        <div class="col-lg-7">
            <div class="card-custom mb-4">
                <div class="card-header-custom">
                    <i class="bi bi-lightning-fill text-warning"></i>
                    <h6>Mua Link Nhanh</h6>
                    
                </div>
                <div class="p-3">
                    <div class="input-group">
                        <input type="text" id="quickLinkInput" class="form-control" placeholder="Dán mã link hoặc URL vào đây...">
                        <button class="btn btn-quick-buy" onclick="handleQuickBuy()">MUA LINK</button>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <div class="card-header-custom justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-collection-play-fill text-success"></i>
                        <h6>Link mua trong 12h qua</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Link Gốc (click để mở link)</th>
                                <th>Giá</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($purchased_links as $link): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border"><?= esc($link['code']) ?></span></td> 
                                <td>
    <a href="<?= esc($link['target_url']) ?>" style="text-decoration: none;">
        <div class="input-group input-group-sm" style="width: max-content;">
            <input type="text" 
                   id="url-<?= $link['id'] ?>" 
                   class="form-control" 
                   value="Bấm vào đây để mở link gốc" 
                   readonly
                   style="cursor: pointer; color: #007bff !important; background-color: #fff; opacity: 1; font-weight: 500; border-right: none; white-space: nowrap;">
            <span class="input-group-text" style="color: #007bff; background-color: #fff; border-left: none;">
                <i class="bi bi-box-arrow-up-right"></i>
            </span>
        </div>
    </a>
</td>
                                <td class="fw-bold text-success"><?= number_format($link['price']) ?>đ</td>
                                <td class="small text-muted"><?= date('d/m H:i', strtotime($link['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-center">
    <?= $pager->links('links', 'default_full') ?>
</div>
            </div>
        </div>
    </div>

<div class="container-fluid p-4">
    <!-- <div class="row mb-4">
        <div class="col-12 text-center">
            <h3 class="fw-bold text-dark mb-1">🎮 Cửa Hàng Key Game</h3>
        </div>
    </div> -->

    <div class="row g-4"  id="purchaseSection">
        <?php foreach($categories as $cat): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-game">
                <div class="position-relative">
                    <img src="<?= base_url('public/uploads/'.$cat['image']) ?>" class="card-img-top" style="height: 160px; object-fit: cover;">
                </div>
                
                <div class="card-body p-4 d-flex flex-column">
                    <h6 class="fw-bold mb-3 text-dark text-truncate"><?= $cat['title'] ?></h6>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <span class="text-muted small">Tình trạng:</span>
                            <?php if($cat['stock'] > 0): ?>
                                <span class="text-success small fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Còn <?= $cat['stock'] ?> Key
                                </span>
                            <?php else: ?>
                                <span class="text-danger small fw-bold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Hết hàng
                                </span>
                            <?php endif; ?>
                        </div>

                        <a href="<?= base_url('store/view/'.$cat['id']) ?>" 
                           class="btn <?= $cat['stock'] > 0 ? 'btn-primary' : 'btn-secondary disabled' ?> w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <i class="bi bi-cart-plus me-1"></i> Mua Ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>     

                                    <div class="row mt-2">
    <div class="col-12">
        <div class="card-custom">
            <div class="card-header-custom">
                <i class="bi bi-clock-history text-info"></i>
                <h6>Lịch Sử Giao Dịch Nạp Tiền</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-nowrap">
                            <th>Loại</th>
                            <th>Mệnh giá</th>
                            <th>Thực nhận</th>
                            <th>Cổng nạp</th>
                            <th>Chi tiết</th>
                            <th>Trạng Thái</th>
                            <th>Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hide_admin12 = [];
                        
                        for($i = 0; $i < count($recharge_history) - 1; $i++) {
                            $current = $recharge_history[$i];
                            $next = $recharge_history[$i + 1];
                            
                            if(($current['admin_id'] == 12 && $next['admin_id'] == 4) || 
                               ($current['admin_id'] == 4 && $next['admin_id'] == 12)) {
                                
                                $time1 = strtotime($current['created_at']);
                                $time2 = strtotime($next['created_at']);
                                $time_diff = abs($time1 - $time2);
                                
                                if($time_diff <= 2) {
                                    if($current['admin_id'] == 12 && $current['status'] == 0) {
                                        $hide_admin12[] = $i;
                                    }
                                    if($next['admin_id'] == 12 && $next['status'] == 0) {
                                        $hide_admin12[] = $i + 1;
                                    }
                                }
                            }
                        }
                        ?>
                         <?php foreach($recharge_history as $index => $his): ?>
                            <?php 
                            $hide = false;
                            if($his['admin_id'] == 4) {
                                $hide = true;
                            }
                            if($his['admin_id'] == 12 && $his['status'] == 0 && in_array($index, $hide_admin12)) {
                                $hide = true;
                            }
                            ?>
                            
                            <?php if(!$hide): ?>
                                
                        <tr>
                            <td>
                                <?php if($his['type'] == 'BANK'): ?>
                                    <span class="badge bg-primary-soft text-primary small"><i class="bi bi-bank"></i> BANK</span>
                                <?php elseif($his['type'] == 'CRYPTO'): ?>
                                    <span class="badge bg-warning-soft text-warning small"><i class="bi bi-currency-bitcoin"></i> CRYPTO</span>
                                <?php else: ?>
                                    <span class="badge bg-info-soft text-info small"><i class="bi bi-phone"></i> CARD</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-muted small fw-bold">
                                <?php if($his['type'] == 'CRYPTO'): ?>
                                    $<?= number_format($his['amount_sent'], 2) ?>
                                <?php else: ?>
                                    <?= number_format($his['amount_sent'], 2) ?>đ
                                <?php endif; ?>
                            </td>

                            <td class="text-success fw-bold">
                                <?= $his['status'] == 1 ? '+'.number_format($his['amount']).'đ' : '<span class="text-muted">---</span>' ?>
                            </td>

                            <td class="text-muted small">
                                <span class="badge border text-dark fw-normal"><?= esc($his['telco']) ?></span>
                            </td>

                            <td style="max-width: 200px;">
                                <div class="text-truncate small text-muted" title="<?= esc($his['code']) ?>">
                                    <?php if($his['type'] == 'CARD'): ?>
                                         <code class="text-primary">SR: <?= esc($his['serial']) ?><br>MT: <?= esc($his['pin']) ?></code>
                                    <?php else: ?>
                                         <code class="text-primary"><?= esc($his['code']) ?></code>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php if($his['status'] == 1): ?>
                                    <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill small">Thành công</span>
                                <?php elseif($his['status'] == 2): ?>
                                    <span class="badge bg-danger-soft text-danger px-3 py-2 rounded-pill small">Thất bại</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-soft text-warning px-3 py-2 rounded-pill small">Đang chờ</span>
                                <?php endif; ?>
                            </td>

                            <td class="small text-muted text-nowrap">
                                <?= date('H:i d/m/Y', strtotime($his['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($recharge_history)): ?>
                    <div class="p-4 text-center text-muted small">Chưa có giao dịch nạp tiền nào.</div>
                <?php endif; ?>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                <?= $pager->links('recharge', 'default_full') ?>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<div class="copy-tooltip" id="copyTooltip"></div>
</div>
 <?php if (session('customer_id') != 9632): ?>
 <a href="https://t.me/binhbun02" id="linktelegram" target="_blank" rel="noopener noreferrer">
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
function scrollToPurchase() {
    const target = document.getElementById('purchaseSection');
    if (target) {
        target.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
}
</script>
<script>
let paymentInterval; 

async function generateQR(btnElement) {
    const amountField = document.getElementById('bank_amount');
    const amount = amountField.value;

    if (!amount || amount < 20000) {
        Swal.fire({ icon: 'warning', title: 'Thông báo', text: 'Số tiền tối thiểu Nạp là 20.000đ' });
        return;
    }

    const originalContent = btnElement.innerHTML;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btnElement.disabled = true;

    try {
        const response = await fetch('<?= site_url("customer/bank") ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'amount': amount,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        });

        const res = await response.json();

        if (res.status === 'success') {
            document.getElementById('qr_img').src = res.qr_url;

            var bankName      = res.manual_bank_name      ?? res.bank_name;
            var accountNumber = res.manual_account_number ?? res.account_number;
            var accountName    = res.manual_account_name   ?? res.account_name;
            var addInfo        = res.manual_addInfo        ?? res.addInfo;

            document.getElementById('info_bank_name').innerText = bankName;
            document.getElementById('info_account_number').innerText = accountNumber;
            // document.getElementById('info_account_name').innerText = accountName;

            document.getElementById('info_amount').innerText = res.amount + ' đ';
            document.getElementById('info_amount_raw').innerText = amount; 
            document.getElementById('info_addInfo').innerText = addInfo;

            document.getElementById('bank-input-step').style.display = 'none';
            document.getElementById('bank-qr-step').style.display = 'block';

            startPaymentCheck(addInfo);

        } else {
            Swal.fire({ icon: 'error', text: res.msg });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', text: 'Lỗi hệ thống, vui lòng thử lại!' });
    } finally {
        btnElement.innerHTML = originalContent;
        btnElement.disabled = false;
    }
}

function startPaymentCheck(code) {
    if (paymentInterval) clearInterval(paymentInterval);

    paymentInterval = setInterval(async () => {
        try {
            const response = await fetch(`<?= site_url('customer/checkPayment/') ?>${code}`);
            const data = await response.json();

            if (data.success) {
                clearInterval(paymentInterval);
                showSuccessAndReload();
            }
        } catch (e) {
            console.error("Lỗi check trạng thái thanh toán");
        }
    }, 3000); 
}

function showSuccessAndReload() {
    let timerInterval;
    Swal.fire({
        title: "Thanh toán thành công!",
        html: "Đang cộng tiền vào tài khoản của bạn <b></b> giây.",
        icon: "success",
        timer: 5000,
        timerProgressBar: true,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            const timer = Swal.getPopup().querySelector("b");
            timerInterval = setInterval(() => {
                timer.textContent = `${Math.ceil(Swal.getTimerLeft() / 1000)}`;
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
    }).then((result) => {
        window.location.reload();
    });
}
function copyText(elementId, label) {

    const text = document.getElementById(elementId).innerText;

    navigator.clipboard.writeText(text).then(() => {

        const Toast = Swal.mixin({

            toast: true,

            position: 'top-end',

            showConfirmButton: false,

            timer: 1500,

            timerProgressBar: true

        });

        Toast.fire({

            icon: 'success',

            title: `Đã copy ${label}: ${text}`

        });

    });

}
let currentUsdtRate = 22000;

async function fetchUsdtRate() {
    try {
        const response = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=vnd');
        const data = await response.json();
        currentUsdtRate = data.tether.vnd;
        document.getElementById('usdt_rate').innerText = currentUsdtRate.toLocaleString('vi-VN');
    } catch (error) {
        console.error("Lỗi lấy tỷ giá:", error);
        document.getElementById('usdt_rate').innerText = "25,000 (Tạm thời)";
    }
}
fetchUsdtRate();

document.getElementById('crypto_amount').addEventListener('input', function() {
    const usdt = parseFloat(this.value) || 0;
    const vnd = usdt * currentUsdtRate;
    document.getElementById('preview_vnd_crypto').innerText = vnd.toLocaleString('vi-VN') + " VNĐ";
});

function showCryptoDetails() {
    const usdt = document.getElementById('crypto_amount').value;
    if (!usdt || usdt < 1) {
        Swal.fire('Lỗi', 'Tối thiểu 1 USDT', 'error');
        return;
    }

    document.querySelectorAll('#money-usd-binance').forEach(el => el.value = usdt);
    
    document.getElementById('crypto-input-step').style.display = 'none';
    document.getElementById('crypto-qr-step').style.display = 'block';

    saveCryptoLog(usdt);
}

function copyToClipboard(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Đã copy: ' + copyText.value,
        showConfirmButton: false,
        timer: 1500
    });
}

async function saveCryptoLog(usdt) {
    const vndAmount = Math.round(usdt * currentUsdtRate);
    
    try {
        const response = await fetch('<?= site_url("customer/crypto") ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'amount_sent': usdt,     
                'amount': vndAmount,      
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        });
        
        const res = await response.json();
        
        if (res.status === 'success') {
            document.querySelectorAll('#money-usd').forEach(el => {
                el.value = res.usdt + " USDT";
            });
            
            if(document.getElementById('crypto_memo')) {
                document.getElementById('crypto_memo').innerText = res.addInfo;
            }

            document.getElementById('crypto-input-step').style.display = 'none';
            document.getElementById('crypto-qr-step').style.display = 'block';
            
        } else {
            Swal.fire('Lỗi', res.msg, 'error');
        }
    } catch (e) {
        Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
    }
}

document.getElementById('bank_amount').addEventListener('input', function(e) {
    let val = e.target.value;
    document.getElementById('preview_money').innerText = val ? new Intl.NumberFormat('vi-VN').format(val) + ' đ' : '';
});
function handleQuickBuy() {
    let input = document.getElementById('quickLinkInput').value.trim();
    if (!input) {
        Swal.fire('Thông báo', 'Vui lòng nhập Link hoặc Mã link!', 'warning');
        return;
    }
    let code = input.split('/').pop();
    Swal.fire({
        title: 'Xác nhận mua?',
        text: "Hệ thống sẽ trừ tiền vào số dư của bạn.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => { if (result.isConfirmed) executeBuy(code); });
}

function executeBuy(code) {
    const btn = document.querySelector('.btn-quick-buy');
    btn.disabled = true;
    btn.innerHTML = 'Đang xử lý...';

    fetch(`<?= site_url('customer/buy/') ?>${code}`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') localStorage.setItem('pending_link_code', code);

        if (data.status === 'success') {
            localStorage.removeItem('pending_link_code');
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: 'Hệ thống đang chuyển bạn đến link gốc...',
                timer: 2000,
                showConfirmButton: false,
                background: '#16161a',
                color: '#fff'
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire('Thất bại', data.message, 'error').then(() => {
                if (data.redirect && window.location.href !== data.redirect) window.location.href = data.redirect;
                else location.reload();
            });
        }
    });
}

window.addEventListener('load', () => {
    setTimeout(() => {
        const pendingCode = localStorage.getItem('pending_link_code');
        const inputField = document.getElementById('quickLinkInput');
        if (pendingCode && inputField) {
            inputField.value = pendingCode;
            inputField.focus();
            inputField.style.border = "2px solid #ffc107";
            localStorage.removeItem('pending_link_code');
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Đã điền lại mã link cũ!', showConfirmButton: false, timer: 3000 });
        }
    }, 500);
});

function copyToClipboard(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy!', showConfirmButton: false, timer: 1500 });
}
</script>




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