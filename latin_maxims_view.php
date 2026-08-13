<?php
require_once 'auth.php';
require_once 'init_db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM latin_maxims WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if(!$m){ header('Location: latin_maxims.php'); exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($m['maxim']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="latin_maxims.php" class="btn btn-secondary mb-3">← Back</a>
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h3><em><?= htmlspecialchars($m['maxim']) ?></em></h3>
        </div>
        <div class="card-body">
            <p><strong>Meaning:</strong> <?= htmlspecialchars($m['meaning']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($m['category']) ?></p>
            <hr>
            <p><strong>Details:</strong></p>
            <p><?= nl2br(htmlspecialchars($m['details'])) ?></p>
            <small class="text-muted">Added: <?= $m['created_at'] ?></small>
        </div>
    </div>
</div>
</body>
</html>