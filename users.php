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

// Handle POST: create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $notice = 'Invalid request token.';
    } else {
        $newuser = trim($_POST['new_username'] ?? '');
        $newpass = $_POST['new_password'] ?? '';
        $newadmin = isset($_POST['new_is_admin']) ? 1 : 0;
        if ($newuser === '' || strlen($newpass) < 6) {
            $notice = 'Username required and password must be at least 6 characters.';
        } else {
            try {
                $hash = password_hash($newpass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (username, password_hash, is_admin, created_at) VALUES (?, ?, ?, ?)")
                    ->execute([$newuser, $hash, $newadmin, date('c')]);
                $notice = "User '{$newuser}' created successfully.";
            } catch (Exception $e) {
                $notice = 'Username already exists. Choose another.';
            }
        }
    }
}

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

  <h2 style="margin-top:24px;font-size:1rem;color:#1a3c5e">➕ Create New User</h2>
  <form method="POST" style="margin:10px 0 24px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="action" value="create"/>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>"/>
    <div>
      <label style="font-size:0.82rem;font-weight:bold;color:#555;display:block;margin-bottom:4px">Username</label>
      <input type="text" name="new_username" required placeholder="username" style="padding:7px 10px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem"/>
    </div>
    <div>
      <label style="font-size:0.82rem;font-weight:bold;color:#555;display:block;margin-bottom:4px">Password (min 6 chars)</label>
      <input type="password" name="new_password" required placeholder="password" style="padding:7px 10px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem"/>
    </div>
    <div style="padding-bottom:2px">
      <label style="font-size:0.82rem;color:#555"><input type="checkbox" name="new_is_admin" value="1" style="width:auto;margin-right:4px"/>Admin</label>
    </div>
    <button type="submit" style="padding:8px 16px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem">Create User</button>
  </form>

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