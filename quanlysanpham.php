<?php
session_start();
include 'config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: dangnhap.php");
    exit();
}

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $ten_san_pham = $_POST['ten_san_pham'];
    $gia = $_POST['gia'];
    $mo_ta = $_POST['mo_ta'];
    $so_luong = $_POST['so_luong'];
    $hang_id = $_POST['hang_id'];
    $cpu = $_POST['cpu'];
    $ram = $_POST['ram'];
    $o_cung = $_POST['o_cung'];
    $gpu = $_POST['gpu'];
    $kich_thuoc_manh_hinh = $_POST['kich_thuoc_manh_hinh'];
    $thong_tin_mang_hinh = $_POST['thong_tin_mang_hinh'];
    $pin = $_POST['pin'];
    $he_dieu_hanh = $_POST['he_dieu_hanh'];
    $trong_luong = $_POST['trong_luong'];

    // Thêm sản phẩm vào bảng sanpham
    $sql = "INSERT INTO sanpham (ten_san_pham, gia, mo_ta, so_luong, hang_id, cpu, ram, o_cung, gpu, kich_thuoc_manh_hinh, thong_tin_mang_hinh, pin, he_dieu_hanh, trong_luong) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsiisisssdsss", $ten_san_pham, $gia, $mo_ta, $so_luong, $hang_id, $cpu, $ram, $o_cung, $gpu, $kich_thuoc_manh_hinh, $thong_tin_mang_hinh, $pin, $he_dieu_hanh, $trong_luong);

    if ($stmt->execute()) {
        $sanpham_id = $stmt->insert_id; // Lấy id sản phẩm vừa thêm

        // Xử lý upload ảnh
        foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
            $file_name = basename($_FILES['images']['name'][$index]);
            $target_file = "uploads/" . $file_name; // Lưu vào thư mục uploads

            if (move_uploaded_file($tmp_name, $target_file)) {
                // Nếu ảnh đầu tiên thì đánh dấu là ảnh chính
                $is_main = ($index == 0) ? 1 : 0;
                $sql_anh = "INSERT INTO anh_sanpham (sanpham_id, anh, is_main) VALUES (?, ?, ?)";
                $stmt_anh = $conn->prepare($sql_anh);
                $stmt_anh->bind_param("isi", $sanpham_id, $target_file, $is_main);
                $stmt_anh->execute();
            }
        }

        $success_message = "Sản phẩm đã được thêm thành công.";
    } else {
        $error_message = "Có lỗi xảy ra khi thêm sản phẩm.";
    }
}


// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM sanpham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Sản phẩm đã được xóa thành công.";
    } else {
        $error_message = "Có lỗi xảy ra khi xóa sản phẩm.";
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT s.*, l.ten_hang FROM sanpham s LEFT JOIN hang l ON s.hang_id = l.id";
$result = $conn->query($sql);

// Lấy danh sách loại sản phẩm
$sql_hang = "SELECT * FROM hang";
$result_hang = $conn->query($sql_hang);
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
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Quản lý sản phẩm</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Quay về Dashboard
        </a>
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addProductModal">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </button>

        <?php
        if (isset($success_message)) {
            echo "<div class='alert alert-success'>{$success_message}</div>";
        }
        if (isset($error_message)) {
            echo "<div class='alert alert-danger'>{$error_message}</div>";
        }
        ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên sản phẩm</th>
                    <th>Hãng</th>
                    <th>Giá</th>
                    <th>Số lượng tồn kho</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["ten_san_pham"] . "</td>";
                        echo "<td>" . $row["ten_hang"] . "</td>";
                        echo "<td>" . number_format($row["gia"], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . $row["so_luong"] . "</td>";
                        echo "<td>
                                <button class='btn btn-sm btn-warning edit-btn' data-id='" . $row["id"] . "'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                <button class='btn btn-sm btn-danger delete-btn' data-id='" . $row["id"] . "'>
                                    <i class='fas fa-trash'></i> Xóa
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
                <div class=" modal-body">
                    <form id="addProductForm">
                        <div class="form-group">
                            <label for="ten_san_pham">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="ten_san_pham" required>
                        </div>
                        <div class="form-group">
                            <label for="hang_id">Hãng</label>
                            <select class="form-control" id="hang_id" required>
                                <?php
                                while ($row_hang = $result_hang->fetch_assoc()) {
                                    echo "<option value='" . $row_hang["id"] . "'>" . $row_hang["ten_hang"] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cpu">CPU</label>
                            <input type="text" class="form-control" id="cpu" required>
                        </div>
                        <div class="form-group">
                            <label for="ram">RAM</label>
                            <input type="text" class="form-control" id="ram" required>
                        </div>
                        <div class="form-group">
                            <label for="o_cung">Ổ cứng</label>
                            <input type="text" class="form-control" id="o_cung" required>
                        </div>
                        <div class="form-group">
                            <label for="gpu">GPU</label>
                            <input type="text" class="form-control" id="gpu" required>
                        </div>
                        <div class="form-group">
                            <label for="kich_thuoc_manh_hinh">Kích thước màn hình</label>
                            <input type="text" class="form-control" id="kich_thuoc_manh_hinh" required>
                        </div>
                        <div class="form-group">
                            <label for="thong_tin_mang_hinh">Thông tin màn hình</label>
                            <input type="text" class="form-control" id="thong_tin_mang_hinh" required>
                        </div>
                        <div class="form-group">
                            <label for="pin">Pin</label>
                            <input type="text" class="form-control" id="pin" required>
                        </div>
                        <div class="form-group">
                            <label for="he_dieu_hanh">Hệ điều hành</label>
                            <input type="text" class="form-control" id="he_dieu_hanh" required>
                        </div>
                        <div class="form-group">
                            <label for="trong_luong">Trọng lượng</label>
                            <input type="text" class="form-control" id="trong_luong" required>
                        </div>
                        <div class="form-group">
                            <label for="gia">Giá</label>
                            <input type="number" class="form-control" id="gia" required>
                        </div>
                        <div class="form-group">
                            <label for="mo_ta">Mô tả</label>
                            <textarea class="form-control" id="mo_ta" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="so_luong">Số lượng</label>
                            <input type="number" class="form-control" id="so_luong" required>
                        </div>
                        <div class="form-group">
                            <label for="images">Ảnh sản phẩm</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple required>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" form="addProductForm">Thêm sản phẩm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Xử lý nút sửa
            $(".edit-btn").click(function () {
                var id = $(this).data("id");
                // Xử lý sửa sản phẩm
                // ...
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
                        window.location.href = "quanlysanpham.php";
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