<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};


// Số lần đăng nhập không thành công được phép
$maxLoginAttempts = 5;

if (isset($_POST['submit'])) {
   $name = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']); // Sử dụng SHA-1 vì mật khẩu trong cơ sở dữ liệu được mã hóa bằng SHA-1

   // Kiểm tra xem tài khoản có bị khóa không
   $checkLockout = $conn->prepare("SELECT user_id, status, password FROM `users` WHERE email = ?");
   $checkLockout->execute([$name]);

   if ($checkLockout->rowCount() > 0) {
      $fetch_admin_data = $checkLockout->fetch(PDO::FETCH_ASSOC);
      $admin_id = $fetch_admin_data['user_id'];
      $status = $fetch_admin_data['status'];
      $hashedPassword = $fetch_admin_data['password'];

      if (!isset($_SESSION['login_attempts'][$admin_id])) {
         // Nếu session không được đặt, đặt giá trị mặc định cho nó
         $_SESSION['login_attempts'][$admin_id] = 0;
      }

      $loginAttempts = $_SESSION['login_attempts'][$admin_id];

      if ($loginAttempts >= $maxLoginAttempts) {
         // Tài khoản đã bị khóa do quá số lần đăng nhập không thành công
         $message[] = 'Tài khoản của bạn đã bị khóa do quá số lần đăng nhập không thành công. Vui lòng liên hệ người quản trị.';

         // Cập nhật trạng thái tài khoản sang 'locked' trong cơ sở dữ liệu
         $updateStatus = $conn->prepare("UPDATE `users` SET status = 'block' WHERE user_id = ?");
         $updateStatus->execute([$admin_id]);
         $_SESSION['login_attempts'][$admin_id] = 0;
      } elseif ($pass === $hashedPassword) {
         if ($status === 'active') {
            // Đăng nhập thành công, đặt lại số lần đăng nhập không thành công trong session
            $_SESSION['login_attempts'][$admin_id] = 0;

            $_SESSION['user_id'] = $admin_id;

            // Tái tạo ID phiên để ngăn chặn session fixation
            session_regenerate_id(true);

            // Thực hiện cập nhật lịch sử đăng nhập
            $ip_address = $_SERVER['REMOTE_ADDR']; // Lấy địa chỉ IP của người dùng

            // Insert lịch sử đăng nhập vào bảng admin_login_history
            $insert_login_history = $conn->prepare("INSERT INTO `user_login_history` (user_id, login_time, ip_address) VALUES (?, NOW(), ?)");
            $insert_login_history->execute([$admin_id, $ip_address]);


            header('location: home.php');
         } else {
            $message[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ người quản trị.';
         }
      } else {
         // Đăng nhập không thành công, tăng số lần đăng nhập không thành công trong session
         $_SESSION['login_attempts'][$admin_id]++;

         $message[] = 'Tên đăng nhập hoặc mật khẩu sai!';
      }
   } else {
      $message[] = 'Tên đăng nhập hoặc mật khẩu sai!';
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
   <title>Đăng nhập</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>
   <?php include 'configs/user_header.php'; ?>




   <section class="form-container">

      <form action="" method="post">
         <h3>Đăng nhập ngay </h3>
         <input type="email" name="email" required placeholder="Nhập gmail" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" required placeholder="Nhập mật khẩu" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">

         <input type="submit" value="Đăng nhập" name="submit" class="btn">
         <p>Bạn không có tài khoản ? <a href="register.php">Đăng ký ngay </a></p>
         <p><a href="reset_pass.php">Quên mật khẩu </a></p>
      </form>

   </section>











   <?php include 'configs/user_footer.php'; ?>






   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

</body>

</html>