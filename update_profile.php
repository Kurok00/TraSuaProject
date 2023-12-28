<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
}

if (isset($_POST['submit'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);

   if (!empty($name)) {
      $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE user_id = ?");
      $update_name->execute([$name, $user_id]);
   }

   if (!empty($email)) {
      $select_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select_email->execute([$email]);
      if ($select_email->rowCount() > 0) {
         $message[] = 'Email đã được sử dụng!';
      } else {
         $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE user_id = ?");
         $update_email->execute([$email, $user_id]);
      }
   }

   if (!empty($number)) {
      $select_number = $conn->prepare("SELECT * FROM `users` WHERE number = ?");
      $select_number->execute([$number]);
      if ($select_number->rowCount() > 0) {
         $message[] = 'Số điện thoại đã được sử dụng!';
      } else {
         $update_number = $conn->prepare("UPDATE `users` SET number = ? WHERE user_id = ?");
         $update_number->execute([$number, $user_id]);
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $select_prev_pass = $conn->prepare("SELECT password FROM `users` WHERE user_id = ?");
   $select_prev_pass->execute([$user_id]);
   $fetch_prev_pass = $select_prev_pass->fetch(PDO::FETCH_ASSOC);
   $prev_pass = $fetch_prev_pass['password'];
   $old_pass = sha1($_POST['old_pass']);
   $new_pass = sha1($_POST['new_pass']);
   $confirm_pass = sha1($_POST['confirm_pass']);

   if ($old_pass != $empty_pass) {
      if ($old_pass != $prev_pass) {
         $message[] = 'Mật khẩu cũ không đúng!';
      } elseif ($new_pass !== $confirm_pass) {
         $message[] = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
      } elseif (strlen($_POST['new_pass']) < 8) {
         $message[] = 'Mật khẩu mới phải có ít nhất 8 ký tự!';
      } else {
         $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE user_id = ?");
         $update_pass->execute([$confirm_pass, $user_id]);
         $message[] = 'Mật khẩu đã được cập nhật!';
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
         <input type="email" name="email" placeholder="<?= $fetch_profile['email']; ?>" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="number" name="number" placeholder="<?= $fetch_profile['number']; ?>"" class=" box" min="0" max="9999999999" maxlength="10">
         <input type="password" name="old_pass" placeholder="Nhập mật khẩu cũ" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
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