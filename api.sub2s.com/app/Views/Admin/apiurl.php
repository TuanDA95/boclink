<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center pt-3">
    <div class="col-lg-10">
        <?= $this->include('Layout/msgStatus') ?>
        
        <form method="post" action="<?= site_url('apiurl/save') ?>">
            <?= csrf_field() ?>

            <?php
                $banks = [
                    "ICB" => "VietinBank", "VCB" => "Vietcombank", "BIDV" => "BIDV", "VBA" => "Agribank",
                    "OCB" => "OCB", "MB" => "MBBank", "TCB" => "Techcombank", "STB" => "Sacombank",
                    "VPB" => "VPBank", "TPB" => "TPBank", "ACB" => "ACB", "HDB" => "HDBank",
                    "VIB" => "VIB", "SHB" => "SHB", "EIB" => "Eximbank", "MSB" => "MSB",
                    "NAB" => "NamABank", "VAB" => "VietABank", "VCCB" => "BVBank", "KLB" => "KienLongBank",
                    "ABB" => "ABBank", "LPB" => "LPBank", "SEA" => "SeABank", "PGB" => "PGBank",
                    "BAB" => "BACABANK", "NCB" => "NHQUOCDAN", "WVN" => "WOORIVN", "SHVN" => "ShinhanBank"
                ];
                ?>


