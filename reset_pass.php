<?php

include 'configs/connect.php';

session_start();


function generateRandomString()
{
    $characters = '0123456789012345678901234567890123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $length = 8;

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function send_pass_reset_email($email, $token)
{
    require("extends/PHPMailer-master/src/PHPMailer.php");
    require("extends/PHPMailer-master/src/SMTP.php");
    require("extends/PHPMailer-master/src/Exception.php");



    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username = "anvnt96@gmail.com";
    $mail->Password = "ghgf pbnh amyi uwde";
    $mail->SetFrom("anvnt96@gmail.com");
    $mail->addAddress($email);
    $mail->Subject = "Update new password";
    $mail->Body = "Click vào đây để đặt lại mật khẩu: http://localhost/TraSuaProject/password.php?reset_token=$token";
    // $mail->Body = "Click vào đây để đặt lại mật khẩu: http://20004153.000webhostapp.com/web3/password.php?reset_token=$token";


    if (!$mail->Send()) {
        $message[] = "Lỗi gửi gmail : " . $mail->ErrorInfo;
    } else {
        $message[] = "Gửi gmail thành công !";
    }
}
// Xử lý yêu cầu đặt lại mật khẩu
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    $check_email_query = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $check_email_query->execute([$email]);

    if ($check_email_query->rowCount() > 0) {
        $user = $check_email_query->fetch();

        // Tạo token reset mật khẩu
        $reset_token = generateRandomString();

        // Lưu token vào cơ sở dữ liệu
        $update_token_query = $conn->prepare("UPDATE `users` SET reset_token = ?, reset_token_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $update_token_query->execute([$reset_token, $email]);

        if ($update_token_query->rowCount() > 0) {
            $message[] = 'Link đặt lại mật khẩu đã được gửi vào email của bạn.';
            send_pass_reset_email($email, $reset_token);
        } else {
            $message[] = 'Có lỗi xảy ra khi yêu cầu đặt lại mật khẩu. Vui lòng thử lại sau.';
        }
    } else {
        $message[] = 'Không tìm thấy tài khoản. Vui lòng liên hệ quản trị viên.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="templates/uploaded_img/system_img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="templates/css/style.css">

</head>

<body>
    <?php include 'configs/user_header.php'; ?>

    <section class="form-container">

        <form action="" method="post">
            <h3>Nhập gmail</h3>
            <input type="email" name="email" required placeholder="Nhập gmail" class="box" maxlength="50">
            <input type="submit" value="Xác nhận thay đổi mật khẩu" name="submit" class="btn">
        </form>

    </section>

    <?php include 'configs/user_footer.php'; ?>


    <!-- custom js file link  -->
    <script src="templates/js/script.js"></script>

</body>

</html>