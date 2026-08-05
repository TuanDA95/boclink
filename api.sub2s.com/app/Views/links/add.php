<?= $this->extend('Layout/Starter') ?>

<?= $this->section('content') ?>
<style>
    /* Tổng thể trang */
    .link-container { background: #f8fafc; min-height: 100vh; padding-top: 2rem; }
    
    /* Card style */
    .glass-card {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    /* Form UI */
    .form-label-custom { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px; display: block; }
    .input-custom {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 15px;
        transition: 0.3s;
    }
    .input-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    /* Table UI */
    .table-modern thead { background: #f1f5f9; }
    .table-modern th { 
        padding: 15px 20px; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        color: #475569;
        border: none;
    }
    .table-modern td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    
    /* Code Badge */
    .badge-code {
        background: #eff6ff;
        color: #1d4ed8;
        font-family: 'Monaco', monospace;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid #dbeafe;
    }

    /* Button Action */
    .btn-action {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: 0.2s;
        border: none;
    }
    .btn-copy { background: #f0fdf4; color: #16a34a; }
    .btn-copy:hover { background: #16a34a; color: #fff; }
    .btn-edit { background: #fff7ed; color: #ea580c; margin-left: 5px; }
    .btn-edit:hover { background: #ea580c; color: #fff; }

    /* Phân trang */
    .pagination-custom nav ul { display: flex; gap: 8px; justify-content: center; margin-top: 25px; list-style: none; }
    .pagination-custom li a, .pagination-custom li span {
        padding: 8px 16px; border-radius: 10px; background: #fff; color: #64748b;
        border: 1px solid #e2e8f0; text-decoration: none; transition: 0.3s;
    }
    .pagination-custom li.active span { background: #3b82f6; color: #fff; border-color: #3b82f6; }
</style>

<div class="link-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-11">
                
                <div class="glass-card p-4 p-md-5 mb-4">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <h3 class="fw-bold text-dark mb-1">Quản lý</h3>
                        </div>
                        <div class="col-md-6 text-md-end">
                             <form action="" method="get" class="d-inline-flex">
                                <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="form-control form-control-sm input-custom me-2" placeholder="Tìm mã code...">
                                <button class="btn btn-dark btn-sm px-3 rounded-3">Lọc</button>
                            </form>
                        </div>
                    </div>

                    <form method="post" action="<?= site_url('admin/link/create') ?>" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-md-3">
                            <label class="form-label-custom">Code</label>
                            <input type="text" name="prefix" placeholder="abc" class="form-control input-custom">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-custom">Target URL</label>
                            <input type="url" name="url" placeholder="https://google.com/..." class="form-control input-custom" required>
                        </div>
                        <div class="col-md-2" hidden>
                            <label class="form-label-custom">Flow</label>
                            <input type="number" name="flow" value="3" class="form-control input-custom">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i> TẠO
                            </button>
                        </div>
                    </form>
                     <div class="card border-0 bg-light shadow-sm">
            <div class="card-body">
                <label class="fw-bold text-muted mb-2">Quicklink API</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url('/st?token=0000aiddsuahwksa9999&url=')) ?>" id="getkeyLink" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey()">Copy</button>
                </div>
            </div>
        </div>
         <div class="card border-0 bg-light shadow-sm">
            <div class="card-body">
                <label class="fw-bold text-muted mb-2">Developer API</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" value="<?= esc(site_url('/devst?token=0000aiddsuahwksa9999&url=')) ?>" id="getkeyLink0" readonly>
                    <button class="btn btn-dark" type="button" onclick="copyGetKey0()">Copy</button>
                </div>
            </div>
            <p> Sử dụng phản hồi JSON (PHP)</p>
                 <pre>
    $token = '0000aiddsuahwksa9999';
    $url   = 'https://link-rut-gon.com';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.sub2s.com/devst?" . http_build_query([
            'token' => $token,
            'url'   => $url,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        echo "Lỗi kết nối";
    } else {
        $data = json_decode($response, true);
        if ($httpCode === 200 && ($data['status'] ?? '') === 'success') {
            echo $data['short_url'];
        } else {
            echo "Lỗi: " . ($data['message'] ?? 'Không rõ nguyên nhân');
        }
    }
                </pre>
        </div>
                </div>

                <div class="glass-card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Link gốc</th>
                                    <th>Mở</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($links)): ?>
                                    <?php foreach ($links as $l): ?>
                                    <tr>
                                        <td>
                                    <button onclick="copyLink('<?= site_url('key/' . $l['code']) ?>')" class="btn-action btn-copy" title="Copy link">
                                            <span class="badge-code"><?= $l['code'] ?></span>
                                            </button></td>
                                       <td>
                                            <div class="full-url-text" style="max-width: 700px;">
                                                <?= esc($l['target_url']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('key/' . $l['code']) ?>" target="_blank" class="text-decoration-none small fw-bold text-primary">
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= site_url('admin/link/edit/' . $l['id']) ?>" class="btn-action btn-edit" title="Chỉnh sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Không có dữ liệu hiển thị.</td></tr>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pagination-custom">
                    <?= $pager->links('group1', 'default_full') ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function copyGetKey() {
    const copyText = document.getElementById("getkeyLink");
    copyText.select();
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });
}
function copyGetKey0() {
    const copyText = document.getElementById("getkeyLink0");
    copyText.select();
    navigator.clipboard.writeText(copyText.value).then(() => {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã copy link!', showConfirmButton: false, timer: 1500 });
    });
}
function copyLink(text) {
    navigator.clipboard.writeText(text).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Đã copy đường dẫn!'
        });
    });
}
</script>

                                        <style>
                                            .full-url-text {
                                                font-size: 0.85rem;
                                                color: #64748b; /* Màu muted hiện đại */
                                                /* Quan trọng: Các thuộc tính giúp hiện full không bị tràn */
                                                word-break: break-all;     /* Ngắt dòng ở bất kỳ ký tự nào */
                                                white-space: normal;       /* Cho phép văn bản xuống dòng bình thường */
                                                line-height: 1.5;          /* Khoảng cách dòng để dễ đọc hơn */
                                                display: block;
                                            }
                                        </style>
<?= $this->endSection() ?>