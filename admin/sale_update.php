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

$edit_id = null;

if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = $conn->prepare("SELECT * FROM sale WHERE id = ?");
    $edit_query->execute([$edit_id]);

    if ($edit_query->rowCount() > 0) {
        $edit_data = $edit_query->fetch(PDO::FETCH_ASSOC);

        // Gán dữ liệu lấy được vào các biến
        $edit_name = $edit_data['name_sale'];
        $edit_selectedProducts = $edit_data['products'];
        $edit_discountPercentage = $edit_data['discount_percentage'];
        $edit_startDate = $edit_data['start_date'];
        $edit_endDate = $edit_data['end_date'];
    } else {
        // Xử lý trường hợp không tìm thấy chương trình giảm giá có $edit_id
        // ...
    }
}
var_dump($edit_id);

// Kiểm tra nếu form đã được submit
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $discountPercentage = $_POST['sale'];
    // Lấy danh sách sản phẩm đã chọn
    $selectedProducts = $_POST['selected_products'];

    // Thực hiện cập nhật dữ liệu trong bảng 'sale'
    $query = $conn->prepare("UPDATE sale SET name_sale = ?, products = ?, discount_percentage = ?, start_date = ?, end_date = ? WHERE id=?");
    $query->execute([$name, $selectedProducts, $discountPercentage, $start_date, $end_date, $edit_id]);
    var_dump($edit_id);
    $message[] = 'Cập nhật chương trình thành công';
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
            <!-- Điền dữ liệu vào trường input -->
            <input type="text" name="name" maxlength="20" class="box" placeholder="Nhập tên chương trình" required oninput="this.value = this.value.replace(/\s/g, '')" value="<?= isset($edit_name) ? $edit_name : ''; ?>">
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

            <!-- Thêm các trường input khác và điền dữ liệu -->
            <label for="start_date">Phần % giảm giá :</label>
            <input type="number" min="0" max="99" required placeholder="%" name="sale" onkeypress="if(this.value.length == 10) return false;" class="box" value="<?= isset($edit_discountPercentage) ? $edit_discountPercentage : ''; ?>">

            <label for="start_date">Ngày bắt đầu:</label>
            <input type="date" id="start_date" name="start_date" class="box" required value="<?= isset($edit_startDate) ? $edit_startDate : ''; ?>">

            <label for="end_date">Ngày kết thúc:</label>
            <input type="date" id="end_date" name="end_date" class="box" required value="<?= isset($edit_endDate) ? $edit_endDate : ''; ?>">

            <input type="submit" value="Cập nhật" name="submit" class="btn">
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
    <script>
    // Hàm để thêm sản phẩm đã chọn vào dropdown và hiển thị trong văn bản
    function showSelectedProducts() {
        var selectedProductName = "<?= isset($edit_selectedProducts) ? $edit_selectedProducts : ''; ?>";
        var selectedText = document.getElementById('selected-text');
        var selectedProductsInput = document.getElementById('selected-products-input');

        // Kiểm tra xem có dữ liệu trong biến $edit_selectedProducts không
        if (selectedProductName.trim() !== '') {
            // Tách các sản phẩm thành mảng
            var selectedProductsArray = selectedProductName.split(', ');

            // Duyệt qua từng sản phẩm và thêm vào dropdown và văn bản
            selectedProductsArray.forEach(function (productName) {
                // Thêm vào dropdown
                var option = document.createElement("option");
                option.value = productName;
                option.text = productName;
                document.getElementById('box_status').add(option);

                // Thêm vào văn bản
                if (selectedText.textContent) {
                    selectedText.textContent += ', ' + productName;
                } else {
                    selectedText.textContent = productName;
                }
            });

            // Gán giá trị vào input hidden
            selectedProductsInput.value = selectedProductName;
        }
    }

    // Gọi hàm khi trang được tải
    showSelectedProducts();
</script>



    <script src="../templates/js/admin_script.js"></script>

</body>

</html>