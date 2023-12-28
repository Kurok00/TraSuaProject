<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

include 'configs/add_cart.php';




?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Chi tiết sản phẩm</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">
   <link rel="stylesheet" href="templates/css/quick_view.css">
   <style>

   </style>
</head>

<body>

   <?php include 'configs/user_header.php'; ?>

   <section class="quick-view">

      <h1 class="title">Xem chi tiết</h1>

      <?php
      $pid = $_GET['pid'];

      // Select product reviews from the database based on the product ID
      $selectReviews = $conn->prepare("SELECT * FROM `product_reviews` WHERE `product_id` = ? AND `status` = 'active' ORDER BY `created_at` DESC");
      $selectReviews->execute([$pid]);
      $reviews = $selectReviews->fetchAll(PDO::FETCH_ASSOC);
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$pid]);
      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
            // Lấy hình ảnh từ bảng products_images
            $select_images = $conn->prepare("SELECT image_url_2,image_url_3,image_url_4,image_url_5 FROM `product_images` WHERE product_id = ?");
            $select_images->execute([$pid]);
            // Kiểm tra có hình ảnh hay không

      ?>

            <form action="" method="post" class="box">
               <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
               <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">



               <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">



               <div class="image-container" id="image-container">
                  <!-- Hình lớn -->
                  <img src="templates/uploaded_img/products_img/<?= $fetch_products['image']; ?>" alt="" class="draggable large-image">
                  <!-- Hình nhỏ -->
                  <div class="thumbnail-container">
                     <?php

                     echo '<div class="thumbnail draggable" style="background-image: url(\'templates/uploaded_img/products_img/' . $fetch_products['image'] . '\');"></div>';


                     if ($select_images->rowCount() > 0) {
                        while ($fetch_images = $select_images->fetch(PDO::FETCH_ASSOC)) {
                           echo '<div class="thumbnail" style="background-image: url(\'templates/uploaded_img/products_img/' . $fetch_images['image_url_2'] . '\');"></div>';
                           echo '<div class="thumbnail" style="background-image: url(\'templates/uploaded_img/products_img/' . $fetch_images['image_url_3'] . '\');"></div>';
                           echo '<div class="thumbnail" style="background-image: url(\'templates/uploaded_img/products_img/' . $fetch_images['image_url_4'] . '\');"></div>';
                           echo '<div class="thumbnail" style="background-image: url(\'templates/uploaded_img/products_img/' . $fetch_images['image_url_5'] . '\');"></div>';
                        }
                     }
                     ?>
                  </div>
               </div>



               <a href="category.php?category=<?= $fetch_products['category_name']; ?>" class="cat"><?= $fetch_products['category_name']; ?></a>
               <div class="name"><?= $fetch_products['name']; ?></div>
               <?php
               // Lấy tên sản phẩm
               $productName = $fetch_products['name'];
               // Truy vấn SQL để kiểm tra chương trình giảm giá đang áp dụng cho sản phẩm
               $queryCheckSale = $conn->prepare("SELECT discount_percentage, start_date, end_date FROM `sale` WHERE FIND_IN_SET(?, products) > 0");
               $queryCheckSale->execute([$productName]);

               // Kiểm tra xem có bản ghi nào được trả về hay không
               if ($queryCheckSale->rowCount() > 0) {
                  $saleInfo = $queryCheckSale->fetch(PDO::FETCH_ASSOC);
                  // Lấy giá trị cột discount_percentage, start_date, end_date
                  $discountPercentage = $saleInfo['discount_percentage'];
                  $startDate = strtotime($saleInfo['start_date']);
                  $endDate = strtotime($saleInfo['end_date']);
                  $currentDate = time();

                  // Kiểm tra xem ngày hiện tại có nằm trong khoảng ngày bắt đầu và ngày kết thúc hay không
                  if ($currentDate >= $startDate && $currentDate <= $endDate) {
                     echo '<p>Sản phẩm đang được giảm giá: ' . $discountPercentage . '%</p>';
                     $fetch_products['price'] = $fetch_products['price'] * ((100 - $discountPercentage) / 100);
                  }
               }
               ?>
               <div class="flex">
                  <div class="price"><?= number_format($fetch_products['price']); ?><span> đ</span></div>
                  <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
               </div>

               <button type="submit" name="add_to_cart" class="cart-btn">Thêm vào giỏ hàng</button>
               <h3>Thông tin về <?= $fetch_products['name']; ?></h3>


               <!-- Other form fields... -->
               <h type="text" name="detail"><?= $fetch_products['detail']; ?></h>



            </form>

      <?php
         }
      } else {
         echo '<p class="empty">Không có sản phẩm </p>';
      }
      ?>
      <!-- Display product reviews -->
      <div class="review-container">
         <h2>Đánh giá khách hàng</h2>
         <?php
         if (count($reviews) > 0) {
            foreach ($reviews as $review) {
               $select_user = $conn->prepare("SELECT name FROM `users` WHERE user_id = ?  ");
               $select_user->execute([$review['user_id']]);

               $user_row = $select_user->fetch(PDO::FETCH_ASSOC);
         ?>
               <div class="review">
                  <p>Date: <?= $review['created_at']; ?></p>
                  <p class="user-info">Tên khách hàng : <?= $user_row['name']; ?></p>
                  <p>Đánh giá: </p>
                  <div class="star">
                     <?php
                     // Lấy giá trị rating từ cột 'rating' của dòng hiện tại
                     $rating = 0;
                     $rating = $review['rating'];

                     // Loop qua giá trị rating và tạo mã HTML tương ứng
                     for ($i = 0; $i < $rating; $i++) {
                        echo '<label for="r' . $i . '"></label>';
                     }
                     ?>
                  </div>
                  <p class="user-info">Tên : <?= $user_row['name']; ?></p>
                  <p>Bình luận: <?= $review['comment']; ?></p>



               </div>
         <?php
            }
         } else {
            echo "<p>Không có đánh giá</p>";
         }
         ?>
      </div>
   </section>

   <?php include 'configs/user_footer.php'; ?>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
         let largeImage = document.querySelector('.large-image');
         let thumbnails = document.querySelectorAll('.thumbnail');

         thumbnails.forEach(function(thumbnail) {
            thumbnail.addEventListener('click', function() {
               // Đặt hình lớn bằng hình ảnh tương ứng được nhấp vào
               largeImage.src = thumbnail.style.backgroundImage.replace('url("', '').replace('")', '');
            });
         });
      });
   </script>
   <script>
      $(document).ready(function() {
         // Đặt chiều cao tối đa cho .review-container để thêm thanh cuộn nếu nó quá cao
         var maxHeight = 400; // Có thể điều chỉnh giá trị tối đa theo mong muốn

         $(".review-container").css("max-height", maxHeight + "px");
         $(".review-container").css("overflow", "hidden");

         // Thêm thanh cuộn nếu .review-container vượt quá chiều cao tối đa
         if ($(".review-container")[0].scrollHeight > maxHeight) {
            $(".review-container").css("overflow-y", "auto");
         }

         // Thêm sự kiện click cho nút "Xem thêm"
         $("#readMoreBtn").on("click", function() {
            if ($("#readMoreBtn").text() == "Xem thêm") {
               $(".review-container").css("max-height", "none");
               $("#readMoreBtn").text("Thu gọn");
            } else {
               $(".review-container").css("max-height", maxHeight + "px");
               $("#readMoreBtn").text("Xem thêm");
            }
         });
      });
   </script>

   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>


</body>

</html>