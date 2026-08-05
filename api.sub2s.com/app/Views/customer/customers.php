<?= $this->extend('customer/Admin') ?>
<?= $this->section('admin_content') ?>
<div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <form action="" method="get" class="d-flex w-100">
        <input type="text" name="search" value="<?= esc($search ?? '') ?>" class="form-control me-2" placeholder="Tìm username...">
        <button class="btn btn-primary btn-sm">Tìm</button>
    </form>
</div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Số dư</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td><strong><?= esc($c['username']) ?></strong></td>
                    <td class="text-success fw-bold"><?= number_format($c['balance']) ?>đ</td>
                    <td class="text-end">
                        <button onclick="handleMoney(<?= $c['id'] ?>, 'add', '<?= esc($c['username']) ?>')" class="btn btn-sm btn-success" title="Cộng tiền"><i class="bi bi-plus-lg"></i></button>
                        <button onclick="handleMoney(<?= $c['id'] ?>, 'sub', '<?= esc($c['username']) ?>')" class="btn btn-sm btn-danger" title="Trừ tiền"><i class="bi bi-dash-lg"></i></button>
                        <button onclick="handleChangePass(<?= $c['id'] ?>, '<?= esc($c['username']) ?>')" class="btn btn-sm btn-warning" title="Đổi mật khẩu"><i class="bi bi-key-fill"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3 custom-pagination">
        <?= $pager->links('cust', 'default_full') ?>
    </div>
</div>

<script>
async function handleMoney(id, type, name) {
    const actionText = (type === 'add') ? "Cộng tiền" : "Trừ tiền";
    const color = (type === 'add') ? "#198754" : "#dc3545";

    const { value: amount } = await Swal.fire({
        title: actionText,
        text: `Nhập số tiền muốn xử lý cho [${name}]`,
        input: 'number',
        inputAttributes: {
            min: 1,
            step: 1
        },
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: color,
        inputValidator: (value) => {
            if (!value || isNaN(value) || value <= 0) {
                return 'Vui lòng nhập số tiền hợp lệ!'
            }
        }
    });

    if (amount) {
        postData('<?= base_url('admin/customers/update-balance') ?>', { 
            id: id, 
            amount: amount, 
            type: type 
        });
    }
}

async function handleChangePass(id, name) {
    const { value: newPass } = await Swal.fire({
        title: 'Đổi mật khẩu',
        text: `Thiết lập mật khẩu mới cho [${name}]`,
        input: 'password',
        inputPlaceholder: 'Nhập mật khẩu mới...',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off',
            minlength: 6
        },
        showCancelButton: true,
        confirmButtonText: 'Thay đổi',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#ffc107',
        inputValidator: (value) => {
            if (!value || value.length < 6) {
                return 'Mật khẩu phải có ít nhất 6 ký tự!'
            }
        }
    });

    if (newPass) {
        postData('<?= base_url('admin/customers/change-password') ?>', { 
            id: id, 
            password: newPass 
        });
    }
}

// Giữ nguyên hàm postData của bạn nhưng thêm thông báo chờ
function postData(url, data) {
    Swal.fire({
        title: 'Đang xử lý...',
        didOpen: () => { Swal.showLoading() },
        allowOutsideClick: false
    });

    let form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    
    let csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    
    if(csrfName) {
        let csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = csrfName;
        csrfInput.value = csrfHash;
        form.appendChild(csrfInput);
    }

    for (let key in data) {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}
</script>
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