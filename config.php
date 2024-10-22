<?php
$servername = "localhost";
$username = "root"; // Thay bằng tên người dùng của bạn
$password = ""; // Thay bằng mật khẩu của bạn
$dbname = "nlcs"; // Thay bằng tên cơ sở dữ liệu của bạn

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
