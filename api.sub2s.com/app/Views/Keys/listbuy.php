<?= $this->extend('Layout/Starter') ?>

<?= $this->section('css') ?>
<?= link_tag("https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css") ?>
<style>
    /* Làm mờ key cho đến khi click hoặc bấm nút hiện */
    .key-sensi {
        filter: blur(5px);
        transition: all 0.3s ease;
        cursor: pointer;
        user-select: none;
    }
    .key-visible { filter: blur(0) !important; user-select: text !important; }
    
    /* Hiển thị mã key chuyên nghiệp */
    .key-wrapper {
        background: #f1f3f5;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px dashed #adb5bd;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
        color: #d63384;
    }
    .key-wrapper:hover { border-color: #0d6efd; background: #e7f1ff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-12">
        <?= $this->include('Layout/msgStatus') ?>
        
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-list-stars me-2"></i>My Generated Keys</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-light" id="btn-toggle-blur">
                        <i class="bi bi-eye"></i> Hiện Key
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover align-middle text-center" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Game</th>
                                <th>Mã Key (Bấm để Copy)</th>
                                <th>Thiết bị</th>
                                <th>Thời hạn (Giờ)</th>
                                <th>Hết hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js") ?>
<?= script_tag("https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js") ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        var isAllVisible = false;

        var table = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, "desc"]],
            ajax: {
                url: "<?= site_url('keys/api') ?>",
                // Gửi thêm params để lọc chỉ lấy key của chính mình
                data: function(d) {
                    d.filter_user = "<?= $user->username ?>"; 
                }
            },
            columns: [
                { data: 'id' },
                { data: 'game', render: d => `<span class="badge bg-primary">${d}</span>` },
                {
                    data: 'user_key',
                    render: function(data) {
                        return `
                            <div class="key-wrapper" onclick="copyToClipboard('${data}', this)">
                                <span class="key-sensi ${isAllVisible ? 'key-visible' : ''}">${data}</span>
                                <i class="bi bi-clipboard text-muted"></i>
                            </div>`;
                    }
                },
                {
                    data: 'devices',
                    render: (data, t, row) => `<span id="devMax-${row.user_key}">${data || 0}/${row.max_devices}</span>`
                },
                { data: 'duration' },
                {
                    data: 'expired',
                    render: d => d ? `<span class="small">${d}</span>` : `<i class="text-muted">Chưa dùng</i>`
                },
                {
                    data: null,
                    render: function(data, t, row) {
                        return `
                            <button class="btn btn-sm btn-outline-danger" onclick="resetUserKey('${row.user_key}')"><i class="bi bi-arrow-clockwise"></i></button>
                            <a href="<?= site_url('keys') ?>/${row.id}" class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i></a>
                        `;
                    }
                }
            ]
        });

        // Nút bật/tắt hiển thị toàn bộ key
        $("#btn-toggle-blur").click(function() {
            isAllVisible = !isAllVisible;
            $(".key-sensi").toggleClass("key-visible", isAllVisible);
            $(this).html(isAllVisible ? '<i class="bi bi-eye-slash"></i> Ẩn Key' : '<i class="bi bi-eye"></i> Hiện Key');
        });
    });

    // Hàm copy và thông báo
    function copyToClipboard(text, el) {
        navigator.clipboard.writeText(text).then(() => {
            const $icon = $(el).find('i');
            $icon.removeClass('bi-clipboard text-muted').addClass('bi-check2-all text-success');
            
            // Alert nhỏ ở góc
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1000
            });
            Toast.fire({ icon: 'success', title: 'Đã copy mã key!' });

            setTimeout(() => {
                $icon.addClass('bi-clipboard text-muted').removeClass('bi-check2-all text-success');
            }, 1500);
        });
    }

    // Giữ nguyên hàm reset của bạn nhưng cập nhật thông báo tiếng Việt cho thân thiện
    function resetUserKey(keys) {
        Swal.fire({
            title: 'Reset thiết bị?',
            text: "Key này sẽ dùng được trên máy mới!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Đồng ý'
        }).then((result) => {
            if (result.isConfirmed) {
                $.getJSON(`<?= site_url('keys/reset') ?>`, { userkey: keys, reset: 1 }, function(data) {
                    if (data.reset) {
                        $(`#devMax-${keys}`).html(`0/${data.devices_max}`);
                        Swal.fire('Đã xong!', 'Thiết bị đã được làm mới.', 'success');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>