<?php

include '../configs/connect.php';

session_start();

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}
$admin_id = $_SESSION['admin_id'];

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $role = $_POST['box_role'];
    $status = $_POST['box_status'];
    $pass = sha1($_POST['pass']);
    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $checkNameQuery = $conn->prepare("SELECT id FROM `admin` WHERE name = ?");
    $checkNameQuery->execute([$name]);

    if ($checkNameQuery->rowCount() > 0) {
        // Tên đăng nhập đã tồn tại, hiển thị thông báo và không thêm vào cơ sở dữ liệu
        $message[] = 'Tên đăng nhập đã tồn tại. Vui lòng chọn một tên đăng nhập khác.';
    } else {

        $sql = "INSERT INTO `admin` (name, role, status, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $role, $status, $pass]);

        header('location:admin_accounts.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
    <title>Thêm quản lý</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">


</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>

    <!-- admin profile update section starts  -->

    <section class="form-container">
        <form action="" method="POST">
            <h3>Tạo tài khoản</h3>
            <input type="text" name="name" maxlength="20" class="box" placeholder="Nhập tên tài khoản" required oninput="this.value = this.value.replace(/\s/g, '')" placeholder="<?= $fetch_profile['name']; ?>">
            <span id="name-error" class="error-message"></span>
            <label for="role">Chức vụ :</label>
            <select name="box_role" class="drop-down">
                <option value="3">Nhân viên</option>
                <option value="2">Quản lý</option>
                <option value="1">Admin</option>
            </select>
            <label for="status">Trang thái :</label>
            <select name="box_status" class="drop-down">
                <option value="active">Hoạt động</option>
                <option value="block">Khóa tài khoản</option>
            </select>
            <input type="password" name="pass" maxlength="20" placeholder="Mật khẩu 8 ký tự" class="box"  pattern=".{8,}" required oninput="this.value = this.value.replace(/\s/g, '')">
            <span id="password-error" class="error-message"></span>
            <input type="submit" value="Tạo" name="submit" class="btn">
        </form>

    </section>

    <script src="../templates/js/admin_script.js"></script>

</body>

</html>