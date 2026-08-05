<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <title><?= BASE_NAME ?> - <?= isset($title) ? $title : 'Panel' ?></title>
    
    <style>
        :root {
            --sidebar-width: 280px;
            --main-bg: #f4f7fe;
            --accent-color: #3b82f6;
        }

        body {
            background-color: var(--main-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* Layout Structure */
        main {
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Đẩy nội dung sang phải khi có sidebar trên Desktop */
        @media (min-width: 992px) {
            main {
                margin-left: var(--sidebar-width);
            }
            .content-wrapper {
                padding: 40px !important;
            }
        }

        /* Content Area */
        .content-wrapper {
            flex: 1;
            padding: 20px;
            /* Background trang trí nhẹ phía sau thay vì đè lên card */
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
        }

        /* Footer Style */
        footer {
            background: #ffffff;
            border-top: 1px solid #e2e8e0;
            padding: 20px 0;
            margin-top: auto;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <?= $this->renderSection('css') ?>
    <?= link_tag('assets/css/natacode.css') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
</head>

<body>
    <?= $this->include('Layout/Header') ?>

    <main>
        <div class="content-wrapper">
            <div class="container-fluid">
                <?= $this->renderSection('content') ?>
            </div>
        </div>

        <footer class="text-center">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <p class="mb-0 small text-muted">
                        &copy; <?= date('Y') ?> <span class="fw-bold text-primary"><?= BASE_NAME ?></span>. All rights reserved.
                    </p>
                    <div class="footer-links mt-2 mt-md-0">
                        <small class="text-muted">Version 3.0.1 (Stable)</small>
                    </div>
                </div>
            </div>
        </footer>
    </main>
 <?php if (session('userid') != '4'): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <a href="https://t.me/ngocbonggaming" id="linktelegram" target="_blank" rel="noopener noreferrer">
			<div id="fcta-telegram-tracking" class="fcta-telegram-mess">
				<span id="fcta-telegram-tracking">Liên hệ Admin</span>
			</div>
			<div class="fcta-telegram-vi-tri-nut">
				<div id="fcta-telegram-tracking" class="fcta-telegram-nen-nut">
					<div id="fcta-telegram-tracking" class="fcta-telegram-ben-trong-nut">
                    <i class="fab fa-facebook-messenger fa-2x"></i>
					</div>
					<div id="fcta-telegram-tracking" class="fcta-telegram-text"> Chat Ngay </div>
				</div>
			</div>
		</a>
        <style>
		@keyframes zoom{
			0%{
				transform:scale(.5);
				opacity:0
		}
			50%{
				opacity:1
		}
			to{
				opacity:0;
				transform:scale(1)
		}
		}
		@keyframes lucidgentelegram{
			0% to{
				transform:rotate(-25deg)
		}
			50%{
				transform:rotate(25deg)
		}
		}
		.jscroll-to-top{
			bottom:100px
		}
		.fcta-telegram-ben-trong-nut svg path{
			fill:#fff
		}
		.fcta-telegram-vi-tri-nut{
			position:fixed;
			bottom:24px;
			right:20px;
			z-index:999
		}
		.fcta-telegram-nen-nut,div.fcta-telegram-mess{
			box-shadow:0 1px 6px rgba(0,0,0,.06),0 2px 32px rgba(0,0,0,.16)
		}
		.fcta-telegram-nen-nut{
			width:50px;
			height:50px;
			text-align:center;
			color:#fff;
			background:#3a9140;
			border-radius:50%;
			position:relative
		}
		.fcta-telegram-nen-nut::after,.fcta-telegram-nen-nut::before{
			content:"";
			position:absolute;
			border:1px solid #3a9140;
			background:#3a914080;
			z-index:-1;
			left:-20px;
			right:-20px;
			top:-20px;
			bottom:-20px;
			border-radius:50%;
			animation:zoom 1.9s linear infinite
		}
		.fcta-telegram-nen-nut::after{
			animation-delay:.4s
		}
		.fcta-telegram-ben-trong-nut,.fcta-telegram-ben-trong-nut i{
			transition:all 1s
		}
		.fcta-telegram-ben-trong-nut{
			position:absolute;
			text-align:center;
			width:30%;
			height:46%;
			left:10px;
			bottom:25px;
			line-height:50px;
			font-size:20px;
			opacity:1
		}
		.fcta-telegram-ben-trong-nut i{
			animation:lucidgentelegram 1s linear infinite
		}
		.fcta-telegram-nen-nut:hover .fcta-telegram-ben-trong-nut,.fcta-telegram-text{
			opacity:0
		}
		.fcta-telegram-nen-nut:hover i{
			transform:scale(.5);
			transition:all .5s ease-in
		}
		.fcta-telegram-text a{
			text-decoration:none;
			color:#fff
		}
		.fcta-telegram-text{
			position:absolute;
			top:6px;
			text-transform:uppercase;
			font-size:12px;
			font-weight:700;
			transform:scaleX(-1);
			transition:all .5s;
			line-height:1.5
		}
		.fcta-telegram-nen-nut:hover .fcta-telegram-text{
			transform:scaleX(1);
			opacity:1
		}
		div.fcta-telegram-mess{
			position:fixed;
			bottom:29px;
			right:58px;
			z-index:99;
			background:#fff;
			padding:7px 25px 7px 15px;
			color:#3a9140;
			border-radius:50px 0 0 50px;
			font-weight:700;
			font-size:15px
		}
		.fcta-telegram-mess span{
			color:#3a9140!important
		}
		span#fcta-telegram-tracking{
			font-family:Roboto;
			line-height:1.5
		}
		.fcta-telegram-text{
			font-family:Roboto
		}
		</style>
            <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.1.0/sweetalert2.all.min.js"></script>

    <?= script_tag('assets/js/natacode.js') ?>
    <?= $this->renderSection('js') ?>

    <script>
        // Hiệu ứng Fade In cho toàn bộ trang khi load xong
        $(document).ready(function() {
            $('.content-wrapper').hide().fadeIn(500);
        });
    </script>
</body>

</html>