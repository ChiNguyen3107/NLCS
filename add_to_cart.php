<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // Kiểm tra sản phẩm có tồn tại và còn hàng không
    $sql = "SELECT id, so_luong FROM sanpham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product && $product['so_luong'] > 0) {
        // Thêm hoặc cập nhật số lượng trong giỏ hàng
        if (isset($_SESSION['cart'][$product_id])) {
            // Kiểm tra xem việc thêm có vượt quá số lượng tồn kho không
            if ($_SESSION['cart'][$product_id] < $product['so_luong']) {
                $_SESSION['cart'][$product_id]++;
                $total_items = 0;
                foreach ($_SESSION['cart'] as $qty) {
                    $total_items += (int)$qty;
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                    'cart_count' => $total_items
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm trong giỏ hàng đã đạt mức tối đa'
                ]);
            }
        } else {
            $_SESSION['cart'][$product_id] = 1;
            $total_items = array_sum($_SESSION['cart']);
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $total_items
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không tồn tại hoặc đã hết hàng'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Yêu cầu không hợp lệ'
    ]);
}
?>