<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: human-rights.php'); exit; }
$stmt = $pdo->prepare("SELECT * FROM human_rights_cases WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: human-rights.php'); exit; }
$title       = $c['case_title']       ?? $c['title']           ?? '—';
$applicant   = $c['applicant_name']   ?? $c['claimant']        ?? '—';
$rights      = $c['rights_violated']  ?? $c['right_violated']  ?? '—';
$article     = $c['article_relied_on']?? $c['article_section'] ?? '—';
$relief      = $c['relief_sought']    ?? $c['remedy']          ?? '—';
$incidentDate= $c['incident_date']    ?? $c['violation_date']  ?? '—';
$background  = $c['background']       ?? $c['summary']         ?? '—';
$notes       = $c['notes']            ?? $c['grounds']         ?? '—';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Human Rights Case #<?php echo $c['id']; ?> — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;font-size:11pt;color:#000;margin:24px}
h1{font-size:16pt;border-bottom:2px solid #000;padding-bottom:6px;margin-bottom:16px}
h2{font-size:12pt;margin:16px 0 6px}
.meta{font-size:9pt;color:#555;margin-bottom:16px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.field label{font-size:8pt;text-transform:uppercase;color:#777;display:block}
.field .val{font-size:10pt}
pre{white-space:pre-wrap;font-family:inherit;font-size:10pt;border:1px solid #ccc;padding:8px;margin-top:4px}
@media print{body{margin:0}.no-print{display:none}}
</style>
</head>
<body>
<div class="no-print" style="margin-bottom:16px">
  <button onclick="window.print()">🖨 Print</button>
  <a href="hr_view.php?id=<?php echo $c['id']; ?>" style="margin-left:12px">← Back</a>
</div>
<h1>🕊️ Human Rights Case — AEP Legal Platform</h1>
<div class="meta">Case #<?php echo $c['id']; ?> | Generated: <?php echo date('d M Y, H:i'); ?></div>
<div class="grid">
  <div class="field"><label>Case Title</label><div class="val"><?php echo htmlspecialchars($title); ?></div></div>
  <div class="field"><label>Status</label><div class="val"><?php echo ucfirst(htmlspecialchars($c['status'])); ?></div></div>
  <div class="field"><label>Applicant / Claimant</label><div class="val"><?php echo htmlspecialchars($applicant); ?></div></div>
  <div class="field"><label>Respondent</label><div class="val"><?php echo htmlspecialchars($c['respondent'] ?? '—'); ?></div></div>
  <div class="field"><label>Rights Violated</label><div class="val"><?php echo htmlspecialchars($rights); ?></div></div>
  <div class="field"><label>Article / Section</label><div class="val"><?php echo htmlspecialchars($article); ?></div></div>
  <div class="field"><label>Incident Date</label><div class="val"><?php echo htmlspecialchars($incidentDate); ?></div></div>
  <div class="field"><label>Relief Sought</label><div class="val"><?php echo htmlspecialchars($relief); ?></div></div>
</div>
<?php if ($background !== '—'): ?>
<h2>Background / Summary</h2>
<pre><?php echo htmlspecialchars($background); ?></pre>
<?php endif; ?>
<?php if ($notes !== '—'): ?>
<h2>Grounds / Notes</h2>
<pre><?php echo htmlspecialchars($notes); ?></pre>
<?php endif; ?>
</body>
</html>
