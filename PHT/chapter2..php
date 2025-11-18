<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>PHT Chương 2 - PHP Căn Bản</title>
</head>
<body>
<h1>Kết quả PHP Căn Bản</h1>

<?php 
$ho_ten = "Phan Văn Tâm";
$diem_tb = 9.5;
$co_di_hoc_chuyen_can = true;

echo "Họ tên: $ho_ten <br>";
echo "Điểm trung bình: $diem_tb <br>";
echo "Chuyên cần: " . ($co_di_hoc_chuyen_can ? "Có đi học" : "Không đi học") . "<br><br>";

if ($diem_tb >= 8.5 && $co_di_hoc_chuyen_can) {
    echo "Xếp loại: Giỏi <br><br>";
} else if ($diem_tb >= 6.5 && $co_di_hoc_chuyen_can) {
    echo "Xếp loại: Khá <br><br>";
} else if ($diem_tb >= 5.0 && $co_di_hoc_chuyen_can) {
    echo "Xếp loại: Trung bình <br><br>";
} else {
    echo "Xếp loại: Yếu (Cần cố gắng thêm!) <br><br>";
}

function chaoMung() {
    echo "Chúc mừng bạn đã hoàn thành PHT Chương 2!<br>";
}

chaoMung();
?>

</body>
</html>
