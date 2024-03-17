<?php
include '../configs/connect.php';

session_start();

// Số lần đăng nhập không thành công được phép
$maxLoginAttempts = 5;

if (isset($_POST['submit'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']); // Sử dụng SHA-1 vì mật khẩu trong cơ sở dữ liệu được mã hóa bằng SHA-1

   // Kiểm tra xem tài khoản có bị khóa không
   $checkLockout = $conn->prepare("SELECT id, status, password FROM `admin` WHERE name = ?");
   $checkLockout->execute([$name]);

   if ($checkLockout->rowCount() > 0) {
      $fetch_admin_data = $checkLockout->fetch(PDO::FETCH_ASSOC);
      $admin_id = $fetch_admin_data['id'];
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
         $updateStatus = $conn->prepare("UPDATE `admin` SET status = 'locked' WHERE id = ?");
         $updateStatus->execute([$admin_id]);
         $_SESSION['login_attempts'][$admin_id] = 0;
      } elseif ($pass === $hashedPassword) {
         if ($status === 'active') {
            // Đăng nhập thành công, đặt lại số lần đăng nhập không thành công trong session
            $_SESSION['login_attempts'][$admin_id] = 0;

            $_SESSION['admin_id'] = $admin_id;

            // Tái tạo ID phiên để ngăn chặn session fixation
            session_regenerate_id(true);

            // Thực hiện cập nhật lịch sử đăng nhập
            $ip_address = $_SERVER['REMOTE_ADDR']; // Lấy địa chỉ IP của người dùng

            // Insert lịch sử đăng nhập vào bảng admin_login_history
            $insert_login_history = $conn->prepare("INSERT INTO `admin_login_history` (admin_id, login_time, ip_address) VALUES (?, NOW(), ?)");
            $insert_login_history->execute([$admin_id, $ip_address]);


            header('location: dashboard.php');
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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Đăng nhập admin</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../templates/css/admin_style.css">

</head>

<body>

   <?php
   if (isset($message)) {
      foreach ($message as $message) {
         echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
      }
   }
   ?>
   <!-- admin login form section starts  -->
   <section class="form-container">

      <form action="" method="POST">
         <h3>Đăng nhập</h3>
         <!-- <p>default username = <span>admin</span> & password = <span>111</span></p> -->
         <input type="text" name="name" maxlength="20" required placeholder="Nhập tên đăng nhập" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" maxlength="20" required placeholder="Nhập mật khấu" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Đăng nhập" name="submit" class="btn">
      </form>

   </section>

</body>

</html>