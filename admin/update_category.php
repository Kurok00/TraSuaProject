<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

if (isset($_POST['update'])) {

    $pid = $_POST['pid'];
    $name = $_POST['name'];
    //$name = filter_var($name, FILTER_SANITIZE_STRING);
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    $update_product = $conn->prepare("UPDATE `category` SET category_name = ? WHERE id = ?");
    $update_product->execute([$name, $pid]);

    $message[] = 'Cập nhật thành công';

    $old_image = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    //$image = filter_var($image, FILTER_SANITIZE_STRING);
    $image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../templates/uploaded_img/category_img/' . $image;

    if (!empty($image)) {
        if ($image_size > 2000000) {
            $message[] = 'images size is too large!';
        } else {
            $update_image = $conn->prepare("UPDATE `category` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $pid]);
            move_uploaded_file($image_tmp_name, $image_folder);
            if (file_exists('../templates/uploaded_img/category_img/' . $old_image)) {
                unlink('../templates/uploaded_img/category_img/' . $old_image);
            }
            $message[] = 'image updated!';
        }
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
    <title>Cập nhật danh mục</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../templatescss/admin_style.css">

</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>
    <!-- update product section starts  -->

    <section class="update-product">
        <h1 class="heading">Chỉnh sửa danh mục</h1>
        <?php
        $update_id = $_GET['update'];
        $show_products = $conn->prepare("SELECT * FROM `category` WHERE id = ?");
        $show_products->execute([$update_id]);
        if ($show_products->rowCount() > 0) {
            while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
        ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                    <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
                    <img src="../templates/uploaded_img/category_img/<?= $fetch_products['image']; ?>" alt="">
                    <span>Cập nhật tên</span>
                    <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box" value="<?= $fetch_products['category_name']; ?>">
                    <span>Cập nhật hình ảnh</span>
                    <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
                    <div class="flex-btn">
                        <input type="submit" value="update" class="btn" name="update">
                        <a href="category.php" class="option-btn">trở lại</a>
                    </div>
                </form>
        <?php
            }
        } else {
            echo '<p class="empty">no products added yet!</p>';
        }
        ?>

    </section>

    <script src="../templates/js/admin_script.js"></script>

</body>

</html>