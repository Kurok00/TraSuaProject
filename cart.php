<?php
include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
}


if (isset($_POST['delete_item'])) {
   $delete_pid = $_POST['delete_item'];

   // Remove the item from the cart based on product ID
   foreach ($_SESSION['cart'] as $key => $cart_item) {
      if ($cart_item['pid'] == $delete_pid) {
         unset($_SESSION['cart'][$key]);
         $message[] = 'Đã xóa sản phẩm khỏi giỏ hàng';
         break;
      }
   }
}


if (isset($_POST['delete_all'])) {
   // Xóa toàn bộ thông tin trong $_SESSION['cart']
   unset($_SESSION['cart']);
   $message[] = 'Đã xóa giỏ hàng';
}


if (isset($_POST['update_qty'])) {
   $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();

   foreach ($product_ids as $pid) {
      if (isset($_POST['update_qty'][$pid])) {
         $new_qty = $_POST['qty'][$pid];
         $new_qty = filter_var($new_qty, FILTER_SANITIZE_STRING);

         // Cập nhật số lượng cho sản phẩm cụ thể
         $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();


         foreach ($cart_items as $key => $cart_item) {
            if ($cart_item['pid'] == $pid) {
               try {
                  $stmt = $conn->prepare("SELECT stock FROM products WHERE id = :pid");
                  $stmt->bindParam(':pid', $pid);
                  $stmt->execute();

                  $row = $stmt->fetch(PDO::FETCH_ASSOC);

                  if ($row && array_key_exists('stock', $row)) {
                     $stock = $row['stock'];
                     $current_qty = $cart_item['qty'];

                     // In ra các giá trị để debug
                     // echo "Tồn kho: $stock, Số lượng hiện tại: $current_qty, Số lượng mới: $new_qty,<br>";


                     // Kiểm tra số lượng tồn kho trước khi cập nhật giỏ hàng
                     if ($new_qty <= $stock) {
                        // Cập nhật số lượng sản phẩm trong giỏ hàng
                        $_SESSION['cart'][$key]['qty'] = $new_qty;
                        $message[] = 'Cập nhật giỏ hàng thành công';
                     } else {
                        $message[] = 'Không thể cập nhật giỏ hàng do số lượng tồn kho không đủ.';
                     }
                  } else {
                     // Không tìm thấy hoặc không có key 'stock' trong kết quả truy vấn
                     $message[] = 'Lỗi khi kiểm tra số lượng tồn kho.';
                  }
               } catch (PDOException $e) {
                  // Xử lý lỗi cơ sở dữ liệu
                  // Ghi log hoặc hiển thị thông báo lỗi
                  $message[] = 'Lỗi cơ sở dữ liệu khi kiểm tra số lượng tồn kho.';
               }

               break;
            }
         }
      }
   }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>cart</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">

   <style>
      .box-container-cart {
         margin: 2rem auto;
         /* Tự động căng giữa trang */
      }

      .cart-table {
         width: 100%;
         max-width: 800px;
         /* Thay đổi giá trị này theo nhu cầu của bạn */
         margin: 0 auto;
         /* Tự động căng giữa trang */
         border-collapse: collapse;
         margin-bottom: 20px;
      }

      .cart-table th,
      .cart-table td {
         font-size: 16px;
         /* Đặt kích thước chữ muốn sử dụng, ví dụ là 16px */

         border: 1px solid #ddd;
         padding: 12px;
         /* Tăng kích thước padding */
         text-align: left;
      }

      .cart-table th {
         background-color: #f2f2f2;
      }

      .cart-table img {
         max-width: 50px;
         max-height: 50px;
         border-radius: 5px;
      }

      .qty {
         padding: 8px;
         /* Thay đổi kích thước padding nếu cần */
         font-size: 14px;
         /* Thay đổi kích thước chữ nếu cần */
         width: 60px;
         /* Thay đổi kích thước input nếu cần */
         border: 1px solid #ddd;
         /* Thay đổi border nếu cần */
         border-radius: 5px;
         /* Thay đổi độ cong của góc nếu cần */
         margin: 0 5px;
         /* Thay đổi margin nếu cần */
         box-sizing: border-box;
         /* Đảm bảo kích thước tính cả padding và border */
      }

      .cart-total {
         margin-top: 20px;
      }

      .btn.disabled {
         opacity: 0.5;
         cursor: not-allowed;
      }

      .more-btn {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-top: 20px;
      }

      .delete-btn {
         background-color: #dc3545;
         color: #fff;
         border: none;
         padding: 12px 24px;
         /* Tăng kích thước padding */
         border-radius: 5px;
         cursor: pointer;
      }

      .delete-btn:hover {
         background-color: #c82333;
      }

      /* Đổi màu cho các nút bên trong */
      .cart-table button {
         background-color: #ffc107;
         /* Màu vàng */
         color: #000;
         /* Màu đen */
         border: none;
         padding: 12px 24px;
         /* Tăng kích thước padding */
         border-radius: 5px;
         cursor: pointer;
      }

      .cart-table button:hover {
         background-color: #ffcd38;
         /* Màu vàng nhạt khi hover */
      }

      /* Adjustments for smaller screens */
      @media (max-width: 768px) {

         .cart-table th,
         .cart-table td {
            font-size: 12px;
         }

         .qty {
            width: 30px;
         }
      }
   </style>

</head>

<body>

   <!-- header section starts  -->
   <?php include 'configs/user_header.php'; ?>
   <!-- header section ends -->

   <div class="heading">
      <h3>Giỏ hàng</h3>
      <p><a href="home.php">home</a> <span> / giỏ hàng</span></p>
   </div>

   <!-- shopping cart section starts  -->

   <section class="products">
      <h1 class="title">Giỏ hàng</h1>
      <div class="box-container-cart">
         <?php
         $grand_total = 0;
         $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

         if (count($cart_items) > 0) {
         ?>
            <form action="" method="post">
               <table class="cart-table">
                  <thead>
                     <tr>
                        <th>Tên</th>
                        <th>Hình ảnh</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Thao tác</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     foreach ($cart_items as $key => $item) {
                        $sub_total = $item['price'] * $item['qty'];
                        $grand_total += $sub_total;
                     ?>
                        <tr>
                           <td><?= $item['name']; ?></td>
                           <td><img src="templates/uploaded_img/products_img/<?= $item['image']; ?>" alt=""></td>

                           <td><?= $item['price']; ?> đ</td>
                           <td>
                              <form action="" method="post">
                                 <input type="number" name="qty[<?= $item['pid']; ?>]" class="qty" min="1" max="99" value="<?= $item['qty']; ?>" maxlength="2">
                                 <input type="hidden" name="product_ids[]" value="<?= $item['pid']; ?>">
                                 <button type="submit" class="fas fa-edit" name="update_qty[<?= $item['pid']; ?>]"></button>
                              </form>
                           </td>
                           <td><?= $sub_total; ?> đ</td>
                           <td>
                              <!-- Nút Xóa -->
                              <form action="" method="post">
                                 <input type="hidden" name="delete_item" value="<?= $item['pid']; ?>">
                                 <button type="submit" class="fas fa-trash-alt delete-btn" onclick="return confirm('Xóa sản phẩm khỏi giỏ hàng?');"></button>
                              </form>

                           </td>

                        </tr>
                     <?php
                     }
                     ?>
                  </tbody>
               </table>
            </form>
         <?php
         } else {
            echo '<p class="empty">Không có sản phẩm nào trong giỏ hàng </p>';
         }
         ?>
      </div>


      <div class="cart-total">
         <p>Tổng tiền sản phẩm : <span><?= $grand_total; ?> đ</span></p>
         <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Thanh toán</a>
      </div>

      <div class="more-btn">
         <form action="" method="post">
            <button type="submit" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" name="delete_all" onclick="return confirm('Xóa giỏ hàng ?');">xóa tất cả</button>
         </form>
         <a href="menu.php" class="btn">Tiếp tục mua sắm</a>
      </div>

   </section>

   <!-- shopping cart section ends -->

   <!-- footer section starts  -->
   <?php include 'configs/user_footer.php'; ?>
   <!-- footer section ends -->

   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

</body>

</html>