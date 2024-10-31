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

    // Truy vấn thông tin đơn hàng và thông tin khách hàng
    $sql = "SELECT d.id, d.ngay_dat, d.ghi_chu, d.phuong_thuc_thanh_toan, d.tong_tien, d.trang_thai, 
               u.email, u.ho_ten, u.dien_thoai 
        FROM donhang d
        LEFT JOIN users u ON d.user_id = u.id 
        WHERE d.id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Lỗi prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $donhang_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $donhang = $result->fetch_assoc();
    } else {
        header("Location: quanlydonhang.php");
        exit();
    }

    // Lấy chi tiết đơn hàng
    $sql_chitiet = "SELECT cd.*, sp.ten_san_pham, sp.gia
                    FROM chitiet_donhang cd
                    JOIN sanpham sp ON cd.sanpham_id = sp.id
                    WHERE cd.donhang_id = ?";
    $stmt_chitiet = $conn->prepare($sql_chitiet);
    if (!$stmt_chitiet) {
        die("Lỗi prepare statement chi tiết: " . $conn->error);
    }
    $stmt_chitiet->bind_param("i", $donhang_id);
    $stmt_chitiet->execute();
    $result_chitiet = $stmt_chitiet->get_result();
    $chitiet_donhang = $result_chitiet->fetch_all(MYSQLI_ASSOC);

} else {
    header("Location: quanlydonhang.php");
    exit();
}

