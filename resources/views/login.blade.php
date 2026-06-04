<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    {{-- THÊM THẺ VIEWPORT: Bắt buộc phải có để trang web co giãn trên điện thoại --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Đăng nhập hệ thống</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
            background: #f0f2f5; 
        }
        
        .box { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 16px rgba(0,0,0,0.1); 
            /* Sửa lại Width để Responsive */
            width: 100%; 
            max-width: 400px; 
            margin: 0 20px; /* Cách lề 2 bên màn hình trên mobile */
            box-sizing: border-box;
        }

        .box h2 {
            margin-top: 0;
            color: #333;
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input { 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box;
            font-size: 16px; /* Kích thước 16px giúp iPhone không bị tự động Zoom khi bấm vào input */
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
        }

        button { 
            width: 100%; 
            padding: 12px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }

        button:hover { 
            background: #0056b3; 
        }

        .error { 
            color: #dc3545; 
            font-size: 0.9em; 
            margin-bottom: 20px; 
            padding: 12px;
            background-color: #f8d7da;
            border-radius: 6px;
            border: 1px solid #f5c6cb;
        }
        
        /* Tinh chỉnh riêng cho màn hình điện thoại rất nhỏ */
        @media (max-width: 480px) {
            .box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center">Đăng Nhập</h2>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf 
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" placeholder="Nhập username..." required>
            
            <label>Mật khẩu:</label>
            <input type="password" name="password" placeholder="Nhập mật khẩu..." required>
            
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>