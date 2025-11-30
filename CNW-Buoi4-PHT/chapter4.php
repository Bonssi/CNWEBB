<?php 

// === THIẾT LẬP KẾT NỐI PDO === 
$host = '127.0.0.1'; 
$dbname = 'cse485_web'; 
$username = 'root'; 
$password = ''; 
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}


// === LOGIC THÊM SINH VIÊN (XỬ LÝ FORM POST) === 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ten_sinh_vien'])) {

    // Lấy dữ liệu POST
    $ten = $_POST['ten_sinh_vien'];
    $email = $_POST['email'];

    // Câu SQL INSERT
    $sql = "INSERT INTO sinhvien (ten_sinh_vien, email) VALUES (?, ?)";

    // Chuẩn bị và chạy lệnh
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ten, $email]);

    // Reload để tránh lỗi khi F5
    header('Location: chapter4.php');
    exit;
}


// === LOGIC LẤY DANH SÁCH SINH VIÊN (SELECT) === 
// Hiển thị STT (số thứ tự) từ 1..n thay cho việc dùng trực tiếp `id`,
// để đảm bảo số thứ tự liên tiếp ngay cả khi có bản ghi bị xóa.
// Dùng biến người dùng để tương thích với MySQL < 8 (không có ROW_NUMBER()).
$sql_select = "SELECT @rownum := @rownum + 1 AS stt, t.* FROM (SELECT * FROM sinhvien ORDER BY ngay_tao DESC) t, (SELECT @rownum := 0) r";
$stmt_select = $pdo->query($sql_select);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>PHT Chương 4 - Website hướng dữ liệu</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; }
        input { margin-right: 10px; }
    </style>
</head>
<body>

    <h2>Thêm Sinh Viên Mới (Chủ đề 4.3)</h2>

    <form action="chapter4.php" method="POST">
        Tên sinh viên: 
        <input type="text" name="ten_sinh_vien" required>
        Email: 
        <input type="email" name="email" required>
        <button type="submit">Thêm</button>
    </form>

    <h2>Danh Sách Sinh Viên (Chủ đề 4.2)</h2>

    <table>
        <tr>
            <th>STT</th>
            <th>Tên Sinh Viên</th>
            <th>Email</th>
            <th>Ngày Tạo</th>
        </tr>

        <?php 
        while ($row = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['stt']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ten_sinh_vien']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ngay_tao']) . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>
</html>
