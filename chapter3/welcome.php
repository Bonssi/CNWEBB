<?php
// Khởi động session
session_start();

// Kiểm tra xem SESSION lưu tên đăng nhập có tồn tại không
if (isset($_SESSION['username'])) {

    // Lấy username từ SESSION
    $loggedInUser = $_SESSION['username'];

    // In ra lời chào mừng
    echo "<h1>Chào mừng BROOO trở lại, $loggedInUser!</h1>";
    echo "<p>Bạn đã đăng nhập thành công.</p>";

    // Link đăng xuất
    echo '<a href="logout.php">Đăng xuất</a>';

} else {
    // Nếu chưa đăng nhập, chuyển hướng về login.html
    header('Location: login.html');
    exit;
}
?>
