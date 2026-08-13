<?php
require_once 'auth.php';
require_once 'init_db.php';

$stmt = $pdo->query("SELECT * FROM company_cases ORDER BY created_at DESC");
$cases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Company Law Cases</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🏢 Company Law Cases</h2>
        <a href="company_create.php" class="btn btn-success">+ New Case</a>
    </div>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Company Name</th>
                <th>RC Number</th>
                <th>Issue Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($cases as $i => $c): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= htmlspecialchars($c['company_name']) ?></td>
                <td><?= htmlspecialchars($c['rc_number']) ?></td>
                <td><?= htmlspecialchars($c['issue_type']) ?></td>
                <td><span class="badge bg-<?= $c['status']=='open'?'success':'secondary' ?>"><?= $c['status'] ?></span></td>
                <td><a href="company_view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>