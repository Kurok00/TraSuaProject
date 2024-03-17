<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];


$id_edit = $_GET['id_edit'];
if ($id_edit) {
   // Thực hiện truy vấn cơ sở dữ liệu để lấy thông tin của admin
   $query = $conn->prepare("SELECT name, role, status FROM admin WHERE id = ?");
   $query->execute([$id_edit]);
   $adminInfo = $query->fetch();

   if ($adminInfo) {
      $name = $adminInfo['name'];
      $role = $adminInfo['role'];
      $status = $adminInfo['status'];
   }
}


if (isset($_POST['submit'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $role = $_POST['box_role'];
   $status = $_POST['box_status'];
   // Kiểm tra xem tên đăng nhập đã tồn tại chưa
   $checkNameQuery = $conn->prepare("SELECT id FROM `admin` WHERE name = ?");
   $checkNameQuery->execute([$name]);

   if ($checkNameQuery->rowCount() > 0) {
      // Tên đăng nhập đã tồn tại, hiển thị thông báo và không thêm vào cơ sở dữ liệu
      $message[] = 'Tên đăng nhập đã tồn tại. Vui lòng chọn một tên đăng nhập khác.';
   } else {
      // Kiểm tra xem mật khẩu có được nhập hay không
      if (!empty($_POST['pass'])) {
         $pass = sha1($_POST['pass']);
         $pass = filter_var($pass, FILTER_SANITIZE_STRING);

         $update_profile = $conn->prepare("UPDATE `admin` SET name = ?, role = ?, status = ?, password = ? WHERE id = ?");
         $update_profile->execute([$name, $role, $status, $pass, $id_edit]);
      } else {
         // Nếu không có mật khẩu được nhập, không cập nhật mật khẩu
         $update_profile = $conn->prepare("UPDATE `admin` SET name = ?, role = ?, status = ? WHERE id = ?");
         $update_profile->execute([$name, $role, $status, $id_edit]);
      }

      $message[] = 'cập nhật thành công!';

      header('location:admin_accounts.php');
   }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
   <title>Cập nhật quản lý</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../templates/css/admin_style.css">


</head>

<body>

   <?php include '../configs/admin_header.php' ?>
   <?php include '../configs/slider.php' ?>
   <!-- admin profile update section starts  -->

   <section class="form-container">

      <form action="" method="POST">
         <h3>Chỉnh sửa thông tin</h3>
         <input type="text" name="name" maxlength="50" placeholder="Nhập tên của bạn" class="box" value="<?= $name ?>">
         <label for="role">Chức vụ :</label>
         <select name="box_role" class="drop-down">
            <option value="3" <?= $role == 3 ? "selected" : "" ?>>Nhân viên</option>
            <option value="2" <?= $role == 2 ? "selected" : "" ?>>Quản lý</option>
            <option value="1" <?= $role == 1 ? "selected" : "" ?>>Admin</option>
         </select>
         <label for="status">Trang thái :</label>
         <select name="box_status" class="drop-down">
            <option value="active" <?= $status == 'active' ? "selected" : "" ?>>Hoạt động</option>
            <option value="block" <?= $status == 'block' ? "selected" : "" ?>>Khóa tài khoản</option>
         </select>

         <input type="password" name="pass" maxlength="20" placeholder="Mật khẩu 8 ký tự" class="box" pattern=".{8,}" >
         <span id="password-error" class="error-message"></span>
         <input type="submit" value="Chỉnh sửa" name="submit" class="btn">
      </form>

   </section>
   <!-- custom js file link  -->
   <script src="../templates/js/admin_script.js"></script>

</body>

</html>