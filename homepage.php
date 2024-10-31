<?php
session_start();
include 'config.php';
$is_logged_in = isset($_SESSION['user_id']);
$isAdmin = $is_logged_in && $_SESSION['user_role'] == 3; // Sửa dòng này
$user_name = $is_logged_in ? $_SESSION['user_email'] : '';
$sql = "SELECT sanpham.id, sanpham.ten_san_pham, sanpham.gia, anh_sanpham.anh 
        FROM sanpham 
        LEFT JOIN anh_sanpham 
        ON sanpham.id = anh_sanpham.sanpham_id 
        WHERE anh_sanpham.is_main = 1"; // Chỉ lấy ảnh đại diện

$result = $conn->query($sql);
?>
<html>

<head>
    <title> Trang Web </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">

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
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php
                        $count = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $quantity) {
                                if (is_numeric($quantity)) {
                                    $count += (int) $quantity;
                                }
                            }
                        }
                        echo $count;
                        ?>
                    </span>
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

    <div class="filter-bar">
        <div class="container">
            <h3>Bộ lọc sản phẩm</h3>
            <div class="filter-options">
                <select id="brand" name="brand">
                    <option value="all">Thương hiệu</option>
                    <?php
                    // Lấy danh sách hãng từ CSDL
                    $sql_hang = "SELECT * FROM hang";
                    $result_hang = $conn->query($sql_hang);
                    while ($row_hang = $result_hang->fetch_assoc()) {
                        echo "<option value='" . $row_hang['id'] . "'>" . $row_hang['ten_hang'] . "</option>";
                    }
                    ?>
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
                <button id="reset-filter" class="reset-filter-btn">Đặt lại</button>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="product-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="product-item">';

                    // Hiển thị ảnh sản phẩm (ảnh đại diện)
                    echo '<div class="product-image">';
                    echo '<img alt="Ảnh sản phẩm ' . htmlspecialchars($row["ten_san_pham"]) . '" src="uploads/' . htmlspecialchars($row["anh"]) . '" />';
                    echo '</div>';

                    // Thông tin sản phẩm
                    echo '<div class="product-info">';

                    // Tên sản phẩm (hiển thị đầy đủ)
                    echo '<h3 class="product-name">' . htmlspecialchars($row["ten_san_pham"]) . '</h3>';

                    // Giá sản phẩm
                    echo '<div class="price">' . number_format($row["gia"], 0, ',', '.') . ' VND</div>';
                    echo '<a href="chitiet_sanpham.php?id=' . $row['id'] . '">Xem chi tiết</a>';
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Load sản phẩm khi trang được tải
            loadProducts();
            $('#reset-filter').click(function () {
                // Reset tất cả các select box về giá trị mặc định
                $('#brand, #price, #cpu, #ram, #storage').val('all');
                // Load lại tất cả sản phẩm
                loadProducts();
            });

            // Xử lý sự kiện khi nhấn nút lọc
            $('#apply-filter').click(function () {
                loadProducts();
            });

            function loadProducts() {
                var brand = $('#brand').val();
                var price = $('#price').val();
                var cpu = $('#cpu').val();
                var ram = $('#ram').val();
                var storage = $('#storage').val();

                $.ajax({
                    url: 'loc_sanpham.php',
                    type: 'GET',
                    data: {
                        brand: brand,
                        price: price,
                        cpu: cpu,
                        ram: ram,
                        storage: storage
                    },
                    success: function (response) {
                        $('.product-list').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi lọc sản phẩm');
                    }
                });
            }
        });
    </script>
</body>

</html>