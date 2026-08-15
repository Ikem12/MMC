<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . '/csrf.php';
csrf_token(); // ensure token exists

// Handle delete (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!csrf_verify()) { die('Invalid CSRF token.'); }
    $pdo->prepare("DELETE FROM employment_cases WHERE id = ?")->execute([(int)$_POST['delete_id']]);
    header('Location: employment_list.php');
    exit;
}

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM employment_cases WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (claimant_name LIKE ? OR case_reference LIKE ? OR respondent_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Employment Cases — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.topbar a:hover{text-decoration:underline}
.hero{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.search-row{display:flex;gap:10px;flex-wrap:wrap}
.search-row input,.search-row select{padding:9px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-primary{background:#c0392b;color:#fff}
.btn-primary:hover{background:#a93226}
.btn-sm{padding:5px 12px;font-size:0.78rem}
.btn-view{background:#1a3c5e;color:#fff}
.btn-view:hover{background:#122840}
.btn-edit{background:#e67e22;color:#fff}
.btn-edit:hover{background:#ca6f1e}
.btn-delete{background:#e74c3c;color:#fff}
.btn-delete:hover{background:#c0392b}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-box{background:#fff;border-radius:8px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.07);border-top:3px solid #c0392b}
.stat-box .num{font-size:1.6rem;font-weight:bold;color:#c0392b}
.stat-box .lbl{font-size:0.75rem;color:#888;margin-top:2px}
.card{background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.07);overflow:hidden}
table{width:100%;border-collapse:collapse;font-size:0.87rem}
th{background:#f4f6f9;color:#555;padding:11px 14px;text-align:left;font-size:0.78rem;text-transform:uppercase}
td{padding:11px 14px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fef9f9}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.72rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-active{background:#cce5ff;color:#004085}
.badge-tribunal{background:#d6d8f7;color:#2c2f8a}
.badge-settled{background:#d4edda;color:#155724}
.badge-won{background:#d4edda;color:#155724}
.badge-lost{background:#f8d7da;color:#721c24}
.badge-withdrawn{background:#e2e3e5;color:#383d41}
.badge-closed{background:#e2e3e5;color:#383d41}
.empty{text-align:center;padding:50px;color:#888}
.empty-icon{font-size:2.5rem;margin-bottom:12px}
.actions{display:flex;gap:6px}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">⚖️ AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="employment_create.php">➕ New Case</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="hero">
  <h1>💼 Employment Law Cases</h1>
  <p>Manage all employment tribunal cases, claims and representations.</p>
</div>

<div class="container">

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-box">
      <div class="num"><?php echo count($cases); ?></div>
      <div class="lbl">Total Cases</div>
    </div>
    <div class="stat-box">
      <div class="num"><?php echo count(array_filter($cases, fn($c) => $c['status'] === 'active')); ?></div>
      <div class="lbl">Active</div>
    </div>
    <div class="stat-box">
      <div class="num"><?php echo count(array_filter($cases, fn($c) => $c['status'] === 'tribunal')); ?></div>
      <div class="lbl">At Tribunal</div>
    </div>
    <div class="stat-box">
      <div class="num"><?php echo count(array_filter($cases, fn($c) => $c['status'] === 'settled')); ?></div>
      <div class="lbl">Settled</div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <form method="GET" class="search-row">
      <input type="text" name="search" placeholder="Search claimant, reference..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="status">
        <option value="">All Statuses</option>
        <option value="draft" <?php echo $status==='draft'?'selected':''; ?>>Draft</option>
        <option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
        <option value="tribunal" <?php echo $status==='tribunal'?'selected':''; ?>>At Tribunal</option>
        <option value="settled" <?php echo $status==='settled'?'selected':''; ?>>Settled</option>
        <option value="won" <?php echo $status==='won'?'selected':''; ?>>Won</option>
        <option value="lost" <?php echo $status==='lost'?'selected':''; ?>>Lost</option>
        <option value="withdrawn" <?php echo $status==='withdrawn'?'selected':''; ?>>Withdrawn</option>
        <option value="closed" <?php echo $status==='closed'?'selected':''; ?>>Closed</option>
      </select>
      <button type="submit" class="btn btn-primary">🔍 Search</button>
      <a href="employment_list.php" class="btn" style="background:#6c757d;color:#fff">Reset</a>
    </form>
    <a href="employment_create.php" class="btn btn-primary">➕ New Employment Case</a>
  </div>

  <!-- TABLE -->
  <div class="card">
    <?php if (empty($cases)): ?>
      <div class="empty">
        <div class="empty-icon">💼</div>
        <p>No employment cases found.</p>
        <br/>
        <a href="employment_create.php" class="btn btn-primary">➕ Create First Case</a>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Case Reference</th>
            <th>Claimant</th>
            <th>Respondent</th>
            <th>Case Type</th>
            <th>Hearing Date</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cases as $i => $c): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><strong><?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></strong></td>
            <td><?php echo htmlspecialchars($c['claimant_name']); ?></td>
            <td><?php echo htmlspecialchars($c['respondent_name']); ?></td>
            <td><?php echo htmlspecialchars($c['case_type']); ?></td>
            <td><?php echo $c['hearing_date'] ? date('d M Y', strtotime($c['hearing_date'])) : '—'; ?></td>
            <td><span class="badge badge-<?php echo htmlspecialchars(); ?>"><?php echo ucfirst(htmlspecialchars()); ?></span></td>
            <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
            <td>
              <div class="actions">
                <a href="employment_view.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-view">👁 View</a>
                <a href="employment_print.php?id=<?php echo $c['id']; ?>" class="btn btn-sm" style="background:#27ae60;color:#fff">🖨 Print</a>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this case?')">
                  <?php echo csrf_input(); ?>
                  <input type="hidden" name="delete_id" value="<?php echo (int)$c['id']; ?>"/>
                  <button type="submit" class="btn btn-danger">🗑 Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>
</body>
</html>