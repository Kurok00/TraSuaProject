<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="templates/css/style.css">
    <link rel="stylesheet" href="templates/css/test.css">

</head>

<body>

    <!-- header section starts  -->
    <?php include 'configs/user_header.php'; ?>
    <!-- header section ends -->

    <div class="heading">
        <h3>Đơn hàng</h3>
        <p><a href="html.php">Trang chủ</a> <span> / Đơn hàng</span></p>
    </div>

    <section class="orders">

        <h1 class="title">Đơn hàng của bạn</h1>
        <div class="nav-links">
            <a href="orders_all.php">Tất cả </a>-
            <a href="orders_wait.php"> Chờ xác nhận </a>-
            <a href="orders_completed.php"> Đã xác nhận </a>-
            <a href="orders_sent.php"> Đơn đang vận chuyển </a>-
            <a href="orders_finish.php"> Đơn hoàn thành </a>-
            <a href="orders_cancel.php"> Đơn đã hủy </a>
        </div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Ngày đặt</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái đơn hàng</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($user_id == '') {
                    echo '<tr><td colspan="6"><p class="empty">Please login to see your orders</p></td></tr>';
                } else {
                    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND payment_status='wait' ORDER BY placed_on DESC");
                    $select_orders->execute([$user_id]);
                    if ($select_orders->rowCount() > 0) {
                        while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                ?>
                            <tr>
                                <td><?= $fetch_orders['id']; ?></td>
                                <td><?= $fetch_orders['placed_on']; ?></td>
                                <td><?= $fetch_orders['total_products']; ?></td>
                                <td><?= number_format( $fetch_orders['total_price']); ?> đ</td>
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
                                        default:
                                            echo 'Không xác định';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="order_details.php?id_order=<?= $fetch_orders['id']; ?>" class="delete-bt">Chi tiết đơn hàng</a>
                                </td>
                            </tr>
                <?php
                        }
                    } else {
                        echo '<tr><td colspan="6"><p class="empty">Không tìm thấy đơn hàng nào!</p></td></tr>';
                    }
                }
                ?>
            </tbody>
        </table>


    </section>




    <!-- footer section starts  -->
    <?php include 'configs/user_footer.php'; ?>
    <!-- footer section ends -->






    <!-- custom js file link  -->
    <script src="templates/js/script.js"></script>

</body>

</html>