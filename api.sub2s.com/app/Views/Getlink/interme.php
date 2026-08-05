<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Get Link - <?= esc($link['code']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;700;800&display=swap" rel="stylesheet">
 <style>
    /* Tổng thể nền với hiệu ứng gradient sâu hơn */
    body { 
        background: radial-gradient(circle at top right, #2d1b4e, #16161a); 
        color: #e0e0e0; 
        font-family: 'Lexend', sans-serif; 
        display: flex; 
        align-items: center; 
        min-height: 100vh;
        margin: 0;
    }

    /* Card chính với hiệu ứng Neon */
    .card-gaming { 
        background: rgba(22, 22, 26, 0.95); 
        border: 1px solid rgba(26, 213, 250, 0.3); 
        border-radius: 24px; 
        padding: 40px 30px; 
        width: 100%; 
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 20px rgba(26, 213, 250, 0.1);
        backdrop-filter: blur(10px);
    }

    /* Hiệu ứng chữ Glitch nhẹ */
    .title-glitch { 
        font-weight: 800; 
        letter-spacing: 2px;
        background: linear-gradient(to right, #1ad5fa, #667eea);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
    }

    /* Box lựa chọn - Glassmorphism style */
    .option-box { 
        background: rgba(255, 255, 255, 0.02); 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        border-radius: 20px; 
        padding: 30px 20px; 
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        height: 100%; 
        display: flex; 
        flex-direction: column; 
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .option-box:hover { 
        transform: translateY(-10px); 
        background: rgba(26, 213, 250, 0.04);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .option-box.border-warning:hover {
        border-color: #ffc107 !important;
        background: rgba(255, 193, 7, 0.04);
    }
    
    .option-box.border-info:hover {
        border-color: #1ad5fa !important;
    }

    /* Button thiết kế lại */
    .btn-custom { 
        width: 100%; 
        padding: 14px; 
        font-weight: 700; 
        border-radius: 12px; 
        text-transform: uppercase; 
        transition: 0.3s;
        border: none;
        letter-spacing: 1px;
    }

    /* Button Mua - Vàng Neon */
    .btn-buy { 
        background: linear-gradient(45deg, #ffc107, #ff9800); 
        color: #000; 
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }
    
    .btn-buy:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.5);
        filter: brightness(1.1);
    }

    /* Button Free - Xanh Neon */
    .btn-free { 
        background: transparent; 
        border: 2px solid #1ad5fa; 
        color: #1ad5fa; 
    }

    .btn-free:hover { 
        background: #1ad5fa; 
        color: #000;
        box-shadow: 0 0 20px rgba(26, 213, 250, 0.4);
    }

    /* Badge cho số lớp/giá */
    .price-tag {
        font-size: 1.5rem;
        color: #fff;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }

    .layer-count {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(26, 213, 250, 0.1);
        border-radius: 8px;
        color: #1ad5fa;
        font-size: 0.85rem;
        margin-top: 10px;
    }

    .text-secondary-custom {
        color: #a0a0a0;
    }
</style>
</head>
<body>
<div id="ip-loader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#16161a; z-index:9999; display:flex; flex-direction:column; align-items:center; justify-content:center;">
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
    <h5 class="text-white pulse">ĐANG XÁC MINH KHU VỰC...</h5>
</div>

<div class="container" id="main-ui" style="display:none;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card-gaming text-center">
                <h3 class="title-glitch mb-3">CHỌN PHƯƠNG THỨC</h3>
                <div class="mb-4">
    <div class="d-flex align-items-center justify-content-center gap-2 p-3" 
         style="background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(26, 213, 250, 0.5); border-radius: 15px;">
        <span class="text-secondary-custom small">MÃ CODE MUA LINK NÀY:</span>
        <span class="fw-bold text-info" id="link-code-text" style="letter-spacing: 2px;"><?= esc($link['code']) ?></span>
        <button class="btn btn-sm btn-outline-info ms-2" onclick="copyCode()" style="border-radius: 8px;">
            <i class="bi bi-copy"></i> COPY
        </button>
    </div>
</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="option-box border-info">
                            <div>
                                <i class="bi bi-hourglass-split fs-1 text-info"></i>
                                <h5 class="mt-3 text-info fw-bold">MIỄN PHÍ</h5>
                                <div class="layer-count">
                                    <i class="bi bi-layers"></i> <span id="txt-count">0</span> lớp bảo vệ
                                </div>
                                <p class="small text-white-custom mt-3"><b id="txt-loc">...</b> Vượt link mã</p>
                            </div>
                            <button onclick="startFreeFlow()" class="btn btn-custom btn-free mt-3">BẮT ĐẦU VƯỢT LINK</button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="option-box border-warning">
                            <div>
                                <i class="bi bi-lightning-charge-fill fs-1 text-warning"></i>
                                <h5 class="mt-3 text-warning fw-bold">MUA LINK</h5>
                                <div class="layer-count">
                                    <i class="bi bi-layers"></i> Bỏ qua <span id="txt-count1">0</span> lớp bảo vệ
                                </div>
                                <h4 class="fw-bold price-tag mt-3" id="txt-price">0đ</h4>
                            </div>
                            <button onclick="buyLink(this)" class="btn btn-custom btn-buy mt-3">MUA NGAY</button>
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

<script>(function() {
    // Chặn chuột phải và phím tắt F12
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.onkeydown = function(e) {
        if (e.keyCode == 123 || (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || (e.ctrlKey && e.keyCode == 85)) {
            return false;
        }
    };

    const _k = "<?= substr(md5($e_key . 'GMV'), 0, 16) ?>";
    function _decode(data) {
        try {
            let b = atob(data), out = '';
            for(let i=0; i<b.length; i++) {
                out += String.fromCharCode(b.charCodeAt(i) ^ _k.charCodeAt(i % _k.length));
            }
            return out;
        } catch (e) { return ""; }
    }

    const serverData = {
        targetUrl: _decode("<?= $e_target ?>"),
        configs: JSON.parse(_decode("<?= $e_configs ?>") || "{}")
    };

    let finalFreeUrl = "";

    async function initPage() {
        try {
            // Lấy vị trí để hiển thị (vẫn giữ Cloudflare để hiện txt-loc cho đẹp)
            const traceRes = await fetch('https://www.cloudflare.com/cdn-cgi/trace');
            const traceText = await traceRes.text();
            const traceData = traceText.split('\n').reduce((acc, line) => {
                const [k, v] = line.split('=');
                if (k) acc[k] = v;
                return acc;
            }, {});

            const loc = traceData.loc || 'VN';
            
            // LOGIC MỚI: Sử dụng trực tiếp dữ liệu từ serverData.configs
            // serverData.targetUrl lúc này đã bao gồm các link rút gọn từ processGenerate
            finalFreeUrl = btoa(serverData.targetUrl);

            // Cập nhật giao diện dựa trên cấu hình phẳng
            const apiCount = serverData.configs.total_api || 0;
            const price = serverData.configs.price || 0;

            if (document.getElementById('txt-count')) document.getElementById('txt-count').innerText = apiCount;
            if (document.getElementById('txt-count1')) document.getElementById('txt-count1').innerText = apiCount;
            if (document.getElementById('txt-price')) {
                document.getElementById('txt-price').innerText = new Intl.NumberFormat('vi-VN').format(price) + 'đ';
            }
            if (document.getElementById('txt-loc')) document.getElementById('txt-loc').innerText = loc;

            // Hiển thị UI
            document.getElementById('ip-loader').style.display = 'none';
            document.getElementById('main-ui').style.display = 'block';

        } catch (e) {
            console.error("System Error:", e);
            // Nếu lỗi fetch Cloudflare vẫn cho hiện UI nhưng để loc là "Unknown"
            document.getElementById('ip-loader').style.display = 'none';
            document.getElementById('main-ui').style.display = 'block';
            finalFreeUrl = btoa(serverData.targetUrl);
        }
    }

    window.startFreeFlow = function() {
        if (!finalFreeUrl) return;
        // Anti-debug đơn giản
        (function(){
            const start = new Date();
            debugger;
            const end = new Date();
            if (end - start > 100) {
                window.location.reload();
                return;
            }
        })(); 
        window.location.href = atob(finalFreeUrl);
    };

    // Khởi chạy
    initPage();
})();
function buyLink(btn) {
    const code = '<?= $link['code'] ?>';
    const priceText = document.getElementById('txt-price').innerText;

    if (!code || code === 'undefined') {
        Swal.fire('Lỗi', 'Không tìm thấy mã định danh link!', 'error');
        return;
    }

    Swal.fire({
        title: 'Thanh toán link VIP',
        html: `Bạn sẽ bị trừ <b class="text-warning">${priceText}</b> để nhận link gốc ngay lập tức.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Thanh toán ngay',
        cancelButtonText: 'Hủy',
        color: '#fff',
        background: '#16161a'
    }).then((result) => {
        if (result.isConfirmed) {
            executeBuy(btn, code);
        }
    });
}

function executeBuy(btn, code) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

    fetch('<?= site_url("customer/buy") ?>/' + code, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') {
            localStorage.setItem('pending_link_code', code);
        }

        if (data.status === 'success') {
            localStorage.removeItem('pending_link_code');
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: 'Hệ thống đang chuyển bạn đến link gốc...',
                timer: 2000,
                showConfirmButton: false,
                background: '#16161a',
                color: '#fff'
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else if (data.status === 'unauthorized') {
            Swal.fire({
                icon: 'warning',
                title: 'Yêu cầu đăng nhập',
                text: 'Bạn cần đăng nhập để thực hiện giao dịch này!',
                confirmButtonText: 'Đến trang Đăng nhập',
                background: '#16161a',
                color: '#fff'
            }).then(() => {
                window.location.href = '<?= site_url('customer/login') ?>';
            });
        } else if (data.redirect) {
            Swal.fire({
                icon: 'error',
                title: 'Số dư không đủ',
                text: data.message,
                confirmButtonText: 'Đến trang Nạp tiền',
                background: '#16161a',
                color: '#fff'
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: data.message,
                background: '#16161a',
                color: '#fff'
            });
            btn.disabled = false;
            btn.innerHTML = 'THANH TOÁN';
        }
    })
    .catch(err => {
        Swal.fire('Lỗi kết nối', 'Vui lòng kiểm tra lại mạng!', 'error');
        btn.disabled = false;
        btn.innerHTML = 'THANH TOÁN';
    });
}

function copyCode() {
    const code = document.getElementById('link-code-text').innerText;
    navigator.clipboard.writeText(code).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Đã copy mã link!',
            showConfirmButton: false,
            timer: 1500,
            background: '#16161a',
            color: '#fff'
        });
    });
}
</script>
</body>
</html>