<?php
include '../configs/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
};
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_category_image = $conn->prepare("SELECT * FROM `category` WHERE id = ?");
    $delete_category_image->execute([$delete_id]);
    $fetch_delete_image = $delete_category_image->fetch(PDO::FETCH_ASSOC);
    unlink('../templates/uploaded_img/category_img/' . $fetch_delete_image['image']);
    $delete_category = $conn->prepare("DELETE FROM `category` WHERE id = ?");
    $delete_category->execute([$delete_id]);
    //    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    //    $delete_cart->execute([$delete_id]);
    header('location:category.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
    <title>Danh mục</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>

    <!-- add category section starts  -->


    <section class="show-products" style="padding-top: 0;">
    <h3>Danh mục</h3>
    <table class="table-category">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Hình ảnh</th>
                <th>Tùy chọn</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $show_products = $conn->prepare("SELECT * FROM `category`");
            $show_products->execute();
            if ($show_products->rowCount() > 0) {
                while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . $fetch_products['id'] . '</td>';
                    echo '<td>' . $fetch_products['category_name'] . '</td>';
                    echo '<td><img src="../templates/uploaded_img/category_img/' . $fetch_products['image'] . '" alt="Hình ảnh"></td>';
                    echo '<td>
                            <a href="update_category.php?update=' . $fetch_products['id'] . '" class="option-bt">Chỉnh sửa</a>/
                            <a href="category.php?delete=' . $fetch_products['id'] . '" class="delete-bt" onclick="return confirm(\'Xóa danh mục này?\');">Xóa danh mục</a>
                        </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" class="empty">Chưa có danh mục nào được thêm!</td></tr>';
            }
            ?>
        </tbody>
    </table>
</section>


    <script src="../templates/js/admin_script.js"></script>

</body>

</html>