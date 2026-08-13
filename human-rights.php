<?php
// FILE: human-rights.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cases = $pdo->query("SELECT * FROM human_rights_cases ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Human Rights Law — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#b5451b;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.7rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#b5451b;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#7a2e0e;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:1100px;margin:30px auto;padding:0 18px}
    .top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px}
    .top-bar h2{font-size:1.2rem;color:#7a2e0e}
    .btn{display:inline-block;padding:8px 18px;background:#b5451b;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem}
    .btn:hover{background:#7a2e0e}
    .btn-green{background:#28a745}
    .btn-green:hover{background:#1e7e34}
    table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
    thead{background:#b5451b;color:#fff}
    th,td{padding:11px 14px;text-align:left;font-size:0.9rem}
    tbody tr:nth-child(even){background:#fdf2ee}
    tbody tr:hover{background:#f9ddd4}
    .badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:bold}
    .badge-open{background:#d4edda;color:#155724}
    .badge-pending{background:#fff3cd;color:#856404}
    .badge-closed{background:#f8d7da;color:#721c24}
    .empty{text-align:center;padding:40px;color:#888;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
    .checklist-btn{display:inline-block;padding:5px 12px;background:#6c757d;color:#fff;border-radius:4px;text-decoration:none;font-size:0.82rem}
    .checklist-btn:hover{background:#495057}
  </style>
</head>
<body>

<header>
  <h1>🕊️ Human Rights Law <span class="file-tag">FILE: human-rights.php</span></h1>
  <p>Fundamental rights, freedoms and constitutional guarantees</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="hr_create.php">New Case</a>
  <a href="hr_checklist.php">Checklist</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">

  <div class="top-bar">
    <h2>All Human Rights Cases</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a href="hr_checklist.php" class="checklist-btn">📋 Checklist</a>
      <a href="hr_create.php" class="btn btn-green">+ New Case</a>
    </div>
  </div>

  <?php if (empty($cases)): ?>
    <div class="empty">
      <p style="font-size:1.1rem;margin-bottom:12px">No human rights cases yet.</p>
      <a href="hr_create.php" class="btn btn-green">+ Create First Case</a>
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Right Violated</th>
          <th>Claimant</th>
          <th>Respondent</th>
          <th>Article / Section</th>
          <th>Remedy Sought</th>
          <th>Status</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $c): ?>
        <tr>
          <td><?php echo $c['id']; ?></td>
          <td><?php echo htmlspecialchars($c['title']); ?></td>
          <td><?php echo htmlspecialchars($c['right_violated'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars($c['claimant'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars($c['respondent'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars($c['article_section'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars($c['remedy'] ?? '—'); ?></td>
          <td>
            <span class="badge badge-<?php echo $c['status']; ?>">
              <?php echo ucfirst($c['status']); ?>
            </span>
          </td>
          <td><?php echo substr($c['created_at'], 0, 10); ?></td>
          <td><a href="hr_view.php?id=<?php echo $c['id']; ?>" class="btn" style="padding:4px 10px;font-size:0.82rem">View</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</div>
</body>
</html>