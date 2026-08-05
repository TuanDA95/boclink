<?= $this->extend('Layout/Starter') ?>
<?= $this->section('css') ?>
<?= link_tag("https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css") ?>
<style>
    /* Card trang trí */
    .card { border-radius: 15px; overflow: hidden; }
    .card-generate { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: none; background: #fdfdfd; }
    
    /* Nút bấm hiện đại */
    .btn { border-radius: 8px; font-weight: 600; transition: all 0.3s; }
    .btn-create-key { background: linear-gradient(45deg, #198754, #20c997); border: none; color: white; }
    .btn-delete-expired { background: transparent; border: 1px solid #ff4d4d; color: #ff4d4d !important; }
    .btn-delete-expired:hover { background: #ff4d4d; color: white !important; }
.text-device-active { color: #2ecc71; font-weight: bold; text-shadow: 0 0 5px rgba(46, 204, 113, 0.2); }
.btn-toggle-status { width: 32px; height: 32px; padding: 0; line-height: 1; border-radius: 50%; }
    /* Key Click Copy Style */
    .key-clickable {
        background: #f1f3f5; padding: 5px 12px; border-radius: 6px; border: 1px dashed #adb5bd;
        font-family: 'Segoe UI Mono', 'Consolas', monospace; color: #0d6efd;
        cursor: pointer; transition: 0.2s; display: inline-block;
    }
    .key-clickable:hover { background: #0d6efd; color: white; border-style: solid; transform: translateY(-2px); }
/* Màu dành cho Key và Thiết bị khi có người đang dùng */
.key-active-using { 
    color: #198754 !important; 
    font-weight: bold; 
    border-color: #198754 !important; /* Đổi màu viền nét đứt sang xanh */
    background: #e8f5e9 !important; /* Nền xanh nhạt cực nhẹ */
}

/* Hiệu ứng cho cột thiết bị */
.text-device-active { 
    color: #198754 !important; 
    font-weight: 800; 
}
    /* Badge trạng thái */
    .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; }
    
    /* Hiệu ứng bảng */
    #datatable tr { transition: all 0.2s; }
    #datatable tr:hover { background-color: rgba(13, 110, 253, 0.05) !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">
    <?= $this->include('Layout/msgStatus') ?>

    <?php if (session()->getFlashdata('generated_keys')) : ?>
        <div class="alert alert-light border-success shadow-sm p-3 mb-4">
            <h6 class="text-success fw-bold"><i class="bi bi-check-circle"></i>Key tạo thành công:</h6>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php foreach(session()->getFlashdata('generated_keys') as $key): ?>
                    <span class="key-clickable" onclick="copySimple('<?= $key ?>')"><?= $key ?></span>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-sm btn-success" onclick="copyAllKeys()">Copy tất cả</button>
        </div>
    <?php endif; ?>

    <div id="formGenerate" class="card card-generate mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Tạo Key</h6>
            <button type="button" class="btn-close btn-close-white" onclick="toggleForm()"></button>
        </div>
        <div class="card-body p-3">
             <?= form_open('keys/generate_custom_action') ?>
              <div class="row mt-2">
                <div class="row">
                    <div class="col-md-5 mb-2"><label class="small fw-bold">Tiền tố</label><input type="text" name="prefix" class="form-control form-control-sm" placeholder="KEYVIP"></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold">Số lượng</label><input type="number" name="quantity" class="form-control form-control-sm" value="1"></div>
                    <div class="col-md-4 mb-2" hidden><label class="small fw-bold">Game</label><?= form_dropdown(['class' => 'form-select form-select-sm', 'name' => 'game'], $game_list, '') ?></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold">Số máy</label><input type="number" name="max_devices" class="form-control form-control-sm" value="1"></div>
                </div>
                <div class="row mt-2">
                <div class="col-md-12">
                    <label class="small fw-bold d-block mb-2">Thời gian nhanh:</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(5, 'hour')">5 Giờ</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(10, 'hour')">10 Giờ</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(12, 'hour')">12 Giờ</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(7, 'day')">Tuần</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(1, 'month')">Tháng</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-quick" onclick="setQuickTime(1, 'year')">Năm</button>
                </div>
                <div class="col-md-4 mt-2">
                    <div class="input-group input-group-sm">
                        <input type="number" name="duration_val" id="d_val" class="form-control" value="1">
                        <select name="duration_unit" id="d_unit" class="form-select">
                            <option value="hour">Giờ</option>
                            <option value="day" selected>Ngày</option>
                            <option value="month">Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-5 mt-2 d-grid">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Tạo Key</button>
                </div>
            </div>
                </div>
             <?= form_close() ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">

        <div class="d-flex justify-content-between align-items-center">
            
            <div style="margin: 5px 10px 5px 20px;">
            <!-- <h5 class="mb-0 fw-bold text-dark d-none d-md-block align-items-center">QUẢN LÝ KEY</h5> -->
                <button class="btn btn-create-key px-4 shadow-sm" onclick="toggleForm()">
                    <i class="bi bi-plus-circle me-1"></i> Tạo Key
                </button>
            </div><div style="margin: 5px 10px 5px 20px;">
<span class="d-block text-secondary small fw-bold">Hôm nay: <span class="h5 mb-0 fw-bold"><?= number_format($created_today) ?>/<span class="h5 mb-0 fw-bold text-success"><?= number_format($activated_today) ?></span></span></span>
                
        </div>
        </div>
<div class="alert alert-secondary border-0 shadow-sm d-flex flex-wrap gap-2 align-items-center mb-3">
    <div class="fw-bold text-dark me-2"><i class="bi bi-gear-fill"></i> Công cụ hàng loạt:</div>
    
    <button class="btn btn-sm btn-warning fw-bold" onclick="resetAllDevices()">
        <i class="bi bi-cpu"></i> Reset All Devices
    </button>

    <div class="vr mx-2"></div>

    <div class="input-group input-group-sm" style="width: auto;">
        <span class="input-group-text bg-primary text-white">Gia hạn All</span>
        <input type="number" id="extra_val" class="form-control" style="width: 70px;" value="1">
        <select id="extra_unit" class="form-select" style="width: 100px;">
            <option value="hour">Giờ</option>
            <option value="day" selected>Ngày</option>
            <option value="month">Tháng</option>
        </select>
        <button class="btn btn-primary" onclick="updateAllExpiry()">Xác nhận</button>
    </div>

    <div>
                <button class="btn btn-sm btn-delete-expired shadow-sm" onclick="deleteAllExpired()">
                    <i class="bi bi-trash3-fill me-1"></i> Dọn dẹp Key hết hạn
                </button>
            </div>
</div>
        
    </div>
            
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable" class="table table-hover align-middle text-center" style="width:100%">
                <thead class="bg-light">
                    <tr>
                        <th class="text-secondary small">ID</th>
                        <!-- <th>GAME</th> -->
                        <th>MÃ KEY</th>
                        <th>THIẾT BỊ</th>
                        <th>THỜI HẠN</th>
                        <th>NGÀY HẾT HẠN</th>
                        <th>THAO TÁC</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js") ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js") ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleForm() { $('#formGenerate').slideToggle(); }
function setQuickTime(val, unit) {
        $('#d_val').val(val);
        $('#d_unit').val(unit);
        $('.btn-quick').removeClass('btn-primary text-white').addClass('btn-outline-secondary');
        $(event.target).removeClass('btn-outline-secondary').addClass('btn-primary text-white');
    }
$(document).ready(function() {
    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, "desc"]],
        ajax: "<?= site_url('keys/api') ?>",
        columns: [
            { data: 'id', name: 'id_keys' },
            // { data: 'game' },
            {
                data: 'user_key',
                render: function(data, type, row) {
                    var keyDisplay = data ? data : '&mdash;';
                    var totalDevice = (row.devices ? parseInt(row.devices) : 0);
                    
                    // Nếu có thiết bị dùng (>0), thêm class 'key-active-using'
                    var activeClass = (totalDevice > 0) ? 'key-active-using' : '';
                    
                    return `<span class="key-clickable ${activeClass}" onclick="copySimple('${keyDisplay}')" title="Click để copy">${keyDisplay}</span>`;
                }
            },
            {
                data: 'devices',
                render: function(data, type, row) {
                    var totalDevice = (row.devices ? parseInt(row.devices) : 0);
                    // Nếu có thiết bị dùng (>0), thêm class 'text-device-active'
                    var activeClass = (totalDevice > 0) ? 'text-device-active' : '';
                    
                    return `<span id="devMax-${row.user_key}" class="${activeClass}">${totalDevice}/${row.max_devices}</span>`;
                }
            },
            { data: 'duration' },
            {
                data: 'expired',
                render: function(data, type, row) {
                    if (!data || data === '0000-00-00 00:00:00') return '<span class="badge bg-light text-dark border">Chưa kích hoạt</span>';
                    var badgeClass = (new Date(data) < new Date()) ? 'bg-danger' : 'bg-success';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: null,
                render: function(data, type, row, meta) {
                    var btnReset = `<button class="btn btn-outline-danger btn-sm" onclick="resetUserKey('${row.user_key}')" title="Reset key?"><i class="bi bi-bootstrap-reboot"></i></button>`;
                    var btnEdits = `<a href="${window.location.origin}/keys/${row.id}" class="btn btn-outline-dark btn-sm" title="Edit?"><i class="bi bi-person"></i></a>`;
                    return `<div class="d-grid gap-2 d-md-block">${btnReset} ${btnEdits}</div>`;
                }
            }
        ]
    });
});
function copyAllKeys() {
    let keys = [];
    // Ưu tiên lấy từ khu vực thông báo vừa tạo xong
    $('.alert .key-clickable').each(function() {
        keys.push($(this).text().trim());
    });

    // Nếu không có thông báo, lấy toàn bộ key đang hiện ở trang hiện tại của bảng
    if (keys.length === 0) {
        $('#datatable .key-clickable').each(function() {
            let txt = $(this).text().trim();
            if (txt !== '&mdash;') keys.push(txt);
        });
    }
    
    if (keys.length > 0) {
        navigator.clipboard.writeText(keys.join('\n'));
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: `Đã copy ${keys.length} key.`,
            timer: 1000,
            showConfirmButton: false
        });
    } else {
        Swal.fire('Lỗi', 'Không tìm thấy key nào để copy!', 'error');
    }
}
// Hàm xử lý xóa tất cả key hết hạn
function deleteAllExpired() {
    Swal.fire({
        title: 'Xóa toàn bộ key hết hạn?',
        text: "Hành động này sẽ xóa vĩnh viễn các key đã quá hạn!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Xác nhận xóa'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("<?= site_url('keys/delete_expired') ?>", function(response) {
                $('#datatable').DataTable().ajax.reload();
                Swal.fire('Đã xóa!', 'Danh sách key đã được dọn dẹp.', 'success');
            });
        }
    });
}

