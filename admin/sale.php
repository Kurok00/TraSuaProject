<?php
include '../configs/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
};

$message = array(); // Khởi tạo mảng để lưu thông báo lỗi



if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_slider = $conn->prepare("DELETE FROM `sale` WHERE id = ?");
    if ($delete_slider->execute([$delete_id])) {
        echo 'Xóa bản ghi thành công!';
    } else {
        echo 'Lỗi khi xóa bản ghi: ' . $delete_slider->errorInfo()[2];
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
    <title>Hình ảnh</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../templates/css/admin_style.css">

</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>
    <!-- add slider section starts  -->



    <section class="show-products" style="padding-top: 0;">
        <h3>Danh sách chương trình giảm giá</h3>
        <table class="table_slider">
            <thead>
                <tr>
                    <th>Stt</th>
                    <th>Tên chương trình</th>
                    <th>Danh sách sản phẩm</th>
                    <th>Phần % giảm giá</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $show_sale = $conn->prepare("SELECT * FROM `sale`");
                $show_sale->execute();
                if ($show_sale->rowCount() > 0) {
                    while ($fetch_sale = $show_sale->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <tr>
                            <td><?= $fetch_sale['id']; ?></td>
                            <td><?= $fetch_sale['name_sale']; ?></td>
                            <td><?= $fetch_sale['products']; ?></td>
                            <td><?= $fetch_sale['discount_percentage']; ?></td>
                            <td><?= $fetch_sale['start_date']; ?></td>
                            <td><?= $fetch_sale['end_date']; ?></td>

                            <td>

                                <a href="sale.php?delete=<?= $fetch_sale['id']; ?>" class="delete-bt" onclick="return confirm('Bạn có chắc chắn muốn xóa chương trình này không?')">Xóa</a>/

                                <a href="sale_update.php?edit=<?= $fetch_sale['id']; ?>" name="edit_id" value="<?= $fetch_sale['id'] ?>">edit</a>

                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="5">Không chương trình giảm giá nào</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </section>





    <script src="../templates/js/admin_script.js"></script>

</body>

</html>