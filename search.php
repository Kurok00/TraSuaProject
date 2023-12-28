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
   <title>Tìm kiếm</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>

   <!-- header section starts  -->
   <?php include 'configs/user_header.php'; ?>
   <!-- header section ends -->

   <!-- search form section starts  -->

   <section class="search-form">
      <form method="post" action="">
         <input type="text" name="search_box" placeholder="Tên sản phẩm..." class="box">
         <button type="submit" name="search_btn" class="fas fa-search"></button>
      </form>
   </section>

   <!-- search form section ends -->


   <section class="products" style="min-height: 100vh; padding-top:0;">

      <div class="box-container">

         <?php
         if (isset($_POST['search_box']) or isset($_POST['search_btn'])) {
            $search_box = $_POST['search_box'];
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE '%{$search_box}%'");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
               while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
                  <form action="" method="post" class="box">
                     <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                     <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
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
                           $fetch_products['price'] = $fetch_products['price'] * ((100 - $discountPercentage) / 100);
                        }
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
               echo '<p class="empty">Không tìm thấy sản phẩm</p>';
            }
         }
         ?>

      </div>

   </section>


   <!-- footer section starts  -->
   <?php include 'configs/user_footer.php'; ?>
   <!-- footer section ends -->



   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

</body>

</html>