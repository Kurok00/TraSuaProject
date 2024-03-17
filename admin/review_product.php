<?php
include '../configs/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}


// Initialize variables for search
$searchInput = "";
$searchColumn = "review_id"; // Default search column

if (isset($_GET['searchInput']) && isset($_GET['searchColumn'])) {
    $searchInput = $_GET['searchInput'];
    $searchColumn = $_GET['searchColumn'];
}

// Initialize $reviews array to avoid undefined variable warning
$reviews = array();

// Prepare the SQL query with the search criteria
if (!empty($searchInput)) {
    $sql = "SELECT * FROM `product_reviews` WHERE $searchColumn LIKE :searchInput";
    $select_review = $conn->prepare($sql);
    $select_review->bindValue(':searchInput', "%$searchInput%", PDO::PARAM_STR);
} else {
    // If there is no search input, select all reviews
    $select_review = $conn->prepare("SELECT * FROM `product_reviews`");
}

$select_review->execute();
$reviews = $select_review->fetchAll(PDO::FETCH_ASSOC);





if (isset($_GET['update'])) {
    $update_id = $_GET['update'];
    $user = $conn->prepare("SELECT status FROM `product_reviews` WHERE id = ?");
    $user->execute([$update_id]);
    $status = $user->fetchColumn(); // Lấy trạng thái hiện tại

    if ($status == 'active') {
        $newStatus = 'block'; // Đổi từ 'active' sang 'block'
    } else {
        $newStatus = 'active'; // Đổi từ 'block' sang 'active'
    }
    $updateStatus = $conn->prepare("UPDATE `product_reviews` SET status = ? WHERE id = ?");
    $updateStatus->execute([$newStatus, $update_id]);
    header('location:review_product.php');
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_reviews = $conn->prepare("DELETE `product_reviews`  WHERE id = ?");
    $delete_reviews->execute([$edlete_id]);
    header('location:review_product.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>
    <link rel="stylesheet" href="../templates/css/style_admin.css">
</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>
    <!-- admin profile update section starts  -->

    <h1 class="heading">Đánh giá của khách hàng</h1>

    <div class="search-bar">
        <form action="" method="GET">
            <input type="text" name="searchInput" placeholder="Tìm kiếm" class="box" value="<?php echo $searchInput; ?>">
            <select name="searchColumn" class="box">
                <option value="review_id" <?php if ($searchColumn === "review_id") echo "selected"; ?>>ID review</option>
                <option value="user_id" <?php if ($searchColumn === "user_id") echo "selected"; ?>>ID user</option>
                <option value="rating" <?php if ($searchColumn === "rating") echo "selected"; ?>>Rating</option>
            </select>
            <button id="searchButton" class="btn_share" type="submit">Tìm</button>
        </form>
    </div>

    <section class="show-products" style="padding-top: 0;">
        <table class="table-category">
            <tr>
                <th>ID</th>
                <th>ID khách hàng</th>
                <th>Tên khách hàng</th>
                <th>Mã đơn</th>
                <th>Mã sp</th>
                <th>Đánh giá</th>
                <th>Bình luận</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
            <?php foreach ($reviews as $review) : ?>
                <?php
                $status = $review['status'];
                // Check the role
                switch ($status) {
                    case "block":
                        $sta = "Khóa";
                        break;
                    default:
                        $sta = "Hoạt động";
                }
                ?>
                <tr>
                    <td><?= $review['id'] ?></td>
                    <td><?= $review['user_id'] ?></td>
                    <?php
                    $user_review = $conn->prepare("SELECT name FROM `users` WHERE `user_id` = :user_id");
                    $user_review->bindParam(':user_id', $review['user_id']);
                    $user_review->execute();
                    $user = $user_review->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <td><?= $user['name'] ?></td>
                    <td><?= $review['id_order'] ?></td>
                    <td><?= $review['product_id'] ?></td>
                    <td><?= $review['rating'] ?> / 5</td>
                    <td><?= $review['comment'] ?></td>
                    <td><?= $review['created_at'] ?></td>
                    <td><a href="review_product.php?update=<?= $review['id'] ?>" class="update-btn" onclick="return confirm('Bạn muốn thay đổi trạng thái đánh giá này');"><?= $sta ?></a></td>
                    <td>
                        <form method="post" action="review_product.php">
                            <input type="hidden" name="delete_id" value="<?= $review['id'] ?>">
                            <button type="submit" class="del-btn" onclick="return confirm('Bạn muốn xóa đánh giá này?')">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

</body>

</html>