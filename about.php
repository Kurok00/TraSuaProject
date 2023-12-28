<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
   <title>Thông tin</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">
   <style>
      /* CSS */
      .star {
         display: flex;
         justify-content: center;
         align-items: center;
         width: 100%;
         vertical-align: middle;
      }

      .star label {
         height: 25px;
         width: 25px;
         position: relative;
         cursor: pointer;
         padding: 0 5px;
         display: flex;
         justify-content: center;
         align-items: center;
      }

      .star label:after {
         transition: all 1s ease-out;
         position: absolute;
         content: "★";
         color: orange;
         font-size: 32px;
      }

      .star input:checked+label:after,
      .star input:checked~label:after {
         content: "★";
         color: gold;
         text-shadow: 0 0 10px gold;
      }
   </style>

</head>

<body>

   <!-- header section starts  -->
   <?php include 'configs/user_header.php'; ?>
   <!-- header section ends -->

   <div class="heading">
      <h3>Thông tin</h3>
      <p><a href="home.php">Trang chủ</a> <span> / thông tin</span></p>
   </div>

   <!-- about section starts  -->

   <section class="about">

      <div class="row">

         <div class="image">
            <img src="templates/uploaded_img/system_img/bartender.png" alt="">
         </div>

         <div class="content">
            <h3>Một số thông tin về chúng tôi</h3>
            <p>TeaBill là một quán trà sữa thú vị và độc đáo nằm tại trung tâm thành phố. Với sứ mệnh mang đến sự độc đáo và tinh tế trong thế giới trà sữa, chúng tôi tự hào về việc kết hợp những hương vị tinh tế, nguyên liệu tươi ngon và chất lượng phục vụ vượt trội.</p>
            <a href="menu.php" class="btn">Xem thực đơn</a>
         </div>

      </div>

   </section>

   <!-- about section ends -->

   <!-- steps section starts  -->

   <section class="steps">

      <h1 class="title">Các bước đặt hàng</h1>

      <div class="box-container">

         <div class="box">
            <img src="templates/uploaded_img/system_img/step-1.png" alt="">
            <h3>Chọn đơn hàng</h3>
            <p>Tìm kiếm sản phẩm muốn mua trên trang web hoặc ứng dụng của cửa hàng.</p>
         </div>

         <div class="box">
            <img src="templates/uploaded_img/system_img/step-2.png" alt="">
            <h3>Giao hàng nhanh</h3>
            <p>Giao hàng nhanh chóng, an toàn đến tận nơi, sản phẩm nguyên vẹn.</p>
         </div>

         <div class="box">
            <img src="templates/uploaded_img/system_img/step-3.png" alt="">
            <h3>Thưởng thức</h3>
            <p>Kiểm tra sản phẩm khi nhận hàng và tận hưởng.</p>
         </div>

      </div>

   </section>

   <!-- steps section ends -->

   <!-- reviews section starts  -->
   <?php
   // Truy vấn tất cả các đánh giá từ cơ sở dữ liệu
   $select_reviews = $conn->prepare("SELECT * FROM `product_reviews` WHERE status='active' ORDER BY created_at DESC");
   $select_reviews->execute();
   ?>
   <section class="reviews">

      <h1 class="title">Đánh giá của khách hàng</h1>

      <div class="swiper reviews-slider">

         <div class="swiper-wrapper">
            <?php
            while ($row = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
               // Lấy tên khách hàng từ bảng user dựa trên user_id
               $select_user = $conn->prepare("SELECT name FROM `users` WHERE user_id = ?  ");
               $select_user->execute([$row['user_id']]);

               $user_row = $select_user->fetch(PDO::FETCH_ASSOC);
            ?>
               <div class="swiper-slide slide">
                  <?php
                  // Sinh số ngẫu nhiên từ 1 đến 9
                  $randomNumber = rand(1, 9);

                  // Đường dẫn đầy đủ đến thư mục chứa hình ảnh
                  $imageDirectory = 'templates/uploaded_img/review_img/';

                  // Tạo đường dẫn đầy đủ cho hình ảnh ngẫu nhiên
                  $imagePath = $imageDirectory . $randomNumber . '.png';
                  ?>

                  <img src="<?php echo $imagePath; ?>" alt="">
                  <h3>Khách hàng: <?= $user_row['name']; ?></h3>

                  

                  <div class="star">
                     <?php
                     // Lấy giá trị rating từ cột 'rating' của dòng hiện tại
                     $rating = $row['rating'];

                     // Loop qua giá trị rating và tạo mã HTML tương ứng
                     for ($i = 0; $i < $rating; $i++) {
                        echo '<label for="r' . $i . '"></label>';
                     }
                     ?>
                  </div>

                  <p>Nhận xét: <?= $row['comment']; ?></p>
               </div>
            <?php
            }
            ?>
         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>

   <!-- reviews section ends -->



















   <!-- footer section starts  -->
   <?php include 'configs/user_footer.php'; ?>
   <!-- footer section ends -->=






   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

   <script>
      var swiper = new Swiper(".reviews-slider", {
         loop: true,
         grabCursor: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            0: {
               slidesPerView: 1,
            },
            700: {
               slidesPerView: 2,
            },
            1024: {
               slidesPerView: 3,
            },
         },
      });
   </script>

</body>

</html>