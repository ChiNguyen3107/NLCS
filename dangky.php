<?php
// Kết nối tới cơ sở dữ liệu
include 'config.php';

$message = '';
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin từ form
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $mat_khau = isset($_POST['mat_khau']) ? $_POST['mat_khau'] : '';
    $xac_nhan_mat_khau = isset($_POST['xac_nhan_mat_khau']) ? $_POST['xac_nhan_mat_khau'] : '';
    $role_id = 2; // Vai trò khách hàng

    // Kiểm tra xem mật khẩu và xác nhận mật khẩu có khớp không
    if ($mat_khau !== $xac_nhan_mat_khau) {
        $message = "Mật khẩu và xác nhận mật khẩu không khớp.";
    } else {
        // Kiểm tra xem email đã tồn tại chưa
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        if ($stmt === false) {
            die("Lỗi prepare statement: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "Email đã được sử dụng. Vui lòng chọn email khác.";
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($mat_khau, PASSWORD_DEFAULT);
            
            // Thêm tài khoản vào cơ sở dữ liệu
            $sql = "INSERT INTO users (email, mat_khau, role_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Lỗi prepare statement: " . $conn->error);
            }
            $stmt->bind_param("ssi", $email, $hashed_password, $role_id);

            if ($stmt->execute()) {
                $registration_success = true;
                $message = "Đăng ký tài khoản thành công!";
            } else {
                $message = "Lỗi: " . $stmt->error;
            }
        }
        
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản Khách Hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form { display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input[type="email"], input[type="password"] {
            padding: 8px;
            margin-top:  5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đăng Ký Tài Khoản</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="mat_khau">Mật khẩu:</label>
            <input type="password" id="mat_khau" name="mat_khau" required>
            <label for="xac_nhan_mat_khau">Xác nhận mật khẩu:</label>
            <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" required>
            <input type="submit" value="Đăng Ký">
        </form>
        <?php if ($registration_success) { ?>
            <script>
                alert("Đăng ký tài khoản thành công!");
                window.location.href = "homepage.php";
            </script>
        <?php } else { ?>
            <p><?php echo $message; ?></p>
            <p>Bạn đã có tài khoản? <a href="dangnhap.php">Đăng nhập</a></p>
        <?php } ?>
    </div>
</body>
</html>