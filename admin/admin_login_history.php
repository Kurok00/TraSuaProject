<?php
include '../configs/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit; // Dừng việc thực hiện mã ngay tại đây sau khi chuyển hướng
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
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
    <h1 class="heading">Lịch sử đăng nhập ADMIN</h1>
    <div class="search-bar">
        <form action="" method="GET"> <!-- Thêm biểu mẫu tại đây -->
            <input type="text" name="searchInput" placeholder="Tìm kiếm" class="box">
            <select name="searchColumn" class="box">
                <option value="admin_id">Tìm ID</option>
                <option value="ip_address">Tìm theo IP</option>
            </select>
            <button id="searchButton" class="btn_share" type="submit">Tìm</button>
        </form>
    </div>
    <!-- show products section starts  -->
    <?php
    if (isset($_GET['searchInput']) && isset($_GET['searchColumn'])) {
        $searchInput = $_GET['searchInput'];
        $searchColumn = $_GET['searchColumn'];
        // Thực hiện truy vấn SQL để tìm kiếm dữ liệu
        $query = "SELECT admin_login_history.id, admin_login_history.admin_id, admin.name, admin_login_history.login_time, admin_login_history.ip_address
                  FROM admin_login_history
                  LEFT JOIN admin ON admin_login_history.admin_id = admin.id
                  WHERE $searchColumn LIKE :searchInput";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':searchInput', "%$searchInput%");
        $stmt->execute();
        // Lấy kết quả tìm kiếm
        $login_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Nếu không có tìm kiếm, thực hiện truy vấn để lấy toàn bộ lịch sử đăng nhập
        $query = "SELECT admin_login_history.id, admin_login_history.admin_id, admin.name, admin_login_history.login_time, admin_login_history.ip_address
                  FROM admin_login_history
                  LEFT JOIN admin ON admin_login_history.admin_id = admin.id";
        $loginHistoryQuery = $conn->query($query);
        $login_history = $loginHistoryQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>
    <section class="show-products" style="padding-top: 0;">
        <?php
        if (count($login_history) > 0) {
        ?>
            <table class="table-category">
                <tr>
                    <th>ID</th>
                    <th>Admin ID</th>
                    <th>Tên</th>
                    <th>Thời gian đăng nhập</th>
                    <th>Địa chỉ IP</th>
                </tr>
                <?php
                foreach ($login_history as $history) {
                ?>
                    <tr>
                        <td><?= $history['id']; ?></td>
                        <td><?= $history['admin_id']; ?></td>
                        <td><?= $history['name']; ?></td>
                        <td><?= $history['login_time']; ?></td>
                        <td><?= $history['ip_address']; ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
        <?php
        } else {
            echo '<p class="empty">Không tìm thấy kết quả.</p>';
        }
        ?>
    </section>
    <!-- custom js file link  -->
    <script src="../templates/js/admin_script.js"></script>
</body>

</html>
