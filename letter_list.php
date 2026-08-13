<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM draft_letters WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: letter_list.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM draft_letters WHERE recipient_name LIKE ? OR ref_no LIKE ? OR letter_type LIKE ? OR subject LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM draft_letters ORDER BY created_at DESC");
}
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Draft Letters — AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar a{color:#fff;text-decoration:none;font-size:0.9rem}
.topbar a:hover{text-decoration:underline}
.container{max-width:1100px;margin:30px auto;padding:0 20px}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
h2{color:#1a3c5e;font-size:1.3rem}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-danger{background:#dc3545;color:#fff}
.btn-danger:hover{background:#b02a37}
.btn-info{background:#17a2b8;color:#fff}
.btn-info:hover{background:#117a8b}
.btn-warning{background:#ffc107;color:#333}
.btn-warning:hover{background:#e0a800}
.search-bar{display:flex;gap:10px;margin-bottom:20px}
.search-bar input{flex:1;padding:9px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem}
.search-bar button{padding:9px 20px;background:#1a3c5e;color:#fff;border:none;border-radius:4px;cursor:pointer}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
table{width:100%;border-collapse:collapse;font-size:0.88rem}
th{background:#1a3c5e;color:#fff;padding:11px 12px;text-align:left}
td{padding:10px 12px;border-bottom:1px solid #f0f0f0;vertical-align:top}
tr:hover td{background:#f8f9ff}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-final{background:#d4edda;color:#155724}
.badge-sent{background:#cce5ff;color:#004085}
.empty{text-align:center;padding:40px;color:#888}
.action-btns{display:flex;gap:6px;flex-wrap:wrap}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:8px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06)}
.stat-number{font-size:1.8rem;font-weight:bold;color:#1a3c5e}
.stat-label{font-size:0.8rem;color:#888;margin-top:4px}
</style>
</head>
<body>

<div class="topbar">
  <strong>⚖️ AEP Legal Platform</strong>
  <div style="display:flex;gap:20px">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="letter_create.php">✉️ New Letter</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">

  <!-- STATS -->
  <?php
    $total = $pdo->query("SELECT COUNT(*) FROM draft_letters")->fetchColumn();
    $draft = $pdo->query("SELECT COUNT(*) FROM draft_letters WHERE status='draft'")->fetchColumn();
    $sent  = $pdo->query("SELECT COUNT(*) FROM draft_letters WHERE status='sent'")->fetchColumn();
  ?>
  <div class="stats">
    <div class="stat-card">
      <div class="stat-number"><?php echo $total; ?></div>
      <div class="stat-label">Total Letters</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?php echo $draft; ?></div>
      <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?php echo $sent; ?></div>
      <div class="stat-label">Sent</div>
    </div>
  </div>

  <div class="page-header">
    <h2>✉️ Draft Letters</h2>
    <a href="letter_create.php" class="btn btn-primary">➕ New Letter</a>
  </div>

  <!-- SEARCH -->
  <form method="GET" class="search-bar">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
           placeholder="Search by recipient, ref no, type or subject..."/>
    <button type="submit">🔍 Search</button>
    <?php if ($search): ?>
      <a href="letter_list.php" class="btn btn-warning">✖ Clear</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <?php if (empty($records)): ?>
      <div class="empty">
        <p style="font-size:2rem">✉️</p>
        <p>No letters found.</p>
        <a href="letter_create.php" class="btn btn-primary" style="margin-top:12px">➕ Draft First Letter</a>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Ref No</th>
            <th>Letter Type</th>
            <th>Recipient</th>
            <th>Subject</th>
            <th>Signatory</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $i => $r): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($r['ref_no'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($r['letter_type'] ?: '—'); ?></td>
            <td><strong><?php echo htmlspecialchars($r['recipient_name']); ?></strong></td>
            <td><?php echo htmlspecialchars(substr($r['subject'], 0, 40)) . (strlen($r['subject']) > 40 ? '...' : ''); ?></td>
            <td><?php echo htmlspecialchars($r['signatory_name'] ?: '—'); ?></td>
            <td>
              <span class="badge badge-<?php echo $r['status']; ?>">
                <?php echo ucfirst($r['status']); ?>
              </span>
            </td>
            <td><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
            <td>
              <div class="action-btns">
                <a href="letter_view.php?id=<?php echo $r['id']; ?>" class="btn btn-info">👁 View</a>
                <a href="letter_print.php?id=<?php echo $r['id']; ?>" class="btn btn-primary" target="_blank">🖨 Print</a>
                <a href="letter_list.php?delete=<?php echo $r['id']; ?>" class="btn btn-danger"
                   onclick="return confirm('Delete this letter?')">🗑 Delete</a>
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