<?php
session_start();

$rosterPath = __DIR__ . '/65HTTT_Danh_sach_diem_danh.csv';
$students = [];
$error = '';

if (!is_readable($rosterPath)) {
		$error = 'Không tìm thấy hoặc không thể đọc file danh sách: ' . basename($rosterPath);
} else {
		if (($f = fopen($rosterPath, 'r')) !== false) {
				$hdr = fgetcsv($f);
				while (($r = fgetcsv($f)) !== false) {
						$students[] = [
								'username' => $r[0] ?? '',
								'lastname' => $r[2] ?? '',
								'firstname' => $r[3] ?? '',
								'city' => $r[4] ?? '',
								'email' => $r[5] ?? '',
						];
				}
				fclose($f);
		}
}

// Handle POST: save attendance CSV and redirect (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$present = $_POST['present'] ?? [];
		$date = date('Ymd');
		$time = date('Y-m-d H:i:s');
		$outName = "attendance_{$date}.csv";
		$outPath = __DIR__ . '/' . $outName;
		if (($out = fopen($outPath, 'w')) !== false) {
				fputcsv($out, ['username','firstname','lastname','city','email','present','timestamp']);
				foreach ($students as $s) {
						$is = in_array($s['username'], $present) ? '1' : '0';
						fputcsv($out, [$s['username'],$s['firstname'],$s['lastname'],$s['city'],$s['email'],$is,$time]);
				}
				fclose($out);
				$_SESSION['diemdanh_saved'] = $outName;
				header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?saved=1');
				exit;
		} else {
				$error = 'Không thể tạo file lưu điểm danh.';
		}
}

$savedFile = $_SESSION['diemdanh_saved'] ?? null;
$showSaved = isset($_GET['saved']);
?>
<!doctype html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Điểm danh - 65HTTT</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body{background:#f8fafc;color:#0f172a;font-family:Inter,Arial,Helvetica,sans-serif}
		.wrap{max-width:1100px;margin:28px auto}
		.card{border-radius:10px}
		table th, table td{vertical-align:middle}
		.small-muted{color:#6b7280}
	</style>
</head>
<body>
	<div class="wrap">
		<div class="card p-0 shadow-sm overflow-hidden">
			<div class="px-4 py-3" style="background:linear-gradient(90deg,#eef2ff,#f0fdf4);">
				<div class="d-flex align-items-center justify-content-between">
					<div>
						<h4 class="mb-0">Điểm danh lớp 65HTTT</h4>
						<div class="small-muted">Danh sách: <?php echo htmlspecialchars(basename($rosterPath)); ?></div>
					</div>
					<div class="text-end">
						<div class="small-muted mb-1"><?php echo date('Y-m-d H:i'); ?></div>
						<div>Học viên: <strong><?php echo count($students); ?></strong></div>
					</div>
				</div>
			</div>

			<div class="p-3">
				<?php if ($error): ?>
					<div class="alert alert-warning"><?php echo htmlspecialchars($error); ?></div>
				<?php endif; ?>

				<?php if ($showSaved && $savedFile): ?>
					<div class="alert alert-success">Điểm danh đã lưu: <strong><?php echo htmlspecialchars($savedFile); ?></strong> — <a href="<?php echo htmlspecialchars($savedFile); ?>">Tải xuống</a></div>
				<?php endif; ?>

				<div class="mb-3 d-flex gap-2 align-items-center">
					<input id="search" class="form-control form-control-sm" style="max-width:360px;" placeholder="Tìm theo MSV hoặc tên...">
					<button type="button" id="toggleAll" class="btn btn-sm btn-outline-primary">Bỏ/Chọn tất cả</button>
					<div class="ms-auto small-muted">Có mặt: <span id="presentCount">0</span></div>
				</div>

				<div class="table-responsive">
					<table class="table table-hover align-middle" id="rosterTable">
						<thead class="table-light sticky-top">
							<tr>
								<th style="width:56px">#</th>
								<th>MSV</th>
								<th>Họ</th>
								<th>Tên</th>
								<th>City</th>
								<th>Email</th>
								<th class="text-center">Có mặt</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($students as $i => $s): $uid = htmlspecialchars($s['username']); ?>
								<tr>
									<td><?php echo $i+1; ?></td>
									<td class="msv"><?php echo $uid; ?></td>
									<td class="lastname"><?php echo htmlspecialchars($s['lastname']); ?></td>
									<td class="firstname"><?php echo htmlspecialchars($s['firstname']); ?></td>
									<td><?php echo htmlspecialchars($s['city']); ?></td>
									<td><?php echo htmlspecialchars($s['email']); ?></td>
									<td class="text-center"><input class="form-check-input presentChk" type="checkbox" name="present[]" value="<?php echo $uid; ?>" checked></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="d-flex gap-2 mt-3">
					<button class="btn btn-accent">Lưu điểm danh</button>
					<a class="btn btn-outline-secondary" href="?">Hủy/Refresh</a>
					<?php if ($savedFile): ?>
						<a class="btn btn-success ms-auto" href="<?php echo htmlspecialchars($savedFile); ?>" download>Tải file vừa lưu</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<script>
		// Search filter
		const search = document.getElementById('search');
		const rows = Array.from(document.querySelectorAll('#rosterTable tbody tr'));
		search?.addEventListener('input', () => {
			const q = search.value.trim().toLowerCase();
			rows.forEach(r => {
				const txt = (r.querySelector('.msv').textContent + ' ' + r.querySelector('.lastname').textContent + ' ' + r.querySelector('.firstname').textContent).toLowerCase();
				r.style.display = q ? (txt.includes(q) ? '' : 'none') : '';
			});
			updateCount();
		});

		// Toggle all
		const toggle = document.getElementById('toggleAll');
		toggle?.addEventListener('click', () => {
			const checks = Array.from(document.querySelectorAll('.presentChk'));
			const anyUnchecked = checks.some(c => !c.checked);
			checks.forEach(c => c.checked = anyUnchecked);
			updateCount();
		});

		// present count
		function updateCount(){
			const visible = rows.filter(r => r.style.display !== 'none');
			const cnt = visible.reduce((s,r)=> s + (r.querySelector('.presentChk').checked ? 1:0), 0);
			document.getElementById('presentCount').textContent = cnt;
		}
		document.querySelectorAll('.presentChk').forEach(c => c.addEventListener('change', updateCount));
		updateCount();
	</script>

	<script>
		// toggle all checkboxes
		const btn = document.getElementById('checkAll');
		btn?.addEventListener('click', () => {
			const checks = Array.from(document.querySelectorAll('.presentChk'));
			const anyUnchecked = checks.some(c => !c.checked);
			checks.forEach(c => c.checked = anyUnchecked);
			btn.textContent = anyUnchecked ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
		});
	</script>
</body>
</html>

