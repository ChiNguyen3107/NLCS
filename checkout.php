<?php
session_start();
include 'config.php';

// Kiểm tra giỏ hàng có tồn tại và không rỗng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}



// Kiểm tra người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['user_id']);
$user_info = null;

if ($is_logged_in) {
    // Lấy thông tin người dùng từ database
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();

        // Xử lý thông tin người dùng
        if ($is_logged_in) {
            // Nếu đã đăng nhập, cập nhật thông tin người dùng nếu có thay đổi
            $user_id = $_SESSION['user_id'];
            $ho_ten = $_POST['ho_ten'];
            $dien_thoai = $_POST['dien_thoai'];
            $dia_chi = $_POST['dia_chi'];

            $update_sql = "UPDATE users SET ho_ten = ?, dien_thoai = ?, dia_chi = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            $stmt->bind_param("sssi", $ho_ten, $dien_thoai, $dia_chi, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật thông tin user: " . $stmt->error);
            }
        } else {
            // Nếu chưa đăng nhập, xử lý đăng ký/cập nhật thông tin
            $ho_ten = $_POST['ho_ten'];
            $dien_thoai = $_POST['dien_thoai'];
            $dia_chi = $_POST['dia_chi'];
            $email = $_POST['email'];

            // Kiểm tra email đã tồn tại chưa
            $check_email = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_email);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Email đã tồn tại
                $user = $result->fetch_assoc();
                $user_id = $user['id'];

                // Cập nhật thông tin
                $update_sql = "UPDATE users SET ho_ten = ?, dien_thoai = ?, dia_chi = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                $stmt->bind_param("sssi", $ho_ten, $dien_thoai, $dia_chi, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi cập nhật thông tin user: " . $stmt->error);
                }
            } else {
                // Tạo tài khoản mới
                $default_password = "11111";
                $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
                $role_id = 2; // Role khách hàng

                $sql = "INSERT INTO users (ho_ten, dien_thoai, dia_chi, email, mat_khau, role_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                $stmt->bind_param("sssssi", $ho_ten, $dien_thoai, $dia_chi, $email, $hashed_password, $role_id);
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi thêm user: " . $stmt->error);
                }
                $user_id = $conn->insert_id;
            }
        }

        // Tính tổng tiền đơn hàng
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql = "SELECT gia FROM sanpham WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi lấy giá sản phẩm: " . $stmt->error);
            }
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $total_amount += $row['gia'] * $quantity;
            }
        }

        // Tạo đơn hàng
        $phuong_thuc_thanh_toan = $_POST['phuong_thuc_thanh_toan'];
        $ghi_chu = isset($_POST['ghi_chu']) ? $_POST['ghi_chu'] : '';
        $trang_thai = 'pending'; // Trạng thái mặc định

        $sql = "INSERT INTO donhang (user_id, ngay_dat, ghi_chu, phuong_thuc_thanh_toan, tong_tien, trang_thai) 
                VALUES (?, NOW(), ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi prepare statement: " . $conn->error);
        }
        $stmt->bind_param("issds", $user_id, $ghi_chu, $phuong_thuc_thanh_toan, $total_amount, $trang_thai);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi tạo đơn hàng: " . $stmt->error);
        }
        $donhang_id = $conn->insert_id;

        // Xử lý chi tiết đơn hàng
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Kiểm tra và cập nhật số lượng sản phẩm
            $sql = "SELECT gia, so_luong FROM sanpham WHERE id = ? FOR UPDATE";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            $stmt->bind_param("i", $product_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi lấy thông tin sản phẩm: " . $stmt->error);
            }
            $result = $stmt->get_result();

            if ($product_info = $result->fetch_assoc()) {
                // Kiểm tra số lượng tồn kho
                if ($product_info['so_luong'] < $quantity) {
                    throw new Exception("Sản phẩm ID {$product_id} không đủ số lượng trong kho");
                }

                // Thêm chi tiết đơn hàng
                $sql = "INSERT INTO chitiet_donhang (donhang_id, sanpham_id, so_luong, gia) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                $stmt->bind_param("iiid", $donhang_id, $product_id, $quantity, $product_info['gia']);
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi thêm chi tiết đơn hàng: " . $stmt->error);
                }

                // Cập nhật số lượng sản phẩm
                $sql = "UPDATE sanpham SET so_luong = so_luong - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                $stmt->bind_param("ii", $quantity, $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi cập nhật số lượng sản phẩm: " . $stmt->error);
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Xóa giỏ hàng
        unset($_SESSION['cart']);

        $_SESSION['order_success'] = "Đặt hàng thành công! Cảm ơn bạn đã mua sắm.";
        header('Location: thank_you.php');
        exit();

    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        echo "Có lỗi xảy ra: " . $e->getMessage();
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Checkout Styles */
        .checkout-container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .checkout-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .checkout-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            padding: 15px 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            transition: all 0.3s ease;
            position: relative;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -10px;
            width: 20px;
            height: 2px;
            background-color: #ddd;
            transform: translateY(-50%);
        }

        .progress-step.active {
            background-color: #007bff;
            color: white;
        }

        .progress-step.active::after {
            background-color: #007bff;
        }

        .progress-step i {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .progress-step span {
            display: block;
            font-size: 14px;
        }

        .checkout-content {
            display: flex;
            gap: 30px;
        }

        .checkout-form {
            flex: 1;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .form-step h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .btn-next,
        .btn-prev,
        .btn-submit {
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-next:hover,
        .btn-prev:hover,
        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-prev {
            background-color: #6c757d;
            margin-right: 10px;
        }

        .btn-prev:hover {
            background-color: #545b62;
        }

        .order-summary {
            flex: 0 0 35%;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            align-self: flex-start;
        }

        .order-summary h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .order-summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-summary-table th,
        .order-summary-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .order-summary-table th {
            font-weight: 600;
            color: #555;
        }

        .order-total {
            font-weight: bold;
            font-size: 1.1em;
            color: #28a745;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }

            .order-summary {
                margin-top: 30px;
            }

            .progress-step span {
                display: none;
            }

            .progress-step i {
                font-size: 20px;
            }

            .btn-next,
            .btn-prev,
            .btn-submit {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="checkout-container">
        <h1 class="checkout-title">Thanh toán</h1>

        <div class="checkout-progress">
            <div class="progress-step active">
                <i class="fas fa-user"></i>
                <span>Thông tin</span>
            </div>
            <div class="progress-step">
                <i class="fas fa-truck"></i>
                <span>Giao hàng</span>
            </div>
            <div class="progress-step">
                <i class="fas fa-credit-card"></i>
                <span>Thanh toán</span>
            </div>
        </div>

        <div class="checkout-content">
            <form action="" method="post" class="checkout-form">
                <div class="form-step active" id="step1">
                    <h2>Thông tin cá nhân</h2>
                    <div class="form-group">
                        <label for="ho_ten">Họ tên:</label>
                        <input type="text" id="ho_ten" name="ho_ten"
                            value="<?php echo $is_logged_in ? htmlspecialchars($user_info['ho_ten']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo $is_logged_in ? htmlspecialchars($user_info['email']) : ''; ?>" required
                            <?php echo $is_logged_in ? 'readonly' : ''; ?>>
                    </div>
                    <div class="form-group">
                        <label for="dien_thoai">Điện thoại:</label>
                        <input type="tel" id="dien_thoai" name="dien_thoai"
                            value="<?php echo $is_logged_in ? htmlspecialchars($user_info['dien_thoai']) : ''; ?>"
                            required>
                    </div>
                    <button type="button" class="btn-next" onclick="nextStep(1)">Tiếp theo</button>
                </div>

                <div class="form-step" id="step2">
                    <h2>Thông tin giao hàng</h2>
                    <div class="form-group">
                        <label for="dia_chi">Địa chỉ:</label>
                        <textarea id="dia_chi" name="dia_chi"
                            required><?php echo $is_logged_in ? htmlspecialchars($user_info['dia_chi']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú:</label>
                        <textarea id="ghi_chu" name="ghi_chu"></textarea>
                    </div>
                    <button type="button" class="btn-prev" onclick="prevStep(2)">Quay lại</button>
                    <button type="button" class="btn-next" onclick="nextStep(2)">Tiếp theo</button>
                </div>

                <div class="form-step" id="step3">
                    <h2>Phương thức thanh toán</h2>
                    <div class="form-group">
                        <label for="phuong_thuc_thanh_toan">Chọn phương thức:</label>
                        <select id="phuong_thuc_thanh_toan" name="phuong_thuc_thanh_toan">
                            <option value="store">Tại cửa hàng</option>
                            <option value="cod">Thanh toán khi nhận hàng</option>
                            <option value="bank">Chuyển khoản ngân hàng</option>
                            <option value="installment">Trả góp</option>
                        </select>
                    </div>
                    <button type="button" class="btn-prev" onclick="prevStep(3)">Quay lại</button>
                    <button type="submit" class="btn-submit">Hoàn tất đặt hàng</button>
                </div>
            </form>

            <div class="order-summary">
                <h3>Tóm tắt đơn hàng</h3>
                <table class="order-summary-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_amount = 0;
                        foreach ($_SESSION['cart'] as $product_id => $quantity):
                            $sql = "SELECT ten_san_pham, gia FROM sanpham WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($product = $result->fetch_assoc()):
                                $subtotal = $product['gia'] * $quantity;
                                $total_amount += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['ten_san_pham']); ?></td>
                                    <td><?php echo $quantity; ?></td>
                                    <td><?php echo number_format($product['gia'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</td>
                                </tr>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Tổng cộng:</strong></td>
                            <td><strong><?php echo number_format($total_amount, 0, ',', '.'); ?> VNĐ</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        function nextStep(currentStep) {
            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + (currentStep + 1)).classList.add('active');
            updateProgress(currentStep + 1);
        }

        function prevStep(currentStep) {
            document.getElementById('step' + currentStep).classList.remove('active');
            document.getElementById('step' + (currentStep - 1)).classList.add('active');
            updateProgress(currentStep - 1);
        }

        function updateProgress(step) {
            const steps = document.querySelectorAll('.progress-step');
            steps.forEach((s, index) => {
                if (index < step) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        }
    </script>
</body>

</html>