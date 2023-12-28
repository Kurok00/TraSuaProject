<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
};

if (isset($_POST['submit'])) {

   $address = $_POST['flat'] . ', ' . $_POST['building'] . ', ' . $_POST['area'] . ', ' . $_POST['town'] . ', ' . $_POST['city'] . ', ' . $_POST['state'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $update_address = $conn->prepare("UPDATE `users` set address = ? WHERE user_id = ?");
   $update_address->execute([$address, $user_id]);

   $message[] = 'đã cập nhật địa chỉ !';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cập nhật địa chỉ</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">


</head>

<body>

   <?php include 'configs/user_header.php' ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>Địa chỉ của bạn </h3>
         <select type="text" class="box" name="state" id="province">
         <option value=""></option>
         </select>
         <select type="text" class="box" name="city" id="district">
            <option value="">chọn Huyện</option>
         </select>
         <select type="text" class="box" name="town" id="ward">
            <option value="">chọn Xã</option>
         </select>

         <input type="text" class="box" placeholder="Đường" required maxlength="50" name="building">
         <input type="text" class="box" placeholder="Số nhà" required maxlength="50" name="flat">
         <input type="text" class="box" placeholder="Khu vực" required maxlength="50" name="area">

         <div id="location-result"></div>
         <input type="submit" value="Lưu" name="submit" class="btn">



      </form>

   </section>




   <?php include 'configs/user_footer.php' ?>









   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.26.1/axios.min.js" integrity="sha512-bPh3uwgU5qEMipS/VOmRqynnMXGGSRv+72H/N260MQeXZIK4PG48401Bsby9Nq5P5fz7hy5UGNmC/W1Z51h2GQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script src="./extends/callApiProvinces-main/index.js"></script>

</body>

</html>