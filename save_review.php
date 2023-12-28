<?php
session_start();
include 'configs/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = $_POST['product_id']; // Lấy ID sản phẩm
    $userID = $_SESSION['user_id']; // Lấy ID người dùng từ session
    $id_order = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Kiểm tra xem có hàng nào có cùng productID và userID trong bảng product_reviews
    $check_query = $conn->prepare("SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ? AND id_order = ?");
    $check_query->execute([$productID, $userID, $id_order]);
    if ($check_query->rowCount() > 0) {
        // Nếu hàng đã tồn tại, thực hiện truy vấn UPDATE
        $update_query = $conn->prepare("UPDATE product_reviews SET rating = ?, comment = ?, created_at = NOW() WHERE product_id = ? AND user_id = ? AND id_order = ?");
        $update_query->execute([$rating, $comment, $productID, $userID, $id_order]);

        // Kiểm tra kết quả của truy vấn UPDATE và trả về phản hồi
        if ($update_query->rowCount() > 0) {
            $messages[] = 'Cập nhật đánh giá thành công.';
        } else {
            $messages[] = 'Cập nhật đánh giá thất bại.';
        }
    } else {
        // Thực hiện truy vấn INSERT để lưu đánh giá vào bảng `product_reviews`
        $insert_query = $conn->prepare("INSERT INTO `product_reviews` (product_id, user_id, rating, comment, created_at, status, id_order) VALUES (?, ?, ?, ?, NOW(), 'block', ?)");
        $insert_query->execute([$productID, $userID, $rating, $comment, $id_order]);

        // Kiểm tra kết quả của truy vấn INSERT và trả về phản hồi
        if ($insert_query->rowCount() > 0) {
            $messages[] = 'Đánh giá đã được lưu thành công.';
        } else {
            $messages[] = 'Lưu đánh giá thất bại.';
        }
    }
    // Chuyển đến trang "product_reviews.php?reviews=15"
header("Location: product_reviews.php?reviews=$id_order");
}