// Hàm Reset All Devices
function resetAllDevices() {
    Swal.fire({
        title: 'Reset toàn bộ thiết bị?',
        text: "Tất cả key sẽ được trống thiết bị để đăng nhập lại!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        confirmButtonText: 'Đúng, Reset hết!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("<?= site_url('keys/reset_all_devices') ?>", function(res) {
                $('#datatable').DataTable().ajax.reload();
                Swal.fire('Thành công!', res.msg, 'success');
            });
        }
    });
}

// Hàm Gia hạn All Key
function updateAllExpiry() {
    let val = $('#extra_val').val();
    let unit = $('#extra_unit').val();

    Swal.fire({
        title: 'Gia hạn hàng loạt?',
        text: `Cộng thêm ${val} ${unit} vào TOÀN BỘ Key của bạn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận gia hạn'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("<?= site_url('keys/update_all_expiry') ?>", { val: val, unit: unit }, function(res) {
                $('#datatable').DataTable().ajax.reload();
                Swal.fire('Đã cập nhật!', res.msg, 'success');
            });
        }
    });
}
// Giữ nguyên hàm copy của bạn
function copySimple(text) {
    if(text === '&mdash;') return;
    navigator.clipboard.writeText(text);
    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1000 });
    Toast.fire({ icon: 'success', title: 'Đã copy!' });
}

     function resetUserKey(keys) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reset'
        }).then((result) => {
            if (result.isConfirmed) {
                Toast.fire({
                    icon: 'info',
                    title: 'Please wait...'
                })

                var base_url = window.location.origin;
                var api_url = `${base_url}/keys/reset`;
                $.getJSON(api_url, {
                        userkey: keys,
                        reset: 1
                    },
                    function(data, textStatus, jqXHR) {
                        if (textStatus == 'success') {
                            if (data.registered) {
                                if (data.reset) {
                                    $(`#devMax-${keys}`).html(`0/${data.devices_max}`);
                                    Swal.fire(
                                        'Reset!',
                                        'Your device key has been reset.',
                                        'success'
                                    )
                                } else {
                                    Swal.fire(
                                        'Failed!',
                                        data.devices_total ? "You don't have any access to this user." : "User key devices already reset.",
                                        data.devices_total ? 'error' : 'warning'
                                    )
                                }
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    "User key no longer exists.",
                                    'error'
                                )
                            }
                        }
                    }
                );
            }
        });
    }
</script>
<?= $this->endSection() ?>