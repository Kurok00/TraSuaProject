<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

if (isset($_POST['submit'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   // Kiểm tra mật khẩu có ít nhất 8 ký tự
   if (strlen($_POST['pass']) < 8) {
      $message[] = 'Mật khẩu phải có ít nhất 8 ký tự';
   } else {
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? OR number = ?");
      $select_user->execute([$email, $number]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if ($select_user->rowCount() > 0) {
         $message[] = 'Email hoặc số điện thoại đã tồn tại';
      } else {
         if ($pass != $cpass) {
            $message[] = 'Mật khẩu không trùng khớp';
         } else {
            $insert_user = $conn->prepare("INSERT INTO `users`(name, email, number, password) VALUES(?,?,?,?)");
            $insert_user->execute([$name, $email, $number, $cpass]);
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
            $select_user->execute([$email, $pass]);
            $row = $select_user->fetch(PDO::FETCH_ASSOC);
            if ($select_user->rowCount() > 0) {
               $_SESSION['user_id'] = $row['id'];
               header('location:home.php');
            }
         }
      }
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
   <title>Đăng ký</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>

   <?php include 'configs/user_header.php'; ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>Đăng ký ngay</h3>
         <input type="text" name="name" required placeholder="Nhập tên" class="box" maxlength="50">
         <input type="email" name="email" required placeholder="Nhập địa chỉ email" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="number" name="number" required placeholder="Nhập số điện thoại" class="box" min="0" max="9999999999" maxlength="10">
         <input type="password" name="pass" required placeholder="Nhập mật khẩu" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="cpass" required placeholder="Nhập lại mật khẩu" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Đăng ký ngay " name="submit" class="btn">
         <p>Bạn đã có tài khoản? <a href="login.php">Đăng nhập ngay </a></p>
      </form>

   </section>

   <?php include 'configs/user_footer.php'; ?>

   <script src="templates/js/script.js"></script>

</body>

</html>