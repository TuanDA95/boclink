
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>𝐖𝐄𝐋𝐂𝐎𝐌𝐄 - Panel</title>
    
    <link href="https://key.gmvmoba.com/assets/css/natacode.css" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Start menu -->
    <style>
    :root { --side-bg: #ffffff; --side-text: #334155; --accent: #3b82f6; }
    body { background: #f8fafc; overflow-x: hidden; }

    /* Sidebar Styles */
    #main-sidebar {
        width: 280px; height: 100vh; position: fixed; left: 0; top: 0;
        background: var(--side-bg); z-index: 1050; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 15px rgba(0,0,0,0.05); border-right: 1px solid #e2e8f0;
    }
    
    #sidebar-overlay {
        display: none; position: fixed; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.4); z-index: 1040; backdrop-filter: blur(2px);
    }

    .nav-custom { padding: 20px; }
    .nav-custom .nav-link {
        display: flex; align-items: center; padding: 12px 15px;
        color: var(--side-text); font-weight: 500; border-radius: 10px; margin-bottom: 5px;
        transition: 0.2s;
    }
    .nav-custom .nav-link:hover { background: #f1f5f9; color: var(--accent); }
    .nav-custom .nav-link i { font-size: 1.2rem; margin-right: 12px; width: 25px; }
    .nav-custom .active { background: #eff6ff; color: var(--accent); }

    /* Header cho Mobile */
    .mobile-header {
        background: #fff; padding: 15px 20px; display: flex; align-items: center;
        position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    @media (max-width: 991.98px) {
        #main-sidebar { margin-left: -280px; }
        #main-sidebar.show { margin-left: 0; }
        #main-sidebar.show + #sidebar-overlay { display: block; }
    }
</style>

<div class="mobile-header d-lg-none">
    <button class="btn btn-light border-0" id="sidebarToggle">
        <i class="bi bi-list fs-3"></i>
    </button>
    <div class="ms-3 fw-bold">𝐖𝐄𝐋𝐂𝐎𝐌𝐄</div>
</div>


<div id="sidebar-overlay"></div><script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const btnToggle = document.getElementById('sidebarToggle');
        const btnClose = document.getElementById('sidebarClose');

        function toggleMenu() {
            sidebar.classList.toggle('show');
        }

        if(btnToggle) btnToggle.onclick = toggleMenu;
        if(btnClose) btnClose.onclick = toggleMenu;
        if(overlay) overlay.onclick = toggleMenu;

        // Tự động đóng khi nhấn vào các liên kết trên mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.onclick = () => {
                if(window.innerWidth < 992) sidebar.classList.remove('show');
            };
        });
    });
</script> 
<style>
    .edit-container {
        background: #f8fafc;
        min-height: calc(100vh - 60px);
        display: flex;
        align-items: center;
    }

    .glass-card {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        padding: 40px;
    }

    .form-label-custom {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 10px;
        display: block;
    }

    .input-custom {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 1rem;
        transition: all 0.3s;
        background-color: #fbfcfd;
    }

    .input-custom:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .btn-save {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-save:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
    }

    .btn-back {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .badge-info-custom {
        background: #eff6ff;
        color: #3b82f6;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>

<div class="edit-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                
                <div class="glass-card">
                    <div class="text-center mb-4">
                        <div class="d-inline-block mb-3">
                            <i class="bi bi-pencil-square text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Chỉnh sửa</h3>
                        <p class="text-muted">Code: <span class="badge-info-custom"><?= $link['code'] ?></span></p>
                    </div>

                    <form method="post" action="<?= site_url('admin/link/update/' . $link['id']) ?>">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label class="form-label-custom">Link gốc</label>
                            <textarea name="url" 
                                    class="form-control input-custom" 
                                    rows="4" 
                                    placeholder="https://example.com/...."
                                    style="word-break: break-all; resize: vertical;" 
                                    required><?= esc($link['target_url']) ?></textarea>
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle me-1"></i> Nội dung sẽ tự động xuống dòng nếu link quá dài.
                            </div>
                        </div>
                        <div class="mb-4" hidden>
                            <label class="form-label-custom">Flow</label>
                            <input type="number" name="flow" class="form-control input-custom" value="<?= esc($link['flow'] ?? 1) ?>">
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                            <a href="<?= site_url('admin/link') ?>" class="btn-back me-md-2">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" class="btn-save">
                                <i class="bi bi-check-lg me-1"></i> Lưu
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>


            <!-- End of content -->
        </div>
    </main>
    <footer class=" bg-body border-top py-3 text-muted">
        <div class="container">
            <small class="text-warning">&copy; 2025 - 𝐖𝐄𝐋𝐂𝐎𝐌𝐄</small>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.0/sweetalert2.all.min.js" integrity="sha512-0UUEaq/z58JSHpPgPv8bvdhHFRswZzxJUT9y+Kld5janc9EWgGEVGfWV1hXvIvAJ8MmsR5d4XV9lsuA90xXqUQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://key.gmvmoba.com/assets/js/natacode.js" type="text/javascript"></script>
    
</body>

</html>


