<?php
// FILE: admin.php — Admin Panel for AEP Legal Platform
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo "<!doctype html><html><head><title>Access Denied</title></head><body style='font-family:Arial;max-width:500px;margin:60px auto;text-align:center'>";
    echo "<h2>&#128683; Access Denied</h2><p>Admin access required.</p><a href='login.php'>&#8592; Login</a></body></html>";
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$notice = '';

// Handle user delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $notice = 'Invalid CSRF token.';
    } else {
        $uid = (int)($_POST['uid'] ?? 0);
        if ($uid === 1) {
            $notice = 'Cannot delete the protected seed user.';
        } else {
            $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['is_admin']) {
                $cnt = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1")->fetchColumn();
                if ($cnt <= 1) { $notice = 'Cannot delete the only admin user.'; }
                else { $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]); $notice = 'User deleted.'; }
            } else {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
                $notice = 'User deleted.';
            }
        }
    }
}

// Stats
$tables = ['criminal_cases','family_cases','tort_cases','employment_cases','company_cases',
           'oil_gas_cases','immigration_cases','admin_law_cases','human_rights_cases',
           'legal_advice','draft_letters','witness_statements','latin_maxims','users'];

$stats = [];
foreach ($tables as $t) {
    try {
        $stats[$t] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    } catch (Exception $e) {
        $stats[$t] = 0;
    }
}

$users = $pdo->query("SELECT id, username, is_admin, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Admin Panel &mdash; AEP Legal Platform</title>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#1a3c5e,#2c3e50);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1100px;margin:0 auto;padding:0 28px 40px}
.notice{padding:12px 16px;background:#d4edda;color:#155724;border-radius:4px;margin-bottom:20px;font-size:0.9rem}
h2{font-size:1.1rem;color:#1a3c5e;margin-bottom:14px;border-bottom:2px solid #1a3c5e;padding-bottom:6px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:32px}
.stat-card{background:#fff;border-radius:8px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
.stat-card .num{font-size:1.8rem;font-weight:bold;color:#1a3c5e}
.stat-card .lbl{font-size:0.75rem;color:#888;margin-top:4px;text-transform:capitalize}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
table{width:100%;border-collapse:collapse;font-size:0.88rem}
th{background:#1a3c5e;color:#fff;padding:10px 14px;text-align:left;font-size:0.82rem}
td{padding:9px 14px;border-bottom:1px solid #f0f0f0}
tr:hover td{background:#f9f9f9}
.badge-admin{background:#d4edda;color:#155724;padding:2px 8px;border-radius:12px;font-size:0.75rem;font-weight:bold}
.badge-user{background:#e2e3e5;color:#383d41;padding:2px 8px;border-radius:12px;font-size:0.75rem}
.btn{padding:8px 18px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-danger{background:#dc3545;color:#fff}
.btn-sm{padding:4px 10px;font-size:0.78rem}
.links-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:32px}
.link-card{background:#fff;border-radius:8px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.07);text-decoration:none;color:#1a3c5e;font-weight:bold;font-size:0.9rem;border-left:4px solid #1a3c5e}
.link-card:hover{background:#f0f5ff}
.link-card .sub{font-size:0.78rem;color:#888;font-weight:normal;margin-top:3px}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="users.php">&#128100; Users</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#9881;&#65039; Admin Panel</h1>
  <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></p>
</div>
<div class="container">

  <?php if ($notice): ?>
    <div class="notice">&#10003; <?php echo htmlspecialchars($notice); ?></div>
  <?php endif; ?>

  <!-- Database Stats -->
  <h2>&#128202; Database Overview</h2>
  <div class="stats-grid">
    <?php
    $labels = [
        'criminal_cases' => '&#9878; Criminal',
        'family_cases' => '&#128106; Family',
        'tort_cases' => '&#9888; Tort',
        'employment_cases' => '&#128188; Employment',
        'company_cases' => '&#127970; Company',
        'oil_gas_cases' => '&#128165; Oil &amp; Gas',
        'immigration_cases' => '&#9992; Immigration',
        'admin_law_cases' => '&#127963; Admin Law',
        'human_rights_cases' => '&#128250; Human Rights',
        'legal_advice' => '&#128196; Legal Advice',
        'draft_letters' => '&#128140; Letters',
        'witness_statements' => '&#128104;&#8205;&#9878; Witnesses',
        'latin_maxims' => '&#9878; Latin Maxims',
        'users' => '&#128100; Users',
    ];
    foreach ($stats as $table => $count): ?>
    <div class="stat-card">
      <div class="num"><?php echo $count; ?></div>
      <div class="lbl"><?php echo $labels[$table] ?? $table; ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick Links -->
  <h2>&#128279; Quick Links</h2>
  <div class="links-grid">
    <a href="dashboard.php" class="link-card">&#127968; Dashboard<div class="sub">Main dashboard</div></a>
    <a href="criminal_list.php" class="link-card">&#9878; Criminal Cases<div class="sub">View all criminal cases</div></a>
    <a href="family_law.php" class="link-card">&#128106; Family Law<div class="sub">View family cases</div></a>
    <a href="tort_list.php" class="link-card">&#9888; Tort Cases<div class="sub">View tort cases</div></a>
    <a href="employment_list.php" class="link-card">&#128188; Employment<div class="sub">Employment cases</div></a>
    <a href="oil_gas_list.php" class="link-card">&#128165; Oil &amp; Gas<div class="sub">Oil &amp; gas cases</div></a>
    <a href="advice_list.php" class="link-card">&#128196; Legal Advice<div class="sub">All advice records</div></a>
    <a href="latin_maxims.php" class="link-card">&#9878; Latin Maxims<div class="sub">Legal maxims database</div></a>
    <a href="users.php" class="link-card">&#128100; Manage Users<div class="sub">User administration</div></a>
    <a href="register.php" class="link-card">&#10133; Add User<div class="sub">Register new user</div></a>
  </div>

  <!-- User Management -->
  <div class="card">
    <h2 style="border:none;padding:0;margin-bottom:16px">&#128100; User Accounts</h2>
    <?php if (empty($users)): ?>
      <p style="color:#888">No users found.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr><th>#</th><th>Username</th><th>Role</th><th>Registered</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?php echo (int)$u['id']; ?></td>
          <td><?php echo htmlspecialchars($u['username']); ?></td>
          <td><?php echo $u['is_admin'] ? '<span class="badge-admin">Admin</span>' : '<span class="badge-user">User</span>'; ?></td>
          <td><?php echo htmlspecialchars(substr($u['created_at'], 0, 10)); ?></td>
          <td>
            <?php if ((int)$u['id'] !== 1): ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete user <?php echo htmlspecialchars($u['username']); ?>?')">
              <input type="hidden" name="action" value="delete_user"/>
              <input type="hidden" name="uid" value="<?php echo (int)$u['id']; ?>"/>
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>"/>
              <button type="submit" class="btn btn-danger btn-sm">&#128465; Delete</button>
            </form>
            <?php else: ?>
              <span style="color:#888;font-size:0.78rem">Protected</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <div style="margin-top:14px">
      <a href="register.php" class="btn btn-primary">&#10133; Add New User</a>
    </div>
  </div>

</div>
</body>
</html>
