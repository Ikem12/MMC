<?php
require_once 'auth.php';
require_once 'init_db.php';

$stmt = $pdo->query("SELECT * FROM family_cases ORDER BY created_at DESC");
$cases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Family Law Cases</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👨‍👩‍👧 Family Law Cases</h2>
        <a href="family_create.php" class="btn btn-primary">+ New Case</a>
    </div>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Case Type</th>
                <th>Petitioner</th>
                <th>Respondent</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($cases as $i => $c): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= htmlspecialchars($c['case_type']) ?></td>
                <td><?= htmlspecialchars($c['petitioner']) ?></td>
                <td><?= htmlspecialchars($c['respondent']) ?></td>
                <td><span class="badge bg-<?= $c['status']=='open'?'success':'secondary' ?>"><?= $c['status'] ?></span></td>
                <td><a href="family_view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>