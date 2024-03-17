<?php
include '../configs/connect.php';

if (isset($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];

    // Sử dụng prepared statement để tìm kiếm sản phẩm theo searchTerm
    $search_query = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $search_query->execute(["%$searchTerm%"]);

    $products = $search_query->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dưới dạng JSON
    echo json_encode($products);
} else {
    echo json_encode([]);
}
