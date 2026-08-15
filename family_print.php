<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: family_law.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM family_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: family_law.php'); exit; }

function row($label, $val) {
    if (!$val) return;
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td>' . nl2br(htmlspecialchars($val)) . '</td></tr>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: Family Case &mdash; <?php echo htmlspecialchars($c['title'] ?: 'Case #' . $c['id']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #e74c3c;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#e74c3c}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#e74c3c;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
.section-title.navy{background:#1a3c5e}
.section-title.green{background:#27ae60}
.section-title.purple{background:#8e44ad}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#e74c3c;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="family_view.php?id=<?php echo $c['id']; ?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>

<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>FAMILY LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['title'] ?: 'Case #' . $c['id']); ?></p>
  <p>Case Type: <?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?> | Status: <?php echo ucfirst($c['status'] ?? 'open'); ?> | Printed: <?php echo date('d/m/Y H:i'); ?></p>
</div>

<div class="section">
  <div class="section-title navy">1. CASE DETAILS</div>
  <table><?php row('Case Title', $c['title']); row('Case Type', $c['case_type']); row('Status', ucfirst($c['status'] ?? 'open')); row('Court', $c['court']); row('Created', substr($c['created_at'] ?? '', 0, 10)); ?></table>
</div>

<div class="section">
  <div class="section-title">2. PARTIES</div>
  <table><?php row('Petitioner', $c['petitioner']); row('Respondent', $c['respondent']); row('Children', $c['children']); ?></table>
</div>

<div class="section">
  <div class="section-title green">3. RELIEF SOUGHT</div>
  <table><?php row('Relief Sought', $c['relief']); ?></table>
</div>

<?php if (!empty($c['summary']) || !empty($c['notes'])): ?>
<div class="section">
  <div class="section-title purple">4. SUMMARY &amp; NOTES</div>
  <table><?php row('Summary', $c['summary']); row('Notes', $c['notes']); ?></table>
</div>
<?php endif; ?>
</body>
</html>
