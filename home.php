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
   <title>home</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
   <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="templates/css/style.css">


</head>

<body>

   <?php include 'configs/user_header.php'; ?>





   <section class="hero">
      <div class="swiper hero-slider">
         <div class="swiper-wrapper">
            <?php
            $select_slides = $conn->prepare("SELECT * FROM `slider`");
            $select_slides->execute();
            if ($select_slides->rowCount() > 0) {
               while ($slide = $select_slides->fetch(PDO::FETCH_ASSOC)) {
            ?>
                  <div class="swiper-slide slide">
                     <div class="content">
                        <span><?php echo $slide['caption']; ?></span>
                        <h3><?php echo $slide['name']; ?></h3>
                        <a href="menu.php" class="btn">Thực đơn</a>
                     </div>
                     <div class="image">
                        <img src="<?php echo 'templates/uploaded_img/slider_img/' . $slide['img']; ?>" alt="">

                     </div>
                  </div>
            <?php
               }
            } else {
               echo '<p>error slider!</p>';
            }
            ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>
   </section>


   <section class="category">

      <h1 class="title">Loại sản phẩm</h1>
      <?php
      $select_categories = $conn->prepare("SELECT * FROM `category`");
      $select_categories->execute();
      if ($select_categories->rowCount() > 0) {
         $categories = $select_categories->fetchAll(PDO::FETCH_ASSOC);
      }
      ?>


      <div class="box-container">
         <?php
         if (!empty($categories)) {
            foreach ($categories as $category) {
         ?>
               <a href="category.php?category=<?= $category['category_name']; ?>" class="box">
                  <img src="templates/uploaded_img/category_img/<?= $category['image']; ?>" alt="">
                  <h3><?= $category['category_name']; ?></h3>
               </a>
         <?php
            }
         } else {
            echo '<p class="empty">No categories available!</p>';
         }
         ?>
      </div>



   </section>




   <section class="products">

      <h1 class="title">Thực đơn</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` WHERE stock > 0 LIMIT 6");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                  <?php
                  $discountPercentage = 0;
                  // Lấy tên sản phẩm
                  $productName = $fetch_products['name'];
                  // Truy vấn SQL để kiểm tra xem sản phẩm có trong bảng sale hay không
                  $queryCheckSale = $conn->prepare("SELECT discount_percentage FROM `sale` WHERE FIND_IN_SET(?, products) > 0");
                  $queryCheckSale->execute([$productName]);

                  // Kiểm tra xem có bản ghi nào được trả về hay không
                  if ($queryCheckSale->rowCount() > 0) {
                     // Lấy giá trị cột discount_percentage
                     $discountPercentage = $queryCheckSale->fetch(PDO::FETCH_ASSOC)['discount_percentage'];

                     // Sử dụng giá trị discount_percentage ở đây (ví dụ: in ra)

                     
                     $fetch_products['price'] = $fetch_products['price'] * ((100 - $discountPercentage) / 100);
                  }
                  ?>
                  <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
                  <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button>
                  <img src="templates/uploaded_img/products_img/<?= $fetch_products['image']; ?>" alt="">
                  <a href="category.php?category=<?= $fetch_products['category_name']; ?>" class="cat"><?= $fetch_products['category_name']; ?></a>
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="flex">
                     <div class="price"><?= number_format($fetch_products['price']); ?><span> đ</span></div>
                     <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                  </div>
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">Không tìm thấy sản phẩm nào!</p>';
         }
         ?>

      </div>

      <div class="more-btn">
         <a href="menu.php" class="btn">Xem thêm</a>
      </div>

   </section>



















   <?php include 'configs/user_footer.php'; ?>


   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <script src="templates/js/script.js"></script>

   <script>
      var swiper = new Swiper(".hero-slider", {
         loop: true,
         grabCursor: true,
         effect: "flip",
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
      });
   </script>

</body>

</html>