<div class="card mb-4 shadow-sm border-info">
            <div class="card mb-4 shadow-sm border-warning">
                <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-back-fill"></i>
                    <h5 class="mb-0">Cấu Hình Thanh Toán (Payment API)</h5>
                </div>
                <div class="card-body bg-light-subtle">
                    <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">ID Doithe1s</label>
                    <input type="text" name="partner_id" class="form-control" value="<?= esc($user->partner_id ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Key Doithe1s</label>
                    <input type="text" name="partner_key" class="form-control" value="<?= esc($user->partner_key ?? '') ?>">
                </div>
                
                <div class="col-md-3">
                        <label class="form-label fw-bold text-primary">Ngân hàng</label>
                        <select name="bank_id" class="form-select">
                            <option value="">-- Chọn ngân hàng --</option>
                            <?php foreach($banks as $code => $name): ?>
                                <option value="<?= $code ?>" <?= (isset($user->bank_id) && $user->bank_id == $code) ? 'selected' : '' ?>>
                                    <?= $name ?> (<?= $code ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary">Số tài khoản</label>
                    <input type="text" name="bank_number" class="form-control" value="<?= esc($user->bank_number ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-primary">Tên chủ tài khoản</label>
                    <input type="text" name="bank_name" class="form-control" value="<?= esc($user->bank_name ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-primary">Tiền tố (Prefix)</label>
                    <input type="text" name="bank_prefix" class="form-control" value="<?= esc($user->bank_prefix ?? 'BBZQT') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-danger">Sepay API Key</label>
                    <input type="text" name="sepay_api_key" class="form-control" value="<?= esc($user->sepay_api_key ?? '') ?>">
                </div>
            </div>
                </div>
            </div>

             <!-- ===== THÊM PHẦN BẬT/TẮT MIỄN PHÍ ===== -->
        <div class="row g-3 mb-4 p-3 rounded border" 
             style="background: <?= (($user->enable_free_global ?? 1) == 1) ? 'rgba(40, 167, 69, 0.08)' : 'rgba(220, 53, 69, 0.08)' ?>; 
                    border-color: <?= (($user->enable_free_global ?? 1) == 1) ? '#28a745' : '#dc3545' ?>;">
            
            <div class="col-md-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="bi bi-unlock-fill <?= (($user->enable_free_global ?? 1) == 1) ? 'text-success' : 'text-danger' ?>"></i>
                            Chức năng Link miễn phí
                            <span class="badge <?= (($user->enable_free_global ?? 1) == 1) ? 'bg-success' : 'bg-danger' ?> ms-2" id="status-badge">
                                <?= (($user->enable_free_global ?? 1) == 1) ? '🟢 ĐANG BẬT' : '🔴 ĐANG TẮT' ?>
                            </span>
                        </h5>
                        <p class="text-muted mb-0 small" id="status-description">
                            <?= (($user->enable_free_global ?? 1) == 1) 
                                ? 'Người dùng có thể sử dụng link miễn phí' 
                                : 'Người dùng KHÔNG thể sử dụng link miễn phí, chỉ có thể mua' ?>
                        </p>
                    </div>
                    <div class="form-check form-switch mt-2 mt-md-0">
                        <input type="hidden" name="enable_free_global" value="0">
                        <input class="form-check-input" type="checkbox" 
                               id="enable_free_global" name="enable_free_global" value="1"
                               style="width: 3.5rem; height: 1.8rem; cursor: pointer;"
                               <?= (($user->enable_free_global ?? 1) == 1) ? 'checked' : '' ?>
                               onchange="updateFreeStatus(this)">
                        <label class="form-check-label fw-bold ms-2" for="enable_free_global" style="font-size: 1.1rem;">
                            <span id="status-text" class="badge <?= (($user->enable_free_global ?? 1) == 1) ? 'bg-success' : 'bg-danger' ?> p-2">
                                <?= (($user->enable_free_global ?? 1) == 1) ? 'BẬT' : 'TẮT' ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Alert thông báo -->
            <div class="col-md-12 mt-2">
                <div class="alert alert-<?= (($user->enable_free_global ?? 1) == 1) ? 'info' : 'warning' ?> mb-0 small">
                    <i class="bi bi-info-circle"></i>
                    <?php if (($user->enable_free_global ?? 1) == 1): ?>
                        <strong>Đang bật:</strong> Người dùng có 2 lựa chọn: <span class="badge bg-info">Miễn phí</span> hoặc <span class="badge bg-warning text-dark">Mua link</span>
                    <?php else: ?>
                        <strong>Đang tắt:</strong> Người dùng chỉ có 1 lựa chọn: <span class="badge bg-warning text-dark">Mua link</span>. 
                        Tính năng miễn phí bị ẩn hoàn toàn.
                    <?php endif; ?>
                    <br><small class="text-muted">* Thay đổi áp dụng <strong>ngay lập tức</strong> cho toàn bộ link trên hệ thống</small>
                </div>
            </div>
        </div>
        <!-- ===== KẾT THÚC PHẦN BẬT/TẮT ===== -->

            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">⚡ Cấu Hình Hệ Thống & Giá Chung</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-danger">💰 Giá 1 Link (VNĐ)</label>
                            <input type="number" name="price_per_api" class="form-control border-danger fw-bold" 
                                   value="<?= esc($user->price_per_api ?? 0) ?>" placeholder="Ví dụ: 500">
                            <small class="text-muted">Áp dụng cho tất cả API bên dưới</small>
                        </div>
                        <div class="col-md-3" hidden>
                            <label class="form-label fw-bold">Thời hạn Key (Giờ)</label>
                            <div class="input-group">
                                <input type="number" name="key_duration" class="form-control" value="<?= esc($user->key_duration ?? 24) ?>">
                                <span class="input-group-text">H</span>
                            </div>
                        </div>
                        <div class="col-md-3" hidden>
                            <label class="form-label fw-bold text-primary">Link Discord</label>
                            <input type="text" name="discord_link" class="form-control" value="<?= esc($user->discord_link ?? 'https://discord.gg/') ?>">
                        </div>
                        <div class="col-md-3" hidden>
                            <label class="form-label fw-bold text-warning">Link Mua Key</label>
                            <input type="text" name="buy_key_link" class="form-control" value="<?= esc($user->buy_key_link ?? 'https://discord.gg/') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 border-end">
                            <span class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                                <img src="https://emojigraph.org/media/joypixels/flag-vietnam_1f1fb-1f1f3.png" width="18"> IP Việt Nam
                            </span>
                            <div id="vn-api-container">
                                <?php 
                                $vn_apis = json_decode($user->vn_short_config ?? '[]', true);
                                foreach ($vn_apis as $index => $api): 
                                ?>
                                    <div class="api-item mb-2 rounded d-flex gap-2 align-items-center shadow-sm">
                                        <div class="flow-switches">
                                            <div class="switch-group form-check form-switch p-0" hidden>
                                                <label>Keyapi</label>
                                                <input type="hidden" name="vn_apis[<?= $index ?>][h_status]" value="0"> <input class="form-check-input sw-h m-0" type="checkbox" name="vn_apis[<?= $index ?>][h_status]" value="1" <?= ($api['h_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                            <div class="switch-group form-check form-switch p-0" hidden>
                                                <label>Keyauto</label>
                                                <input type="hidden" name="vn_apis[<?= $index ?>][p_status]" value="0"> <input class="form-check-input sw-p m-0" type="checkbox" name="vn_apis[<?= $index ?>][p_status]" value="1" <?= ($api['p_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                            <div class="switch-group form-check form-switch p-0">
                                                <label>Link</label>
                                                <input type="hidden" name="vn_apis[<?= $index ?>][i_status]" value="0"> <input class="form-check-input sw-i m-0" type="checkbox" name="vn_apis[<?= $index ?>][i_status]" value="1" <?= ($api['i_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                        <input type="text" name="vn_apis[<?= $index ?>][base]" class="form-control form-control-sm api-input-link" value="<?= esc($api['base'] ?? '') ?>" placeholder="Link API">
                                        <button type="button" class="btn-delete-api" onclick="this.closest('.api-item').remove()">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2 w-100 fw-bold" onclick="addApiField('vn')">+ THÊM LỚP VIỆT NAM</button>
                        </div>

                        <div class="col-md-6">
                            <span class="fw-bold text-danger mb-3 d-flex align-items-center gap-2">🌎 IP Quốc Tế (Global)</span>
                            <div id="global-api-container">
                                <?php 
                                $global_apis = json_decode($user->global_short_config ?? '[]', true);
                                foreach ($global_apis as $index => $api): 
                                ?>
                                    <div class="api-item mb-2 rounded d-flex gap-2 align-items-center shadow-sm">
                                        <div class="flow-switches">
                                            <div class="switch-group form-check form-switch p-0" hidden>
                                                <label>Keyapi</label>
                                                <input type="hidden" name="global_apis[<?= $index ?>][h_status]" value="0"> <input class="form-check-input sw-h m-0" type="checkbox" name="global_apis[<?= $index ?>][h_status]" value="1" <?= ($api['h_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                            <div class="switch-group form-check form-switch p-0" hidden>
                                                <label>Keyauto</label>
                                                <input type="hidden" name="global_apis[<?= $index ?>][p_status]" value="0"> <input class="form-check-input sw-p m-0" type="checkbox" name="global_apis[<?= $index ?>][p_status]" value="1" <?= ($api['p_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                            <div class="switch-group form-check form-switch p-0">
                                                <label>Link</label>
                                                <input type="hidden" name="global_apis[<?= $index ?>][i_status]" value="0"> <input class="form-check-input sw-i m-0" type="checkbox" name="global_apis[<?= $index ?>][i_status]" value="1" <?= ($api['i_status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                        <input type="text" name="global_apis[<?= $index ?>][base]" class="form-control form-control-sm api-input-link" value="<?= esc($api['base'] ?? '') ?>" placeholder="Link API">
                                        <button type="button" class="btn-delete-api" onclick="this.closest('.api-item').remove()">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm mt-2 w-100 fw-bold" onclick="addApiField('global')">+ THÊM LỚP QUỐC TẾ</button>
                        </div>
                    </div>

                    <hr class="my-4">
                    <button class="btn btn-primary w-100 py-3 fw-bold fs-5 shadow"><i class="bi bi-save"></i> LƯU TẤT CẢ CẤU HÌNH</button>
                </div>
            </div>
        </form>

        <?php if (!empty($user->username)) : ?>
        <div class="card border-0 bg-light shadow-sm">
            <div class="card-body">
                <label class="fw-bold text-muted mb-2">Callback card</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url('webhook/card')) ?>" id="getkeyLink1" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey1()">Copy</button>
                </div>
            </div>

        </div>
        <div class="card border-0 bg-light shadow-sm">
            <div class="card-body">
                <label class="fw-bold text-muted mb-2">Callback bank</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url('public/callback/sepay.php')) ?>" id="getkeyLink2" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey2()">Copy</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>
<style>
    /* Custom Input & API Item */
    .api-item {
        background: #ffffff;
        border: 1px solid #e9ecef !important;
        padding: 10px !important;
        transition: all 0.2s ease;
    }
    
    .api-item:hover {
        border-color: #dee2e6 !important;
        background-color: #f8f9fa !important;
    }

    /* Input Link tinh tế hơn */
    .api-input-link {
        border: 1px solid #dee2e6 !important;
        background-color: #fff !important;
        padding: 5px 12px;
        border-radius: 6px;
    }
    .api-input-link:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.1);
        background-color: #fff !important;
    }

    /* Nút xóa X dạng tròn */
    .btn-delete-api {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #dc3545;
        background: #fff0f0;
        
        border: 2px solid;
        transition: all 0.2s ease;
        font-size: 14px;
        text-decoration: none;
    }
    .btn-delete-api:hover {
        background: #dc3545;
        color: #fff;
        transform: rotate(90deg);
    }

    /* Container Switches */
    .flow-switches {
        display: flex;
        gap: 12px;
        padding: 4px 12px;
        background: #f1f3f5;
        border-radius: 8px;
    }

    .switch-group {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .switch-group label {
        font-size: 10px;
        font-weight: 800;
        color: #495057;
        margin-bottom: 1px;
    }

    .form-switch .form-check-input {
        width: 2.2em;
        height: 1.1em;
        cursor: pointer;
    }

    .sw-h:checked { background-color: #0d6efd !important; border-color: #0d6efd !important; }
    .sw-p:checked { background-color: #198754 !important; border-color: #198754 !important; }
    .sw-i:checked { background-color: #f59e0b !important; border-color: #f59e0b !important; }
</style>
<script>
 function updateFreeStatus(checkbox) {
    const statusBadge = document.getElementById('status-badge');
    const statusText = document.getElementById('status-text');
    const statusDesc = document.getElementById('status-description');
    const alertBox = document.querySelector('.alert');
    const container = checkbox.closest('.row.g-3');
    
    if (checkbox.checked) {
        // BẬT
        statusBadge.className = 'badge bg-success ms-2';
        statusBadge.textContent = '🟢 ĐANG BẬT';
        
        statusText.className = 'badge bg-success p-2';
        statusText.textContent = 'BẬT';
        
        statusDesc.textContent = 'Người dùng có thể sử dụng link miễn phí';
        
        container.style.background = 'rgba(40, 167, 69, 0.08)';
        container.style.borderColor = '#28a745';
        
        document.querySelector('.bi-unlock-fill').className = 'bi bi-unlock-fill text-success';
        
        if (alertBox) {
            alertBox.className = 'alert alert-info mb-0 small';
            alertBox.innerHTML = `
                <i class="bi bi-info-circle"></i>
                <strong>Đang bật:</strong> Người dùng có 2 lựa chọn: <span class="badge bg-info">Miễn phí</span> hoặc <span class="badge bg-warning text-dark">Mua link</span>
                <br><small class="text-muted">* Thay đổi áp dụng <strong>ngay lập tức</strong> cho toàn bộ link trên hệ thống</small>
            `;
        }
    } else {
        // TẮT
        statusBadge.className = 'badge bg-danger ms-2';
        statusBadge.textContent = '🔴 ĐANG TẮT';
        
        statusText.className = 'badge bg-danger p-2';
        statusText.textContent = 'TẮT';
        
        statusDesc.textContent = 'Người dùng KHÔNG thể sử dụng link miễn phí, chỉ có thể mua';
        
        container.style.background = 'rgba(220, 53, 69, 0.08)';
        container.style.borderColor = '#dc3545';
        
        document.querySelector('.bi-unlock-fill').className = 'bi bi-unlock-fill text-danger';
        
        if (alertBox) {
            alertBox.className = 'alert alert-warning mb-0 small';
            alertBox.innerHTML = `
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Đang tắt:</strong> Người dùng chỉ có 1 lựa chọn: <span class="badge bg-warning text-dark">Mua link</span>. 
                Tính năng miễn phí bị ẩn hoàn toàn.
                <br><small class="text-muted">* Thay đổi áp dụng <strong>ngay lập tức</strong> cho toàn bộ link trên hệ thống</small>
            `;
        }
    }
    
    // ===== THÊM: TỰ ĐỘNG SUBMIT FORM =====
    // Tìm form cha và submit
    const form = checkbox.closest('form');
    if (form) {
        // Hiển thị trạng thái đang lưu
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang lưu...';
            submitBtn.disabled = true;
            
            // Submit form
            form.submit();
            
            // Khôi phục sau 2 giây (phòng trường hợp form không reload)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        } else {
            form.submit();
        }
    }
    // ===== KẾT THÚC =====
}

function addApiField(group) {
    const container = document.getElementById(group + '-api-container');
    const index = Date.now();
    
    const html = `
    <div class="api-item mb-2 rounded d-flex gap-2 align-items-center shadow-sm animate__animated animate__fadeInLeft">
        <div class="flow-switches">
            <div class="switch-group form-check form-switch p-0" hidden>
                <label>H</label>
                <input type="hidden" name="${group}_apis[${index}][h_status]" value="0">
                <input class="form-check-input sw-h m-0" type="checkbox" name="${group}_apis[${index}][h_status]" value="1" checked>
            </div>
            <div class="switch-group form-check form-switch p-0" hidden>
                <label>P</label>
                <input type="hidden" name="${group}_apis[${index}][p_status]" value="0">
                <input class="form-check-input sw-p m-0" type="checkbox" name="${group}_apis[${index}][p_status]" value="1" checked>
            </div>
            <div class="switch-group form-check form-switch p-0">
                <label>I</label>
                <input type="hidden" name="${group}_apis[${index}][i_status]" value="0">
                <input class="form-check-input sw-i m-0" type="checkbox" name="${group}_apis[${index}][i_status]" value="1" checked>
            </div>
        </div>
        <input type="text" name="${group}_apis[${index}][base]" class="form-control form-control-sm api-input-link" placeholder="Dán link API mới...">
        <button type="button" class="btn-delete-api" onclick="this.closest('.api-item').remove()">✕</button>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', html);
}


function copyGetKey1() {
    const copyText = document.getElementById("getkeyLink1");
    copyText.select();
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });
}
function copyGetKey2() {
    const copyText = document.getElementById("getkeyLink2");
    copyText.select();
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });
}

let quickBanks = [];

// 1. Tự động load dữ liệu từ file trên Server khi trang web mở ra
$(document).ready(function() {
    $.get('<?= site_url("apiurl/get_quick_banks") ?>', function(data) {
        quickBanks = data;
        renderBanks();
    }).fail(function() {
        console.error("Không thể load file JSON từ server");
    });
});

// 2. Hàm lưu dữ liệu lên Server (Ghi vào file)
function updateBankToServer() {
    const jsonData = JSON.stringify(quickBanks);
    
    let csrfInput = $('input[name^="csrf_"]'); 
    let csrfName = csrfInput.attr('name');
    let csrfHash = csrfInput.val();

    $.ajax({
        url: '<?= site_url("apiurl/update_quick_banks") ?>',
        method: 'POST',
        data: {
            [csrfName]: csrfHash,
            quick_banks_json: jsonData
        },
        dataType: 'JSON',
        success: function(res) {
            if(res.token) {
                $('input[name="' + csrfName + '"]').val(res.token);
            }
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã đồng bộ Server', showConfirmButton: false, timer: 1000 });
        },
        error: function() {
            Swal.fire('Lỗi', 'Không thể lưu file lên server. Kiểm tra quyền ghi file!', 'error');
        }
    });
}

// 3. Hàm render bảng
function renderBanks() {
    const currentBankId = $('select[name="bank_id"]').val();
    const currentBankNum = $('input[name="bank_number"]').val();
    let html = '';

    quickBanks.forEach((item, index) => {
        const isActive = (item.id === currentBankId && item.number === currentBankNum);
        html += `
        <tr class="${isActive ? 'table-warning' : ''}">
            <td><span class="badge ${isActive ? 'bg-success' : 'bg-primary'}">${item.id}</span></td>
            <td><b>${item.number}</b><br><small class="text-muted">${item.name}</small></td>
            <td><code>${item.prefix}</code></td>
            <td><small>${item.sepay.slice(0, 5)}***${item.sepay.slice(-3)}</small></td>
            <td class="text-center">
                <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input bank-active-sw" type="checkbox" ${isActive ? 'checked' : ''} onclick="useBank(${index}, this)">
                </div>
            </td>
            <td class="text-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBank(${index})"><i class="bi bi-pencil-square"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBank(${index})"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    $('#quickBankList').html(html || '<tr><td colspan="6" class="text-center text-muted">Danh sách trống</td></tr>');
}

// 4. Hàm thêm/sửa
function saveQuickBank() {
    const index = parseInt($('#edit_index').val());
    const bank = {
        id: $('#q_bank_id').val(),
        number: $('#q_bank_number').val(),
        name: $('#q_bank_name').val(),
        prefix: $('#q_bank_prefix').val(),
        sepay: $('#q_sepay_key').val()
    };

    if(!bank.number || !bank.sepay) return Swal.fire('Lỗi', 'Nhập đủ thông tin!', 'error');

    if (index === -1) quickBanks.push(bank);
    else quickBanks[index] = bank;

    renderBanks();
    updateBankToServer(); // Lưu ngay lên file
    cancelEdit();
    $('#quickBankForm').hide();
}

function removeBank(index) {
    if(confirm('Xóa ngân hàng này khỏi server?')) {
        quickBanks.splice(index, 1);
        renderBanks();
        updateBankToServer();
    }
}

// Các hàm khác (editBank, cancelEdit, toggleBankForm) giữ nguyên như cũ...
function editBank(index) {
    let item = quickBanks[index];
    $('#edit_index').val(index);
    $('#q_bank_id').val(item.id);
    $('#q_bank_number').val(item.number);
    $('#q_bank_name').val(item.name);
    $('#q_bank_prefix').val(item.prefix);
    $('#q_sepay_key').val(item.sepay);
    $('#btn_save_quick').text('Cập nhật').addClass('btn-warning');
    $('#btn_cancel_edit, #quickBankForm').show();
}

function cancelEdit() {
    $('#edit_index').val('-1');
    $('#btn_save_quick').text('Lưu lại').removeClass('btn-warning');
    $('#btn_cancel_edit').hide();
    $('#q_bank_number, #q_bank_name, #q_sepay_key').val('');
}

function toggleBankForm() { $('#quickBankForm').toggle(); }

function useBank(index, el) {
    if($(el).is(':checked')) {
        let selected = quickBanks[index];
        $('select[name="bank_id"]').val(selected.id);
        $('input[name="bank_number"]').val(selected.number);
        $('input[name="bank_name"]').val(selected.name);
        $('input[name="bank_prefix"]').val(selected.prefix);
        $('input[name="sepay_api_key"]').val(selected.sepay);
        
        // Tự động submit form chính
        setTimeout(() => { $('form[action*="apiurl/save"]').submit(); }, 500);
    }
}
</script>
<?= $this->endSection() ?>