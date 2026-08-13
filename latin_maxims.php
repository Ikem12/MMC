<?php
require_once 'auth.php';
require_once 'init_db.php';

$stmt = $pdo->query("SELECT * FROM latin_maxims ORDER BY created_at DESC");
$maxims = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Latin Maxims</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚖️ Latin Maxims</h2>
        <a href="latin_create.php" class="btn btn-primary">+ Add Maxim</a>
    </div>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Maxim</th>
                <th>Meaning</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($maxims as $i => $m): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><em><?= htmlspecialchars($m['maxim']) ?></em></td>
                <td><?= htmlspecialchars($m['meaning']) ?></td>
                <td><?= htmlspecialchars($m['category']) ?></td>
                <td>
                    <a href="latin_maxims_view.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>