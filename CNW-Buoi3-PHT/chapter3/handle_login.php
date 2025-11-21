<?php
// Khởi động session
session_start();

// Kiểm tra xem form đã được gửi chưa
if (isset($_POST['username'])) {

    // Lấy dữ liệu username và password
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Kiểm tra logic đăng nhập (giả lập)
    if ($user == 'admin' && $pass == '123') {

        // Lưu tên username vào SESSION
        $_SESSION['username'] = 'BROOO';

        // Chuyển hướng sang welcome.php
        header('Location: welcome.php');
        exit;

    } else {
        // Nếu thất bại, quay lại login.html kèm thông báo lỗi
        header('Location: login.html?error=1');
        exit;
    }

} else {
    // Nếu truy cập trực tiếp file này
    header('Location: login.html');
    exit;
}
?>
