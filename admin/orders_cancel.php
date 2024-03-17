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
    $message[] = 'payment status updated!';
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

</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>
    <!-- placed orders section starts  -->

    <section class="show-products" style="padding-top: 0;">
        <h1 class="heading">Đơn hàng</h1>

        <table class="table-category">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ngày đặt</th>
                    <th>Tên</th>
                    <th>Tổng tiền ($)</th>
                    <th>Hình thức thanh toán</th>
                    <th>Trạng thái đơn hàng</th>
                    <th>Chi tiết đơn hàng</th>
                    <th>Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'cancel' ORDER BY placed_on DESC");

                $select_orders->execute();
                if ($select_orders->rowCount() > 0) {
                    while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {

                ?>
                        <tr>
                            <td><?= $fetch_orders['id']; ?></td>
                            <td><?= $fetch_orders['placed_on']; ?></td>
                            <td><?= $fetch_orders['name']; ?></td>
                            <td><?= $fetch_orders['total_price']; ?> $</td>
                            <td><?= $fetch_orders['method']; ?></td>
                            <td>
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
                                    case 'finish':
                                        echo 'Giao hàng thành công';
                                        break;
                                    case 'cancel':
                                        echo 'Đơn hàng đã hủy';
                                        break;
                                    default:
                                        echo 'Không xác định';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="pllaced_orders.php?placed=<?= $fetch_orders['id']; ?>" class="placed" onclick="return confirm('Bạn muốn xem chi tiết đơn hàng này');">Chi tiết </a>
                            </td>
                            <td>
                                <div class="flex-btn">
                                    <a href="orders_cancel.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Xóa đơn hàng ?');">Xóa</a>
                                </div>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="11" class="empty"> Không tìm thấy đơn hàng </td></tr>';
                }
                ?>
            </tbody>
        </table>
    </section>


    <!-- placed orders section ends -->









    <!-- custom js file link  -->
    <script src="../templates/js/admin_script.js"></script>

</body>

</html>