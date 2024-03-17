<?php
include '../configs/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
};
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../templates/uploaded_img/category_img/' . $image;

    $select_category = $conn->prepare("SELECT * FROM `category` WHERE category_name = ?");
    $select_category->execute([$name]);

    if ($select_category->rowCount() > 0) {
        $message[] = 'category name already exists!';
    } else {
        if ($image_size > 2000000) {
            $message[] = 'image size is too large';
        } else {
            move_uploaded_file($image_tmp_name, $image_folder);

            $insert_category = $conn->prepare("INSERT INTO `category`(category_name, image) VALUES(?,?)");
            $insert_category->execute([$name, $image]);

            $message[] = 'Đã thêm danh mục mới ';
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

    <section class="add-category">

        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Thêm danh mục</h3>
            <input type="text" required placeholder="Tên danh mục" name="name" maxlength="100" class="box">
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            <input type="submit" value="Thêm danh mục" name="add_category" class="btn">
        </form>

    </section>



    <script src="../templates/js/admin_script.js"></script>

</body>

</html>