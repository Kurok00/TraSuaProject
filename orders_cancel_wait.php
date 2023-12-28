<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
};
unset($_SESSION['VNPAY_ORDER_ID']);

// Kiểm tra xem đã cung cấp tham số id_order từ URL hay chưa
if (isset($_GET['id_order'])) {
    $id_order = $_GET['id_order'];
} else {
    // Nếu không có tham số, bạn có thể thực hiện xử lý báo lỗi hoặc điều hướng người dùng đến một trang khác.
    echo "Lỗi: Không tìm thấy đơn hàng cụ thể.";
    exit;
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
    <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="templates/css/style.css">
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

    <!-- header section starts  -->
    <?php include 'configs/user_header.php'; ?>
    <!-- header section ends -->

    <div class="heading">
        <h3>Đơn hàng</h3>
        <p><a href="html.php">Trang chủ</a> <span> / Đơn hàng</span></p>
    </div>

    <section class="orders">

        <h1 class="title">Đơn hàng của bạn</h1>

        <div class="box-container">

            <?php
            if ($user_id == '') {
                echo '<p class="empty">Bạn chưa đăng nhập </p>';
            } else {
                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND id=?");
                $select_orders->execute([$user_id, $id_order]);
                if ($select_orders->rowCount() > 0) {
                    while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            ?>
                        <div class="box">
                            <p> Mã người dùng : <span><?= $fetch_orders['user_id']; ?></span> </p>
                            <p> Ngày đặt : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                            <p> Tên : <span><?= $fetch_orders['name']; ?></span> </p>
                            <p> Gmail : <span><?= $fetch_orders['email']; ?></span> </p>
                            <p> Số đt : <span><?= $fetch_orders['number']; ?></span> </p>
                            <p> Địa chỉ : <span><?= $fetch_orders['address']; ?></span> </p>
                            <p> Sản phẩm : <span><?= $fetch_orders['total_products']; ?></span> </p>
                            <p> Tổng tiền : <span><?= number_format($fetch_orders['total_price']); ?> đ</span> </p>
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

                            <p> Trạng thái thanh toán: <span>
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
                                            echo 'Đơn hàng bị hủy';
                                            break;
                                        default:
                                            echo 'Không xác định';
                                    }
                                    ?>
                                </span> </p>
                            <!-- Form for cancellation reason -->
                            <form action="" method="POST">
                                <p> Lý do hủy đơn hàng : </p>
                                <textarea name="cancel_reason" id="cancel_reason" required></textarea>
                                <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                                <button type="submit" name="cancel_order">Xác nhận hủy đơn hàng</button>
                            </form>

                        </div>
            <?php
                    }
                } else {
                    echo '<p class="empty">no orders placed yet!</p>';
                }
            }
            ?>

        </div>

    </section>

    <!-- footer section starts  -->
    <?php include 'configs/user_footer.php'; ?>
    <!-- footer section ends -->

    <!-- custom js file link  -->
    <script src="templates/js/script.js"></script>

</body>

</html>