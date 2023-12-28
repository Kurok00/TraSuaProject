<?php
require_once('extends\TCPDF-main\tcpdf.php');
include 'configs/connect.php';



// Kiểm tra nếu tham số export_pdf và user_id đã được truyền
if (isset($_GET['export_pdf']) && isset($_GET['order_id'])) {
    // Nhận giá trị user_id và order_id từ URL
    $user_id = $_GET['export_pdf'];
    $id_order = $_GET['order_id'];

} else {

}


// Hàm chuyển đổi chữ cái có dấu thành chữ cái không dấu
function convertToNonUnicode($str)
{
    $unicode = array(
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd' => 'đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
    );

    foreach ($unicode as $nonUnicode => $uni) {
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }

    return $str;
}

// Select orders
$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND id = ?");
$select_orders->execute([$user_id, $id_order]);

// Check if order exists
if ($select_orders->rowCount() > 0) {
    $fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC);

    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();

    // Add store information
    $pdf->SetFont('times', 'B', 14);
    $pdf->Cell(0, 10, 'TEABILL', 0, 1, 'C'); // Thay "Your Store Name" bằng tên cửa hàng của bạn
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(0, 10, 'Address: VINH LONG CITY', 0, 1, 'C'); // Thay "Your Store Address" bằng địa chỉ cửa hàng của bạn
    $pdf->Cell(0, 10, 'Phone: 0901234567', 0, 1, 'C'); // Thay "Your Store Phone" bằng số điện thoại cửa hàng của bạn
    $pdf->Cell(0, 10, 'Email: Yukata7951936@gmail.com', 0, 1, 'C'); // Thay "Your Store Email" bằng địa chỉ email cửa hàng của bạn
    $pdf->Ln(10);

    // Extract product details
    $totalProducts = $fetch_orders['total_products'];

    if (!empty($totalProducts)) {
        $productsArray = explode(" - ", $totalProducts);

        // Add invoice information
        $pdf->SetFont('times', 'B', 16);
        $pdf->Cell(0, 10, 'SALES INVOICE', 0, 1, 'C');
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 10, 'Order ID: ' . $fetch_orders['id'], 0, 1, 'L');
        $pdf->Cell(0, 10, 'Date: ' . $fetch_orders['placed_on'], 0, 1, 'L');
        $pdf->Ln(10);

        // Add customer information
        $pdf->Cell(0, 10, 'Customer Information:', 0, 1, 'L');
        $pdf->Cell(0, 10, 'Name: ' . convertToNonUnicode($fetch_orders['name']), 0, 1, 'L');
        $pdf->Cell(0, 10, 'Email: ' . convertToNonUnicode($fetch_orders['email']), 0, 1, 'L');
        $pdf->Cell(0, 10, 'Address: ' . convertToNonUnicode($fetch_orders['address']), 0, 1, 'L');
        $pdf->Ln(10);

        // Add product details in a table
        $pdf->Cell(20, 10, 'Stt', 1); // Giảm chiều rộng của cột Stt
        $pdf->Cell(30, 10, 'Product ID', 1);
        $pdf->Cell(60, 10, 'Product Name', 1); // Tăng chiều rộng của cột Product Name
        $pdf->Cell(30, 10, 'Price', 1);
        $pdf->Cell(20, 10, 'Quantity', 1);
        $pdf->Cell(30, 10, 'Total price', 1); // Thêm cột ASD
        $pdf->Ln();

        $counter = 1;

        foreach ($productsArray as $productInfo) {
            $productParts = explode(" (", $productInfo);

            if (count($productParts) == 2) {
                $productName = trim($productParts[0]);
                $quantity = trim(explode(" x ", $productParts[1])[1]); // Lấy phần tử thứ hai
                $price = trim(explode(" x ", $productParts[1])[0]); // Lấy phần tử thứ nhất
                


                // Loại bỏ dấu ")"
                $quantity = rtrim($quantity, ')');

                $select_product_id = $conn->prepare("SELECT id, price FROM `products` WHERE name = ?");
                $select_product_id->execute([convertToNonUnicode($productName)]);
                $product = $select_product_id->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $productID = $product['id'];
                    

                    // Tính giá trị cho cột ASD
                    $asdValue = $price * $quantity;

                    // Add row to the table
                    $pdf->Cell(20, 10, $counter, 1); // Giảm chiều rộng của cột Stt
                    $pdf->Cell(30, 10, $productID, 1);
                    $pdf->Cell(60, 10, convertToNonUnicode($productName), 1); // Tăng chiều rộng của cột Product Name
                    $pdf->Cell(30, 10, $price, 1);
                    $pdf->Cell(20, 10, $quantity, 1); // Giảm chiều rộng của cột Quantity
                    $pdf->Cell(30, 10, $asdValue, 1); // Thêm cột ASD
                    $pdf->Ln();

                    $counter++;
                }
            }
        }

        // Tính tổng số tiền trước thuế
        $totalBeforeTax = $fetch_orders['total_price'];

        // Tính số tiền thuế
        $vatRate = 0.1; // 10% VAT
        $vatAmount = $totalBeforeTax * $vatRate;

        // Tính tổng số tiền sau thuế
        $totalAfterTax = $totalBeforeTax - $vatAmount;

        // Add total price
        $pdf->SetX($pdf->GetPageWidth() - 90); // Đặt vị trí bắt đầu từ phải trang
        $pdf->MultiCell(0, 10, 'Total Price: ' . number_format($totalAfterTax, 0, ',', '.') . ' VND', 0, 'L');

        // Add VAT
        $pdf->SetX($pdf->GetPageWidth() - 90); // Đặt vị trí bắt đầu từ phải trang
        $pdf->MultiCell(0, 10, 'VAT (10%): ' . number_format($vatAmount, 0, ',', '.') . ' VND', 0, 'L');

        // Add Total After Tax
        $pdf->SetX($pdf->GetPageWidth() - 90);
        $pdf->SetFont('', 'B', 16); // Đặt font size là 16, và không chỉ định tên font để sử dụng font mặc định
        $pdf->MultiCell(0, 10, 'Total After Tax: ' . number_format($totalBeforeTax, 0, ',', '.') . ' VND', 0, 'L');
        $pdf->SetFont('', '', 12); // Trả lại font size về 12 cho các nội dung sau đó (nếu có)

        // Save the PDF file
        $pdf->Output('Invoice_'.$id_order.'.pdf', 'D');
        echo "ok.";
    } else {
        echo "Không timg thấy đơn hàng.";
    }
} else {
    echo "Order not found.";
}
?>