// Xử lý cập nhật thông tin đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trang_thai = $_POST['trang_thai'];
    $ghi_chu = $_POST['ghi_chu'];
    $phuong_thuc_thanh_toan = $_POST['phuong_thuc_thanh_toan'];
    $so_luong_moi = $_POST['so_luong'];

    try {
        // Bắt đầu transaction
        $conn->begin_transaction();

        // Cập nhật thông tin đơn hàng
        $sql = "UPDATE donhang SET 
        trang_thai = ?, 
        ghi_chu = ?, 
        phuong_thuc_thanh_toan = ?, 
        ngay_cap_nhat = NOW() 
    WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi prepare statement: " . $conn->error);
        }

        $stmt->bind_param("sssi", $trang_thai, $ghi_chu, $phuong_thuc_thanh_toan, $donhang_id);

        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi cập nhật đơn hàng: " . $stmt->error);
        }

        // Cập nhật số lượng và tính lại tổng tiền
        $tong_tien = 0;
        foreach ($chitiet_donhang as $index => $item) {
            $so_luong_moi_item = $so_luong_moi[$index];
            $gia = $item['gia'];
            $thanh_tien = $so_luong_moi_item * $gia;
            $tong_tien += $thanh_tien;

            // Cập nhật số lượng trong chi tiết đơn hàng
            $sql_update_chitiet = "UPDATE chitiet_donhang SET so_luong = ?, gia = ? WHERE id = ?";
            $stmt_update_chitiet = $conn->prepare($sql_update_chitiet);
            if (!$stmt_update_chitiet) {
                throw new Exception("Lỗi prepare statement cập nhật chi tiết: " . $conn->error);
            }
            $stmt_update_chitiet->bind_param("idi", $so_luong_moi_item, $thanh_tien, $item['id']);
            if (!$stmt_update_chitiet->execute()) {
                throw new Exception("Lỗi khi cập nhật chi tiết đơn hàng: " . $stmt_update_chitiet->error);
            }
        }

        // Cập nhật tổng tiền đơn hàng
        $sql_update_tong = "UPDATE donhang SET tong_tien = ? WHERE id = ?";
        $stmt_update_tong = $conn->prepare($sql_update_tong);
        if (!$stmt_update_tong) {
            throw new Exception("Lỗi prepare statement cập nhật tổng tiền: " . $conn->error);
        }
        $stmt_update_tong->bind_param("di", $tong_tien, $donhang_id);
        if (!$stmt_update_tong->execute()) {
            throw new Exception("Lỗi khi cập nhật tổng tiền đơn hàng: " . $stmt_update_tong->error);
        }

        // Commit transaction
        $conn->commit();

        // Chuyển hướng về trang quản lý đơn hàng
        header("Location: quanlydonhang.php");
        exit();

    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa đơn hàng #<?php echo $donhang_id; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <h2 class="mb-4">Sửa đơn hàng #<?php echo $donhang_id; ?></h2>

        <a href="quanlydonhang.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thông tin khách hàng</h5>
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($donhang['ho_ten']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($donhang['email']); ?></p>
                <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($donhang['dien_thoai']); ?></p>
            </div>
        </div>

        <form method="POST" id="editOrderForm">
            <div class="form-group">
                <label for="trang_thai">Trạng thái:</label>
                <select name="trang_thai" id="trang_thai" class="form-control">
                    <option value="pending " <?php echo ($donhang['trang_thai'] == 'pending') ? 'selected' : ''; ?>>Chờ xử
                        lý</option>
                    <option value="processing" <?php echo ($donhang['trang_thai'] == 'processing') ? 'selected' : ''; ?>>
                        Đang xử lý</option>
                    <option value="shipped" <?php echo ($donhang['trang_thai'] == 'shipped') ? 'selected' : ''; ?>>Đã giao
                    </option>
                    <option value="cancelled" <?php echo ($donhang['trang_thai'] == 'cancelled') ? 'selected' : ''; ?>>Đã
                        hủy</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ghi_chu">Ghi chú:</label>
                <textarea name="ghi_chu" id="ghi_chu" class="form-control"
                    rows="3"><?php echo htmlspecialchars($donhang['ghi_chu']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="phuong_thuc_thanh_toan">Phương thức thanh toán:</label>
                <select name="phuong_thuc_thanh_toan" id="phuong_thuc_thanh_toan" class="form-control">
                    <option value="store" <?php echo ($donhang['phuong_thuc_thanh_toan'] == 'store') ? 'selected' : ''; ?>>Tại cửa hàng</option>
                    <option value="cod" <?php echo ($donhang['phuong_thuc_thanh_toan'] == 'cod') ? 'selected' : ''; ?>>
                        Thanh toán khi nhận hàng</option>
                    <option value="bank" <?php echo ($donhang['phuong_thuc_thanh_toan'] == 'bank') ? 'selected' : ''; ?>>
                        Chuyển khoản ngân hàng</option>
                    <option value="installment" <?php echo ($donhang['phuong_thuc_thanh_toan'] == 'installment') ? 'selected' : ''; ?>>Trả góp</option>
                </select>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chitiet_donhang as $index => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['ten_san_pham']); ?></td>
                            <td>
                                <input type="number" name="so_luong[]" value="<?php echo $item['so_luong']; ?>" min="1"
                                    class="form-control" data-gia="<?php echo $item['gia']; ?>">
                            </td>
                            <td class="gia" data-gia="<?php echo $item['gia']; ?>">
                                <?php echo number_format($item['gia'], 0, ',', '.'); ?> VNĐ
                            </td>
                            <td class="thanh-tien">
                                <?php echo number_format($item['gia'] * $item['so_luong'], 0, ',', '.'); ?> VNĐ
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Tổng tiền:</th>
                        <th id="tong-tien">
                            <?php echo number_format($donhang['tong_tien'], 0, ',', '.'); ?> VNĐ
                        </th>
                    </tr>
                </tfoot>
            </table>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </form>

        <script>
            $(document).ready(function () {
                $('#editOrderForm').on('change', 'input[name="so_luong[]"]', function () {
                    var index = $(this).closest('tr').index();
                    var so_luong = parseInt($(this).val()) || 0;
                    // Lấy giá và xử lý chuỗi để chỉ giữ lại số
                    var gia = parseInt($(this).closest('tr').find('td:eq(2)').text()
                        .replace(/[^\d]/g, '')) || 0;

                    // Tính thành tiền
                    var thanh_tien = so_luong * gia;

                    // Cập nhật thành tiền cho sản phẩm
                    $(this).closest('tr').find('td:eq(3)').text(
                        new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(thanh_tien).replace('₫', 'VNĐ')
                    );

                    // Tính tổng tiền
                    var tong_tien = 0;
                    $('tbody tr').each(function () {
                        var thanh_tien_item = parseInt($(this).find('td:eq(3)').text()
                            .replace(/[^\d]/g, '')) || 0;
                        tong_tien += thanh_tien_item;
                    });

                    // Cập nhật tổng tiền
                    $('#tong-tien').text(
                        new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(tong_tien).replace('₫', 'VNĐ')
                    );
                });
            });
        </script>
    </div>
</body>

</html>