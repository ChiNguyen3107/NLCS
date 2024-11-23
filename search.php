<?php
session_start(); // Khởi động session
include 'config.php'; // Kết nối đến cơ sở dữ liệu

$search_query = ''; // Khởi tạo biến tìm kiếm
$result = null; // Khởi tạo biến kết quả

if (isset($_GET['search'])) {
    $search_query = htmlspecialchars($_GET['search']); // Bảo mật đầu vào

    // Câu truy vấn tìm kiếm sản phẩm
    $sql = "SELECT s.*, h.ten_hang, a.anh 
            FROM sanpham s 
            LEFT JOIN hang h ON s.hang_id = h.id 
            LEFT JOIN anh_sanpham a ON s.id = a.sanpham_id 
            WHERE a.is_main = 1 and sanpham.trang_thai = 'active' AND s.ten_san_pham LIKE ?";

    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query . "%"; // Thêm ký tự % để tìm kiếm
    $stmt->bind_param("s", $like_query); // Ràng buộc tham số
    $stmt->execute(); // Thực thi truy vấn
    $result = $stmt->get_result(); // Lấy kết quả

    // Hiển thị kết quả tìm kiếm
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="product-item">';
            echo '<div class="product-image">';
            echo '<img src="uploads/' . htmlspecialchars($row["anh"]) . '" alt="' . htmlspecialchars($row["ten_san_pham"]) . '">';
            echo '</div>';
            echo '<div class="product-info">';
            echo '<h3 class="product-name">' . htmlspecialchars($row["ten_san_pham"]) . '</h3>';
            echo '<div class="price">' . number_format($row["gia"], 0, ',', '.') . ' VNĐ</div>';
            echo '<a href="chitiet_sanpham.php?id=' . $row['id'] . '">Xem chi tiết</a>';
            echo '</div>'; // Kết thúc div.product-info
            echo '</div>'; // Kết thúc div.product-item
        }
    } else {
        echo '<p>Không tìm thấy sản phẩm nào khớp với từ khóa: <strong>' . htmlspecialchars($search_query) . '</strong></p>';
    }
} else {
    echo '<p>Vui lòng nhập từ khóa tìm kiếm.</p>';
}

$conn->close(); // Đóng kết nối
?>