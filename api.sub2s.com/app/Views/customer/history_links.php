<div class="">
    <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>Lịch sử mua Link</h5>
    
    <div class="table-responsive shadow-sm rounded border bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <!-- <th style="width: 80px;">ID</th> -->
                    <th>Khách hàng</th>
                    <th>Mã Link</th>
                    <th>Link gốc</th>
                    <th>Giá tiền</th>
                    <th>Thời gian mua</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($links)): ?>
                    <?php foreach ($links as $link): ?>
                    <tr>
                        <!-- <td><span class="text-muted">#<?= $link['id'] ?></span></td> -->
                       <td>
                            <span class="fw-bold text-dark">
                                <?php 
                                    $db = \Config\Database::connect();
                                    $customer = $db->table('customers')->where('id', $link['customer_id'])->get()->getRow();
                                    echo esc($customer->username ?? 'Không thấy');
                                ?>
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= esc($link['code']) ?></span></td> 
                               

                        <td>
                                    <div class="input-group input-group-sm" style="max-width: 250px;">
                                        <input type="text" id="url-<?= $link['id'] ?>" class="form-control" value="<?= esc($link['target_url']) ?>" readonly>
                                        <button class="btn btn-outline-primary" onclick="copyToClipboard('url-<?= $link['id'] ?>')"><i class="bi bi-copy"></i></button>
                                    </div>
                                </td>
                        <td>
                            <span class="text-danger fw-bold">-<?= number_format($link['price']) ?>đ</span>
                        </td>
                        <td class="small text-muted">
                            <?= date('H:i d/m/Y', strtotime($link['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Chưa có giao dịch mua link nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-center custom-pagination">
    <?php if (isset($pager)): ?>
        <?= $pager->links('group_links', 'default_full') ?>
    <?php endif; ?>
</div>
</div>

<style>
    /* CSS cho phân trang giống phong cách Bootstrap chuyên nghiệp */
    .custom-pagination nav ul { display: flex; list-style: none; gap: 5px; padding: 0; }
    .custom-pagination nav ul li a, 
    .custom-pagination nav ul li span {
        display: block;
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .custom-pagination nav ul li.active span {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
    .custom-pagination nav ul li a:hover {
        background-color: #e9ecef;
    }
</style>
<script>
    function copyToClipboard(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy!', showConfirmButton: false, timer: 1500 });
}
</script>