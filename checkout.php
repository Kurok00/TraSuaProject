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



   $name = $_POST['name'];
   $number = $_POST['number'];
   $email = $_POST['email'];
   $method = $_POST['method'];
   $address = $_POST['address'];
   $total_products = '';
   $first_order_discount = $_POST['first_order_discount'];

   // Lấy thông tin giỏ hàng từ phiên
   if (isset($_SESSION['cart'])) {
      $total_price = 0; // Đặt lại tổng giá
      foreach ($_SESSION['cart'] as $item) {
         $total_products .= $item['name'] . ' (' . $item['price'] . ' x ' . $item['qty'] . ') - ';
         $total_price += ($item['price'] * $item['qty']);
      }
      $VAT = $total_price * 0.1;
      $total_bill = $total_price + $VAT - $first_order_discount;
   }


   if (isset($_POST["method"])) {
      $selectedMethod = $_POST["method"];

      // Kiểm tra giá trị và thực hiện các hành động tương ứng
      switch ($selectedMethod) {
         case "cash on delivery":


            // Thực hiện lưu trữ đơn hàng trong bảng orders
            $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, VAT) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_bill, $VAT]);

            // Lấy ID của bản ghi vừa được thêm vào
            $order_id = $conn->lastInsertId();
            // Lưu $order_id vào $_SESSION
            // Lặp qua mảng giỏ hàng trong $_SESSION để cập nhật số lượng tồn kho
            foreach ($_SESSION['cart'] as $item) {
               $pid = $item['pid'];
               $qty = $item['qty'];

               // Truy vấn để lấy số lượng tồn kho hiện tại của sản phẩm
               $stmt = $conn->prepare("SELECT stock FROM products WHERE id = :pid");
               $stmt->bindParam(':pid', $pid);
               $stmt->execute();
               $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $current_stock = $row['stock'];

               // Kiểm tra xem số lượng tồn kho có đủ để cập nhật không
               if ($qty <= $current_stock) {
                  // Cập nhật số lượng tồn kho mới sau khi khách hàng mua
                  $new_stock = $current_stock - $qty;
                  $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                  $update_stock->execute([$new_stock, $pid]);
               }
            }

            // Xóa thông tin giỏ hàng từ phiên
            unset($_SESSION['cart']);

            header('location:order_details.php?id_order=' . $order_id);

            $message[] = 'Đơn hàng được đặt thành công!';
            // Thực hiện hành động cho thanh toán khi nhận hàng
            break;
            //============================================================================================================//
         case "VNPAY":
            // Thực hiện hành động cho thanh toán VNPAY

            // Thực hiện lưu trữ đơn hàng trong bảng orders
            $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, VAT) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_bill, $VAT]);

            // Lấy ID của bản ghi vừa được thêm vào
            $order_id = $conn->lastInsertId();
            // Lưu $order_id vào $_SESSION
            $_SESSION['VNPAY_ORDER_ID'] = $order_id;
            // Lặp qua mảng giỏ hàng trong $_SESSION để cập nhật số lượng tồn kho
            foreach ($_SESSION['cart'] as $item) {
               $pid = $item['pid'];
               $qty = $item['qty'];

               // Truy vấn để lấy số lượng tồn kho hiện tại của sản phẩm
               $stmt = $conn->prepare("SELECT stock FROM products WHERE id = :pid");
               $stmt->bindParam(':pid', $pid);
               $stmt->execute();
               $row = $stmt->fetch(PDO::FETCH_ASSOC);
               $current_stock = $row['stock'];

               // Kiểm tra xem số lượng tồn kho có đủ để cập nhật không
               if ($qty <= $current_stock) {
                  // Cập nhật số lượng tồn kho mới sau khi khách hàng mua
                  $new_stock = $current_stock - $qty;
                  $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                  $update_stock->execute([$new_stock, $pid]);
               }
            }

            // Xóa thông tin giỏ hàng từ phiên
            unset($_SESSION['cart']);


            // Thực hiện hành động cho thanh toán khi nhận hàng
            echo '<form action="vnpay_php/vnpay_create_payment.php" method="post" id="vnpayForm">';
            echo '<input type="hidden" name="VNPAY_ORDER_AMOUNT" value="' . $total_bill . '">';
            echo '</form>';
            echo '<script>document.getElementById("vnpayForm").submit();</script>';


            break;
         case "paytm":
            // Thực hiện hành động cho thanh toán atm
            break;
         default:
            // Hành động mặc định nếu không có giá trị nào phù hợp
            break;
      }
   } else {
      echo "Lỗi: Không có giá trị method được gửi.";
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
   <title>Thanh toán</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="templates/css/style.css">
   <script src="https://www.paypal.com/sdk/js?client-id=ASEPPQgDuN2ayoH8DQmCBNwxhg6Yrwtlxx0RKle5sx4goanfWgo-OPl0bUAW6yzzmbIdSm46Cjf0oiRL&currency=USD"></script>
   <script src="./js/app.js"></script>


</head>

<body>

   <!-- header section starts  -->
   <?php include 'configs/user_header.php'; ?>
   <!-- header section ends -->

   <div class="heading">
      <h3>Thanh toán</h3>
      <p><a href="home.php">Trang chủ</a> <span> / thanh toán</span></p>
   </div>

   <section class="checkout">

      <h1 class="title">Hóa đơn</h1>

      <form action="" method="post">

         <div class="cart-items">
            <h3>Các mặt hàng</h3>
            <?php
            $grand_total = 0;

            // Kiểm tra xem có sản phẩm trong giỏ hàng không
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
               foreach ($_SESSION['cart'] as $item) {
                  $sub_total = $item['price'] * $item['qty'];
                  $grand_total += $sub_total;
            ?>
                  <p>
                     <span class="name"><?= $item['name']; ?></span>
                     <span class="price"><?= number_format($item['price']); ?>đ x <?= $item['qty']; ?></span>
                  </p>
            <?php
               }
            } else {
               echo '<p class="empty">Giỏ hàng của bạn trống trơn</p>';
            }
            // Kiểm tra xem người dùng đã đặt hàng lần đầu tiên hay chưa
            $check_first_order_query = $conn->prepare("SELECT COUNT(*) as count FROM `orders` WHERE user_id = ?");
            $check_first_order_query->execute([$user_id]);
            $result = $check_first_order_query->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] == 0) {
               // Nếu là đơn hàng đầu tiên, thực hiện giảm giá 20%
               $discount_percentage = 20;
               $discount_amount = ($grand_total * $discount_percentage) / 100;
               // Lưu giá trị giảm giá vào biến để sử dụng sau này
               $first_order_discount = $discount_amount;

               // Thêm thông báo giảm giá vào thông báo thành công
               echo '<p>
        <span class="name">Đơn hàng đầu tiên giảm 20%</span>
        <span class="price">' . number_format($first_order_discount) . ' đ</span>
    </p>';
            } else {
               // Nếu không phải là đơn hàng đầu tiên, giá trị giảm giá là 0
               $first_order_discount = 0;
            }


            $VAT = $grand_total * 0.1;
            $total_bill = $grand_total + $VAT - $first_order_discount;





            // Thêm giá trị giảm giá vào biểu mẫu ẩn
            echo '<input type="hidden" name="first_order_discount" value="' . $first_order_discount . '">';


            ?>

            <p>
               <span class="name">VAT 10%</span>
               <span class="price"><?= number_format($VAT) ?> đ</span>
            </p>
            <p class="grand-total"><span class="name">Tổng trị giá :</span><span class="price"><?= number_format($total_bill); ?> đ</span></p>

            <a href="cart.php" class="btn">Xem giỏ hàng</a>
         </div>


         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $total_bill; ?>">
         <input type="hidden" name="first_order_discount" value="<?= $first_order_discount; ?>">
         <input type="hidden" name="vat" value="<?= $VAT; ?>">
         <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
         <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
         <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
         <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">

         <div class="user-info">
            <h3>Thông tin của bạn</h3>
            <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
            <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
            <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email'] ?></span></p>
            <a href="update_profile.php" class="btn">Cập nhật thông tin</a>
            <h3>Địa chỉ nhận hàng</h3>
            <p><i class="fas fa-map-marker-alt"></i><span><?php if ($fetch_profile['address'] == '') {
                                                               echo 'Nhập địa chỉ của bạn';
                                                            } else {
                                                               echo $fetch_profile['address'];
                                                            } ?></span></p>
            <a href="update_address.php" class="btn">Cập nhật địa chỉ</a>
            <select name="method" class="box" required>
               <option value="" disabled selected>Chọn phương thức thanh toán --</option>
               <option value="cash on delivery">Thanh toán khi nhận hàng</option>
               <option value="VNPAY">Thanh toán VNPAY</option>
               <option value="paytm">Thanh toán ATM</option>

            </select>
            <input type="submit" value="Xác nhận đặt hàng" class="btn <?php if ($fetch_profile['address'] == '') {
                                                                           echo 'disabled';
                                                                        } ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">


         </div>

      </form>

   </section>

   <!-- footer section starts  -->
   <?php include 'configs/user_footer.php'; ?>
   <!-- footer section ends --


   <!-- custom js file link  -->
   <script src="templates/js/script.js"></script>

</body>

</html>