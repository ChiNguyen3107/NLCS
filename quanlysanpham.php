<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Xử lý cập nhật trạng thái sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $product_id = intval($_POST['product_id']);
    $new_status = $_POST['new_status'];

    $sql = "UPDATE sanpham SET trang_thai = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $product_id);

    if ($stmt->execute()) {
        $success_message = "Cập nhật trạng thái sản phẩm thành công.";
    } else {
        $error_message = "Có lỗi xảy ra: " . $stmt->error;
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT sanpham.id, sanpham.ten_san_pham, sanpham.gia, sanpham.so_luong, sanpham.trang_thai, hang.ten_hang 
        FROM sanpham 
        LEFT JOIN hang ON sanpham.hang_id = hang.id 
        ORDER BY sanpham.id ASC";
$result = $conn->query($sql);

// Lấy danh sách hãng để hiển thị trong dropdown
$sql_hang = "SELECT * FROM hang";
$result_hang = $conn->query($sql_hang);

// Xử lý lọc theo hãng
$hang_id = isset($_GET['hang_id']) ? intval($_GET['hang_id']) : 0;

// Lấy danh sách sản phẩm
$sql1 = "SELECT sanpham.id, sanpham.ten_san_pham, sanpham.gia, sanpham.so_luong, hang.ten_hang 
        FROM sanpham 
        LEFT JOIN hang ON sanpham.hang_id = hang.id";

// Nếu có hãng được chọn, thêm điều kiện vào truy vấn
if ($hang_id > 0) {
    $sql1 .= " WHERE sanpham.hang_id = $hang_id";
}

$sql1 .= " ORDER BY sanpham.id ASC"; // Sắp xếp theo ID
$result = $conn->query($sql1);
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .container {
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .product-name {
            max-width: 150px;
            /* Chiều rộng tối đa cho tên sản phẩm */
            overflow: hidden;
            /* Ẩn phần thừa */
            white-space: nowrap;
            /* Ngăn không cho xuống dòng */
            text-overflow: ellipsis;
            /* Hiển thị dấu ba chấm khi tên quá dài */
        }
    </style>
</head>

<body>
    <?php
    if (isset($success_message)) {
        echo "<div class='alert alert-success'>{$success_message}</div>";
    }
    if (isset($error_message)) {
        echo "<div class='alert alert-danger'>{$error_message}</div>";
    }
    ?>

    <div class="container">
        <h2 class="mb-4">Quản lý sản phẩm</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Quay về Dashboard
        </a>
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addProductModal">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </button>

        <form method="GET" class="mb-3">
            <label for="hang_id">Chọn hãng:</label>
            <select name="hang_id" id="hang_id" class="form-control" onchange="this.form.submit()">
                <option value="0">Tất cả</option>
                <?php while ($row_hang = $result_hang->fetch_assoc()): ?>
                    <option value="<?php echo $row_hang['id']; ?>" <?php echo ($hang_id == $row_hang['id']) ? 'selected' : ''; ?>>
                        <?php echo $row_hang['ten_hang']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>




        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><a href="?sort=ten_san_pham">Tên sản phẩm</a></th>
                    <th><a href="?sort=ten_hang">Hãng</a></th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td class='product-name'>" . htmlspecialchars($row["ten_san_pham"]) . "</td>";
                        echo "<td>" . $row["ten_hang"] . "</td>";
                        echo "<td>" . number_format($row["gia"], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . $row["so_luong"] . "</td>";
                        echo "<td>";
                        echo "<form method='POST' style='display: inline;'>";
                        echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                        echo "<select name='new_status' class='form-control status-select' onchange='this.form.submit()'>";
                        echo "<option value='active' " . ($row['trang_thai'] == 'active' ? 'selected' : '') . ">Còn hàng</option>";
                        echo "<option value='inactive' " . ($row['trang_thai'] == 'inactive' ? 'selected' : '') . ">Ngừng bán</option>";
                        echo "<option value='out_of_stock' " . ($row['trang_thai'] == 'out_of_stock' ? 'selected' : '') . ">Hết hàng</option>";
                        echo "</select>";
                        echo "<input type='hidden' name='update_status' value='1'>";
                        echo "</form>";
                        echo "</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-warning edit-btn' data-id='" . $row["id"] . "'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='15'>Không có sản phẩm nào</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal thêm sản phẩm -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Thêm sản phẩm mới</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="themsanpham.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="ten_san_pham">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="ten_san_pham" name="ten_san_pham" required>
                        </div>
                        <div class="form-group">
                            <label for="hang_id">Hãng</label>
                            <select class="form-control" id="hang_id" name="hang_id" required>
                                <?php
                                while ($row_hang = $result_hang->fetch_assoc()) {
                                    echo "<option value='" . $row_hang["id"] . "'>" . $row_hang["ten_hang"] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gia">Giá</label>
                            <input type="number" class="form-control" id="gia" name="gia" required>
                        </div>
                        <div class="form-group">
                            <label for="cpu">CPU</label>
                            <input type="text" class="form-control" id="cpu" name="cpu" required>
                        </div>
                        <div class="form-group">
                            <label for="ram">RAM</label>
                            <input type="text" class="form-control" id="ram" name="ram" required>
                        </div>
                        <div class="form-group">
                            <label for="o_cung">Ổ cứng</label>
                            <input type="text" class="form-control" id="o_cung" name="o_cung" required>
                        </div>
                        <div class="form-group">
                            <label for="gpu">GPU</label>
                            <input type="text" class="form-control" id="gpu" name="gpu" required>
                        </div>
                        <div class="form-group">
                            <label for="kich_thuoc_manh_hinh">Kích thước màn hình</label>
                            <input type="text" class="form-control" id="kich_thuoc_manh_hinh"
                                name="kich_thuoc_manh_hinh" required>
                        </div>
                        <div class="form-group">
                            <label for="thong_tin_mang_hinh">Thông tin màn hình</label>
                            <input type="text" class="form-control" id="thong_tin_mang_hinh" name="thong_tin_mang_hinh"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="pin">Pin</label>
                            <input type="text" class="form-control" id="pin" name="pin" required>
                        </div>
                        <div class="form-group">
                            <label for="he_dieu_hanh">Hệ điều hành</label>
                            <input type="text" class="form-control" id="he_dieu_hanh" name="he_dieu_hanh" required>
                        </div>
                        <div class="form-group">
                            <label for="trong_luong">Trọng lượng</label>
                            <input type="text" class="form-control" id="trong_luong" name="trong_luong" required>
                        </div>

                        <div class="form-group">
                            <label for="mo_ta">Mô tả</label>
                            <textarea class="form-control" id="mo_ta" name="mo_ta" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="so_luong">Số lượng</label>
                            <input type="number" class="form-control" id="so_luong" name="so_luong" required>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Ảnh sản phẩm</label>
                            <input type="file" id="product_images" name="images[]" multiple accept="image/*">
                        </div>

                        <div class="form-group">
                            <label for="main_image">Ảnh đại diện sản phẩm</label>
                            <input type="file" id="main_image" name="main_image_file" accept="image/*" required>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal sửa sản phẩm -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Sửa sản phẩm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" action="edit_product.php" method="post"> <input type="hidden"
                            id="edit_product_id" name="product_id">

                        <div class="form-group">
                            <label for="edit_ten_san_pham">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="edit_ten_san_pham" name="ten_san_pham" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_hang_id">Hãng:</label>
                            <select class="form-control" id="edit_hang_id" name="hang_id">
                                <?php
                                // Đặt lại con trỏ kết quả về đầu nếu cần
                                $result_hang->data_seek(0); // Đặt lại con trỏ về đầu nếu đã duyệt qua trước đó
                                
                                while ($row_hang = $result_hang->fetch_assoc()) {
                                    // Kiểm tra xem hãng này có phải là hãng của sản phẩm đang sửa không
                                    $selected = ($row_hang["id"] == $sanpham["hang_id"]) ? 'selected' : '';
                                    echo "<option value='" . $row_hang["id"] . "' $selected>" . $row_hang["ten_hang"] . "</option>";
                                }
                                ?>
                            </select>


                        </div>

                        <div class="form-group">
                            <label for="edit_gia">Giá</label>
                            <input type="number" class="form-control" id="edit_gia" name="gia" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_cpu">CPU</label>
                            <input type="text" class="form-control" id="edit_cpu" name="cpu" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_ram">RAM</label>
                            <input type="text" class="form-control" id="edit_ram" name="ram" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_o_cung">Ổ cứng</label>
                            <input type="text" class="form-control" id="edit_o_cung" name="o_cung" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_gpu">GPU</label>
                            <input type="text" class="form-control" id="edit_gpu" name="gpu" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_kich_thuoc_manh_hinh">Kích thước màn hình</label>
                            <input type="text" class="form-control" id="edit_kich_thuoc_manh_hinh"
                                name="kich_thuoc_manh_hinh" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_thong_tin_mang_hinh">Thông tin màn hình</label>
                            <input type="text" class="form-control" id="edit_thong_tin_mang_hinh"
                                name="thong_tin_mang_hinh" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_pin">Pin</label>
                            <input type="text" class="form-control" id="edit_pin" name="pin" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_he_dieu_hanh">Hệ điều hành</label>
                            <input type="text" class="form-control" id="edit_he_dieu_hanh" name="he_dieu_hanh" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_trong_luong">Trọng lượng</label>
                            <input type="text" class="form-control" id="edit_trong_luong" name="trong_luong" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_so_luong">Số lượng</label>
                            <input type="number" class="form-control" id="edit_so_luong" name="so_luong" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- <script src="script.js"></script> -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- <script>
        $(document).ready(function () {
            // Xử lý nút sửa
            $(".edit-btn").click(function () {
                var id = $(this).data("id");

                // Gửi yêu cầu lấy thông tin sản phẩm
                $.ajax({
                    type: "GET",
                    url: "get_product.php",
                    data: { id: id },
                    success: function (data) {
                        // Kiểm tra dữ liệu trả về từ máy chủ
                        try {
                            var sanpham = JSON.parse(data); // Phân tích chuỗi JSON thành đối tượng

                            console.log(sanpham); // In toàn bộ sản phẩm để kiểm tra
                            console.log("hang_id:", sanpham.hang_id); // Kiểm tra giá trị hang_id sau khi đã có đối tượng sanpham

                            // Kiểm tra nếu sản phẩm có dữ liệu
                            if (sanpham) {
                                // Cập nhật các trường trong modal
                                $("#edit_product_id").val(sanpham.id);
                                $("#edit_ten_san_pham").val(sanpham.ten_san_pham);
                                $("#edit_gia").val(sanpham.gia);
                                $("#edit_mo_ta").val(sanpham.mo_ta);
                                $("#edit_so_luong").val(sanpham.so_luong);
                                $("#edit_cpu").val(sanpham.cpu);
                                $("#edit_ram").val(sanpham.ram);
                                $("#edit_o_cung").val(sanpham.o_cung);
                                $("#edit_gpu").val(sanpham.gpu);
                                $("#edit_kich_thuoc_manh_hinh").val(sanpham.kich_thuoc_manh_hinh);
                                $("#edit_thong_tin_mang_hinh").val(sanpham.thong_tin_mang_hinh);
                                $("#edit_pin").val(sanpham.pin);
                                $("#edit_he_dieu_hanh").val(sanpham.he_dieu_hanh);
                                $("#edit_trong_luong").val(sanpham.trong_luong);

                                // Cập nhật giá trị cho dropdown hãng
                                $("#edit_hang_id").val(sanpham.hang_id); // Sử dụng hang_id để chọn option

                                // Hiển thị modal sửa sản phẩm
                                $("#editProductModal").modal("show");
                            } else {
                                alert("Không tìm thấy sản phẩm.");
                            }
                        } catch (e) {
                            console.error("Lỗi khi phân tích JSON:", e);
                            alert("Đã xảy ra lỗi khi xử lý dữ liệu sản phẩm.");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log("Lỗi:", textStatus, errorThrown);
                        alert("Đã xảy ra lỗi khi lấy thông tin sản phẩm.");
                    }
                });
            });



            // Xử lý nút xóa
            $(".delete-btn").click(function () {
                var id = $(this).data("id");
                if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
                    window.location.href = "quanlysanpham.php?delete=" + id;
                }
            });

            // Xử lý form thêm sản phẩm
            $("#addProductForm").submit(function (event) {
                event.preventDefault();
                var ten_san_pham = $("#ten_san_pham").val();
                var gia = $("#gia").val();
                var mo_ta = $("#mo_ta").val();
                var so_luong = $("#so_luong").val();
                var hang_id = $("#hang_id").val();
                var cpu = $("#cpu").val();
                var ram = $("#ram").val();
                var o_cung = $("#o_cung").val();
                var gpu = $("#gpu").val();
                var kich_thuoc_manh_hinh = $("#kich_thuoc_manh_hinh").val();
                var thong_tin_mang_hinh = $("#thong_tin_mang_hinh").val();
                var pin = $("#pin").val();
                var he_dieu_hanh = $("#he_dieu_hanh").val();
                var trong_luong = $("#trong_luong").val();



                $.ajax({
                    type: "POST",
                    url: "quanlysanpham.php",
                    data: {
                        add_product: true,
                        ten_san_pham: ten_san_pham,
                        gia: gia,
                        mo_ta: mo_ta,
                        so_luong: so_luong,
                        hang_id: hang_id,
                        cpu: cpu,
                        ram: ram,
                        o_cung: o_cung,
                        gpu: gpu,
                        kich_thuoc_manh_hinh: kich_thuoc_manh_hinh,
                        thong_tin_mang_hinh: thong_tin_mang_hinh,
                        pin: pin,
                        he_dieu_hanh: he_dieu_hanh,
                        trong_luong: trong_luong
                    },
                    success: function (data) {
                        console.log(data); // Kiểm tra dữ liệu trả về từ server
                        window.location.href = "quanlysanpham.php";
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log("Lỗi:", textStatus, errorThrown);
                    }
                });
            });

        });
    </script> -->

    <script>
        $(document).ready(function () {
            // Xử lý nút sửa
            $(".edit-btn").click(function () {
                var id = $(this).data("id");

                // Gửi yêu cầu lấy thông tin sản phẩm
                $.ajax({
                    type: "GET",
                    url: "get_product.php",
                    data: { id: id },
                    success: function (data) {
                        try {
                            var sanpham = JSON.parse(data); // Phân tích chuỗi JSON thành đối tượng

                            // Kiểm tra nếu sản phẩm có dữ liệu
                            if (sanpham && sanpham.id) {
                                // Cập nhật các trường trong modal
                                $("#edit_product_id").val(sanpham.id);
                                $("#edit_ten_san_pham").val(sanpham.ten_san_pham);
                                $("#edit_gia").val(sanpham.gia);
                                $("#edit_so_luong").val(sanpham.so_luong);
                                $("#edit_cpu").val(sanpham.cpu);
                                $("#edit_ram").val(sanpham.ram);
                                $("#edit_o_cung").val(sanpham.o_cung);
                                $("#edit_gpu").val(sanpham.gpu);
                                $("#edit_kich_thuoc_manh_hinh").val(sanpham.kich_thuoc_manh_hinh);
                                $("#edit_thong_tin_mang_hinh").val(sanpham.thong_tin_mang_hinh);
                                $("#edit_pin").val(sanpham.pin);
                                $("#edit_he_dieu_hanh").val(sanpham.he_dieu_hanh);
                                $("#edit_trong_luong").val(sanpham.trong_luong);
                                $("#edit_hang_id").val(sanpham.hang_id); // Cập nhật giá trị cho dropdown hãng

                                // Hiển thị modal sửa sản phẩm
                                $("#editProductModal").modal("show");
                            } else {
                                alert("Không tìm thấy sản phẩm.");
                            }
                        } catch (e) {
                            console.error("Lỗi khi phân tích JSON:", e);
                            alert("Đã xảy ra lỗi khi xử lý dữ liệu sản phẩm.");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log("Lỗi:", textStatus, errorThrown);
                        alert("Đã xảy ra lỗi khi lấy thông tin sản phẩm.");
                    }
                });
            });



            // Xử lý nút xóa
            $(".delete-btn").click(function () {
                var id = $(this).data("id");
                if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
                    window.location.href = "quanlysanpham.php?delete=" + id;
                }
            });

            // Xử lý form sửa sản phẩm
            $("#editProductForm").submit(function (e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "edit_product.php",
                    data: formData,
                    success: function (data) {
                        alert("Cập nhật sản phẩm thành công.");
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log("Lỗi:", textStatus, errorThrown);
                        alert("Đã xảy ra lỗi khi cập nhật sản phẩm.");
                    }
                });
            });
        });
    </script>

    <?php
    $conn->close();
    ?>
</body>

</html>