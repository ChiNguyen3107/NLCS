<?php
include 'config.php';

$message = '';
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $mat_khau = isset($_POST['mat_khau']) ? $_POST['mat_khau'] : '';
    $xac_nhan_mat_khau = isset($_POST['xac_nhan_mat_khau']) ? $_POST['xac_nhan_mat_khau'] : '';
    $ho_ten = isset($_POST['ho_ten']) ? $_POST['ho_ten'] : '';
    $dien_thoai = isset($_POST['dien_thoai']) ? $_POST['dien_thoai'] : '';

    // Lấy giá trị địa chỉ trực tiếp từ form
    $province = isset($_POST['province']) ? $_POST['province'] : '';
    $district = isset($_POST['district']) ? $_POST['district'] : '';
    $ward = isset($_POST['ward']) ? $_POST['ward'] : '';
    $specific_address = isset($_POST['specific_address']) ? $_POST['specific_address'] : '';

    // Tạo địa chỉ đầy đủ
    $dia_chi = $specific_address . ', ' . $ward . ', ' . $district . ', ' . $province;

    $role_id = 2; // Vai trò khách hàng

    if ($mat_khau !== $xac_nhan_mat_khau) {
        $message = "Mật khẩu và xác nhận mật khẩu không khớp.";
    } else {
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        if (!$stmt) {
            die("Lỗi prepare statement: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email đã được sử dụng. Vui lòng chọn email khác.";
        } else {
            $hashed_password = password_hash($mat_khau, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (email, mat_khau, role_id, ho_ten, dien_thoai, dia_chi) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Lỗi prepare statement: " . $conn->error);
            }
            $stmt->bind_param("ssisss", $email, $hashed_password, $role_id, $ho_ten, $dien_thoai, $dia_chi);

            if ($stmt->execute()) {
                $registration_success = true;
                $message = "Đăng ký tài khoản thành công!";
            } else {
                $message = "Lỗi: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Tài Khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            padding: 20px;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            transition: transform 0.3s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .register-header .icon {
            font-size: 3em;
            color: #71b7e6;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 12px 40px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #71b7e6;
            outline: none;
            box-shadow: 0 0 10px rgba(113, 183, 230, 0.3);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #9b59b6, #71b7e6);
            transform: translateY(-2px);
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #71b7e6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #9b59b6;
        }

        .error {
            background-color: #ffe6e6;
            color: #d63031;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success {
            background-color: #e6ffe6;
            color: #27ae60;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 20px;
            }

            .register-header h2 {
                font-size: 1.5em;
            }

            .form-group input {
                padding: 10px 35px;
                font-size: 14px;
            }

            .submit-btn {
                padding: 10px;
                font-size: 14px;
            }
        }

        .form-group select {
            width: 100%;
            padding: 12px 40px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: white url("data:image/svg+xml;utf8,<svg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>") no-repeat right 10px center;
        }

        .form-group select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .form-group select:focus {
            border-color: #71b7e6;
            outline: none;
            box-shadow: 0 0 10px rgba(113, 183, 230, 0.3);
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus icon"></i>
            <h2>Đăng Ký Tài Khoản</h2>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $registration_success ? 'success' : 'error'; ?>">
                <i class="<?php echo $registration_success ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="xac_nhan_mat_khau" placeholder="Xác nhận mật khẩu" required>
            </div>
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="ho_ten" placeholder="Họ và tên" required>
            </div>
            <div class="form-group">
                <i class="fas fa-phone"></i>
                <input type="tel" name="dien_thoai" placeholder="Số điện thoại" required>
            </div>
            <!-- Thêm phần địa chỉ vào form đăng ký -->
            <div class="form-group">
                <i class="fas fa-map-marker-alt"></i>
                <select id="province" name="province" required>
                    <option value="">Chọn tỉnh/thành phố</option>
                </select>
            </div>

            <div class="form-group">
                <i class="fas fa-map-marker-alt"></i>
                <select id="district" name="district" required disabled>
                    <option value="">Chọn quận/huyện</option>
                </select>
            </div>

            <div class="form-group">
                <i class="fas fa-map-marker-alt"></i>
                <select id="ward" name="ward" required disabled>
                    <option value="">Chọn phường/xã</option>
                </select>
            </div>

            <div class="form-group">
                <i class="fas fa-home"></i>
                <input type="text" id="specific_address" name="specific_address" placeholder="Số nhà, tên đường"
                    required>
            </div>
            <button type="submit" class="submit-btn">Đăng Ký</button>
        </form>
        <div class="links">
            <a href="dangnhap.php">Đã có tài khoản? Đăng nhập ngay</a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script>
        var citis = document.getElementById("province");
        var districts = document.getElementById("district");
        var wards = document.getElementById("ward");

        var Parameter = {
            url: "https://raw.githubusercontent.com/kenzouno1/DiaGioiHanhChinhVN/master/data.json",
            method: "GET",
            responseType: "application/json",
        };

        var promise = axios(Parameter);
        promise.then(function (result) {
            renderCity(result.data);
        });

        function renderCity(data) {
            for (const x of data) {
                citis.options[citis.options.length] = new Option(x.Name, x.Id);
            }

            citis.onchange = function () {
                districts.length = 1;
                wards.length = 1;

                if (this.value != "") {
                    const result = data.filter(n => n.Id === this.value);

                    for (const k of result[0].Districts) {
                        districts.options[districts.options.length] = new Option(k.Name, k.Id);
                    }
                }
                districts.disabled = false;
            };

            districts.onchange = function () {
                wards.length = 1;

                if (this.value != "") {
                    const dataCity = data.filter((n) => n.Id === citis.value);
                    const dataDistricts = dataCity[0].Districts.filter(n => n.Id === this.value)[0];

                    for (const w of dataDistricts.Wards) {
                        wards.options[wards.options.length] = new Option(w.Name, w.Id);
                    }
                }
                wards.disabled = false;
            };
        }
    </script>
</body>

</html>