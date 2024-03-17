<?php
ob_start();
include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM `users` WHERE user_id = ?");
   $delete_users->execute([$delete_id]);
   header('location:users_accounts.php');
}

if (isset($_GET['update'])) {
   $update_id = $_GET['update'];
   $user = $conn->prepare("SELECT status FROM `users` WHERE user_id = ?");
   $user->execute([$update_id]);
   $status = $user->fetchColumn(); // Lấy trạng thái hiện tại

   if ($status == 'active') {
      $newStatus = 'block'; // Đổi từ 'active' sang 'block'
   } else {
      $newStatus = 'active'; // Đổi từ 'block' sang 'active'
   }
   $updateStatus = $conn->prepare("UPDATE `users` SET status = ? WHERE user_id = ?");
   $updateStatus->execute([$newStatus, $update_id]);
   header('location:users_accounts.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>tài khoản người dùng</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">
</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>
   <!-- user accounts section starts  -->

   <section class="accounts">
   <h1 class="heading">Tài khoản người dùng</h1>
   <div class="search-bar">
      <input type="text" placeholder="nhập từ khóa tìm kiếm" id="searchInput" class="box">
      <button id="searchButton" class="btn" class="box">Tìm</button>
   </div>

   <?php
   // Tìm kiếm
   if (isset($_GET['search'])) {
      $search_query = $_GET['search'];
      $sql = "SELECT * FROM `users` WHERE name LIKE :search_query OR email LIKE :search_query OR number LIKE :search_query OR address LIKE :search_query";
      try {
         $stmt = $conn->prepare($sql);
         $search_query = "%$search_query"; // Thêm dấu % để tìm kiếm theo từ khóa bất kỳ
         $stmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
         $stmt->execute();
         $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
         echo "Query failed: " . $e->getMessage();
      }
   ?>
      <?php include 'table_search.php' ?>
   <?php
   } else { // Nếu không có tìm kiếm, thực hiện truy vấn hiển thị tất cả
      $sql = "SELECT * FROM `users`";
      try {
         $stmt = $conn->query($sql);
         $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $usersPerPage = 10; // Số dòng trên mỗi trang
         // Trang hiện tại (mặc định là trang 1)
         $currentPage = 1;
         if (isset($_GET['page'])) {
            $currentPage = $_GET['page'];
         }
         // Câu truy vấn SQL
         $sql = "SELECT * FROM `users`";
         $stmt = $conn->prepare($sql);
         $stmt->execute();
         $totalUsers = $stmt->rowCount(); // Đếm tổng số dòng trong cơ sở dữ liệu
         $totalPages = ceil($totalUsers / $usersPerPage); // Tính tổng số trang

         // Câu truy vấn SQL với OFFSET và LIMIT
         $offset = ($currentPage - 1) * $usersPerPage;
         $sql = "SELECT * FROM `users` LIMIT :limit OFFSET :offset";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(':limit', $usersPerPage, PDO::PARAM_INT);
         $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
         $stmt->execute();
         $usersOnCurrentPage = $stmt->fetchAll(PDO::FETCH_ASSOC); // Sử dụng biến khác ở đây
      } catch (PDOException $e) {
         echo "Query failed: " . $e->getMessage();
      }
   

   ?>
      <?php include 'tablee.php' ?>
      <?php
      // Sử dụng hàm phân trang
      echo '<div class="pagination">';
      generatePagination($currentPage, $totalPages, 2);
      echo '</div>';
   }
      ?>
   <?php
   function generatePagination($currentPage, $totalPages, $pagesToShow)
   {
      // Hiển thị trang trước nếu không ở trang đầu tiên
      for ($i = $currentPage - $pagesToShow; $i < $currentPage; $i++) {
         if ($i > 0) {
            echo '<a href="users_accounts.php?page=' . $i . '" class="pagination-link">' . $i . '</a>';
         }
      }

      // Hiển thị trang hiện tại
      echo '<a href="users_accounts.php?page=' . $currentPage . '" class="pagination-link current-link">' . $currentPage . '</a>';

      // Hiển thị trang sau nếu chưa đạt trang cuối cùng
      for ($i = $currentPage + 1; $i <= $currentPage + $pagesToShow; $i++) {
         if ($i <= $totalPages) {
            echo '<a href="users_accounts.php?page=' . $i . '" class="pagination-link">' . $i . '</a>';
         }
      }
   }
   ?>
</section>
   <script>
      document.getElementById("searchButton").addEventListener("click", function(event) {
         event.preventDefault(); // Prevent the default form submission
         var searchQuery = document.getElementById("searchInput").value;
         window.location.href = "users_accounts.php?search=" + searchQuery;
      });
   </script>
   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>