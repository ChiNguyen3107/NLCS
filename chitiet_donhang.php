<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Lấy ID đơn hàng từ URL
if (isset($_GET['id'])) {
    $donhang_id = intval($_GET['id']);

    // Truy vấn thông tin đơn hàng
    $sql = "SELECT donhang.*, users.email FROM donhang LEFT JOIN users ON donhang.user_id = users.id WHERE donhang.id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Lỗi prepare statement: " . $conn->error); // Thêm thông báo lỗi
    }

    $stmt->bind_param("i", $donhang_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        header("Location: quanlydonhang.php");
        exit();
    }
} else {
    header("Location: quanlydonhang.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Chi tiết đơn hàng #<?php echo $row['id']; ?></h2>
        <table>
            <tr>
                <th>Email</th>
                <td><?php echo $row['email']; ?></td>
            </tr>
            <tr>
                <th>Ngày đặt</th>
                <td><?php echo $row['ngay_dat']; ?></td>
            </tr>
            <tr>
                <th>Phương thức thanh toán</th>
                <td><?php echo $row['phuong_thuc_thanh_toan']; ?></td>
            </tr>
            <tr>
                <th>Tổng tiền</th>
                <td><?php echo number_format($row['tong_tien'], 0, ',', '.') . ' VNĐ'; ?></td>
            </tr>
            <tr>
                <th>Ghi chú</th>
                <td><?php echo $row['ghi_chu']; ?></td>
            </tr>
        </table>
        <h3>Danh sách sản phẩm</h3>
        <table>
            <thead>
                <tr>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Truy vấn danh sách sản phẩm trong đơn hàng
                $sql = "SELECT sanpham.ten_san_pham, donhang_sanpham.so_luong, donhang_sanpham.gia 
                        FROM donhang_sanpham 
                        LEFT JOIN sanpham ON donhang_sanpham.san_pham_id = sanpham.id 
                        WHERE donhang_sanpham.don_hang_id = ?";
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    die("Lỗi prepare statement: " . $conn->error); // Thêm thông báo lỗi
                }

                $stmt->bind_param("i", $donhang_id);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $row['ten_san_pham']; ?></td>
                        <td><?php echo $row['so_luong']; ?></td>
                        <td><?php echo number_format($row['gia'], 0, ',', '.') . ' VNĐ'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>