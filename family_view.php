<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM family_cases WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if(!$c){ header('Location: family_law.php'); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($c['title']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="family_law.php" class="btn btn-secondary mb-3">← Back</a>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>👨‍👩‍👧 <?= htmlspecialchars($c['title']) ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Case Type:</strong> <?= htmlspecialchars($c['case_type']) ?></p>
                    <p><strong>Petitioner:</strong> <?= htmlspecialchars($c['petitioner']) ?></p>
                    <p><strong>Respondent:</strong> <?= htmlspecialchars($c['respondent']) ?></p>
                    <p><strong>Children:</strong> <?= htmlspecialchars($c['children']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Relief Sought:</strong> <?= htmlspecialchars($c['relief'] ?? '—') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($c['status'] ?? '—') ?></p>
                    <p><strong>Created:</strong> <?= htmlspecialchars(substr($c['created_at'] ?? '', 0, 10)) ?></p>
                </div>
            </div>
            <?php if (!empty($c['notes'])): ?>
            <hr/>
            <p><strong>Notes:</strong></p>
            <pre style="background:#f8f9fa;padding:12px;border-radius:4px"><?= htmlspecialchars($c['notes']) ?></pre>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="family_law.php" class="btn btn-secondary">← Back to List</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
