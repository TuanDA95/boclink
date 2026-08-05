<?= $this->extend('customer/Admin') ?>
<?= $this->section('admin_content') ?>
<div class="">
    <!-- <h5 class="fw-bold mb-4">Lịch sử nạp ATM/BANK (Duyệt thủ công)</h5> -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle bg-white border">
            <thead class="table-dark">
                <tr>
                    <th>Khách hàng</th>
                    <th>Số tiền</th>
                    <th>Nội dung</th>
                    <th>Thời gian</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($banks as $b): ?>
                <tr>
                    <td><span class="d-block fw-bold"><?= esc($b['customer_username'] ?? 'Không rõ') ?></span></td>
                   <td class="fw-bold">
            <?php if($b['type'] == 'CRYPTO'): ?>
                <div class="text-warning">$<?= number_format($b['amount_sent'], 2) ?></div>
                <div class="small text-muted" style="font-size: 0.75rem;">≈ <?= number_format($b['amount']) ?>đ</div>
            <?php else: ?>
                <div class="text-primary"><?= number_format($b['amount']) ?>đ</div>
            <?php endif; ?>
        </td> <td><code class="text-danger fw-bold"><?= $b['code'] ?></code></td>
                    <td class="small text-muted"><?= date('H:i d/m/Y', strtotime($b['created_at'])) ?></td>
                    <td class="text-center">
                        <?= ($b['status'] == 1) ? '<span class="badge bg-success">Thành công</span>' : '<span class="badge bg-warning text-dark">Chờ duyệt</span>' ?>
                    </td>
                   <td class="text-center">
                            <?php if($b['status'] != 1): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-primary shadow-sm px-3" 
                                        onclick="confirmApprove('<?= $b['id'] ?>', '<?= number_format($b['amount']) ?>', '<?= esc($b['customer_username']) ?>')">
                                    <i class="bi bi-check2-circle"></i> Phê duyệt
                                </button>
                            <?php elseif($b['status'] == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-3">
                                    <i class="bi bi-check-all"></i>thanh toán
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 text-white">Đã hủy/Lỗi</span>
                            <?php endif; ?>
                        </td>

                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                        function confirmApprove(id, amount, username) {
                            Swal.fire({
                                title: 'Xác nhận phê duyệt?',
                                html: `Bạn đang thực hiện cộng <b>${amount}đ</b> cho tài khoản <b>${username}</b>.<br><small class="text-danger">Hành động này không thể hoàn tác!</small>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#0d6efd',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Đúng, phê duyệt ngay!',
                                cancelButtonText: 'Hủy'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?= base_url('admin/approve-bank/') ?>/" + id;
                                }
                            })
                        }
                        </script>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 custom-pagination">
        <?= $pager->links('bank', 'default_full') ?>
    </div>
</div>

<style>
    /* Làm đẹp số trang phân trang */
    .custom-pagination ul { display: flex; list-style: none; padding: 0; justify-content: center; gap: 5px; }
    .custom-pagination li a { 
        padding: 8px 16px; border: 1px solid #dee2e6; color: #0d6efd; 
        text-decoration: none; border-radius: 5px; transition: 0.3s;
    }
    .custom-pagination li.active a { background: #0d6efd; color: white; border-color: #0d6efd; }
    .custom-pagination li a:hover:not(.active) { background: #f8f9fa; }
</style>
<?= $this->endSection() ?>