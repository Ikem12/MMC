<?php
// FILE: users.php
// Unprotected user list for AEP Legal Platform
// WARNING: This page is intentionally unprotected. Keep server on localhost.

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$notice = '';
$action = $_GET['action'] ?? '';

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // avoid deleting last admin (basic safety: ensure at least one admin remains)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_admin = 1");
    $stmt->execute();
    $adminCount = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['is_admin'] && $adminCount <= 1) {
        $notice = "Cannot delete the only admin user.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $notice = "User deleted (id={$id}).";
    }
}

// fetch users
$stmt = $pdo->query("SELECT id, username, is_admin, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Users — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;max-width:980px;margin:24px auto;padding:18px;color:#222}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:8px;border:1px solid #e6e6e6;text-align:left}
    th{background:#f5f8fb}
    a.button{display:inline-block;padding:6px 10px;background:#0b63a8;color:#fff;text-decoration:none;border-radius:4px}
    .notice{margin-top:12px;color:#2a8a2a}
    .warn{color:#aa3300}
  </style>
</head>
<body>
  <h1>Users <span style="font-size:12px;color:#666">FILE: users.php (UNPROTECTED)</span></h1>

  <p><a class="button" href="admin.php">Admin</a> <a href="index.php" style="margin-left:8px">Home</a></p>

  <?php if ($notice): ?>
    <div class="notice"><?php echo nl2br(htmlspecialchars($notice)); ?></div>
  <?php endif; ?>

  <?php if (empty($users)): ?>
    <p>No users found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Admin</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo htmlspecialchars($u['id']); ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo $u['is_admin'] ? 'Yes' : 'No'; ?></td>
            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
            <td>
              <?php if ((int)$u['id'] !== 1): /* allow deletion except id=1 seed user as extra safety */ ?>
                <a href="?action=delete&id=<?php echo urlencode($u['id']); ?>" onclick="return confirm('Delete user?');">Delete</a>
              <?php else: ?>
                <span class="warn">Protected</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>