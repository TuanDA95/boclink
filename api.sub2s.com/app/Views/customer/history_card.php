<?= $this->extend('customer/Admin') ?>
<?= $this->section('admin_content') ?>
<div class="">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-card-heading me-2 text-primary"></i>Lịch sử nạp Thẻ cào
        </h5>
    </div>
    
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle bg-white mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3">Khách hàng</th>
                    <th>Loại thẻ</th>
                    <th>Mệnh giá</th>
                    <th>Thực nhận</th>
                    <th>Mã thẻ / Seri</th>
                    <th class="text-center">Time</th>
                    <th class="text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cards)): ?>
                    <?php foreach($cards as $card): ?>
                    <tr>
                        <td>
                           <span class="d-block fw-bold text-dark">
                            <?= $card['customer_username'] ?? ($card['username'] ?? 'ID: ' . $card['customer_id']) ?>
                        </span>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3">
                                <?= strtoupper($card['telco']) ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?= number_format($card['amount_sent']) ?>đ
                        </td>
                        <td class="fw-bold text-success">
                            <?= number_format($card['amount']) ?>đ
                        </td>
                        <td>
                            <div class="bg-light p-2 rounded border small">
                                <code class="text-primary">SR: <?= esc($card['serial']) ?><br>MT: <?= esc($card['pin']) ?></code>
                            </div>
                        </td>
                        <td class="text-center small">
                            <?= ($card['created_at']) ?>
                        </td>
                        <td class="text-center">
                            <?php if($card['status'] == 1): ?>
                                <span class="badge rounded-pill bg-success px-3">Thành công</span>
                            <?php elseif($card['status'] == 2): ?>
                                <span class="badge rounded-pill bg-danger px-3">Thẻ lỗi</span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-warning text-dark px-3">Chờ xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Không có dữ liệu lịch sử nạp thẻ.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 custom-pagination">
        <?= $pager->links('card', 'default_full') ?>
    </div>
</div>

<style>
    /* Bo góc nhẹ cho table responsive */
    .table-responsive { border: 1px solid #dee2e6; }
    .table thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
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