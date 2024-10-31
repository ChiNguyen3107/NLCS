<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Kiểm tra xem có yêu cầu cập nhật sản phẩm không
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    $ten_san_pham = $_POST['ten_san_pham'];
    $gia = $_POST['gia'];
    $so_luong = $_POST['so_luong'];
    $cpu = $_POST['cpu'];
    $ram = $_POST['ram'];
    $o_cung = $_POST['o_cung'];
    $gpu = $_POST['gpu'];
    $kich_thuoc_manh_hinh = $_POST['kich_thuoc_manh_hinh'];
    $thong_tin_mang_hinh = $_POST['thong_tin_mang_hinh'];
    $pin = $_POST['pin'];
    $he_dieu_hanh = $_POST['he_dieu_hanh'];
    $trong_luong = $_POST['trong_luong'];
    $hang_id = $_POST['hang_id'];

    // Cập nhật thông tin sản phẩm
    $sql = "UPDATE sanpham SET ten_san_pham = ?, gia = ?, so_luong = ?, cpu = ?, ram = ?, o_cung = ?, gpu = ?, kich_thuoc_manh_hinh = ?, thong_tin_mang_hinh = ?, pin = ?, he_dieu_hanh = ?, trong_luong = ?, hang_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdisissssssssi", $ten_san_pham, $gia, $so_luong, $cpu, $ram, $o_cung, $gpu, $kich_thuoc_manh_hinh, $thong_tin_mang_hinh, $pin, $he_dieu_hanh, $trong_luong, $hang_id, $id);
    if ($stmt->execute()) {
        $success_message = "Cập nhật sản phẩm thành công.";
    } else {
        $error_message = "Có lỗi xảy ra khi cập nhật sản phẩm.";
    }
}

// Đóng kết nối
$conn->close();
?>