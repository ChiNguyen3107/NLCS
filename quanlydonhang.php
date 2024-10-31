<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Xử lý xóa đơn hàng
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM donhang WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Đơn hàng đã được xóa thành công.";
    } else {
        $error_message = "Có lỗi xảy ra khi xóa đơn hàng.";
    }
}

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    $sql = "UPDATE donhang SET trang_thai = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $success_message = "Cập nhật trạng thái đơn hàng thành công.";
    } else {
        $error_message = "Có lỗi xảy ra khi cập nhật trạng thái đơn hàng.";
    }
}

// Lấy danh sách đơn hàng
$sql = "SELECT donhang.*, users.email, users.ho_ten, users.dien_thoai 
        FROM donhang 
        LEFT JOIN users ON donhang.user_id = users.id 
        ORDER BY donhang.ngay_dat DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .container {
            margin-top: 20px;
        }

        .table th {
            vertical-align: middle;
        }

        .status-select {
            min-width: 150px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Quản lý đơn hàng</h2>

        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Quay về Dashboard
        </a>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Thông tin liên hệ</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php echo $row['ho_ten']; ?><br>
                                <small><?php echo $row['email']; ?></small>
                            </td>
                            <td><?php echo $row['dien_thoai']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?></td>
                            <td><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="new_status" class="form-control status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo ($row['trang_thai'] == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="processing" <?php echo ($row['trang_thai'] == 'processing') ? 'selected' : ''; ?>>Đang xử lý</option>
                                        <option value="shipped" <?php echo ($row['trang_thai'] == 'shipped') ? 'selected' : ''; ?>>Đã giao</option>
                                        <option value="cancelled" <?php echo ($row['trang_thai'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>

                                <button class="btn btn-sm btn-warning edit-btn"
                                    onclick="window.location.href='sua_donhang.php?id=<?php echo $row['id']; ?>'">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>

                                <button class="btn btn-sm btn-danger delete-btn"
                                    onclick="deleteOrder(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function deleteOrder(id) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
                window.location.href = 'quanlydonhang.php?delete=' + id;
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>