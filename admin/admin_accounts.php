<?php
include '../configs/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['update'])) {
   $update_id = $_GET['update'];
   $user = $conn->prepare("SELECT status FROM `admin` WHERE id = ?");
   $user->execute([$update_id]);
   $status = $user->fetchColumn(); // Lấy trạng thái hiện tại

   if ($status == 'active') {
      $newStatus = 'block'; // Đổi từ 'active' sang 'block'
   } else {
      $newStatus = 'active'; // Đổi từ 'block' sang 'active'
   }
   $updateStatus = $conn->prepare("UPDATE `admin` SET status = ? WHERE id = ?");
   $updateStatus->execute([$newStatus, $update_id]);
   header('location:admin_accounts.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Tài khoản quản lý</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">

</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>

   <?php
   // Query to select all rows from the admin table
   $sql = "SELECT * FROM `admin`";

   try {
      $stmt = $conn->query($sql);
      $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
   } catch (PDOException $e) {
      echo "Query failed: " . $e->getMessage();
   }
   ?>

   <h1 class="heading">Admin Table</h1>
   <table class="admin-table">
      <tr>
         <th>ID</th>
         <th>Tên</th>
         <th>Chức vụ</th>
         <th>Trạng thái</th>
         <th>Thao tác</th>
      </tr>
      <?php foreach ($admins as $admin) : ?>
         <?php
                $status = $admin['status'];

                // Check the role
                switch ($status) {
                    case "block":
                        $sta = "Tài khoản bị khóa";
                        break;
                    default:
                        $sta = "Hoạt động";
                }


         // Get the role of the admin
         $role = $admin['role'];

         // Check the role
         switch ($role) {
            case  "1"; {
                  $role = "Admin";
               }
               break;
            case  "2"; {
                  $role = "Quản lý";
               }
               break;
            case  "3"; {
                  $role = "Nhân viên";
               }
               break;
            default: {
                  $role = "Tài khoản không xác định";
               }
         }

         ?>
         <tr>
            <td><?= $admin['id'] ?></td>
            <td><?= $admin['name'] ?></td>
            <td><?= $role ?></td>
            <td><a href="admin_accounts.php?update=<?= $admin['id']; ?>" class="update-btn" onclick="return confirm('Bạn muốn thay đổi trạng thái tài khoản này ');"><?= $sta ?></a></td>
            <td> <a href="update_admin.php?id_edit=<?= $admin['id'] ?>">Chỉnh sửa</a>
               <input type="hidden" name="id_edit" value="<?= $admin['id'] ?>">
               <form action="" method="post">
                  <input type="hidden" name="id_delete" value="<?= $admin['id'] ?>">
                  <input type="submit" name="delete" value="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
               </form>
            </td>
         </tr>
      <?php endforeach; ?>
   </table>


   <?php
   if (isset($_POST['delete'])) {
      $id_delete = $_POST['id_delete'];

      // Query to delete the admin from the table
      $sql = "DELETE FROM `admin` WHERE id = ?";

      try {
         $stmt = $conn->prepare($sql);
         $stmt->execute([$id_delete]);
         echo '<script>window.location.href = "admin_accounts.php";</script>';
      } catch (PDOException $e) {
         echo "Query failed: " . $e->getMessage();
      }
   }
   ?>
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>