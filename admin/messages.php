<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:messages.php');
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

   <!-- messages section starts  -->


   <section class="show-products" style="padding-top: 0;">
      <h1 class="heading">Tin nhắn</h1>
      
         <?php
         $select_messages = $conn->prepare("SELECT * FROM `messages`");
         $select_messages->execute();
         if ($select_messages->rowCount() > 0) {
         ?>
            <table class="table_slider">
               <tr>
                  <th>Tên</th>
                  <th>Số điện thoại</th>
                  <th>Email</th>
                  <th>Nội dung</th>
                  <th> Thao tác</th> <!-- Column for action buttons -->
               </tr>
               <?php
               while ($fetch_messages = $select_messages->fetch(PDO::FETCH_ASSOC)) {
               ?>
                  <tr>
                     <td><?= $fetch_messages['name']; ?></td>
                     <td><?= $fetch_messages['number']; ?></td>
                     <td><?= $fetch_messages['email']; ?></td>
                     <td><?= $fetch_messages['message']; ?></td>
                     <td>
                        <!-- <a href="send_messages.php?send=<?= $fetch_messages['user_id']; ?>" class="send-btn">Phản hồi</a> -->
                        <a href="messages.php?delete=<?= $fetch_messages['id']; ?>" class="delete-btn" onclick="return confirm('Bạn muốn xóa phản hồi này?');">Xóa</a>
                     </td>
                  </tr>
               <?php
               }
               ?>
            </table>
         <?php
         } else {
            echo '<p class="empty">Không có phản hồi nào</p>';
         }
         ?>    
   </section>




   <!-- messages section ends -->

   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>