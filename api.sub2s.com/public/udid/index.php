<?php 
$udid = isset($_GET['UDID']) ? $_GET['UDID'] : null;
$baseUrl = "https://key.gmvmoba.com/udid"; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Xác minh thiết bị - GMVMOBA</title>

<style>
body { font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; text-align:center; padding:15px; background:#f4f4f9; color:#333; }
.card{ max-width:400px; margin:0 auto; background:white; padding:30px; border-radius:20px; box-shadow:0 10px 25px rgba(0,0,0,0.1); }
.btn{ display:inline-block; background:#007aff; color:white; padding:15px 30px; border-radius:12px; text-decoration:none; font-weight:bold; margin-top:20px; }
.btn:hover{ background:#0056b3; }
.udid-text{ background:#eee; padding:10px; border-radius:8px; font-family:monospace; word-break:break-all; margin:15px 0; color:#d63384; }
.success-icon{ font-size:50px; color:#4cd964; margin-bottom:10px; }
.loader{ margin-top:15px; color:#777; font-size:14px; }

/* CSS cho Popup Thông báo */
#guideModal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.7);
  backdrop-filter: blur(5px);
}
.modal-content {
  background-color: #fff;
  margin: 15% auto;
  padding: 25px;
  border-radius: 20px;
  width: 85%;
  max-width: 350px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  animation: slideUp 0.4s ease;
}
@keyframes slideUp {
  from { transform: translateY(100px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.modal-content h3 { color: #ff3b30; margin-top: 0; }
.step-box { text-align: left; background: #f8f8f8; padding: 15px; border-radius: 10px; margin: 15px 0; font-size: 15px; line-height: 1.6; }
.step-box b { color: #007aff; }
.close-btn { background: #007aff; color: white; padding: 12px 25px; border-radius: 10px; border: none; font-weight: bold; width: 100%; cursor: pointer; }
</style>
</head>

<body>

<div id="guideModal">
  <div class="modal-content">
    <h3>🔥 BƯỚC CUỐI CÙNG 🔥</h3>
    <p>Hồ sơ cấu hình đã tải về thành công. Vui lòng thực hiện các bước sau:</p>
    <div class="step-box">
      1. Thoát trình duyệt Safari.<br>
      2. Mở ứng dụng <b>Cài đặt</b> trên điện thoại.<br>
      3. Vào <b>Cài đặt chung</b>.<br>
      4. Chọn <b>Quản lý VPN & thiết bị</b> (Tìm và mở Đã tải về hồ sơ).<br>
      5. Nhấn <b>Cài đặt</b> ở góc trên cùng.
    </div>
    <button class="close-btn" onclick="document.getElementById('guideModal').style.display='none'">TÔI ĐÃ HIỂU</button>
  </div>
</div>

<div class="card">
<?php if ($udid): ?>
  <div class="success-icon">✓</div>
  <h2>Xác Minh UDID Thành Công</h2>
  <p>Thiết bị của bạn đã được nhận diện</p>
  <p>Tiếp tục lấy key Vào game chiến ngay thôi nhé</p>
  <a href="https://key.gmvmoba.com/gmvmoba/getkey" target="_blank" class="btn">Lấy Key Miễn Phí</a>
<?php else: ?>
  <h2>XÁC MINH THIẾT BỊ</h2>
  <p>Đang chuẩn bị cấu hình nhận diện thiết bị...</p>
  <div class="loader">Vui lòng chờ trong giây lát...</div>
  <a href="/public/binhbun.mobileconfig" class="btn" id="manualBtn">Bấm vào đây để tải lại</a>
<?php endif; ?>
</div>

<?php if (!$udid): ?>
<center style="margin-top:20px;">
<iframe width="350" height="315" src="https://www.youtube.com/embed/M5uBwgrHdvo" title="YouTube video player" frameborder="0" allowfullscreen></iframe>
</center>
<?php endif; ?>

<?php if (!$udid): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const isiOS = /iPad|iPhone|iPod/.test(navigator.userAgent);

  if (isiOS) {
    setTimeout(function () {
      window.location.href = "/public/binhbun.mobileconfig";
      
      setTimeout(function() {
        document.getElementById('guideModal').style.display = 'block';
      }, 3000);
    }, 1200);
  }
});

document.getElementById('manualBtn').addEventListener('click', function() {
    setTimeout(function() {
        document.getElementById('guideModal').style.display = 'block';
    }, 2000);
});
</script>
<?php endif; ?>

</body>
</html>