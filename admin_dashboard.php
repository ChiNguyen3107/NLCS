<?php
session_start();
include 'config.php';

// Kiểm tra xem người dùng đã đăng nhập và có quyền admin không
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Lấy thông tin người dùng
$user_email = $_SESSION['user_email'];

// Thực hiện các truy vấn để lấy thống kê
$sql_products = "SELECT COUNT(*) as total FROM sanpham";
$sql_users = "SELECT COUNT(*) as total FROM users";
$sql_orders = "SELECT COUNT(*) as total FROM donhang";
$sql_revenue = "SELECT SUM(tong_tien) as total FROM donhang WHERE trang_thai != 'cancelled'";

$result_products = $conn->query($sql_products);
$result_users = $conn->query($sql_users);
$result_orders = $conn->query($sql_orders);
$result_revenue = $conn->query($sql_revenue);

// Khởi tạo các biến với giá trị mặc định
$total_products = 0;
$total_users = 0;
$total_orders = 0;
$total_revenue = 0;

// Gán giá trị từ kết quả truy vấn
if ($result_products && $row = $result_products->fetch_assoc()) {
    $total_products = $row['total'];
}

if ($result_users && $row = $result_users->fetch_assoc()) {
    $total_users = $row['total'];
}

if ($result_orders && $row = $result_orders->fetch_assoc()) {
    $total_orders = $row['total'];
}

if ($result_revenue && $row = $result_revenue->fetch_assoc()) {
    $total_revenue = $row['total'] ?? 0; // Sử dụng null coalescing operator
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .dashboard {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #333;
            color: #fff;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px 20px;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li:hover {
            background-color: #555;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 10px;
        }

        main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h3 {
            margin-top: 0;
            color: #333;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="homepage.php"><i class="fas fa-home"></i> Trang Chủ</a></li>
                <li><a href="quanlysanpham.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Người dùng</a></li>
                <li><a href="quanlydonhang.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Cài đặt</a></li>
                <li><a href="dangxuat.php">Đăng xuất</a></li>
            </ul>
        </div>
        <div class="main-content">
            <header>
                <h1>Xin chào, Admin</h1>
                <div class="user-info">

                </div>
            </header>
            <main>
                <div class="card">
                    <h3>Tổng số sản phẩm</h3>
                    <p><?php echo number_format($total_products); ?></p>
                </div>
                <div class="card">
                    <h3>Tổng số người dùng</h3>
                    <p><?php echo number_format($total_users); ?></p>
                </div>
                <div class="card">
                    <h3>Tổng số đơn hàng</h3>
                    <p><?php echo number_format($total_orders); ?></p>
                </div>
                <div class="card">
                    <h3>Tổng doanh thu</h3>
                    <p><?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ</p>
                </div>
            </main>
        </div>
    </div>
</body>

</html>