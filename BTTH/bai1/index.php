<?php
$data = @file_get_contents(__DIR__ . '/flowers.json');
$flowers = json_decode($data, true) ?: [];
?>
<!doctype html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Cửa hàng hoa</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<nav class="navbar navbar-expand bg-white mb-4">
		<div class="container">
			<a class="navbar-brand" href="index.php">Cửa hàng hoa</a>
			<div class="ms-auto">
				<a class="btn btn-sm btn-outline-primary" href="admin.php">Quản trị</a>
			</div>
		</div>
	</nav>

	<div class="container">
		<div class="hero mb-4">
			<h1>Chào mừng đến cửa hàng hoa</h1>
			<p>Chọn bó hoa ưng ý hoặc thêm mẫu mới từ trang quản trị.</p>
			<div class="mt-2"><a href="flowers.json" target="_blank" rel="noopener" class="small text-muted">Xem dữ liệu (flowers.json)</a></div>
		</div>

		<div class="row gy-4">
			<?php if (empty($flowers)): ?>
				<div class="col-12">
					<div class="alert alert-info">Không có hoa nào trong kho. Hãy thêm từ <a href="admin.php">Quản trị</a>.</div>
				</div>
			<?php endif; ?>

			<?php foreach ($flowers as $f): ?>
				<div class="col-sm-6 col-md-4">
					<div class="card product-card">
						<?php $img = 'images/' . ($f['image'] ?? ''); ?>
						<?php if (!empty($f['image']) && file_exists(__DIR__ . '/images/' . $f['image'])): ?>
							<img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($f['name']); ?>">
						<?php else: ?>
							<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:200px;color:#6b7280">No image</div>
						<?php endif; ?>
						<div class="card-body">
							<h5 class="card-title"><?php echo htmlspecialchars($f['name']); ?></h5>
							<p class="card-text text-muted small"><?php echo nl2br(htmlspecialchars($f['description'])); ?></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</body>
</html>

