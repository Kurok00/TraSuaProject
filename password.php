<?php
include 'configs/connect.php';

session_start();

// Kiểm tra và làm sạch đầu vào
$reset_token = isset($_GET['reset_token']) ? filter_var($_GET['reset_token'], FILTER_SANITIZE_STRING) : null;

// Kiểm tra xem reset_token có hợp lệ không
if (empty($reset_token)) {
    // Xử lý reset_token không hợp lệ
    die("Liên kết không hợp lệ");
}

// Truy vấn thông tin hồ sơ người dùng
$fetch_profile_stmt = $conn->prepare("SELECT user_id, name FROM users WHERE reset_token = ? AND reset_token_expiration > NOW()");
$fetch_profile_stmt->execute([$reset_token]);
$fetch_profile = $fetch_profile_stmt->fetch(PDO::FETCH_ASSOC);

if (!$fetch_profile) {
    // Xử lý người dùng không tồn tại hoặc liên kết không hợp lệ
    die("Liên kết không hợp lệ hoặc đã hết hạn.");
}

$user_id = $fetch_profile['user_id'];

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

    if (!empty($name)) {
        $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE user_id = ?");
        $update_name->execute([$name, $user_id]);
    }

    $new_pass = sha1($_POST['new_pass']);
    $confirm_pass = sha1($_POST['confirm_pass']);

    if ($new_pass !== $confirm_pass) {
        $message[] = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
    } elseif (strlen($_POST['new_pass']) < 8) {
        $message[] = 'Mật khẩu mới phải có ít nhất 8 ký tự!';
    } else {
        $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE user_id = ?");
        $update_pass->execute([$confirm_pass, $user_id]);

        $message[] = 'Mật khẩu đã được cập nhật!';

        // Reset giá trị của $_POST
        $_POST = array();

        // Reset các giá trị liên quan đến việc reset mật khẩu
        $update_use = $conn->prepare("UPDATE `users` SET reset_token = NULL, reset_token_expiration = NULL,last_password_reset_time= now() WHERE user_id = ?");
        $update_use->execute([$user_id]);
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>

    <!-- header section starts  -->
    <?php include 'configs/user_header.php'; ?>
    <!-- header section ends -->

    <section class="form-container update-form">

        <form action="" method="post">
            <h3>Cập nhật thông tin</h3>
            <input type="text" name="name" placeholder="<?= $fetch_profile['name']; ?>" class="box" maxlength="50">
            <input type="password" name="new_pass" placeholder="Nhập mật khẩu mới" class="box" minlength="8" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="confirm_pass" placeholder="Nhập lại mật khẩu" class="box" minlength="8" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Cập nhật " name="submit" class="btn">
        </form>

    </section>


    <?php include 'configs/user_footer.php'; ?>



    <!-- custom js file link  -->
    <script src="templates/js/script.js"></script>

</body>

</html>