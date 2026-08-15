<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: latin_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM latin_maxims WHERE id = ?');
$stmt->execute([$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$m) { header('Location: latin_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: <?php echo htmlspecialchars($m['maxim']); ?> &mdash; AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #2c3e50;padding-bottom:14px;margin-bottom:24px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#2c3e50}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:20px}
.section-title{background:#2c3e50;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
.section-title.navy{background:#1a3c5e}
.section-title.green{background:#27ae60}
table{width:100%;border-collapse:collapse}
th{width:30%;padding:8px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:8px 10px;font-size:0.85rem;border:1px solid #ddd}
.maxim-box{background:#f0f4ff;border:2px solid #2c3e50;border-radius:6px;padding:20px;text-align:center;margin-bottom:24px}
.maxim-box .latin{font-size:1.4rem;font-style:italic;color:#1a3c5e;font-weight:bold}
.maxim-box .meaning{font-size:1rem;color:#555;margin-top:8px}
.details-box{background:#f9f9f9;border:1px solid #ddd;padding:14px;border-radius:4px;font-size:0.88rem;line-height:1.6;white-space:pre-wrap}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#2c3e50;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="latin_maxims_view.php?id=<?php echo $m['id']; ?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
  <a href="latin_list.php" style="margin-left:12px;font-size:0.88rem;">&#128203; All Maxims</a>
</div>

<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>LATIN LEGAL MAXIM</h2>
  <p>Category: <?php echo htmlspecialchars($m['category'] ?? 'General'); ?> &nbsp;|&nbsp; Printed: <?php echo date('d/m/Y H:i'); ?></p>
</div>

<div class="maxim-box">
  <div class="latin">&#8220;<?php echo htmlspecialchars($m['maxim']); ?>&#8221;</div>
  <div class="meaning"><?php echo htmlspecialchars($m['meaning']); ?></div>
</div>

<div class="section">
  <div class="section-title navy">MAXIM DETAILS</div>
  <table>
    <tr><th>Latin Phrase</th><td><em><?php echo htmlspecialchars($m['maxim']); ?></em></td></tr>
    <tr><th>English Meaning</th><td><?php echo htmlspecialchars($m['meaning']); ?></td></tr>
    <tr><th>Category</th><td><?php echo htmlspecialchars($m['category'] ?? 'General'); ?></td></tr>
    <tr><th>Date Added</th><td><?php echo substr($m['created_at'] ?? '', 0, 10); ?></td></tr>
  </table>
</div>

<?php if (!empty($m['details'])): ?>
<div class="section">
  <div class="section-title green">FULL DETAILS &amp; USAGE</div>
  <div class="details-box"><?php echo htmlspecialchars($m['details']); ?></div>
</div>
<?php endif; ?>

</body>
</html>
