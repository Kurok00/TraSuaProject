<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['add_product'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $stock = $_POST['stock'];
   $stock = filter_var($stock, FILTER_SANITIZE_STRING);
   $detail = $_POST['detail'];
   $detail = filter_var($detail, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../templates/uploaded_img/products_img/' . $image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if ($select_products->rowCount() > 0) {
      $message[] = 'Sản phẩm đã tồn tại!';
   } else {
      if ($image_size > 2000000) {
         $message[] = 'Kích thước ảnh quá lớn';
      } else {
         move_uploaded_file($image_tmp_name, $image_folder);

         // Thêm sản phẩm vào bảng `products`
         $insert_product = $conn->prepare("INSERT INTO `products` (name, category_name, price, stock,detail, image) VALUES (?, ?, ?, ?,?, ?)");
         $insert_product->execute([$name, $category, $price, $stock, $detail, $image]);

         // Lấy id sản phẩm vừa thêm
         $product_id = $conn->lastInsertId();
         // Kiểm tra xem product_id đã tồn tại trong bảng `product_images` chưa
         $check_product_id = $conn->prepare("SELECT * FROM `product_images` WHERE product_id = ?");
         $check_product_id->execute([$product_id]);

         if ($check_product_id->rowCount() === 0) {
            // Nếu product_id chưa tồn tại thì tạo mới 1 hàng
            $update_image = $conn->prepare("INSERT INTO `product_images` (product_id) VALUES (?)");
            $update_image->execute([$product_id]);
            // Thêm 4 ảnh vào bảng `product_images`
            for ($i = 2; $i <= 5; $i++) {
               $image_key = 'image' . $i;
               $image_name = $_FILES[$image_key]['name'];
               $image_name = filter_var($image_name, FILTER_SANITIZE_STRING);
               $image_size = $_FILES[$image_key]['size'];
               $image_tmp_name = $_FILES[$image_key]['tmp_name'];
               $image_folder = '../templates/uploaded_img/products_img/' . $image_name;
               // Thêm ảnh vào bảng `product_images`
               move_uploaded_file($image_tmp_name, $image_folder);
               $insert_images = $conn->prepare("UPDATE `product_images` SET image_url_$i = ? WHERE product_id = ?");
               $insert_images->execute([$image_name, $product_id]);
            }

            $message[] = 'Thêm mới thành công !';
         }
      }
   }
}

// Query để lấy tất cả các hàng từ bảng category
$sql = "SELECT category_name FROM category";

try {
   // Chuẩn bị câu lệnh
   $stmt = $conn->prepare($sql);

   // Thực hiện câu lệnh
   $stmt->execute();

   // Lấy tất cả các hàng từ kết quả trả về
   $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
   echo "Truy vấn thất bại: " . $e->getMessage();
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

   <section class="form-container">

      <form action="" method="POST" enctype="multipart/form-data">
         <h3>Thêm sản phẩm</h3>
         <input type="text" required placeholder="Tên sản phẩm" name="name" maxlength="100" class="box">
         <input type="number" min="0" max="9999999999" required placeholder="Giá sản phẩm" name="price" onkeypress="if(this.value.length == 10) return false;" class="box">
         <select name="category" class="box" required>
            <option value="" disabled selected>Loại Sản phẩm --</option>
            <?php foreach ($categories as $category) : ?>
               <option value="<?= $category['category_name'] ?>"><?= $category['category_name'] ?></option>
            <?php endforeach; ?>
         </select>
         <input type="text" required placeholder="Số lượng" name="stock" class="box" min="0">

         <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
         <!-- Thêm 4 ô để thêm ảnh -->
         <input type="file" name="image2" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
         <input type="file" name="image3" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
         <input type="file" name="image4" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
         <input type="file" name="image5" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
         <textarea type="text" required placeholder="Miêu tả sản phẩm" name="detail" class="box"></textarea>

         <input type="submit" value="Thêm sản phẩm" name="add_product" class="btn">
      </form>

   </section>

   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>