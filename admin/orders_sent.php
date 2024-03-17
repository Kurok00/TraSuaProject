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
    $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ?, order_status = 'paid' WHERE id = ?");
    $update_status->execute([$payment_status, $order_id]);
    $message[] = 'Trạng thái đã cập nhật!';
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_order->execute([$delete_id]);
    header('location:orders_sent.php');
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
                    <th>Tổng tiền(VNĐ)</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái đơn hàng</th>
                    <th>Chi tiết đơn hàng</th>
                    <th>Tùy chọn</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'sent' ORDER BY placed_on DESC");
                $select_orders->execute();
                if ($select_orders->rowCount() > 0) {
                    while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <tr>
                            <td><?= $fetch_orders['id']; ?></td>
                            <td><?= $fetch_orders['placed_on']; ?></td>
                            <td><?= $fetch_orders['name']; ?></td>
                            <td><?= $fetch_orders['total_price']; ?> VND</td>
                            <td><?= $fetch_orders['total_products']; ?></td>
                            <td>
                                <form action="" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                                    <select name="payment_status" class="drop-down">
                                        <option value="wait" <?= ($fetch_orders['payment_status'] === 'wait') ? 'selected' : '' ?>>Chờ xác nhận</option>
                                        <option value="completed" <?= ($fetch_orders['payment_status'] === 'completed') ? 'selected' : '' ?>>Đang chuẩn bị đơn</option>
                                        <option value="sent" <?= ($fetch_orders['payment_status'] === 'sent') ? 'selected' : '' ?>>Đã gửi</option>
                                        <option value="finish" <?= ($fetch_orders['payment_status'] === 'finish') ? 'selected' : '' ?>>Giao hàng thành công</option>
                                    </select>
                                    <div class="flex-btn">
                                        <button type="submit" name="update_payment" class="btn">Cập nhật</button>
                                    </div>
                                </form>
                            </td>

                            <td>
                                <a href="placed_orders.php?placed=<?= $fetch_orders['id']; ?>" class="placed" onclick="return confirm('Bạn muốn xem chi tiết đơn hàng này');">Chi tiết </a>
                            </td>
                            <td>
                                <div class="flex-btn">
                                    <a href="orders_sent.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">Xóa</a>
                                </div>
                                <!-- <div class="flex-btn">
                                    <a href="placed_orders_cancel.php?cancel=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Bạn muốn hủy đơn hàng');">Hủy</a>
                                </div> -->
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="11" class="empty">Không tìm thấy đơn hàng</td></tr>';
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