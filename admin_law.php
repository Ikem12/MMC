<?php
// FILE: admin_law.php — Administrative Law landing / list page
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$sql = "SELECT * FROM admin_law_cases WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (client_name LIKE ? OR case_reference LIKE ? OR case_title LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total    = count($cases);
$active   = count(array_filter($cases, fn($c) => $c['status'] === 'active'));
$closed   = count(array_filter($cases, fn($c) => in_array($c['status'], ['closed','won','lost'])));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Administrative Law &mdash; AEP Legal Platform</title>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
header{background:#6c3483;color:#fff;padding:24px 20px;text-align:center}
header h1{font-size:1.7rem;margin-bottom:4px}
header p{font-size:0.9rem;opacity:0.85}
nav{background:#4a235a;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
nav a:hover{background:rgba(255,255,255,0.3)}
.container{max-width:1100px;margin:30px auto;padding:0 18px}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px}
.stat-card{background:#fff;border-radius:8px;padding:18px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
.stat-card .num{font-size:2rem;font-weight:bold;color:#6c3483}
.stat-card .lbl{font-size:0.82rem;color:#888;margin-top:4px}
.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px}
.top-bar h2{font-size:1.2rem;color:#4a235a}
.btn{display:inline-block;padding:8px 18px;background:#6c3483;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem;border:none;cursor:pointer}
.btn:hover{background:#4a235a}
.btn-green{background:#28a745}
.btn-green:hover{background:#1e7e34}
.toolbar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.toolbar input,.toolbar select{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem}
.toolbar input{flex:1;min-width:200px}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
thead{background:#6c3483;color:#fff}
th,td{padding:11px 14px;text-align:left;font-size:0.88rem}
tbody tr:nth-child(even){background:#f9f0fa}
tbody tr:hover{background:#f0d6f5}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.78rem;font-weight:bold}
.badge-draft{background:#ecf0f1;color:#7f8c8d}
.badge-active{background:#d5f5e3;color:#27ae60}
.badge-court{background:#fde8d8;color:#e67e22}
.badge-tribunal{background:#e8daef;color:#6c3483}
.badge-won{background:#d5f5e3;color:#27ae60}
.badge-lost{background:#fadbd8;color:#c0392b}
.badge-settled{background:#d6eaf8;color:#2980b9}
.badge-withdrawn{background:#fef9e7;color:#f39c12}
.badge-closed{background:#ecf0f1;color:#7f8c8d}
.empty{text-align:center;padding:40px;color:#888;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
</style>
</head>
<body>
<header>
  <h1>🏛️ Administrative Law</h1>
  <p>Judicial review, tribunal proceedings, public law and regulatory matters</p>
</header>
<nav>
  <a href="index.php">🏠 Home</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="admin_law_create.php">New Case</a>
  <a href="tort.php">Tort Law</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="logout.php">Logout</a>
</nav>
<div class="container">

  <div class="stats">
    <div class="stat-card"><div class="num"><?php echo $total; ?></div><div class="lbl">Total Cases</div></div>
    <div class="stat-card"><div class="num"><?php echo $active; ?></div><div class="lbl">Active Cases</div></div>
    <div class="stat-card"><div class="num"><?php echo $closed; ?></div><div class="lbl">Closed / Resolved</div></div>
  </div>

  <div class="top-bar">
    <h2>All Administrative Law Cases</h2>
    <a href="admin_law_create.php" class="btn btn-green">+ New Case</a>
  </div>

  <div class="toolbar">
    <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap">
      <input type="text" name="search" placeholder="Search by client, reference or case title..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach(['draft','active','court','tribunal','won','lost','settled','withdrawn','closed'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn">🔍 Search</button>
      <a href="admin_law.php" class="btn">✖ Clear</a>
    </form>
  </div>

  <?php if (empty($cases)): ?>
    <div class="empty">
      <p style="font-size:1.1rem;margin-bottom:12px">No administrative law cases found.</p>
      <a href="admin_law_create.php" class="btn btn-green">+ Create First Case</a>
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Case Reference</th>
          <th>Case Title / Client</th>
          <th>Case Type</th>
          <th>Status</th>
          <th>Date Filed</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $c): ?>
        <tr>
          <td><?php echo $c['id']; ?></td>
          <td><?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></td>
          <td><strong><?php echo htmlspecialchars($c['case_title'] ?: $c['client_name'] ?: 'N/A'); ?></strong></td>
          <td><?php echo htmlspecialchars($c['case_type'] ?: '—'); ?></td>
          <td><span class="badge badge-<?php echo htmlspecialchars($c['status'] ?? 'draft'); ?>"><?php echo ucfirst(htmlspecialchars($c['status'] ?? 'draft')); ?></span></td>
          <td><?php echo $c['date_filed'] ? date('d/m/Y', strtotime($c['date_filed'])) : '—'; ?></td>
          <td>
            <a href="admin_law_view.php?id=<?php echo $c['id']; ?>" style="color:#6c3483;font-weight:bold;font-size:0.82rem;text-decoration:none;margin-right:8px">👁 View</a>
            <a href="admin_law_print.php?id=<?php echo $c['id']; ?>" style="color:#27ae60;font-weight:bold;font-size:0.82rem;text-decoration:none">🖨 Print</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</div>
</body>
</html>
