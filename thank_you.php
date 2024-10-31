<?php
session_start();
include 'config.php';

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cảm ơn</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cảm ơn đã mua hàng!</h1>
        <p>Đơn hàng của bạn đã được ghi nhận. Chúng tôi sẽ liên hệ với bạn sớm nhất.</p>
        <a href="homepage.php" class="btn">Tiếp tục mua sắm</a>
    </div>
</body>
</html>