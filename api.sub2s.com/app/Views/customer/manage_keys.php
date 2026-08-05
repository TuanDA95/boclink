<?= $this->extend('customer/Admin') ?>
<?= $this->section('admin_content') ?>

<style>
    .key-badge { font-family: 'Monaco', monospace; font-size: 0.85rem; }
    .card { border-radius: 15px; border: none; overflow: hidden; }
    .card-header { background: #f8f9fa !important; border-bottom: 1px solid #eee; font-weight: bold; }
    .table-hover tbody tr:hover { background-color: #f1f5f9; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid #e2e8f0; }
    .btn-update-price { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('admin/game-categories') ?>" class="btn btn-light rounded-circle me-3 shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h3 class="fw-bold mb-0 text-dark">🔑 Quản lý Key: <?= $category->title ?></h3>
            <span class="badge bg-primary rounded-pill">ID: #<?= $category->id ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="fas fa-plus-circle me-2 text-success"></i>Thêm Key Hàng Loạt</div>
                <div class="card-body">
                    <form action="<?= base_url('admin/add-keys') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="category_id" value="<?= $category->id ?>">
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label small">Time</label>
                                <input type="number" name="time_val" class="form-control" placeholder="7" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label small">Đơn vị</label>
                                <select name="time_unit" class="form-select">
                                    <option value="Giờ">Giờ</option>
                                    <option value="Ngày" selected>Ngày</option>
                                    <option value="Tháng">Tháng</option>
                                    <option value="Vĩnh viễn">Vĩnh viễn</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Giá bán (VNĐ)</label>
                            <input type="number" name="price" class="form-control text-danger" placeholder="10000"  required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Danh sách Key (Mỗi dòng 1 Key)</label>
                            <textarea name="keys_list" class="form-control" rows="6" placeholder="KEY-AAA&#10;KEY-BBB" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">Thêm vào kho</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-warning border-opacity-25">
                <div class="card-header btn-update-price"><i class="fas fa-edit me-2"></i>Sửa Giá Hàng Loạt</div>
                <div class="card-body">
                    <form action="<?= base_url('admin/update-key-price') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="category_id" value="<?= $category->id ?>">
                        <div class="mb-3">
                            <label class="form-label small">Chọn loại thời gian muốn đổi giá</label>
                            <select name="duration" class="form-select border-warning border-opacity-50">
                                <?php 
                                    $durations = array_unique(array_column($keys, 'duration'));
                                    foreach($durations as $d): 
                                ?>
                                    <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Giá mới (VNĐ)</label>
                            <input type="number" name="price" class="form-control border-warning border-opacity-50" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Cập nhật giá</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Kho Key (<?= count($keys) ?>)</span>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmDeleteAll()">
                        <i class="fas fa-trash-alt me-1"></i> Xóa tất cả
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-uppercase text-muted">
                                    <th class="ps-4">Loại Key</th>
                                    <th>Mã Key</th>
                                    <th>Đơn giá</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($keys as $k): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                                <?= $k['duration'] ?>
                                            </span>
                                        </td>
                                        <td><code class="key-badge text-primary fw-bold"><?= $k['key_code'] ?></code></td>
                                        <td class="fw-bold"><?= number_format($k['price']) ?>đ</td>
                                        <td class="text-end pe-4">
    <div class="btn-group">
        <button class="btn btn-sm btn-light text-primary rounded-circle me-1" 
                onclick='editSingleKey(<?= json_encode($k) ?>)'>
            <i class="fas fa-edit">Sửa</i>
        </button>
        <a href="<?= base_url('admin/delete-key/' . $k['id']) ?>" 
           class="btn btn-sm btn-light text-danger rounded-circle" 
           onclick="return confirm('Xóa key này?')">
            <i class="fas fa-trash-alt">Xóa</i>
        </a>
    </div>
</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editKeyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/update-single-key') ?>" method="POST" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <input type="hidden" name="key_id" id="key_id">
            
            <div class="modal-header border-0 bg-light">
                <h5 class="fw-bold mb-0"><i class="fas fa-edit me-2 text-primary"></i>Sửa Thông Tin Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body ">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Mã Key hệ thống</label>
                    <input type="text" name="key_code" id="edit_key_code" class="form-control font-monospace border-primary-subtle" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Thời gian sử dụng</label>
                    <div class="input-group">
                        <input type="number" name="time_val" id="edit_time_val" class="form-control" placeholder="Số lượng">
                        <select name="time_unit" id="edit_time_unit" class="form-select bg-light" style="max-width: 130px;">
                            <option value="Giờ">Giờ</option>
                            <option value="Ngày">Ngày</option>
                            <option value="Tháng">Tháng</option>
                            <option value="Vĩnh viễn">Vĩnh viễn</option>
                        </select>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted">Giá bán (VNĐ)</label>
                    <div class="input-group">
                        <input type="number" name="price" id="edit_price" class="form-control fw-bold text-danger" required>
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0  pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cập nhật ngay</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSingleKey(data) {
    document.getElementById('key_id').value = data.id;
    document.getElementById('edit_key_code').value = data.key_code;
    document.getElementById('edit_price').value = data.price;

    // Xử lý tách chuỗi "7 Ngày" thành số 7 và đơn vị "Ngày"
    const duration = data.duration;
    if (duration === "Vĩnh viễn") {
        document.getElementById('edit_time_val').value = "";
        document.getElementById('edit_time_unit').value = "Vĩnh viễn";
        document.getElementById('edit_time_val').disabled = true;
    } else {
        const parts = duration.split(" ");
        document.getElementById('edit_time_val').value = parts[0]; // Lấy số
        document.getElementById('edit_time_unit').value = parts[1]; // Lấy đơn vị
        document.getElementById('edit_time_val').disabled = false;
    }
    
    var editModal = new bootstrap.Modal(document.getElementById('editKeyModal'));
    editModal.show();
}

// Lắng nghe sự thay đổi của đơn vị để ẩn/hiện ô nhập số
document.getElementById('edit_time_unit').addEventListener('change', function() {
    const valInput = document.getElementById('edit_time_val');
    if (this.value === "Vĩnh viễn") {
        valInput.value = "";
        valInput.disabled = true;
    } else {
        valInput.disabled = false;
    }
});

function confirmDeleteAll() {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Hành động này sẽ xóa TOÀN BỘ key chưa bán trong danh mục này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Vâng, xóa hết!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= base_url('admin/delete-all-keys/' . $category->id) ?>";
        }
    })
}
</script>
<?= $this->endSection() ?>
