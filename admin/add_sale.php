<?php
include '../configs/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (empty($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

// Lấy thông tin của tất cả sản phẩm để hiển thị trong dropdown
$query = $conn->prepare("SELECT id, name FROM products");
$query->execute();
$result = $query->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra nếu form đã được submit
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $discountPercentage = $_POST['sale'] ;
    // Lấy danh sách sản phẩm đã chọn
    $selectedProducts = $_POST['selected_products'] ;
    // Thêm dữ liệu vào bảng 'sale'
    $query = $conn->prepare("INSERT INTO sale (name_sale, products, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    
    $query->execute([$name, $selectedProducts, $discountPercentage, $start_date, $end_date]);

    $message[] = 'Thêm chương trình thành công';
}





// Hàm kiểm tra xem sản phẩm đã được chọn trước đó hay chưa
function isProductAlreadySelected($productName)
{
    global $conn;

    // Lấy danh sách sản phẩm đã chọn từ bảng sale
    $query = $conn->prepare("SELECT products FROM sale");
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    // Duyệt qua từng bản ghi trong kết quả
    foreach ($result as $row) {
        // Chuyển chuỗi sản phẩm thành mảng
        $selectedProducts = explode(', ', $row['products']);

        // Kiểm tra xem sản phẩm đã tồn tại trong mảng hay không
        if (in_array($productName, $selectedProducts)) {
            $message[] = 'tồn tại';
            return true; // Sản phẩm đã tồn tại
        }
    }

    return false; // Sản phẩm chưa tồn tại
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../templates/uploaded_img/admin_img/user.png">
    <title>Thêm chương trình giảm giá</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        #selected-text {
            display: inline-block;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 20px;
            /* Điều chỉnh kích thước chữ ở đây */
        }

        button {
            margin-left: 5px;
            cursor: pointer;
            padding: 5px 10px;
            background-color: #dc3545;
            /* Màu đỏ */
            color: #fff;
            /* Màu trắng */
            border: none;
            border-radius: 5px;
            font-size: 14px;
            /* Điều chỉnh kích thước chữ ở đây */
        }

        button:hover {
            background-color: #c82333;
            /* Màu đỏ nhạt khi di chuột qua */
        }
    </style>
</head>

<body>

    <?php include '../configs/admin_header.php' ?>
    <?php include '../configs/slider.php' ?>

    <!-- admin profile update section starts  -->

    <section class="form-container">
        <form action="" method="POST">
            <h3>Tạo chương trình giảm giá</h3>
            <input type="text" name="name" maxlength="20" class="box" placeholder="Nhập tên chương trình" required oninput="this.value = this.value.replace(/\s/g, '')">
            <span id="name-error" class="error-message"></span>
            <!-- Dropdown để chọn sản phẩm -->
            <label for="box_status">Sản phẩm :</label>
            <!-- Dropdown để chọn sản phẩm -->
            <select id="box_status" class="drop-down" onchange="addSelectedProduct(this)">
    <?php
    // Hiển thị tùy chọn cho mỗi sản phẩm
    foreach ($result as $row) {
        // Kiểm tra xem sản phẩm đã tồn tại trong cột products hay không
        if (!isProductAlreadySelected($row['name'])) {
            echo '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
        }
    }
    ?>
</select>
            <div id="selected-products">
                <label for="selected">Sản phẩm đã chọn:</label>
                <span id="selected-text"></span>
                <input type="hidden" id="selected-products-input" name="selected_products" />
                <button type="button" onclick="clearAndSubmit()">Xóa</button>
            </div>

            <label for="start_date">Phần % giảm giá :</label>
            <input type="number" min="0" max="99" required placeholder="%" name="sale" onkeypress="if(this.value.length == 10) return false;" class="box">

            <!-- Ô nhập ngày bắt đầu -->
            <label for="start_date">Ngày bắt đầu:</label>
            <input type="date" id="start_date" name="start_date" class="box" required>

            <!-- Ô nhập ngày kết thúc -->
            <label for="end_date">Ngày kết thúc:</label>
            <input type="date" id="end_date" name="end_date" class="box" required>

            <input type="submit" value="Tạo" name="submit" class="btn">
        </form>
    </section>


    <script>
        function addSelectedProduct(selectElement) {
            var selectedProductName = selectElement.value;
            var selectedText = document.getElementById('selected-text');
            var selectedProductsInput = document.getElementById('selected-products-input');

            // Kiểm tra xem sản phẩm đã tồn tại trong văn bản hay chưa
            if (!isProductSelected(selectedProductName)) {
                // Nếu sản phẩm chưa có, thêm vào văn bản
                if (selectedText.textContent) {
                    selectedText.textContent += ', ' + selectedProductName;
                } else {
                    selectedText.textContent = selectedProductName;
                }

                // Thêm sản phẩm vào input hidden
                selectedProductsInput.value += selectedProductName + ',';
            }
        }

        function isProductSelected(productName) {
            var selectedText = document.getElementById('selected-text');

            // Kiểm tra xem sản phẩm đã tồn tại trong văn bản hay không
            return selectedText.textContent.includes(productName);
        }

        function clearSelected() {
            var selectedText = document.getElementById('selected-text');
            var selectedProductsInput = document.getElementById('selected-products-input');

            // Xóa toàn bộ văn bản và giá trị của input hidden
            selectedText.textContent = '';
            selectedProductsInput.value = '';
        }

        function clearAndSubmit() {
            clearSelected();
            // Gửi form lên server hoặc thực hiện các bước khác theo nhu cầu của bạn
            document.forms[0].submit(); // Make sure to update this if you have multiple forms on the page
        }
        
    </script>



    <script src="../templates/js/admin_script.js"></script>

</body>

</html>