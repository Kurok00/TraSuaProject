<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Trang chủ</title>

   <!-- font awesome cdn link  -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">
   <style>

   </style>

   <?php
   include '../configs/connect.php';

   session_start();

   $admin_id = $_SESSION['admin_id'];

   if (!isset($admin_id)) {
      header('location:admin_login.php');
   }
   ?>

   <?php
   // Thêm phần lấy dữ liệu danh mục và số lượng sản phẩm cho từng danh mục từ cơ sở dữ liệu
   $select_categories = $conn->prepare("SELECT category_name, COUNT(*) AS count FROM products GROUP BY category_name");
   $select_categories->execute();
   $category_data = $select_categories->fetchAll(PDO::FETCH_ASSOC);

   // Biến category_labels và category_counts chứa thông tin danh mục và số lượng sản phẩm
   $category_labels = [];
   $category_counts = [];

   foreach ($category_data as $category) {
      $category_labels[] = $category['category_name'];
      $category_counts[] = $category['count'];
   }

   // Retrieve data for order status pie chart (orders placed today)
   $select_order_status = $conn->prepare("SELECT payment_status, COUNT(*) AS count FROM orders WHERE DATE(placed_on) = CURDATE() GROUP BY payment_status");
   $select_order_status->execute();
   $order_status_data = $select_order_status->fetchAll(PDO::FETCH_ASSOC);

   $order_status_labels = [];
   $order_status_counts = [];

   foreach ($order_status_data as $status) {
      switch ($status['payment_status']) {
         case 'wait':
            $order_status_labels[] = 'Chờ xác nhận';
            break;
         case 'completed':
            $order_status_labels[] = 'Đang chuẩn bị đơn';
            break;
         case 'sent':
            $order_status_labels[] = 'Đã gửi';
            break;
         case 'finish':
            $order_status_labels[] = 'Giao hàng thành công';
            break;
         case 'cancel':
            $order_status_labels[] = 'Đã hủy';
            break;
         default:
            $order_status_labels[] = 'Không xác định';
      }
      $order_status_counts[] = $status['count'];
   }
   // Lấy dữ liệu doanh thu trong 6 tháng gần nhất
   $select_revenue = $conn->prepare("SELECT DATE_FORMAT(placed_on, '%Y-%m') as month, SUM(total_price) as revenue 
                                  FROM orders 
                                  WHERE DATE(placed_on) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                  GROUP BY month");
   $select_revenue->execute();
   $revenue_data = $select_revenue->fetchAll(PDO::FETCH_ASSOC);

   $revenue_months = [];
   $revenue_values = [];

   foreach ($revenue_data as $revenue) {
      $revenue_months[] = $revenue['month'];
      $revenue_values[] = $revenue['revenue'];
   }


   ?>
   <?php
   $select_revenue_7days = $conn->prepare("SELECT DATE_FORMAT(placed_on, '%Y-%m-%d') as day, SUM(total_price) as revenue 
                                  FROM orders 
                                  WHERE DATE(placed_on) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                  GROUP BY day");
   $select_revenue_7days->execute();
   $revenue_data_7days = $select_revenue_7days->fetchAll(PDO::FETCH_ASSOC);

   $revenue_days_7days = [];
   $revenue_values_7days = [];

   foreach ($revenue_data_7days as $revenue_7days) {
      $revenue_days_7days[] = $revenue_7days['day'];
      $revenue_values_7days[] = $revenue_7days['revenue'];
   }
   ?>
   <?php
   $select_revenue_3months = $conn->prepare("SELECT DATE_FORMAT(placed_on, '%Y-%m') as month, SUM(total_price) as revenue 
                                  FROM orders 
                                  WHERE DATE(placed_on) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                                  GROUP BY month");
   $select_revenue_3months->execute();
   $revenue_data_3months = $select_revenue_3months->fetchAll(PDO::FETCH_ASSOC);

   $revenue_months_3months = [];
   $revenue_values_3months = [];

   foreach ($revenue_data_3months as $revenue_3months) {
      $revenue_months_3months[] = $revenue_3months['month'];
      $revenue_values_3months[] = $revenue_3months['revenue'];
   }
   ?>
   <?php
   $select_reviews = $conn->prepare("SELECT rating, COUNT(*) AS count FROM product_reviews GROUP BY rating");
   $select_reviews->execute();
   $review_data = $select_reviews->fetchAll(PDO::FETCH_ASSOC);

   $review_ratings = [];
   $review_counts = [];

   foreach ($review_data as $review) {
      $review_ratings[] = $review['rating'];
      $review_counts[] = $review['count'];
   }
   ?>




   <style>
      .canvas-container {
         width: 300px;
         /* Chiều rộng mong muốn */
         height: 200px;
         /* Chiều cao mong muốn */
      }


      #myChart_revenue {
         width: 100%;
         /* Chiều rộng tối đa là 100% của phần tử cha */
         max-width: 300px;
         /* Chiều rộng tối đa không vượt quá 300px */
         height: 50px;
         /* Chiều cao giảm xuống 150px */
      }
   </style>

</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>

   <!-- admin dashboard section starts  -->

   <section class="dashboard">

      <h1 class="heading">dashboard</h1>

      <div class="box-container">

         <div class="box">
            <h3>welcome!</h3>
            <p><?= $fetch_profile['name']; ?></p>
            <!-- <a href="update_profile.php" class="btnn">Cập nhật thông tin</a> -->
         </div>

         <div class="box">
            <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['wait']);
            while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
               $total_pendings += $fetch_pendings['total_price'];
            }
            ?>
            <h3><span></span><?= number_format($total_pendings); ?><span> VNĐ</span></h3>
            <p>Đơn hàng chờ</p>
            <!-- <a href="placed_orders.php" class="btnn">Xem</a> -->
         </div>

         <div class="box">
            <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
               $total_completes += $fetch_completes['total_price'];
            }
            ?>
            <h3><span></span><?= number_format($total_completes); ?><span> VNĐ</span></h3>
            <p>Đơn xác nhận</p>
            <!-- <a href="placed_orders.php" class="btnn">Xem</a> -->
         </div>

         <div class="box">
            <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['finish']);
            while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
               $total_completes += $fetch_completes['total_price'];
            }
            ?>
            <h3><span></span><?= number_format($total_completes); ?><span> VNĐ</span></h3>
            <p>Đơn hoàn thành</p>
            <!-- <a href="placed_orders.php" class="btnn">Xem</a> -->
         </div>

         <div class="box">
            <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['cancel']);
            while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
               $total_completes += $fetch_completes['cancel'];
            }
            ?>
            <h3><span></span><?= number_format($total_completes); ?><span> VNĐ</span></h3>
            <p>Đơn đã hủy</p>
            <!-- <a href="placed_orders.php" class="btnn">Xem</a> -->
         </div>

         <div class="box">
            <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $numbers_of_orders = $select_orders->rowCount();
            ?>
            <h3><?= $numbers_of_orders; ?></h3>
            <p>Tổng đơn</p>
            <!-- <a href="placed_orders.php" class="btnn">Đơn hàng</a> -->
         </div>

         <div class="box">
            <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $numbers_of_products = $select_products->rowCount();
            ?>
            <h3><?= $numbers_of_products; ?></h3>
            <p>Số lượng sản phẩm</p>
            <!-- <a href="products.php" class="btnn">Sản phẩm</a> -->
         </div>

         <div class="box">
            <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $numbers_of_users = $select_users->rowCount();
            ?>
            <h3><?= $numbers_of_users; ?></h3>
            <p>Tài khoản người dùng</p>
            <!-- <a href="users_accounts.php" class="btnn">Người dùng</a> -->
         </div>

         <div class="box">
            <?php
            $select_admins = $conn->prepare("SELECT * FROM `admin`");
            $select_admins->execute();
            $numbers_of_admins = $select_admins->rowCount();
            ?>
            <h3><?= $numbers_of_admins; ?></h3>
            <p>Tài khoản quản lý</p>
            <!-- <a href="admin_accounts.php" class="btnn">Người quản trị</a> -->
         </div>

         <div class="box">
            <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $numbers_of_messages = $select_messages->rowCount();
            ?>
            <h3><?= $numbers_of_messages; ?></h3>
            <p>Tin nhắn mới</p>
            <!-- <a href="messages.php" class="btnn">Tin nhắn</a> -->
         </div>

         <div class="box">

            <canvas id="myChart_category" style="width:100%;max-width:300px"></canvas>
            <a>Biểu đồ sản phẩm</a>
         </div>
         <div class="box">
            <canvas id="myChart_order_status" style="width:100%;max-width:300px"></canvas>
            <a>Biểu đồ Trang thái đơn hàng trong ngày</a>
         </div>
         <div class="box">
            <canvas id="myChart_revenue_7days" style="width:100%;max-width:300px"></canvas>
            <a>Biểu đồ Doanh thu 7 ngày gần nhất</a>
         </div>

         <div class="box">
            <canvas id="myChart_revenue_3months" style="width:100%;max-width:300px"></canvas>
            <a>Biểu đồ Doanh thu 3 tháng gần nhất</a>
         </div>
         <div class="box">
            <canvas id="myChart_reviews" style="width:100%;max-width:300px"></canvas>
            <a>Biểu đồ Đánh giá của người dùng với sản phẩm</a>
         </div>





      </div>
   </section>

   <script>
      // JavaScript code for order status pie chart
      var orderStatusValues = <?php echo json_encode($order_status_labels); ?>;
      var orderStatusCounts = <?php echo json_encode($order_status_counts); ?>;
      var orderStatusColors = [
         "#FF5733",
         "#36A2EB",
         "#33FF57",
         "#9B59B6",
         "#FFC300"
      ];

      new Chart("myChart_order_status", {
         type: "pie",
         data: {
            labels: orderStatusValues,
            datasets: [{
               backgroundColor: orderStatusColors,
               data: orderStatusCounts
            }]
         },
         options: {
            title: {
               display: true,
               text: "Biểu đồ Trạng thái đơn hàng trong ngày"
            }
         }
      });

      var xValues = <?php echo json_encode($category_labels); ?>; // Sử dụng danh mục sản phẩm
      var yValues = <?php echo json_encode($category_counts); ?>; // Số lượng sản phẩm cho từng danh mục
      var barColors = [
         "#b91d47",
         "#00aba9",
         "#2b5797",
         "#e8c3b9",
         "#1e7145"
      ];

      new Chart("myChart_category", {
         type: "doughnut",
         data: {
            labels: xValues,
            datasets: [{
               backgroundColor: barColors,
               data: yValues
            }]
         },
         options: {
            title: {
               display: true,
               text: "Biểu đồ Số lượng sản phẩm theo danh mục"
            }
         }
      });

      // Thêm mã JavaScript cho biểu đồ tròn doanh thu 7 ngày gần nhất
      var revenueValues7Days = <?php echo json_encode($revenue_values_7days); ?>;
      var revenueLabels7Days = <?php echo json_encode($revenue_days_7days); ?>;

      new Chart("myChart_revenue_7days", {
         type: "doughnut",
         data: {
            labels: revenueLabels7Days,
            datasets: [{
               backgroundColor: barColors, // Sử dụng màu từ biểu đồ sản phẩm
               data: revenueValues7Days
            }]
         },
         options: {
            title: {
               display: true,
               text: "Doanh thu 7 ngày gần nhất"
            }
         }
      });

      // Thêm mã JavaScript cho biểu đồ tròn doanh thu 3 tháng gần nhất
      var revenueValues3Months = <?php echo json_encode($revenue_values_3months); ?>;
      var revenueLabels3Months = <?php echo json_encode($revenue_months_3months); ?>;

      new Chart("myChart_revenue_3months", {
         type: "doughnut",
         data: {
            labels: revenueLabels3Months,
            datasets: [{
               backgroundColor: barColors, // Sử dụng màu từ biểu đồ sản phẩm
               data: revenueValues3Months
            }]
         },
         options: {
            title: {
               display: true,
               text: "Doanh thu 3 tháng gần nhất"
            }
         }
      });

      // Ánh xạ giá trị rating vào số sao tương ứng
      var starLabels = ["1 sao", "2 sao", "3 sao", "4 sao", "5 sao"];
      var reviewValues = <?php echo json_encode($review_counts); ?>;
      var reviewLabels = <?php echo json_encode($review_ratings); ?>;

      // Tạo mảng chứa số sao tương ứng với rating
      var mappedLabels = reviewLabels.map(function(label) {
         return starLabels[label - 1];
      });

      new Chart("myChart_reviews", {
         type: "doughnut",
         data: {
            labels: mappedLabels,
            datasets: [{
               backgroundColor: barColors,
               data: reviewValues
            }]
         },
         options: {
            title: {
               display: true,
               text: "Biểu đồ Đánh giá của người dùng với sản phẩm"
            }
         }
      });
   </script>


   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>