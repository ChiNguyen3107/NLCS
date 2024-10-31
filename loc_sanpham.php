<?php
include 'config.php';

// Câu truy vấn cơ bản
$sql = "SELECT DISTINCT s.*, h.ten_hang, a.anh 
        FROM sanpham s 
        LEFT JOIN hang h ON s.hang_id = h.id 
        LEFT JOIN anh_sanpham a ON s.id = a.sanpham_id 
        WHERE a.is_main = 1";

$conditions = [];
$params = [];
$types = "";

// Lọc theo thương hiệu
if (isset($_GET['brand']) && $_GET['brand'] != 'all') {
    $conditions[] = "s.hang_id = ?";
    $params[] = $_GET['brand'];
    $types .= "i";
}

// Lọc theo giá
if (isset($_GET['price']) && $_GET['price'] != 'all') {
    switch ($_GET['price']) {
        case '0-10m':
            $conditions[] = "s.gia < 10000000";
            break;
        case '10m-15m':
            $conditions[] = "s.gia BETWEEN 10000000 AND 15000000";
            break;
        case '15m-20m':
            $conditions[] = "s.gia BETWEEN 15000000 AND 20000000";
            break;
        case '20m+':
            $conditions[] = "s.gia > 20000000";
            break;
    }
}

// Lọc theo CPU
if (isset($_GET['cpu']) && $_GET['cpu'] != 'all') {
    $conditions[] = "s.cpu LIKE ?";
    $params[] = "%" . str_replace('-', ' ', $_GET['cpu']) . "%";
    $types .= "s";
}

// Lọc theo RAM
if (isset($_GET['ram']) && $_GET['ram'] != 'all') {
    $conditions[] = "s.ram LIKE ?";
    $params[] = "%" . $_GET['ram'] . "%";
    $types .= "s";
}

// Lọc theo ổ cứng
if (isset($_GET['storage']) && $_GET['storage'] != 'all') {
    $conditions[] = "s.o_cung LIKE ?";
    $params[] = "%" . $_GET['storage'] . "%";
    $types .= "s";
}

// Thêm điều kiện vào câu truy vấn
if (!empty($conditions)) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Tạo output HTML
$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= '<div class="product-item">';
        $output .= '<div class="product-image">';
        if ($row["anh"]) {
            $output .= '<img src="uploads/' . htmlspecialchars($row["anh"]) . '" alt="' . htmlspecialchars($row["ten_san_pham"]) . '">';
        } else {
            $output .= '<img src="uploads/no-image.jpg" alt="No image">';
        }
        $output .= '</div>';
        $output .= '<div class="product-info">';
        $output .= '<h3 class="product-name">' . htmlspecialchars($row["ten_san_pham"]) . '</h3>';
        $output .= '<div class="price">' . number_format($row["gia"], 0, ',', '.') . ' VNĐ</div>';
        $output .= '<a href="chitiet_sanpham.php?id=' . $row['id'] . '">Xem chi tiết</a>';
        $output .= '</div>';
        $output .= '</div>';
    }
} else {
    $output = '<p>Không có sản phẩm nào khớp với bộ lọc!</p>';
}

echo $output;
?>