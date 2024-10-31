<?php
session_start(); // Đảm bảo session đã được khởi động
include 'config.php';
$is_logged_in = isset($_SESSION['user_id']);

// Lấy ID sản phẩm từ URL
if (isset($_GET['id'])) {
    $sanpham_id = intval($_GET['id']);

    // Truy vấn thông tin sản phẩm
    $sql = "SELECT sanpham.*, hang.ten_hang 
            FROM sanpham 
            JOIN hang ON sanpham.hang_id = hang.id 
            WHERE sanpham.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sanpham_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy thông tin sản phẩm!";
        exit();
    }

    // Truy vấn hình ảnh sản phẩm
    $sql_images = "SELECT anh FROM anh_sanpham WHERE sanpham_id = ?";
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bind_param("i", $sanpham_id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();

    $images = [];
    while ($row = $result_images->fetch_assoc()) {
        $images[] = $row['anh'];
    }
} else {
    echo "Không có ID sản phẩm!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm - <?php echo $product['ten_san_pham']; ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <style>
        .product-detail {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .product-images {
            flex: 1;
            min-width: 280px;
            margin-right: 20px;
            position: relative;
            /* Đảm bảo các nút mũi tên có thể được định vị chính xác */
        }

        #main-image {
            width: 700px;
            /* Chiều rộng cố định cho ảnh */
            height: 700px;
            /* Chiều cao cố định cho ảnh */
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            object-fit: cover;
            /* Đảm bảo ảnh sẽ được cắt để vừa với kích thước cố định */
        }


        .thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            margin-top: 10px;
        }

        .thumbnails img {
            width: 90px;
            /* Chiều rộng cố định cho ảnh thu nhỏ */
            height: 8 0px;
            /* Chiều cao cố định cho ảnh thu nhỏ */
            object-fit: cover;
            /* Đảm bảo ảnh thu nhỏ sẽ được cắt cho vừa khung */
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }


        .thumbnails img:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }

        .product-info {
            flex: 1;
            min-width: 280px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .product-info h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .product-info h2 {
            color: #e63946;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .product-info h2 .original-price {
            font-size: 16px;
            color: #aaa;
            text-decoration: line-through;
            margin-left: 15px;
        }

        .product-info table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .product-info table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .product-info table td:first-child {
            font-weight: bold;
            color: #666;
            width: 35%;
        }

        .configurations label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .buy-now {
            display: block;
            width: 100%;
            background-color: #ff5722;
            color: #fff;
            padding: 12px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .buy-now:hover {
            background-color: #e64a19;
        }

        .installment-options {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        .installment-options div {
            flex: 1;
            background-color: #1976d2;
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .installment-options div:hover {
            background-color: #115293;
        }

        

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .share-buttons button {
            display: flex;
            align-items: center;
            background-color: #3b5998;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .share-buttons button i {
            margin-right: 5px;
        }

        .share-buttons button:hover {
            background-color: #2d4373;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .product-detail {
                flex-direction: column;
            }

            .product-info {
                margin-top: 20px;
            }
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

    <div class="product-detail">
        <div class="product-images">
            <?php if (!empty($images)): ?>
                <img id="main-image" src="uploads/<?php echo $images[0]; ?>" alt="Ảnh sản phẩm lớn">
                <div class="thumbnails">
                    <?php foreach ($images as $image): ?>
                        <img src="uploads/<?php echo $image; ?>"
                            onclick="document.getElementById('main-image').src = 'uploads/<?php echo $image; ?>'">
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <p>Không có ảnh nào cho sản phẩm này.</p>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1><?php echo $product['ten_san_pham']; ?></h1>
            <h2><i class="fas fa-tag"></i> <?php echo number_format($product['gia'], 0, ',', '.'); ?> VND</h2>

            <table>
                <tr>
                    <td>Hãng sản phẩm</td>
                    <td><?php echo $product['ten_hang']; ?></td>
                </tr>
                <tr>
                    <td>Mô tả</td>
                    <td><?php echo nl2br($product['mo_ta']); ?></td>
                </tr>
                <tr>
                    <td>Tình trạng</td>
                    <td><?php if ($product['so_luong'] > 0) {
                        $tinh_trang = "Còn hàng";
                    } else {
                        $tinh_trang = "Hết hàng";
                    }

                    // In ra tình trạng
                    echo $tinh_trang; ?></td>
                </tr>
                <!-- Thêm các thông số kỹ thuật khác nếu cần -->
            </table>

            <div class="product-actions">
                <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="add-to-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                    </button>
                </form>
            </div>
            <div class="installment-options">
                <div>TRẢ GÓP QUA THẺ TÍN DỤNG</div>
                <div>TRẢ GÓP QUA CÔNG TY TÀI CHÍNH</div>
            </div>
            <div class="benefits">
                <div>QUÀ TẶNG/KHUYẾN MẠI</div>
                <ul>
                    <li><i class="fas fa-check"></i> Tặng Windows 11 bản quyền theo máy</li>
                    <li><i class="fas fa-check"></i> Balo thời trang</li>
                    <li><i class="fas fa-check"></i> Chuột không dây + Bàn di cao cấp</li>
                    <li><i class="fas fa-check"></i> Tặng gói cài đặt, bảo dưỡng, vệ sinh máy trọn đời</li>
                    <li><i class="fas fa-check"></i> Tặng Voucher giảm giá cho lần mua tiếp theo</li>
                </ul>
            </div>

            <div class="contact-info">
                <div>YÊN TÂM MUA SẮM TẠI LAPTOP AZ</div>
                <ul>
                    <li><i class="fas fa-star"></i> Chất lượng sản phẩm là hàng đầu</li>
                    <li><i class="fas fa-star"></i> Dùng test máy 15 ngày nếu lỗi 1 đổi 1</li>
                    <li><i class="fas fa-star"></i> Hỗ trợ và hậu mãi sau bán hàng tốt nhất</li>
                    <li><i class="fas fa-star"></i> Trả góp ưu đãi lãi suất qua thẻ visa</li>
                    <li><i class="fas fa-star"></i> Giao hàng miễn phí toàn quốc nhanh nhất</li>
                </ul>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> Khu II, ĐHCT</li>
                    <li><i class="fas fa-phone-alt"></i> Hotline: 0835 886 837</li>
                </ul>
            </div>
            <div class="share-buttons">
                <button><i class="fab fa-facebook-f"></i> Share</button>
                <button><i class="fab fa-twitter"></i> Tweet</button>
                <button><i class="fab fa-linkedin-in"></i> LinkedIn</button>
            </div>
            <h2>Thông Số Kỹ Thuật</h2>
            <table>
                <tr>
                    <td>Hãng sản phẩm</td>
                    <td><?php echo $product['ten_hang']; ?></td>
                </tr>
                <tr>
                    <td>Mô tả</td>
                    <td><?php echo nl2br($product['mo_ta']); ?></td>
                </tr>
                <tr>
                    <td>CPU</td>
                    <td><?php echo $product['cpu']; ?></td>
                </tr>
                <tr>
                    <td>RAM</td>
                    <td><?php echo $product['ram']; ?></td>
                </tr>
                <tr>
                    <td>Ổ cứng</td>
                    <td><?php echo $product['o_cung']; ?></td>
                </tr>
                <tr>
                    <td>GPU</td>
                    <td><?php echo $product['gpu']; ?></td>
                </tr>
                <tr>
                    <td>Kích thước màn hình</td>
                    <td><?php echo $product['kich_thuoc_manh_hinh']; ?> inch</td>
                </tr>
                <tr>
                    <td>Thông tin màn hình</td>
                    <td><?php echo nl2br($product['thong_tin_mang_hinh']); ?></td>
                </tr>
                <tr>
                    <td>Pin</td>
                    <td><?php echo $product['pin']; ?></td>
                </tr>
                <tr>
                    <td>Hệ điều hành</td>
                    <td><?php echo $product['he_dieu_hanh']; ?></td>
                </tr>
                <tr>
                    <td>Trọng lượng</td>
                    <td><?php echo $product['trong_luong']; ?> kg</td>
                </tr>
                <tr>
                    <td>Tình trạng</td>
                    <td><?php echo $product['so_luong'] > 0 ? "Còn hàng" : "Hết hàng"; ?></td>
                </tr>
                <!-- Thêm các thông số kỹ thuật khác nếu cần -->
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.add-to-cart-form').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'add_to_cart.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            alert('Đã thêm sản phẩm vào giỏ hàng');
                            // Cập nhật số lượng hiển thị trên icon giỏ hàng
                            updateCartCount();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
                    }
                });
            });

            function updateCartCount() {
                $.ajax({
                    type: 'GET',
                    url: 'get_cart_count.php',
                    success: function (response) {
                        $('.cart-count').text(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error updating cart count:', error);
                    }
                });
            }
        });
    </script>
</body>
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

</html>