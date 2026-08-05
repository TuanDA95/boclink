<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="alert alert-primary bg-white shadow-sm rounded-4 border-0 mb-4">
        <h5 class="fw-bold text-dark"><i class="bi bi-cpu-fill text-primary"></i>API Dylib</h5> <a href="https://github.com/binhbun/project/raw/refs/heads/main/GMV.zip">
<button class="btn btn-primary">
        💾
 Download 
    </button></a>
        <p class="text-muted small">Dán link này vào hằng số <code>API_DYLIB_SERVER</code> trong file Tweak.mm</p>
        <div class="input-group">
            <input type="text" class="form-control bg-light" id="apiUrl" value="<?= base_url('api/check-bundle/'.$user->username) ?>" readonly>
            <button class="btn btn-primary px-4" onclick="copyText()">Copy Link</button>
        </div>
         <div class="card border-0 bg-light shadow-sm">
            <div class="card-body">
                 <?php if (session('userid') === '4'): ?>
                <label class="fw-bold text-muted mb-2">Link GETKEY công khai của bạn</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url($user->username.'/getkey')) ?>" id="getkeyLink" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey()">Copy</button>
                </div>
                 <?php else: ?>
<label class="fw-bold text-muted mb-2">Link GETKEY công khai của bạn</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url($user->username.'/getkeyauto')) ?>" id="getkeyLink" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey()">Copy</button>
                </div>
                     <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0">Game</h5><?php if ($user->level == 1): ?>
                    <?php $total_devices = array_sum($active_counts ?? []); ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary fw-normal">
                        <i class="bi bi-device-stat"></i> Thiết bị hôm nay: <strong><?= number_format($total_devices) ?></strong>
                    </span>
                <?php endif; ?>
            <!-- <span class="badge bg-light text-dark border fw-normal">Tổng cộng: <?= count($list_configs) ?> mục</span> -->
        </div>
        <div class="card-body p-4">
                <div class="row mb-3">
    <div class="col-md-4">
        <form action="" method="GET">
            <div class="input-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Nhập Bundle ID game..." 
                       value="<?= esc($search ?? '') ?>">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= site_url('/admin/bundle-manager') ?>" class="btn btn-secondary">Xóa lọc</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                       <tr>
                            <th>Bundle ID</th>
                            <th>Version</th>
                            <?php if ($user->level == 1): ?>
                            <th>Hôm nay</th> 
                            <?php endif; ?>
                            <th>Trạng thái</th>
                            <th>Bảo mật</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list_configs as $item): ?>
                        <tr>
                            <td><code class="text-danger"><?= esc($item['bundle_id']) ?></code></td>
                            <td><i class="bi bi-phone"></i> <?= esc($item['last_app_version'] ?? '---') ?></td>
                            <?php if ($user->level == 1): ?>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-people-fill"></i> <?= $active_counts[$item['bundle_id']] ?? 0 ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <?php if($item['status'] == 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">Bảo trì</span>
                                <?php endif; ?>
                            </td>

                            <td>
    <?php if(isset($item['require_key']) && $item['require_key'] == 0): ?>
        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2">
            <i class="bi bi-unlock-fill"></i> FREE
        </span>
    <?php else: ?>
        <span class="badge bg-info-subtle text-info border border-info px-3 py-2">
            <i class="bi bi-shield-lock-fill"></i> BẬT KEY
        </span>
    <?php endif; ?>
</td>
                            <td class="text-end">
                                <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                    <button class="btn btn-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#edit<?= $item['id'] ?>">
                                        <i class="bi bi-sliders"></i> Sửa
                                    </button>
                                    <button class="btn btn-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#delete<?= $item['id'] ?>">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 d-flex justify-content-center">
                <?= $pager->links('default', 'default_full') ?>
            </div>
        </div>
    </div>

    <?php foreach($list_configs as $item): ?>
        <div class="modal fade" id="edit<?= $item['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="<?= site_url('admin/bundle-update') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Cấu hình: <?= esc($item['game_name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="last_app_version" value="<?= $item['last_app_version'] ?>">
                            <input type="hidden" name="version" value="<?= esc($item['version']) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-danger small">Lưu ý: Thay đổi version nếu bật bảo trì để tránh chặn bản cũ.</label>
                                <select name="status" class="form-select border-0 bg-light">
                                    <option value="1" <?= $item['status'] == 1 ? 'selected' : '' ?>>✅ Hoạt động</option>
                                    <option value="0" <?= $item['status'] == 0 ? 'selected' : '' ?>>❌ Bảo trì</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cấu hình nhập Key</label>
                                <select name="require_key" class="form-select border-0 bg-light fw-bold text-primary">
                                    <option value="1" <?= (isset($item['require_key']) && $item['require_key'] == 1) ? 'selected' : '' ?>>🔐 BẬT (Yêu cầu nhập Key)</option>
                                    <option value="0" <?= (isset($item['require_key']) && $item['require_key'] == 0) ? 'selected' : '' ?>>🔓 TẮT (Vào thẳng Menu)</option>
                                </select>
                                <div class="form-text small text-muted">Nếu chọn TẮT, người dùng sẽ không cần nhập Key và không cần lấy UDID.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Link tải bản mới (IPA/Web)</label>
                                <input type="text" name="update_link" class="form-control border-0 bg-light" value="<?= esc($item['update_link']) ?>" placeholder="https://...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nội dung thông báo</label>
                                <textarea name="message" class="form-control border-0 bg-light" rows="3"><?= esc($item['message']) ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold">Lưu Thay Đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="delete<?= $item['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 text-center">
                    <div class="modal-body p-4">
                        <div class="text-danger mb-3"><i class="bi bi-exclamation-octagon" style="font-size: 3rem;"></i></div>
                        <h5 class="fw-bold">Xóa Bundle này?</h5>
                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Hủy</button>
                            <a href="<?= site_url('admin/bundle-delete/'.$item['id']) ?>" class="btn btn-danger px-4 rounded-3">Xóa ngay</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    /* Chống bị đơ màn hình khi đóng modal trên mobile */
    body.modal-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
    }
    
    /* CSS cho Pager giữ nguyên của bạn */
    nav[aria-label="Page navigation"] ul { display: flex; padding-left: 0; list-style: none; gap: 5px; }
    nav[aria-label="Page navigation"] li a, nav[aria-label="Page navigation"] li span {
        padding: 0.375rem 0.75rem; font-size: 0.9rem; color: #0d6efd; background-color: #fff; border: 1px solid #dee2e6; border-radius: 8px; text-decoration: none;
    }
    nav[aria-label="Page navigation"] li.active span { color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
</style>
<script>
function copyGetKey() {
    const copyText = document.getElementById("getkeyLink");
    copyText.select();
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });
}
function copyText() {

    var copyText = document.getElementById("apiUrl");

    copyText.select();

     navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });

}

</script>

<?= $this->endSection() ?>