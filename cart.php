<?php
session_start();
include 'config.php';

// Khởi tạo giỏ hàng trong session nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hiển thị giỏ hàng
$cart_items = [];
$total_amount = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $sql = "SELECT * FROM sanpham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $cart_items[] = [
            'id' => $product_id,
            'ten_san_pham' => $product['ten_san_pham'],
            'gia' => $product['gia'],
            'so_luong' => $quantity,
            'tong' => $product['gia'] * $quantity
        ];
        $total_amount += $product['gia'] * $quantity;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Giỏ hàng</title>
</head>

<body>
    <div class="cart-container">
        <h1 class="cart-title">Giỏ hàng của bạn</h1>

        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <p>Giỏ hàng trống</p>
                <a href="homepage.php" class="cart-continue-shopping">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo $item['ten_san_pham']; ?></td>
                            <td>
                                <input type="number" min="1" value="<?php echo $item['so_luong']; ?>"
                                    class="cart-quantity-input"
                                    onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                            </td>
                            <td><?php echo number_format($item['gia'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo number_format($item['tong'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <button onclick="removeItem(<?php echo $item['id']; ?>)" class="cart-remove-btn">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Tổng cộng:</strong></td>
                        <td colspan="2" class="cart-total"><strong><?php echo number_format($total_amount, 0, ',', '.'); ?>
                                VNĐ</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="cart-actions">
                <a href="homepage.php" class="cart-continue-shopping">Tiếp tục mua sắm</a>
                <a href="checkout.php" class="cart-checkout">Thanh toán</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(productId, quantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function removeItem(productId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }
        }
    </script>
</body>

</html>