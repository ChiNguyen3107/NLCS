<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy phiên
session_destroy();

// Chuyển hướng về trang chủ
header("Location: homepage.php");
exit();
?>