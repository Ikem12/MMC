<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: appeal_list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM grounds_of_appeal WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: appeal_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>View Grounds of Appeal — AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar a{color:#fff;text-decoration:none;font-size:0.9rem}
.topbar a:hover{text-decoration:underline}
.container{max-width:900px;margin:30px auto;padding:0 20px}
.card{background:#fff;border-radius:10px;padding:36px;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
.btn-row{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.85rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-secondary{background:#6c757d;color:#fff}
.btn-secondary:hover{background:#545b62}
.btn-danger{background:#dc3545;color:#fff}
.btn-danger:hover{background:#b02a37}
.section{margin-bottom:24px}
.section-title{background:#1a3c5e;color:#fff;padding:8px 14px;border-radius:4px;font-size:0.85rem;font-weight:bold;margin-bottom:12px}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.detail-item{background:#f8f9ff;padding:12px;border-radius:4px;border-left:3px solid #1a3c5e}
.detail-label{font-size:0.75rem;color:#888;font-weight:bold;text-transform:uppercase;margin-bottom:4px}
.detail-value{font-size:0.9rem;color:#333}
.detail-full{background:#f8f9ff;padding:14px;border-radius:4px;border-left:3px solid #1a3c5e}
.detail-full .detail-value{line-height:1.8;white-space:pre-wrap}
.badge{display:inline-block;padding:4px 14px;border-radius:12px;font-size:0.8rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-final{background:#d4edda;color:#155724}
.badge-filed{background:#cce5ff;color:#004085}
.authorities-box{background:#f0f4ff;border:1px solid #c5d0e6;border-radius:6px;padding:14px;font-size:0.88rem;line-height:1.8;white-space:pre-wrap}
.grounds-box{background:#fff8f0;border:1px solid #ffd199;border-radius:6px;padding:14px;font-size:0.9rem;line-height:1.8;white-space:pre-wrap}
h2{color:#1a3c5e;margin-bottom:4px;font-size:1.3rem}
p.sub{color:#888;font-size:0.85rem;margin-bottom:20px}
</style>
</head>
<body>

<div class="topbar">
  <strong>⚖️ AEP Legal Platform</strong>
  <div style="display:flex;gap:20px">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="appeal_list.php">📋 All Appeals</a>
    <a href="appeal_create.php">➕ New Appeal</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">

    <h2>🏛️ Grounds of Appeal Record</h2>
    <p class="sub">
      Appeal No: <strong><?php echo htmlspecialchars($r['case_number'] ?: 'N/A'); ?></strong>
      &nbsp;|&nbsp;
      Date: <strong><?php echo date('d M Y', strtotime($r['created_at'])); ?></strong>
      &nbsp;|&nbsp;
      Status: <span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span>
    </p>

    <div class="btn-row">
      <a href="appeal_print.php?id=<?php echo $r['id']; ?>" class="btn btn-primary" target="_blank">🖨 Print / PDF</a>
      <a href="appeal_list.php" class="btn btn-secondary">← Back to List</a>
      <a href="appeal_list.php?delete=<?php echo $r['id']; ?>" class="btn btn-danger"
         onclick="return confirm('Delete this appeal?')">🗑 Delete</a>
    </div>

    <!-- CASE DETAILS -->
    <div class="section">
      <div class="section-title">⚖️ CASE DETAILS</div>
      <div class="detail-grid">
        <div class="detail-item" style="grid-column:1/-1">
          <div class="detail-label">Case Title</div>
          <div class="detail-value"><strong><?php echo htmlspecialchars($r['case_title']); ?></strong></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Appeal Case Number</div>
          <div class="detail-value"><?php echo htmlspecialchars($r['case_number'] ?: '—'); ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Judgment Date</div>
          <div class="detail-value"><?php echo $r['judgment_date'] ? date('d M Y', strtotime($r['judgment_date'])) : '—'; ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Lower Court</div>
          <div class="detail-value"><?php echo htmlspecialchars($r['lower_court'] ?: '—'); ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Appeal Court</div>
          <div class="detail-value"><?php echo htmlspecialchars($r['appeal_court'] ?: '—'); ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Filing Party</div>
          <div class="detail-value"><?php echo htmlspecialchars($r['party'] ?: '—'); ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Counsel</div>
          <div class="detail-value"><?php echo htmlspecialchars($r['lawyer_name'] ?: '—'); ?></div>
        </div>
      </div>
    </div>

    <!-- INTRODUCTION -->
    <div class="section">
      <div class="section-title">📌 INTRODUCTION</div>
      <div class="detail-full">
        <div class="detail-value"><?php echo nl2br(htmlspecialchars($r['introduction'] ?: '—')); ?></div>
      </div>
    </div>

    <!-- GROUNDS -->
    <div class="section">
      <div class="section-title">📋 GROUNDS OF APPEAL</div>
      <div class="grounds-box">
        <?php echo nl2br(htmlspecialchars($r['grounds'] ?: '—')); ?>
      </div>
    </div>

    <!-- ARGUMENTS -->
    <div class="section">
      <div class="section-title">⚖️ ARGUMENTS IN SUPPORT</div>
      <div class="detail-full">
        <div class="detail-value"><?php echo nl2br(htmlspecialchars($r['arguments'] ?: '—')); ?></div>
      </div>
    </div>

    <!-- AUTHORITIES -->
    <div class="section">
      <div class="section-title">📚 LIST OF AUTHORITIES</div>
      <div class="authorities-box">
        <?php echo nl2br(htmlspecialchars($r['authorities'] ?: '—')); ?>
      </div>
    </div>

    <!-- RELIEF SOUGHT -->
    <div class="section">
      <div class="section-title">🙏 RELIEF SOUGHT</div>
      <div class="detail-full">
        <div class="detail-value"><?php echo nl2br(htmlspecialchars($r['relief_sought'] ?: '—')); ?></div>
      </div>
    </div>

  </div>
</div>
</body>
</html>