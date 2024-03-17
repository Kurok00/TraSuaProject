<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};

if (isset($_POST['update_payment'])) {

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_status->execute([$payment_status, $order_id]);
   $message[] = 'Trạng thái đã cập nhật!!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Đơn hàng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">
   <style>
      /* CSS cho nút xuất PDF */
      a[name="export_pdf"] {
         display: inline-block;
         padding: 10px 15px;
         background-color: #4CAF50;
         /* Màu nền */
         color: #fff;
         /* Màu chữ */
         text-decoration: none;
         border-radius: 5px;
         /* Bo tròn viền */
         transition: background-color 0.3s;
      }

      /* Hover effect */
      a[name="export_pdf"]:hover {
         background-color: #45a049;
         /* Màu nền khi di chuột vào */
      }
   </style>

</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>
   <!-- placed orders section starts  -->

   <section class="placed-orders">

      <h1 class="heading">Đơn hàng</h1>

      <div class="box-container">
         <?php
         if (isset($_GET['placed']) && is_numeric($_GET['placed'])) {
            $placed = $_GET['placed'];

            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE id = :placed");
            $select_orders->bindParam(':placed', $placed, PDO::PARAM_INT);
            $select_orders->execute();

            if ($select_orders->rowCount() > 0) {
               $fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC);
         ?>
               <div class="box">
                  <p> Mã người dùng : <span><?= $fetch_orders['user_id']; ?></span> </p>
                  <p> Ngày đặt : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                  <p> Tên : <span><?= $fetch_orders['name']; ?></span> </p>
                  <p> Gmail : <span><?= $fetch_orders['email']; ?></span> </p>
                  <p> Số đt : <span><?= $fetch_orders['number']; ?></span> </p>
                  <p> Địa chỉ : <span><?= $fetch_orders['address']; ?></span> </p>
                  <p> Sản phẩm : <span><?= $fetch_orders['total_products']; ?></span> </p>
                  <p> Tổng tiền : <span><?= $fetch_orders['total_price']; ?> đ</span> </p>
                  <p> Hình thức thanh toán: <span>
                        <?php
                        switch ($fetch_orders['method']) {
                           case 'cash on delivery':
                              echo 'Thanh toán khi nhận hàng';
                              break;
                           default:
                              echo $fetch_orders['method'];
                              break;
                        }
                        ?>
                     </span> </p>

                  <p> Trạng thái thanh toán: <span>
                        <?php
                        if ($fetch_orders['order_status'] === 'paid') {
                           echo 'Đã thanh toán';
                        } else {
                           echo 'Chưa thanh toán';
                        }
                        ?>
                     </span> </p>
                  <p> Trạng thái đơn hàng : <span>
                        <?php
                        switch ($fetch_orders['payment_status']) {
                           case 'wait':
                              echo 'Chờ xác nhận';
                              break;
                           case 'completed':
                              echo 'Đang chuẩn bị đơn';
                              break;
                           case 'sent':
                              echo 'Đã gửi';
                              break;
                           case 'finish':
                              echo 'Giao hàng thành công';
                              break;
                           default:
                              echo 'Không xác định';
                        }
                        ?>
                     </span> </p>
                  <!-- Thay thế nút "edit" bằng nút xuất PDF -->
                  <a href="../PDF.php?export_pdf=<?= $fetch_orders['user_id']; ?>&order_id=<?= $placed; ?>" name="export_pdf">Xuất PDF</a>

                  <form action="" method="POST">
                     <div class="flex-btn">
                        <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">Xóa</a>
                     </div>
                  </form>
               </div>
         <?php
            } else {
               echo '<p class="empty">No order found for the specified ID.</p>';
            }
         } else {
            echo '<p class="empty">Invalid or missing "placed" parameter.</p>';
         }
         ?>
      </div>


   </section>

   <!-- placed orders section ends -->

   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>