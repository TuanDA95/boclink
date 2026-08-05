<?= $this->extend('customer/Admin') ?>
<?= $this->section('admin_content') ?>

<style>
    /* Sort Form Styles */
    .sort-form-wrapper {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), 
                    opacity 0.3s ease,
                    margin 0.3s ease;
        opacity: 0;
        margin-top: 0;
    }
    
    .sort-form-wrapper.show {
        max-height: 800px;
        opacity: 1;
        margin-top: 20px;
    }
    
    .sort-form-wrapper .table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .sort-form-wrapper .table thead {
        background: #f8f9fa;
    }
    
    .sort-form-wrapper .table th {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .sort-form-wrapper .table td {
        vertical-align: middle;
        padding: 12px 16px;
    }
    
    .sort-form-wrapper .form-control-sm {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        text-align: center;
        font-weight: 600;
    }
    
    .sort-form-wrapper .form-control-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .sort-form-wrapper .btn-primary {
        border-radius: 10px;
        padding: 10px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .sort-form-wrapper .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    
    .sort-icon {
        display: inline-block;
        transition: transform 0.3s ease;
    }
    
    .sort-icon.rotated {
        transform: rotate(180deg);
    }
    
    .nav-pills .nav-link {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #e9ecef;
        transform: translateY(-2px);
    }
</style>
<style>
    /* Custom Styles */
    .game-card { border-radius: 15px; transition: all 0.3s ease; border: none; overflow: hidden; }
    .game-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .card-img-container { position: relative; height: 180px; }
    .card-img-top { height: 100%; object-fit: cover; }
    .status-badge { position: absolute; top: 10px; right: 10px; backdrop-filter: blur(5px); }
    .btn-action { border-radius: 8px; font-weight: 600; transition: all 0.2s; }
    .stock-label { background: #f0f7ff; padding: 5px 12px; border-radius: 8px; display: inline-block; }
    
    /* Style cho Tab Navigation */
    .nav-pills .nav-link { color: #6c757d; border-radius: 10px; padding: 12px 20px; font-weight: 600; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2); }
    /* Custom Pagination Styling */
.pagination {
    gap: 5px; /* Khoảng cách giữa các con số */
}

.pagination .page-item .page-link {
    border: none;
    border-radius: 8px !important;
    color: #495057;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s;
    background: #f8f9fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
}

.pagination .page-item .page-link:hover:not(.active) {
    background-color: #e9ecef;
    transform: translateY(-2px);
}

.pagination .page-item.disabled .page-link {
    background: transparent;
    color: #ced4da;
}
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
       
        <div class="col-md-5 text-md-end">
            <ul class="nav nav-pills justify-content-md-end" id="adminTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active me-2" id="game-tab" data-bs-toggle="tab" data-bs-target="#game-pane" type="button" role="tab">
                        <i class="fas fa-gamepad me-1"></i> Danh mục
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
                        <i class="fas fa-history me-1"></i> Lịch sử bán
                    </button>
                </li>
                 <li class="nav-item" role="presentation">
            <button class="nav-link" id="sort-tab" type="button" onclick="toggleSortForm()">
                <i class="fas fa-sort me-1 sort-icon" id="sortIcon"></i> Sắp xếp
            </button>
        </li>
            </ul>
        </div>
    </div>


    <div class="sort-form-wrapper" id="sortFormWrapper">
        <form action="<?= base_url('admin/update-sort-order') ?>" method="POST" id="sortOrderForm">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 100px;">STT</th>
                            <th>Tên danh mục</th>
                            <th style="width: 150px;">Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $index => $cat): ?>
                            <tr>
                                <td>
                                    <input type="number" 
                                           name="sort_order[<?= $cat['id'] ?>]" 
                                           value="<?= $cat['sort_order'] ?? $index + 1 ?>" 
                                           class="form-control form-control-sm" 
                                           style="width: 70px;" 
                                           min="1" 
                                           max="<?= count($categories) ?>" 
                                           required>
                                </td>
                                <td>
                                    <strong><?= esc($cat['title']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= number_format($cat['stock_count'] ?? 0) ?> Key</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="toggleSortForm()">
                    <i class="fas fa-times me-1"></i> Đóng
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Cập nhật thứ tự
                </button>
            </div>
        </form>
        <br>
    </div>


    <div class="tab-content" id="adminTabContent">
        <div class="tab-pane fade show active" id="game-pane" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Game</h5>
                <button class="btn btn-primary btn-sm btn-action shadow" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus-circle me-1"></i> Thêm mới
                </button>
            </div>


            <div class="row">
                <?php foreach ($categories as $cat): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card game-card shadow-sm h-100">
                            <div class="card-img-container">
                                <img src="<?= base_url('public/uploads/' . $cat['image']) ?>" class="card-img-top" alt="<?= $cat['title'] ?>">
                                <span class="badge status-badge <?= $cat['status'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $cat['status'] == 1 ? 'Hiển thị' : 'Ẩn' ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="fw-bold mb-2 text-truncate"><?= $cat['title'] ?></h5>
                                <div class="stock-label mb-3 w-100">
                                    <small class="text-muted d-block">Tồn kho:</small>
                                    <span class="fw-bold text-primary fs-5"><?= number_format($cat['stock_count']) ?></span> <small>Key</small>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="<?= base_url('admin/manage-keys/' . $cat['id']) ?>" class="btn btn-primary btn-sm btn-action">Quản lý kho Key</a>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm flex-grow-1" onclick="editCat(<?= htmlspecialchars(json_encode($cat)) ?>)">Sửa</button>
                                        <a href="<?= base_url('admin/delete-category/' . $cat['id']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa danh mục?')"><i class="fas fa-trash">Xóa</i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
       
        <div class="tab-pane fade" id="history-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark">Lịch sử bán Key</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Khách hàng</th>
                                <th>Mã Key</th>
                                <th>Thời hạn</th>
                                <th>Game</th>
                                <th class="ps-4">Ngày giao dịch</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales_history)): ?>
                                <?php foreach ($sales_history as $row): ?>
                                <tr> <td><span class="badge bg-light text-dark border fw-normal"><?= esc($row['customer_name'] ?? 'Ẩn danh') ?></span></td>
                                    <td><code class="text-danger fw-bold"><?= esc($row['key_code']) ?></code></td>
                                  <td>
                                   <small class="text-muted"><?= $row['duration'] ?></small>
                                    </td> <td>
                                        <div class="fw-bold"><?= esc($row['game_title'] ?? 'N/A') ?></div>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <div class="fw-bold small"><?= date('d/m/Y', strtotime($row['sold_at'])) ?></div>
                                        <div class="text-muted small"><?= date('H:i', strtotime($row['sold_at'])) ?></div>
                                    </td>
                                    
                                    <td class="fw-bold text-success"><?= number_format($row['price']) ?>đ</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5">Không có dữ liệu giao dịch.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($history_pager)): ?>
                    <div class="card-footer bg-white d-flex justify-content-center py-3 border-0">
                        <?= $history_pager ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/edit-category') ?>" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h5 class="fw-bold">Chỉnh sửa Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <img id="edit_preview" src="" class="rounded shadow-sm mb-2" style="max-height: 100px;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tiêu đề Game</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Thay đổi ảnh (Bỏ trống nếu giữ cũ)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Link File / Link Tải</label>
                    <input type="url" name="file_link" id="edit_file_link" class="form-control" placeholder="https://example.com/file.zip">
                    <div class="form-text small text-muted">Dán link tải công cụ hoặc hướng dẫn tại đây.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="1">Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>
            </div>
            <div class="p-3">
                <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/add-category') ?>" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="fw-bold mb-0">Thêm Danh mục Game mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề Game</label>
                    <input type="text" name="title" class="form-control" placeholder="Ví dụ: PUBG Mobile, Valorant..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh minh họa</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <div class="form-text text-muted small">Nên sử dụng ảnh tỉ lệ 16:9 để hiển thị đẹp nhất.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Link File / Link Tải</label>
                    <input type="url" name="file_link" class="form-control" placeholder="https://example.com/file.zip">
                    <div class="form-text small text-muted">Dán link tải công cụ hoặc hướng dẫn tại đây.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái ban đầu</label>
                    <select name="status" class="form-select">
                        <option value="1">Hiển thị</option>
                        <option value="0">Tạm ẩn</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>
<script>
function editCat(data) {
    const modalElement = document.getElementById('editCategoryModal'); 
    if(!modalElement) {
        alert("Lỗi: Không tìm thấy Modal sửa!");
        return;
    }

    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_title').value = data.title;
    document.getElementById('edit_status').value = data.status;
    document.getElementById('edit_file_link').value = data.file_link || '';
    document.getElementById('edit_preview').src = "<?= base_url('public/uploads/') ?>/" + data.image;

    var myModal = new bootstrap.Modal(modalElement);
    myModal.show();
}
</script>
<script>

// Giữ Tab khi chuyển trang
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('page_history')) {
        const historyTab = new bootstrap.Tab(document.querySelector('#history-tab'));
        historyTab.show();
    }
});
</script>
<script>
// Toggle Sort Form
function toggleSortForm() {
    const wrapper = document.getElementById('sortFormWrapper');
    const icon = document.getElementById('sortIcon');
    const sortTab = document.getElementById('sort-tab');
    
    wrapper.classList.toggle('show');
    icon.classList.toggle('rotated');
    sortTab.classList.toggle('active');
    
    // Scroll đến form nếu đang hiển thị
    if (wrapper.classList.contains('show')) {
        setTimeout(() => {
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
}

// Auto close form khi submit thành công
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sortOrderForm');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang cập nhật...';
            btn.disabled = true;
        });
    }
});
</script>
<?= $this->endSection() ?>