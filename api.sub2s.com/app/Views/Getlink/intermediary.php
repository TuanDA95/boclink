<?php 
$enable_free = $enable_free ?? true; 
?>
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

    /* ===== Interstitial (trang trung gian) ===== */
    .interstitial-img-wrap {
        position: relative;
        display: inline-block;
        cursor: pointer;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(26, 213, 250, 0.3);
        max-width: 100%;
    }

    .interstitial-img-wrap img {
        display: block;
        max-width: 100%;
        height: auto;
        user-select: none;
        -webkit-user-drag: none;
    }

    .interstitial-img-wrap .tap-hint {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 10px;
        background: rgba(0,0,0,0.55);
        color: #fff;
        font-size: 0.85rem;
        text-align: center;
        pointer-events: none;
    }

    .interstitial-img-wrap.locked-visual:hover {
        filter: brightness(1.05);
    }

    #btn-continue-free[disabled] {
        opacity: 0.45;
        cursor: not-allowed;
        filter: grayscale(0.5);
    }

    #btn-continue-free:not([disabled]) {
        animation: pulse-glow 1.2s ease-in-out infinite alternate;
    }

    @keyframes pulse-glow {
        from { box-shadow: 0 0 10px rgba(26, 213, 250, 0.3); }
        to   { box-shadow: 0 0 22px rgba(26, 213, 250, 0.6); }
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

<div class="container" id="interstitial-ui" style="display:none;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card-gaming text-center">
                <h3 class="title-glitch mb-3">XÁC NHẬN CAPTCHA ĐỂ NHẬN LINK</h3>
                <p class="text-secondary-custom mb-4">Để tránh spam vui lòng xác minh Robot.</p>

                <div style="position: relative;">
                    <div id="imageModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; display: flex; justify-content: center; align-items: center; z-index: 1000;">
                        <a href="https://hai8g.com/4/11414370" class="linkads1" target="_blank" id="imgmodal" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></a>
                    </div>

                    <div class="interstitial-img-wrap locked-visual mb-4" id="interstitial-img-wrap" onclick="onInterstitialImageClick()">
                        <img id="interstitial-img" src="https://i.imgur.com/Os6qgXp.jpg" alt="click ad">
                        <div class="tap-hint" id="tap-hint">Nhấn vào trên làm theo và xác minh</div>
                    </div>
                </div>

                <button id="btn-continue-free" class="btn btn-custom btn-free" disabled onclick="continueToFreeLink()">
                    TIẾP TỤC
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.onkeydown = function(e) {
        if (e.keyCode == 123 || (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || (e.ctrlKey && e.keyCode == 85)) {
            return false;
        }
    };

    const _k = "<?= substr(md5($e_key . 'GMV'), 0, 16) ?>";
    function _decode(data) {
        let b = atob(data), out = '';
        for(let i=0; i<b.length; i++) {
            out += String.fromCharCode(b.charCodeAt(i) ^ _k.charCodeAt(i % _k.length));
        }
        return out;
    }

    const serverData = {
        targetUrl: _decode("<?= $e_target ?>"),
        configs: JSON.parse(_decode("<?= $e_configs ?>")),
        enableFree: <?= $enable_free ? 'true' : 'false' ?>
    };

    let finalFreeUrl = "";
    let imageClicked = false;
    const AD_CLICK_URL = "https://ngocbonggaming.github.io/direc-2/";
    const UNLOCKED_IMG = "https://i.imgur.com/DaR9mmK.jpeg";

    async function initPage() {
        // Lấy vị trí qua Cloudflare trace - nếu fail (VPN/Adblock/mạng chặn)
        // thì fallback về 'VN' thay vì làm treo cả trang
        let loc = 'VN';
        try {
            const traceRes = await fetch('https://www.cloudflare.com/cdn-cgi/trace');
            const traceText = await traceRes.text();
            const traceData = traceText.split('\n').reduce((acc, line) => {
                const [k, v] = line.split('=');
                if (k) acc[k] = v;
                return acc;
            }, {});
            loc = traceData.loc || 'VN';
        } catch (traceErr) {
            console.warn('Không lấy được vị trí qua Cloudflare trace, dùng mặc định VN:', traceErr);
        }

        try {
            const config = (loc === 'VN') ? serverData.configs.VN : serverData.configs.GLOBAL;
            if (!config) {
                throw new Error('Không tìm thấy config cho vị trí: ' + loc);
            }

            let wrappedUrl = serverData.targetUrl;
            if (config.apis && config.apis.length > 0) {
                const reversedApis = [...config.apis].reverse();
                reversedApis.forEach(api => {
                    if (api.base) {
                        wrappedUrl = api.base + encodeURIComponent(wrappedUrl);
                    }
                });
            }

            finalFreeUrl = btoa(wrappedUrl);

            if (!serverData.enableFree) {
                const freeOption = document.querySelector('.option-box.border-info');
                if (freeOption) {
                    freeOption.style.display = 'none';
                }
                
                const buyCol = document.querySelector('.col-md-6:last-child');
                if (buyCol) {
                    buyCol.className = 'col-md-12';
                    buyCol.style.maxWidth = '100%';
                    buyCol.style.flex = '0 0 100%';
                }
                
                const container = document.querySelector('.row.g-3');
                if (container) {
                    const notice = document.createElement('div');
                    notice.className = 'col-12 mb-3';
                    notice.innerHTML = `
                        <div class="alert alert-warning text-center" style="background: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; border-radius: 12px; color: #ffffff;">
                            <i class="bi bi-info-circle" style="color: #ffc107;"></i> 
                            <strong style="color: #ffffff;">Thông báo:</strong> 
                            <span style="color: #ffffff;">Hết Slot Vượt Link Free. Mua Link để tiếp tục!</span>
                        </div>
                    `;
                    container.prepend(notice);
                }
            }

            document.getElementById('txt-count').innerText = config.apis.length;
            document.getElementById('txt-count1').innerText = config.apis.length;
            document.getElementById('txt-price').innerText = new Intl.NumberFormat().format(config.price) + 'đ';
            document.getElementById('txt-loc').innerText = loc;

            document.getElementById('ip-loader').style.display = 'none';
            document.getElementById('main-ui').style.display = 'block';

        } catch (e) {
            // Luôn tắt loader để không bị xoay vô hạn, và log lỗi thật ra console để debug
            console.error("System Error:", e);
            document.getElementById('ip-loader').innerHTML =
                '<div class="text-center text-white px-3">' +
                '<i class="bi bi-exclamation-triangle text-warning fs-1"></i>' +
                '<h5 class="mt-3">Đã xảy ra lỗi khi tải trang</h5>' +
                '<p class="text-secondary-custom small">' + (e && e.message ? e.message : 'Vui lòng tắt VPN/Adblock và thử lại.') + '</p>' +
                '<button class="btn btn-outline-info btn-sm mt-2" onclick="location.reload()">Thử lại</button>' +
                '</div>';
        }
    }

    window.startFreeFlow = function() {
        // Kiểm tra enableFree
        if (!serverData.enableFree) {
            Swal.fire({
                icon: 'error',
                title: '⚠️ Tính năng đã bị khóa',
                text: 'Chức năng miễn phí hiện đang bị tắt bởi quản trị viên!',
                confirmButtonText: 'Đóng',
                background: '#16161a',
                color: '#fff',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        if (!finalFreeUrl) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi hệ thống',
                text: 'Không thể tạo link miễn phí!',
                background: '#16161a',
                color: '#fff'
            });
            return;
        }

        // Chuyển sang trang trung gian thay vì redirect thẳng
        document.getElementById('main-ui').style.display = 'none';
        document.getElementById('interstitial-ui').style.display = 'block';

        // reset trạng thái mỗi lần vào lại
        imageClicked = false;
        document.getElementById('btn-continue-free').disabled = true;
        const imgWrap = document.getElementById('interstitial-img-wrap');
        imgWrap.classList.add('locked-visual');
        imgWrap.style.pointerEvents = 'auto';
        document.getElementById('interstitial-img').src = "https://i.imgur.com/Os6qgXp.jpg";
        document.getElementById('tap-hint').innerText = 'Nhấn vào ảnh để tiếp tục';

        const adModal = document.getElementById('imageModal');
        if (adModal) adModal.style.display = 'flex';
    };

    window.onInterstitialImageClick = function() {
        if (imageClicked) return; // chặn spam click
        imageClicked = true;

        const wrap = document.getElementById('interstitial-img-wrap');
        const hint = document.getElementById('tap-hint');
        const img = document.getElementById('interstitial-img');

        wrap.classList.remove('locked-visual');
        wrap.style.pointerEvents = 'none'; // không cho click lại

        // Mở url quảng cáo ở tab mới
        window.open(AD_CLICK_URL, '_blank');

        hint.innerText = 'Đang xử lý...';

        // Đếm ngược 3s ẩn (không hiển thị số giây lên UI)
        setTimeout(() => {
            img.src = UNLOCKED_IMG;
            hint.innerText = 'Đã mở khóa! Bấm nút bên dưới';
            document.getElementById('btn-continue-free').disabled = false;
        }, 5000);
    };

    window.continueToFreeLink = function() {
        if (!finalFreeUrl) return;
        (function(){debugger;})();
        window.location.href = atob(finalFreeUrl);
    };

    const imgModalLink = document.getElementById('imgmodal');
    if (imgModalLink) {
        imgModalLink.addEventListener('click', function() {
            document.getElementById('imageModal').style.display = 'none';
        });
    }

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