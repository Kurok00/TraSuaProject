<?php

include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_order->execute([$delete_id]);
    header('location:placed_orders.php');
}

// Add code to handle order cancellation with a reason
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $cancel_reason = $_POST['cancel_reason'];
    $cancel_info = $cancel_reason . ' (' . date('Y-m-d H:i:s') . ')';
    // Update the order status or perform any other cancellation logic
    // For example, you can add a new column in your 'orders' table for the cancellation reason
    $update_status = $conn->prepare("UPDATE `orders` SET payment_status = 'cancel', note = ? WHERE id = ?");
    $update_status->execute([$cancel_info, $order_id]);

    $message[] = 'Đơn hàng đã được hủy!';
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
    <style>
        /* Style for the cancellation form */
        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            height: 100px;
            resize: vertical;
            /* Allow vertical resizing of the textarea */
            margin-bottom: 15px;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Optional: Add some styling to improve the overall appearance */
        .box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        /* Adjustments for better mobile responsiveness */
        @media only screen and (max-width: 600px) {
            textarea {
                height: 150px;
            }
        }
    </style>
</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>
    <!-- placed orders section starts  -->

    <section class="placed-orders">

        <h1 class="heading">Đơn hàng</h1>

        <div class="box-container">
            <?php
            if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
                $placed = $_GET['cancel'];

                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE id = :cancel");
                $select_orders->bindParam(':cancel', $placed, PDO::PARAM_INT);
                $select_orders->execute();

                if ($select_orders->rowCount() > 0) {
                    $fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC);
            ?>
                    <div class="box">
                        <p> Mã người dùng : <span><?= $fetch_orders['user_id']; ?></span> </p>
                        <p> Ngày đặt : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                        <p> Tên : <span><?= $fetch_orders['name']; ?></span> </p>
                        <p> Gmail : <span><?= $fetch_orders['email']; ?></span> </p>
                        <p> Số đt : <span><?= $fetch_orders['number']; ?></span> </p>
                        <p> Địa chỉ : <span><?= $fetch_orders['address']; ?></span> </p>
                        <p> Sản phẩm : <span><?= $fetch_orders['total_products']; ?></span> </p>
                        <p> Tổng tiền : <span><?= $fetch_orders['total_price']; ?> đ</span> </p>
                        <p> Hình thức thanh toán: <span>
                                <?php
                                switch ($fetch_orders['method']) {
                                    case 'cash on delivery':
                                        echo 'Thanh toán khi nhận hàng';
                                        break;
                                    default:
                                        echo $fetch_orders['method'];
                                        break;
                                }
                                ?>
                            </span> </p>

                        <p> Trang thái thanh toán: <span>
                                <?php
                                if ($fetch_orders['order_status'] === 'paid') {
                                    echo 'Đã thanh toán';
                                } else {
                                    echo 'Chưa thanh toán';
                                }
                                ?>
                            </span> </p>
                        <p> Trạng thái đơn hàng : <span>

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
                                    case 'cancel':
                                        echo 'Đơn hàng đã hủy';
                                        break;
                                    default:
                                        echo 'Không xác định';
                                }
                                ?>
                            </span> </p>
                        <!-- <form action="" method="POST">
                            <div class="flex-btn">
                                <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">Xóa</a>
                            </div>
                        </form> -->
                        <!-- Form for cancellation reason -->
                        <form action="" method="POST">
                            <p> Lý do hủy đơn hàng : </p>
                            <textarea name="cancel_reason" id="cancel_reason" required></textarea>
                            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                            <button type="submit" name="cancel_order">Xác nhận hủy đơn hàng</button>
                        </form>
                    </div>
            <?php
                } else {
                    echo '<p class="empty">No order found for the specified ID.</p>';
                }
            } else {
                echo '<p class="empty">Invalid or missing "placed" parameter.</p>';
            }
            ?>
        </div>


    </section>

    <!-- placed orders section ends -->

    <!-- custom js file link  -->
    <script src="../templates/js/admin_script.js"></script>

</body>

</html>