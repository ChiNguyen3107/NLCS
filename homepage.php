<?php
session_start();
include 'config.php';
$is_logged_in = isset($_SESSION['user_id']);
$isAdmin = $is_logged_in && $_SESSION['user_role'] == 3; // Sửa dòng này
$user_name = $is_logged_in ? $_SESSION['user_email'] : '';
$sql = "SELECT s.id, s.ten_san_pham, s.gia, a.anh 
        FROM sanpham s 
        LEFT JOIN anh_sanpham a ON s.id = a.sanpham_id 
        WHERE a.is_main = 1 OR a.is_main IS NULL
        LIMIT 8";
$result = $conn->query($sql);
?>
<html>

<head>
    <title> Trang Web </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset mặc định */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Cài đặt font chung */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background-color: ;
            padding: 20px 0;
            color: white;
        }

        .logo-section {
            display: flex;
            align-items: center;
        }

        .logo-section i {
            font-size: 40px;
            margin-right: 10px;
        }

        .brand-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .brand-info p {
            font-size: 14px;
        }

        .search-bar {
            margin-top: 20px;
            display: flex;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: none;
            border-radius: 5px;
        }

        .search-bar button {
            padding: 10px 15px;
            border: none;
            background-color: #333;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .search-bar button:hover {
            background-color: #555;
        }

        .contact-info {
            margin-top: 10px;
            font-size: 16px;
        }


        /* Main Banner */
        .main-banner {
            position: relative;
        }

        .banner-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .banner-buttons button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 24px;
        }

        .banner-buttons #prevBtn {
            left: 10px;
        }

        .banner-buttons #nextBtn {
            right: 10px;
        }

        .banner-buttons button:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        /* Product Section */
        .product-section {
            padding: 40px 0;
        }

        .filter-bar {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .filter-bar label {
            font-weight: bold;
        }

        .filter-bar select {
            padding: 8px;
            margin-left: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .product-item {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s;
            width: calc(25% - 20px);
        }

        .product-item:hover {
            transform: scale(1.05);
        }

        .product-item img {
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .product-info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .product-info .price {
            color: #4CAF50;
            font-size: 16px;
        }

        .product-info .old-price {
            color: #888;
            text-decoration: line-through;
            margin-left: 10px;
        }

        /* Footer */
        .footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
            margin-top: 40px;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .footer-info h3 {
            margin-bottom: 15px;
        }

        .footer-info ul {
            list-style-type: none;
        }

        .footer-info ul li {
            margin-bottom: 10px;
        }

        .footer-info ul li a {
            color: white;
            text-decoration: none;
        }

        .footer-info ul li a:hover {
            color: #4CAF50;
        }

        .social-icons {
            text-align: center;
        }

        .social-icons a {
            color: white;
            font-size: 20px;
            margin: 0 10px;
            text-decoration: none;
        }

        .social-icons a:hover {
            color: #4CAF50;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="logo-section">
                <i class="fas fa-laptop"></i>
                <div class="brand-info">
                    <h1>Laptop88</h1>
                    <p>Trang web bán laptop uy tín số 1 Việt Nam</p>
                </div>
            </div>
            <div class="search-bar">
                <input placeholder="Tìm kiếm sản phẩm..." type="text" />
                <button> Tìm kiếm </button>
            </div>
            <div class="contact-info">
                <div>
                    <i class="fas fa-phone-alt"></i> 0835.886.837
                </div>

            </div>
            <div class="cart">
                <a href="#" id="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
            </div>
            <div class="account">
                <div class="account-dropdown">
                    <button class="account-btn">
                        <i class="fas fa-user"></i>
                        <?php if (!$is_logged_in): ?>
                            <span>Đăng nhập</span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-content">
                        <?php if ($is_logged_in): ?>
                            <!-- <a href="profile.php">Thông tin tài khoản</a> -->
                            <?php if ($isAdmin): ?>
                                <a href="admin_dashboard.php">Admin Dashboard</a>
                            <?php endif; ?>
                            <a href="dangxuat.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="dangnhap.php">Đăng nhập</a>
                            <a href="dangky.php">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


        </div>
    </div>
    </div>
    <div class="navbar">
        <div class="navbar-container">
            <div class="nav-links">
                <a href="#"> Trang Chủ </a>
                <a href="#"> Laptop mới </a>
                <a href="#"> Laptop like new </a>
                <a href="#"> Bảo hành </a>
                <a href="#"> Trả góp </a>
            </div>

        </div>
    </div>
    <div class="main-banner">
        <div class="banner-container">
            <div class="banner-item"><img src="image/banner1.jpg" alt="banner1"></div>
            <div class="banner-item"><img src="image/banner2.jpg" alt="banner2"></div>
            <div class="banner-item"><img src="image/banner3.jpg" alt="banner3"></div>
            <div class="banner-item"><img src="image/banner4.jpg" alt="banner4"></div>
            <div class="banner-item"><img src="image/banner5.jpg" alt="banner5"></div>
            <div class="banner-item"><img src="image/banner6.jpg" alt="banner6"></div>
            <div class="banner-item"><img src="image/banner7.jpg" alt="banner7"></div>
            <div class="banner-item"><img src="image/banner8.jpg" alt="banner8"></div>
            <div class="banner-item"><img src="image/banner9.jpg" alt="banner9"></div>
            <div class="banner-item"><img src="image/banner10.jpg" alt="banner10"></div>
            <div class="banner-item"><img src="image/banner11.jpg" alt="banner11"></div>
            <div class="banner-item"><img src="image/banner12.jpg" alt="banner12"></div>
        </div>
        <div class="banner-buttons">
            <button id="prevBtn">&#10094;</button>
            <button id="nextBtn">&#10095;</button>
        </div>
    </div>

    <div class="product-section">
        <div class="filter-bar">
            <div class="container">
                <h3>Bộ lọc sản phẩm</h3>
                <div class="filter-options">
                    <select id="brand" name="brand">
                        <option value="all">Thương hiệu</option>
                        <option value="dell">Dell</option>
                        <option value="hp">HP</option>
                        <option value="lenovo">Lenovo</option>
                        <option value="asus">Asus</option>
                        <option value="acer">Acer</option>
                    </select>
                    <select id="price" name="price">
                        <option value="all">Giá</option>
                        <option value="0-10m">Dưới 10 triệu</option>
                        <option value="10m-15m">10 - 15 triệu</option>
                        <option value="15m-20m">15 - 20 triệu</option>
                        <option value="20m+">Trên 20 triệu</option>
                    </select>
                    <select id="cpu" name="cpu">
                        <option value="all">CPU</option>
                        <option value="intel-i3">Intel Core i3</option>
                        <option value="intel-i5">Intel Core i5</option>
                        <option value="intel-i7">Intel Core i7</option>
                        <option value="amd-ryzen">AMD Ryzen</option>
                    </select>
                    <select id="ram" name="ram">
                        <option value="all">RAM</option>
                        <option value="4gb">4GB</option>
                        <option value="8gb">8GB</option>
                        <option value="16gb">16GB</option>
                        <option value="32gb">32GB</option>
                    </select>
                    <select id="storage" name="storage">
                        <option value="all">Ổ cứng</option>
                        <option value="ssd-256gb">SSD 256GB</option>
                        <option value="ssd-512gb">SSD 512GB</option>
                        <option value="ssd-1tb">SSD 1TB</option>
                        <option value="hdd-1tb">HDD 1TB</option>
                    </select>
                    <button id="apply-filter" class="apply-filter-btn">Lọc</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <h2> Các Sản Phẩm </h2>
        <div class="product-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="product-item">';
                    if (!empty($row["anh"])) {
                        echo '<img alt="' . htmlspecialchars($row["ten_san_pham"]) . '" src="' . htmlspecialchars($row["anh"]) . '" width="250" height="150" />';
                    } else {
                        echo '<img alt="No Image" src="placeholder_image.jpg" width="250" height="150" />';
                    }
                    echo '<div class="product-info">';
                    echo '<h3>' . htmlspecialchars($row["ten_san_pham"]) . '</h3>';
                    echo '<div class="price">' . number_format($row["gia"], 0, ',', '.') . ' VND</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "Không có sản phẩm nào.";
            }
            ?>
        </div>
    </div>
    </div>
    <footer class="footer">
        <div class="container">
            <div class="footer-info">
                <div class="footer-column">
                    <h3>Về Chúng Tôi</h3>
                    <ul>
                        <li><a href="#">Thông Tin Công Ty</a></li>
                        <li><a href="#">Tuyển Dụng</a></li>
                        <li><a href="#">Báo Chí</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Dịch Vụ Khách Hàng</h3>
                    <ul>
                        <li><a href="#">Liên Hệ</a></li>
                        <li><a href="#">Theo Dõi Đơn Hàng</a></li>
                        <li><a href="#">Đổi Trả</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Liên Kết Nhanh</h3>
                    <ul>
                        <li><a href="#">Sản Phẩm Mới</a></li>
                        <li><a href="#">Bán Chạy</a></li>
                        <li><a href="#">Ưu Đãi Đặc Biệt</a></li>
                    </ul>
                </div>
            </div>
            <div class="social-icons">
                <a href="#" target="_blank"><i class="fa fa-facebook"></i></a>
                <a href="#" target="_blank"><i class="fa fa-twitter"></i></a>
                <a href="#" target="_blank"><i class="fa fa-instagram"></i></a>
            </div>
            <div class="footer-bottom">
                &copy; 2023 Công Ty TNHH Thương Mại Điện Tử Nguyendeptrai
            </div>
        </div>
    </footer>
    <script src="script.js"></script>
</body>

</html>