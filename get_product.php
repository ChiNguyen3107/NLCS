<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Lấy thông tin sản phẩm
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Thêm JOIN với bảng hang để lấy thông tin hãng
    $sql = "SELECT s.*, h.ten_hang FROM sanpham s 
            LEFT JOIN hang h ON s.hang_id = h.id 
            WHERE s.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sanpham = $result->fetch_assoc();
        echo json_encode($sanpham);
    } else {
        echo json_encode([]);
    }
}
?>