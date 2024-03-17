<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};



if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];

   // Kiểm tra xem có dữ liệu liên quan trong bảng product_reviews không
   $check_reviews = $conn->prepare("SELECT * FROM `product_reviews` WHERE product_id = ?");
   $check_reviews->execute([$delete_id]);

   if ($check_reviews->rowCount() > 0) {
       // Có dữ liệu liên quan, xử lý theo nhu cầu của bạn (ví dụ: xóa hoặc cập nhật)
       // Ví dụ: Xóa dữ liệu liên quan
       $delete_reviews = $conn->prepare("DELETE FROM `product_reviews` WHERE product_id = ?");
       $delete_reviews->execute([$delete_id]);
   }

   // Tiếp tục xóa ảnh và sản phẩm từ bảng products
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);

   // Xóa ảnh
   unlink('../templates/uploaded_img/products_img/' . $fetch_delete_image['image']);

   // Xóa sản phẩm từ bảng products
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);

   header('location:products.php');
}





// Xử lý tìm kiếm sản phẩm
if (isset($_GET['searchTerm']) && isset($_GET['searchColumn'])) {
   $searchTerm = $_GET['searchTerm'];
   $searchColumn = $_GET['searchColumn'];

   // Thực hiện truy vấn SQL dựa trên cột tìm kiếm được chọn
   $search_query = $conn->prepare("SELECT * FROM products WHERE $searchColumn LIKE ?");
   $searchTerm = '%' . str_replace(' ', '%', $searchTerm) . '%'; // Thay khoảng trắng thành '%' để tìm kiếm khoảng trắng
   $search_query->execute([$searchTerm]);

   $search_results = $search_query->fetchAll(PDO::FETCH_ASSOC);
} else {
   // Hiển thị tất cả sản phẩm nếu không có điều kiện tìm kiếm
   $allProductsQuery = $conn->query("SELECT * FROM products");
   $search_results = $allProductsQuery->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Sản phẩm</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">


   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">

</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>
   <!-- add products section starts  -->
   <h1 class="heading">Danh sách sản phẩm</h1>
   <div class="search-bar">
      <input type="text" placeholder="Tìm kiếm sản phẩm" id="searchInput" class="box">
      <select id="searchColumn" class="box">
         <option value="name">Tên sản phẩm</option>
         <option value="category_name">Danh mục</option>
         <option value="id">ID</option>
         <option value="price">Giá</option>
         <option value="stock">Số lượng</option>
      </select>
      <button id="searchButton" class="btn_share">Tìm</button>
   </div>

   <!-- show products section starts  -->
   <section class="show-products" style="padding-top: 0;">
      <?php
      if (isset($search_results) && count($search_results) > 0) {
      ?>
         <table class="table-category">
            <tr>
               <th>Id</th>
               <th>Ảnh</th>
               <th>Tên sản phẩm</th>
               <th>Giá (đ)</th>
               <th>Danh mục</th>
               <th>Số lượng</th>
               <th>Chi tiết</th>
               <th>Tùy chọn</th>
            </tr>
            <?php
            foreach ($search_results as $product) {
            ?>
               <tr>
                  <td><?= $product['id']; ?></td>
                  <td><img src="../templates/uploaded_img/products_img/<?= $product['image']; ?>" alt=""></td>
                  <td><?= $product['name']; ?></td>
                  <td><?= $product['price']; ?></td>
                  <td><?= $product['category_name']; ?></td>
                  <td><?= $product['stock']; ?></td>
                  <td><?= $product['detail']; ?></td>
                  <td>
                     <a href="update_product.php?update=<?= $product['id']; ?>" class="option-btn">Chỉnh sửa</a>
                     <a href="products.php?delete=<?= $product['id']; ?>" class="delete-btn" onclick="return confirm('Xóa sản phẩm này?');">Xóa sản phẩm</a>
                  </td>
               </tr>
            <?php
            }
            ?>
         </table>
      <?php
      } else {
         echo '<p class="empty">Không tìm thấy sản phẩm nào</p>';
      }
      ?>

   </section>


   <script>
      document.getElementById("searchButton").addEventListener("click", function() {
         var searchTerm = document.getElementById("searchInput").value;
         var searchColumn = document.getElementById("searchColumn").value;

         // Gửi yêu cầu AJAX để thực hiện tìm kiếm dựa trên searchTerm và searchColumn
         var xhr = new XMLHttpRequest();
         xhr.open('GET', 'search_products.php?searchTerm=' + searchTerm + '&searchColumn=' + searchColumn, true);

         xhr.onload = function() {
            if (xhr.status === 200) {
               var productContainer = document.querySelector(".table-category");

               // Xóa tất cả các sản phẩm hiện tại trong bảng
               var rows = productContainer.querySelectorAll("tr");
               for (var i = rows.length - 1; i > 0; i--) {
                  var row = rows[i];
                  row.parentNode.removeChild(row);
               }

               var products = JSON.parse(xhr.responseText);

               if (products.length > 0) {
                  // Tạo header row cho bảng
                  var headerHTML = `<tr>
                <th>Id</th>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá ($)</th>
                <th>Danh mục</th>
                <th>Số lượng</th>
                <th>Tùy chọn</th>
            </tr>`;
                  productContainer.innerHTML += headerHTML;

                  // Hiển thị sản phẩm tìm thấy
                  products.forEach(function(product) {
                     // Tạo HTML cho từng sản phẩm và thêm vào productContainer
                     var productHTML = `<tr>
                    <td>${product.id}</td>
                    <td><img src="../templates/uploaded_img/products_img/${product.image}" alt=""></td>
                    <td>${product.name}</td>
                    <td>${product.price}</td>
                    <td>${product.category_name}</td>
                    <td>${product.stock}</td>
                    <td>
                        <a href="update_product.php?update=${product.id}" class="option-btn">Chỉnh sửa</a>
                        <a href="products.php?delete=${product.id}" class="delete-btn" onclick="return confirm('Xóa sản phẩm này?');">Xóa sản phẩm</a>
                    </td>
                </tr>`;
                     productContainer.innerHTML += productHTML;
                  });
               } else {
                  // Hiển thị thông báo nếu không tìm thấy sản phẩm
                  productContainer.innerHTML += '<tr><td colspan="7" class="empty">Không tìm thấy sản phẩm nào</td></tr>';
               }
            }
         };

         xhr.send();
      });
   </script>



   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>