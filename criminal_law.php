<?php
require_once 'auth.php';
require_once 'init_db.php';

$stmt = $pdo->query("SELECT * FROM criminal_cases ORDER BY created_at DESC");
$cases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Criminal Law Cases</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚖️ Criminal Law Cases</h2>
        <a href="criminal_create.php" class="btn btn-danger">+ New Case</a>
    </div>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Accused</th>
                <th>Charge</th>
                <th>Court</th>
                <th>Plea</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($cases as $i => $c): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= htmlspecialchars($c['accused']) ?></td>
                <td><?= htmlspecialchars($c['charge']) ?></td>
                <td><?= htmlspecialchars($c['court']) ?></td>
                <td><?= htmlspecialchars($c['plea']) ?></td>
                <td><span class="badge bg-<?= $c['status']=='open'?'success':'secondary' ?>"><?= $c['status'] ?></span></td>
                <td><a href="criminal_view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>