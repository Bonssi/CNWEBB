<?php
// Simple admin to add/delete flowers stored in flowers.json
session_start();
$roster = [];
$jsonPath = __DIR__ . '/flowers.json';
$imagesDir = __DIR__ . '/images';
@mkdir($imagesDir, 0755);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Add new flower
		if (isset($_POST['action']) && $_POST['action'] === 'add') {
				$name = trim($_POST['name'] ?? '');
				$desc = trim($_POST['description'] ?? '');

				if ($name === '') {
						$error = 'Tên hoa không được để trống.';
				} else {
						// handle upload
						$filename = '';
						if (!empty($_FILES['image']['name'])) {
								$tmp = $_FILES['image']['tmp_name'];
								$orig = basename($_FILES['image']['name']);
								$ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
								$allow = ['jpg','jpeg','png','gif'];
								if (!in_array($ext, $allow)) {
										$error = 'Chỉ chấp nhận ảnh JPG/PNG/GIF.';
								} else {
										$filename = time() . '_' . preg_replace('/[^a-z0-9._-]/i','_', $orig);
										if (!move_uploaded_file($tmp, $imagesDir . '/' . $filename)) {
												$error = 'Không thể lưu file ảnh.';
										}
								}
						}

						if ($error === '') {
								$data = @file_get_contents($jsonPath);
								$arr = json_decode($data, true) ?: [];
								$arr[] = [
										'name' => $name,
										'description' => $desc,
										'image' => $filename
								];
								file_put_contents($jsonPath, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
								$_SESSION['msg'] = 'Đã thêm hoa thành công.';
								header('Location: ' . $_SERVER['PHP_SELF']);
								exit;
						}
				}
		}

		// Delete entry
		if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['index'])) {
				$idx = (int)$_POST['index'];
				$data = @file_get_contents($jsonPath);
				$arr = json_decode($data, true) ?: [];
				if (isset($arr[$idx])) {
						// optionally remove image
						if (!empty($arr[$idx]['image']) && file_exists($imagesDir . '/' . $arr[$idx]['image'])) {
								@unlink($imagesDir . '/' . $arr[$idx]['image']);
						}
						array_splice($arr, $idx, 1);
						file_put_contents($jsonPath, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
						$_SESSION['msg'] = 'Đã xóa mục.';
				}
				header('Location: ' . $_SERVER['PHP_SELF']);
				exit;
		}
}

$data = @file_get_contents($jsonPath);
$flowers = json_decode($data, true) ?: [];
?>
<!doctype html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Quản trị - Cửa hàng hoa</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container py-4">
		<div class="d-flex align-items-center mb-3">
			<h3 class="me-auto">Quản trị cửa hàng hoa</h3>
			<div class="d-flex gap-2">
				<a class="btn btn-outline-secondary" href="index.php">Xem trang khách</a>
				<a class="btn btn-outline-info" href="flowers.json" target="_blank" rel="noopener">Xem JSON</a>
			</div>
		</div>

		<?php if (!empty($_SESSION['msg'])): ?>
			<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
		<?php endif; ?>

		<?php if ($error): ?>
			<div class="alert alert-warning"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<div class="card mb-4 p-3">
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="add">
				<div class="row g-2">
					<div class="col-md-4">
						<input name="name" class="form-control" placeholder="Tên hoa" required>
					</div>
					<div class="col-md-5">
						<input name="description" class="form-control" placeholder="Mô tả ngắn">
					</div>
					<div class="col-md-2">
						<input type="file" name="image" accept="image/*" class="form-control form-control-sm">
					</div>
					<div class="col-md-1">
						<button class="btn btn-accent">Thêm</button>
					</div>
				</div>
			</form>
		</div>

		<div class="card p-3">
			<h5>Danh sách hoa</h5>
			<?php if (empty($flowers)): ?>
				<div class="text-muted">Chưa có mục nào.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-sm align-middle">
						<thead>
							<tr><th>#</th><th>Tên</th><th>Mô tả</th><th>Ảnh</th><th></th></tr>
						</thead>
						<tbody>
							<?php foreach ($flowers as $i => $f): ?>
								<tr>
									<td><?php echo $i+1; ?></td>
									<td><?php echo htmlspecialchars($f['name']); ?></td>
									<td class="text-muted small"><?php echo htmlspecialchars($f['description']); ?></td>
									<td style="width:120px;"><?php if (!empty($f['image']) && file_exists($imagesDir . '/' . $f['image'])): ?><img src="images/<?php echo htmlspecialchars($f['image']); ?>" style="height:48px;object-fit:cover;border-radius:6px;"><?php else: ?><span class="text-muted">No image</span><?php endif; ?></td>
									<td class="text-end">
										<form method="post" style="display:inline" onsubmit="return confirm('Xác nhận xóa?');">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="index" value="<?php echo $i; ?>">
											<button class="btn btn-sm btn-outline-danger">Xóa</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>

