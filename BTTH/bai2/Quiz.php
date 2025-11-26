<?php
session_start();

$quizFile = __DIR__ . '/Quiz.txt';
$raw = file_exists($quizFile) ? file_get_contents($quizFile) : '';

function parse_quiz($txt) {
        $out = [];
        $blocks = preg_split('/\R{2,}/u', trim($txt));
        foreach ($blocks as $b) {
                $lines = preg_split('/\R/u', trim($b));
                $q = ['text'=>'', 'opts'=>[], 'ans'=>[]];
                foreach ($lines as $ln) {
                        $ln = trim($ln);
                        if ($ln === '') continue;
                        if (preg_match('/^ANSWER:\s*(.+)$/i', $ln, $m)) {
                                preg_match_all('/[A-Z]/i', strtoupper($m[1]), $m2);
                                $q['ans'] = array_values(array_map('strtoupper', $m2[0] ?? []));
                                continue;
                        }
                        if (preg_match('/^([A-Z])[\.\)]\s*(.+)$/u', $ln, $m)) {
                                $q['opts'][strtoupper($m[1])] = trim($m[2]);
                                continue;
                        }
                        $q['text'] .= ($q['text']? ' ': '') . $ln;
                }
                if ($q['text']) $out[] = $q;
        }
        return $out;
}

$questions = $raw ? parse_quiz($raw) : [];

if (isset($_GET['reset'])) { unset($_SESSION['quiz_result']); header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $score = 0; $fb = [];
        foreach ($questions as $i => $q) {
                $name = 'q'.$i;
                $chosen = strtoupper($_POST[$name] ?? '');
                $correct = in_array($chosen, $q['ans']);
                if ($correct) $score++;
                $fb[$i] = ['chosen'=>$chosen, 'correct'=>$correct];
        }
        $_SESSION['quiz_result'] = ['score'=>$score, 'feedback'=>$fb];
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
}

$result = $_SESSION['quiz_result'] ?? null;
$showAnswers = $result && (isset($_GET['showAnswers']) && $_GET['showAnswers']=='1');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Quiz Android</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{--bg1:#f7fafc;--bg2:#ffffff;--card:#ffffff;--muted:#6b7280;--accent1:#0ea5a4;--accent2:#7c3aed;--text:#0f172a}
        body{background:linear-gradient(180deg,var(--bg1),var(--bg2));color:var(--text);font-family:Inter,Arial,Helvetica,sans-serif}
        .wrap{max-width:980px;margin:36px auto;padding:26px;background:var(--card);border-radius:14px;box-shadow:0 8px 30px rgba(15,23,42,0.06)}
        header .lead{color:var(--muted)}
        .card{background:#ffffff;border:1px solid rgba(15,23,42,0.06);border-radius:10px}
        .card .form-check{padding:10px;border-radius:8px;transition:all .12s}
        .card .form-check:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(15,23,42,0.06)}
        .form-check-input:checked + .form-check-label{color:var(--accent1);font-weight:600}
        .form-check-label{color:var(--text)}
        .small-muted{color:var(--muted)}
        .correct{background:linear-gradient(90deg,#f0fdf4,#ecfdf3);border-left:4px solid #16a34a}
        .wrong{background:linear-gradient(90deg,#fff7f7,#fff1f2);border-left:4px solid #ef4444}
        .btn-accent{background:linear-gradient(90deg,var(--accent1),var(--accent2));border:0;color:#fff}
        @media (max-width:600px){.wrap{padding:18px;margin:18px}}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="mb-3 text-center">
            <h2 class="mb-1">Bài Trắc Nghiệm Android</h2>
            <div class="lead small-muted">Bấm chọn đáp án rồi nhấn <strong>Nộp bài</strong>. Đáp án chỉ hiển thị sau khi nộp.</div>
        </div>

            <?php if ($result): ?>
                <div class="alert alert-success text-center">Kết quả: <strong class="mx-1"><?= $result['score'] ?></strong>/<?= count($questions) ?></div>
            <?php endif; ?>

        <form method="post">
            <?php foreach ($questions as $i => $q): $name='q'.$i; $chosen = $result['feedback'][$i]['chosen'] ?? null; ?>
                <div class="card mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div><strong>Câu <?= $i+1 ?>.</strong></div>
                        <div class="small-muted"><?= count($q['opts']) ?> lựa chọn</div>
                    </div>
                    <div class="mb-2" style="font-weight:600"><?= htmlspecialchars($q['text']) ?></div>

                    <?php foreach ($q['opts'] as $letter => $txt):
                        $isChosen = ($chosen === $letter);
                        $cls = '';
                        if ($result) $cls = in_array($letter, $q['ans']) ? 'correct' : ($isChosen ? 'wrong' : '');
                    ?>
                        <div class="form-check <?= $cls ?> mb-1">
                            <input class="form-check-input" type="radio" name="<?= $name ?>" id="<?= $name ?>_<?= $letter ?>" value="<?= $letter ?>" <?= $isChosen? 'checked':'' ?> <?= $result? 'disabled':'' ?> />
                            <label class="form-check-label ms-2" for="<?= $name ?>_<?= $letter ?>"><strong><?= $letter ?>.</strong> <?= htmlspecialchars($txt) ?></label>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($result): ?>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <div class="small-muted">Trạng thái: <?php if (($result['feedback'][$i]['chosen'] ?? null)): ?>
                                <?= ($result['feedback'][$i]['correct'] ?? false) ? '<span class="badge bg-success">Đúng</span>' : '<span class="badge bg-danger">Sai</span>' ?>
                                <?php else: ?> <span class="text-muted">Bạn chưa chọn</span><?php endif; ?></div>
                            <?php if ($showAnswers): ?>
                                <div class="small-muted">Đáp án: <strong><?= htmlspecialchars(implode(', ', $q['ans'])) ?></strong></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="d-grid gap-2 mt-2">
                <?php if (!$result): ?>
                    <button class="btn btn-accent btn-lg text-dark">Nộp bài</button>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-light flex-fill" href="?showAnswers=1">Hiển thị đáp án</a>
                        <a class="btn btn-secondary flex-fill" href="?reset=1">Làm lại</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>
</html>