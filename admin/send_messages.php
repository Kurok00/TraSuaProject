<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};


if (isset($_POST['send'])) {

    $pid = $_POST['pid'];
    $pid = filter_var($pid, FILTER_SANITIZE_STRING);
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);
    $stock = $_POST['stock'];
    $stock = filter_var($stock, FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $category = filter_var($category, FILTER_SANITIZE_STRING);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Phản hồi</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">

</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>
   <!-- update product section starts  -->

   <section class="update-product">

      <h1 class="heading">Phản hồi</h1>

      <?php
      $send_id = $_GET['user_id'];
      $show_send = $conn->prepare("SELECT * FROM `users` WHERE user_id = ?");
      $show_send->execute([$send_id]);
         while ($fetch_send = $show_send->fetch(PDO::FETCH_ASSOC)) {
      ?>
            <form action="" method="POST" enctype="multipart/form-data">
               <span>Tên khách hàng</span>
               <input type="text" required placeholder="" name="name" maxlength="100" class="box" value="<?= $fetch_send['name']; ?>">
               <span>Địa chỉ email</span>
               <input type="text" required placeholder="" name="email" maxlength="100" class="box" value="<?= $fetch_send['email']; ?>">
               <span>Tiêu đề</span>
               <input type="text" required placeholder="" name="titel_email" maxlength="100" class="box" >
               <span>Nội dung</span>
               <input type="text" required placeholder="" name="main_content" maxlength="100" class="box" >
               <div class="flex-btn">
                  <input type="submit" value="send" class="btn" name="send">
                  <a href="messages.php" class="option-btn">Gửi</a>
               </div>
            </form>
      <?php
         }
      ?>

   </section>
   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>