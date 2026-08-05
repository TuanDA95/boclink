<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #0ea5e9;
            --dark: #0f172a;
            --light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

       body {
            background-color: var(--dark);
            color: var(--light);
            display: flex;
            justify-content: center;
            align-items: center;
            /* Đổi min-height thành tự động trên mobile để không bị lỗi cắt trang */
            min-height: 100vh;
            /* QUAN TRỌNG: Cho phép cuộn khi nội dung dài */
            overflow-y: auto; 
            overflow-x: hidden;
            padding: 40px 0; /* Tạo khoảng trống trên dưới cho mobile */
        }
        /* Hiệu ứng nền */
        .bg-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(15, 23, 42, 0) 70%);
            z-index: -1;
        }

        .container {
            display: flex;
            gap: 2rem;
            width: 90%;
            max-width: 1000px;
            flex-wrap: wrap;
            padding: 20px;
        }

        .card {
            flex: 1;
            min-width: 300px;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem 2rem;
            border-radius: 24px;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: block;
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: #94a3b8;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-admin {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .btn-admin:hover {
            background: #4f46e5;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }

        .btn-customer {
            background: var(--secondary);
            color: white;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);
        }

        .btn-customer:hover {
            background: #0284c7;
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.6);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .card {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    
    <div class="container"><div class="card">
            <div>
                <span class="icon">🔑</span>
                <h2>Mua Link / Key GMVMOBA</h2>
                <p>Hệ thống tự động cung cấp link và key chất lượng cao, giao dịch tức thì và bảo mật tuyệt đối.</p>
            </div>
            <a href="/customer/dashboard" class="btn btn-customer">Join Mua Link / Key</a>
        </div>
        <div class="card">
            <div>
                <span class="icon">🖥️</span>
                <h2>Thuê Server Key riêng</h2>
                <p>Quản lý hệ thống, cấu hình API và vận hành máy chủ key chuyên nghiệp dành cho đối tác.</p>
            </div>
            <a href="/admin" class="btn btn-admin">Join Server Key</a>
        </div>

        
    </div>
</body>
</html>