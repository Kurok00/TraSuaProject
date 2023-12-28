<?php

include 'configs/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
};

// Kiểm tra xem đã cung cấp tham số id_order từ URL hay chưa
if (isset($_GET['reviews'])) {
    $id_order = $_GET['reviews'];
} else {
    // Nếu không có tham số, bạn có thể thực hiện xử lý báo lỗi hoặc điều hướng người dùng đến một trang khác.
    echo "Lỗi: Không tìm thấy đơn hàng cụ thể.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="templates/css/style.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
    <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'>
    <link rel='stylesheet' href='https://raw.githubusercontent.com/kartik-v/bootstrap-star-rating/master/css/star-rating.min.css'>

</head>
<style>
    input[type="submit"][name="submit"] {
        color: blue;
        /* Chọn màu xanh dương */
    }

    /* Áp dụng CSS cho bảng */
    .orders-table {
        width: 100%;
        /* Độ rộng của bảng */
        margin: 0 auto;
        /* Để bảng nằm giữa trang */
        border-collapse: collapse;
        /* Loại bỏ khoảng trắng giữa các ô */
        border-radius: 10px;
        /* Bo góc toàn bộ bảng */
        font-size: 20px;
        /* Kích thước chữ to lên */
    }

    /* CSS để căn giữa cột trừ cột nội dung */
    .orders-table tbody td:not(:last-child) {
        text-align: center;
    }

    /* CSS cho nội dung trong cột cuối cùng (nếu bạn muốn) */
    .orders-table tbody td:last-child {
        text-align: left;
        width: 100px;
    }

    /* Điều chỉnh chiều rộng của cột "Thao tác" */

    /* Điều chỉnh chiều rộng của cột "Đánh giá" */
    .orders-table tbody td:nth-child(3) {
        width: 200px;
        /* Điều chỉnh kích thước theo ý muốn */
    }

    /* Áp dụng CSS cho header row */
    .orders-table thead th {
        background-color: #333;
        /* Màu nền của header */
        color: #fff;
        /* Màu chữ của header */
        text-align: center;
        padding: 10px;
        /* Khoảng cách bên trong các ô */
        border: 1px solid #000;
        /* Kẻ viền màu đen */
    }

    /* Áp dụng CSS cho data rows */
    .orders-table tbody td {
        padding: 10px;
        /* Khoảng cách bên trong các ô */
        border: 1px solid #000;
        /* Kẻ viền màu đen */
    }

    /* CSS để căn giữa hình ảnh và thiết lập kích thước cố định */
    .product-image {
        display: block;
        /* Để hình ảnh được hiển thị thành một block element */
        margin: 0 auto;
        /* Căn giữa theo chiều ngang */
        max-width: 100px;
        /* Đặt kích thước cố định cho chiều rộng */
        max-height: 100px;
        /* Đặt kích thước cố định cho chiều cao */
    }

    /* Góc bo tròn */
    .orders-table thead th:first-child {
        border-top-left-radius: 10px;
    }

    .orders-table thead th:last-child {
        border-top-right-radius: 10px;
    }

    .orders-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 10px;
    }

    .orders-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 10px;
    }


    .star {
        display: inline-block;
        /* Display the star container as inline-block */
        width: 100%;
        /* Set a fixed width for the container */
        vertical-align: middle;
        /* Vertically align the container in the cell */
    }

    .star>* {
        float: right;
    }

    .star label {
        height: 25px;
        width: 25px;
        position: relative;
        cursor: pointer;
        padding: 0 5px;
        display: inline-block;
        /* Display the star labels as inline-block */
    }

    /* The rest of your existing CSS for star labels */


    /* The rest of your existing CSS for star labels */

    .star label:nth-of-type(5):after {
        animation-delay: 0.5s;
    }

    .star label:nth-of-type(4):after {
        animation-delay: 0.4s;
    }

    .star label:nth-of-type(3):after {
        animation-delay: 0.3s;
    }

    .star label:nth-of-type(2):after {
        animation-delay: 0.2s;
    }

    .star label:nth-of-type(1):after {
        animation-delay: 0.1s;
    }

    .star label:after {
        transition: all 1s ease-out;
        position: absolute;
        content: "☆";
        color: orange;
        font-size: 32px;
    }

    .star input {
        display: none;
    }

    .star input:checked+label:after,
    .star input:checked~label:after {
        content: "★";
        color: gold;
        text-shadow: 0 0 5px gold;
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
        <div id="message"></div>

        <?php
        echo '<div id="message"></div>';
        $message[] = '<div id="message"></div>';

        $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND id = ?");
        $select_orders->execute([$user_id, $id_order]);

        if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                $totalProducts = $fetch_orders['total_products'];
                $productsArray = explode(" - ", $totalProducts);

                echo '<table class="orders-table">';
                echo '<thead><tr><th>Tên sản phẩm </th><th>Hình ảnh</th><th>Đánh giá</th><th>Nội dung </th><th>Thao tác</th></tr></thead>';
                echo '<tbody>';

                foreach ($productsArray as $productInfo) {
                    $productParts = explode(" (", $productInfo);

                    if (count($productParts) == 2) {
                        $productName = trim($productParts[0]);
                        $quantity = trim(explode(" x ", $productParts[1])[0]);

                        $select_product_id = $conn->prepare("SELECT id,image FROM `products` WHERE name = ?");
                        $select_product_id->execute([$productName]);
                        $product = $select_product_id->fetch(PDO::FETCH_ASSOC);
                        if ($product) {
                            $productID = $product['id'];

                            echo '<tr>';
                            echo '<form method="post" action="save_review.php">'; // Thêm form
                            echo '<input type="hidden" name="product_id" value="' . $productID . '">';
                            echo '<input type="hidden" name="order_id" value="' . $id_order . '">';
                            echo '<td>' . $productName . '</td>';
                            echo '<td>';
                            echo '<img class="product-image" src="templates/uploaded_img/products_img/' . $product['image'] . '" alt="Hình ảnh">';
                            echo '</td>';

                            echo '<td>
                        <div class="star">
                            <input type="radio" id="r1-' . $productID . '" name="rating" value="5">
                            <label for="r1-' . $productID . '"></label>
                
                            <input type="radio" id="r2-' . $productID . '" name="rating" value="4">
                            <label for="r2-' . $productID . '"></label>
                
                            <input type="radio" id="r3-' . $productID . '" name="rating" value="3">
                            <label for="r3-' . $productID . '"></label>
                
                            <input type="radio" id="r4-' . $productID . '" name="rating" value="2">
                            <label for="r4-' . $productID . '"></label>
                
                            <input type="radio" id="r5-' . $productID . '" name="rating" value="1">
                            <label for="r5-' . $productID . '"></label>
                        </div>
                    </td>';

                            echo '<td>';
                            echo '<div class="comment">';
                            echo '<textarea name="comment" rows="5" cols="40"></textarea>';
                            echo '</div>';
                            echo '</td>';
                            echo '<div id="product-reviews">';

                            $check_query = $conn->prepare("SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ? AND id_order = ?");
                            $check_query->execute([$productID, $user_id, $id_order]);
                            if ($check_query->rowCount() > 0) {
                                echo '<td> Bạn đã đánh giá sản phẩm </td>';
                            } else {
                                echo '<td>';
                                echo '<input type="submit" name="submit" value="Lưu đánh giá">'; // Thêm nút lưu
                                echo '</td>';
                            }
                            echo '</div>';
                            echo '</form>';
                            echo '</tr>';
                        } else {
                            echo 'Sản phẩm đã bị thay đổi, vui lòng quay lại';
                        }
                    }
                }


                echo '</tbody></table>';
            }
        } else {
            echo 'Không tìm thấy đơn hàng .';
        }
        ?>

    </section>

    <!-- footer section starts  -->
    <?php include 'configs/user_footer.php'; ?>
    <!-- footer section ends -->

    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                e.preventDefault(); // Ngăn chặn sự kiện gửi biểu mẫu mặc định
                var form = $(this);
                var message = $('#message');

                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Xóa nội dung của container message
                        message.empty();

                        // Hiển thị thông báo phản hồi
                        if (response.length > 0) {
                            $.each(response, function(index, value) {
                                message.append('<p>' + value + '</p>');
                            });
                        }

                        // Tải lại nội dung container "product-reviews"
                        $('#product-reviews').load('product_reviews.php?id_order=<?php echo $id_order; ?>');
                    }
                });
            });
        });
    </script>




    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- custom js file link  -->
    <script src="templates/js/script.js"></script>

</body>

</html>