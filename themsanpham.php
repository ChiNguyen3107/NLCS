<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    // Lấy dữ liệu từ form
    $ten_san_pham = $_POST['ten_san_pham'];
    $gia = $_POST['gia'];
    $mo_ta = $_POST['mo_ta'];
    $so_luong = $_POST['so_luong'];
    $hang_id = $_POST['hang_id'];
    $cpu = $_POST['cpu'];
    $ram = $_POST['ram'];
    $o_cung = $_POST['o_cung'];
    $gpu = $_POST['gpu'];
    $kich_thuoc_manh_hinh = $_POST['kich_thuoc_manh_hinh'];
    $thong_tin_mang_hinh = $_POST['thong_tin_mang_hinh'];
    $pin = $_POST['pin'];
    $he_dieu_hanh = $_POST['he_dieu_hanh'];
    $trong_luong = $_POST['trong_luong'];

    // Thêm sản phẩm vào bảng sanpham
    $sql = "INSERT INTO sanpham (ten_san_pham, gia, mo_ta, so_luong, hang_id, cpu, ram, o_cung, gpu, kich_thuoc_manh_hinh, thong_tin_mang_hinh, pin, he_dieu_hanh, trong_luong) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsiisssssssss", $ten_san_pham, $gia, $mo_ta, $so_luong, $hang_id, $cpu, $ram, $o_cung, $gpu, $kich_thuoc_manh_hinh, $thong_tin_mang_hinh, $pin, $he_dieu_hanh, $trong_luong);

    if ($stmt->execute()) {
        $sanpham_id = $stmt->insert_id;

        // Xử lý upload nhiều ảnh
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = "uploads/";
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            $max_file_size = 5 * 1024 * 1024; // 5MB

            // Kiểm tra và xử lý ảnh đại diện
            if (isset($_FILES['main_image_file'])) {
                $main_image_file = $_FILES['main_image_file'];
                $main_file_name = $main_image_file['name'];
                $main_file_size = $main_image_file['size'];
                $main_file_tmp = $main_image_file['tmp_name'];

                // Kiểm tra loại file và kích thước
                $main_file_ext = strtolower(pathinfo($main_file_name, PATHINFO_EXTENSION));
                if (!in_array($main_file_ext, $allowed_types)) {
                    $error_message = "Chỉ cho phép các file ảnh JPG, JPEG, PNG và GIF cho ảnh đại diện.";
                    exit();
                }
                if ($main_file_size > $max_file_size) {
                    $error_message = "File ảnh đại diện không được vượt quá 5MB.";
                    exit();
                }

                // Tạo tên file mới cho ảnh đại diện
                $main_new_file_name = uniqid() . '.' . $main_file_ext;

                // Di chuyển file tạm vào thư mục uploads
                move_uploaded_file($main_file_tmp, $upload_dir . $main_new_file_name);

                // Thêm ảnh đại diện vào bảng anh_sanpham
                $sql = "INSERT INTO anh_sanpham (sanpham_id, anh, is_main) VALUES (?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $sanpham_id, $main_new_file_name);
                $stmt->execute();
            }

            // Xử lý các ảnh khác
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $file_tmp = $_FILES['images']['tmp_name'][$key];

                // Kiểm tra loại file và kích thước
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types)) {
                    $error_message = "Chỉ cho phép các file ảnh JPG, JPEG, PNG và GIF.";
                    continue;
                }
                if ($file_size > $max_file_size) {
                    $error_message = "File không được vượt quá 5MB.";
                    continue;
                }

                // Tạo tên file mới để tránh trùng lặp
                $new_file_name = uniqid() . '.' . $file_ext;

                // Di chuyển file tạm vào thư mục uploads
                move_uploaded_file($file_tmp, $upload_dir . $new_file_name);

                // Thêm ảnh vào bảng anh_sanpham với is_main = 0
                $sql = "INSERT INTO anh_sanpham (sanpham_id, anh, is_main) VALUES (?, ?, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $sanpham_id, $new_file_name);
                $stmt->execute();
            }
        }



        header("Location: quanlysanpham.php");
        exit();
    } else {
        $error_message = "Thêm sản phẩm thất bại.";
    }
}
?>