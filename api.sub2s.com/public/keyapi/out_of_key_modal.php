<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<title>Hết Key</title>
<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #020617;
    min-height: 100vh;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* overlay full màn hình */
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.65);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px; /* mobile không bị sát mép */
    z-index: 9999;
}

/* popup */
.modal {
    width: 100%;
    max-width: 420px;
    background: #0b1220;
    border-radius: 16px;
    padding: clamp(20px, 4vw, 28px);
    text-align: center;
    border: 1px solid rgba(0,212,255,.35);
    box-shadow: 0 0 40px rgba(0,212,255,.25);
}

/* tiêu đề */
.modal h2 {
    color: #fff;
    margin-bottom: 12px;
    font-size: clamp(18px, 4.5vw, 22px);
}

/* nội dung */
.modal p {
    color: #cbd5f5;
    font-size: clamp(13px, 3.5vw, 14px);
    line-height: 1.55;
    margin-bottom: 10px;
}

/* nhóm nút */
.modal .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    max-width: 260px;
    margin: 8px auto 0;
    padding: 12px 18px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none !important;
}

/* nút mua key */
.admin {
    background: #22c55e;
    color: #fff;
}

/* nút refresh */
.btn-refresh {
    background: linear-gradient(135deg,#00d4ff,#6c63ff);
    color: #fff;
}

/* nút discord */
.btn-discord {
    background: #5865F2;
    color: #fff;
}

/* hover desktop */
@media (hover:hover) {
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0,0,0,.25);
    }
}

/* màn hình lớn */
@media (min-width: 768px) {
    .modal {
        max-width: 460px;
    }
}

</style>
</head>
<body>

<div class="overlay">
    <div class="modal">
        <h2>❌ Đã hết key Free</h2>
        <p>
            Tham Gia Nhóm Discord chờ thống báo để mới để nhận thêm key free.
        </p>

        <a class="btn btn-discord" href="https://discord.com/invite/wY6xFY6BzK" target="_blank">
            Tham gia Discord
        </a>
        <h2>Hoặc Mua Key Liên Hệ</h2>
<p>
            Hỗ trợ cả Card & Bank
        </p>
        <a class="btn admin" href="https://zalo.me/0965870531"> Admin Zalo: 0965870531</a>
        <!-- <a class="btn admin" href="https://zalo.me/0965870531"> Admin 2 Zalo: 0327305966</a> -->



        <a class="btn btn-refresh" href="/public/keyapi/api.php">
            🔄 Thử lại
        </a>
        <p id="status" style="margin-top:12px;font-size:13px;color:#94a3b8"></p>
    </div>
</div>
</body>
</html>
