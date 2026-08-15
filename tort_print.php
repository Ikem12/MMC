<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: tort_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM tort_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: tort_list.php'); exit; }

function row($label, $val) {
    if (!$val) return;
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td>' . nl2br(htmlspecialchars($val)) . '</td></tr>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: Tort Case &mdash; <?php echo htmlspecialchars($c['claimant_name'] ?: 'Case #' . $c['id']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #6a0572;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#6a0572}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#6a0572;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
.section-title.navy{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.green{background:#27ae60}
.section-title.orange{background:#e67e22}
.section-title.teal{background:#16a085}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#6a0572;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="tort_view.php?id=<?php echo $c['id']; ?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>

<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>TORT LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['claimant_name'] ?: 'Unknown'); ?> v <?php echo htmlspecialchars($c['defendant_name'] ?: 'Unknown'); ?></p>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> | Status: <?php echo ucfirst($c['status'] ?? 'draft'); ?> | Printed: <?php echo date('d/m/Y H:i'); ?></p>
</div>

<div class="section">
  <div class="section-title navy">1. CASE DETAILS</div>
  <table><?php row('Case Type', $c['case_type']); row('Case Reference', $c['case_reference']); row('Status', ucfirst($c['status'] ?? 'draft')); row('Lawyer', $c['lawyer_name']); row('Law Firm', $c['law_firm']); ?></table>
</div>

<div class="section">
  <div class="section-title">2. CLAIMANT</div>
  <table><?php row('Full Name', $c['claimant_name']); row('Date of Birth', $c['claimant_dob']); row('Email', $c['claimant_email']); row('Phone', $c['claimant_phone']); row('Address', $c['claimant_address']); ?></table>
</div>

<div class="section">
  <div class="section-title red">3. DEFENDANT</div>
  <table><?php row('Defendant Name', $c['defendant_name']); row('Contact', $c['defendant_contact']); row('Address', $c['defendant_address']); ?></table>
</div>

<div class="section">
  <div class="section-title orange">4. INCIDENT</div>
  <table><?php row('Incident Date', $c['incident_date']); row('Location', $c['incident_location']); row('Description', $c['incident_description']); ?></table>
</div>

<div class="section">
  <div class="section-title">5. INJURY &amp; DAMAGES</div>
  <table><?php row('Injury Description', $c['injury_description']); row('Medical Treatment', $c['medical_treatment']); row('Prognosis', $c['prognosis']); row('Total Damages Claimed', $c['damages_claimed']); row('Special Damages', $c['special_damages']); row('General Damages', $c['general_damages']); ?></table>
</div>

<div class="section">
  <div class="section-title green">6. LIABILITY &amp; COURT</div>
  <table><?php row('Liability Admitted', $c['liability_admitted']); row('Contributory Negligence', $c['contributory_negligence']); row('Court Proceedings Issued', $c['court_issued']); row('Court Name', $c['court_name']); row('Court Date', $c['court_date']); ?></table>
</div>

<div class="section">
  <div class="section-title teal">7. LEGAL SUBMISSIONS &amp; OUTCOME</div>
  <table><?php row('Legal Basis', $c['legal_basis']); row('Evidence Available', $c['evidence_available']); row('Representations', $c['representations']); row('Settlement Offers', $c['settlement_offers']); row('Without Prejudice', $c['without_prejudice']); row('Outcome / Notes', $c['outcome']); ?></table>
</div>
</body>
</html>
