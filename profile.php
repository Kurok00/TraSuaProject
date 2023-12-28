<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Thông tin</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>

   <!-- header section starts  -->
   <?php include 'configs/user_header.php'; ?>
   <!-- header section ends -->

   <section class="user-details">

      <div class="user">
         <?php

         $select_profile = $conn->prepare("SELECT * FROM `users` WHERE user_id = ?");
         $select_profile->execute([$user_id]);
         if ($select_profile->rowCount() > 0) {
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

         ?>
            <img src="images/user-icon.png" alt="">
            <p><i class="fas fa-user"></i><span><span><?= $fetch_profile['name']; ?></span></span></p>
            <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number']; ?></span></p>
            <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email']; ?></span></p>
            <a href="update_profile.php" class="btn">Cập nhật thông tin</a>
            <p class="address"><i class="fas fa-map-marker-alt"></i><span><?php if ($fetch_profile['address'] == '') {
                                                                              echo 'please enter your address';
                                                                           } else {
                                                                              echo $fetch_profile['address'];
                                                                           } ?></span></p>

            <a href="update_address.php" class="btn">Cập nhật địa chỉ</a>
         <?php
         }
         ?>
      </div>

   </section>

   <?php include 'configs/user_footer.php'; ?>

   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

</body>

</html>