<?php
// FILE: users.php
// Admin-only user management for AEP Legal Platform

session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo "403 Forbidden — admin access required. <a href='login.php'>Login</a>";
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$notice = '';

// Handle POST delete with CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $notice = 'Invalid request token.';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_admin = 1");
        $stmt->execute();
        $adminCount = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['is_admin'] && $adminCount <= 1) {
            $notice = "Cannot delete the only admin user.";
        } elseif ($id === 1) {
            $notice = "Cannot delete the protected seed user.";
        } else {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            $notice = "User deleted (id={$id}).";
        }
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
  <h1>Users</h1>

  <p><a class="button" href="dashboard.php">Dashboard</a> <a href="index.php" style="margin-left:8px">Home</a></p>

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
              <?php if ((int)$u['id'] !== 1): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete user?')">
                  <input type="hidden" name="action" value="delete"/>
                  <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"/>
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>"/>
                  <button type="submit" style="background:#dc3545;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer">Delete</button>
                </form>
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