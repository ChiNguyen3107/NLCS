<?php
// Kết nối tới cơ sở dữ liệu
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin từ form
    $email = $_POST['email'];
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
    $role_id = 3; // Vai trò admin

    // Thêm tài khoản vào cơ sở dữ liệu
    $sql = "INSERT INTO users (email, mat_khau, role_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $email, $mat_khau, $role_id);

    if ($stmt->execute()) {
        echo "Tài khoản admin đã được thêm thành công!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Tài Khoản Admin</title>
</head>
<body>
    <h2>Thêm Tài Khoản Admin</h2>
    <form method="POST" action="">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>
        <label for="mat_khau">Mật Khẩu:</label>
        <input type="password" name="mat_khau" required>
        <br>
        <input type="submit" value="Thêm Tài Khoản">
    </form>
</body>
</html>
