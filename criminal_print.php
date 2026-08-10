<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: criminal_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM criminal_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: criminal_list.php'); exit; }
function row($label,$val){if(!$val)return;echo '<tr><th>'.htmlspecialchars($label).'</th><td>'.nl2br(htmlspecialchars($val)).'</td></tr>';}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: R v <?php echo htmlspecialchars($c['defendant_name']);?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #2c3e50;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#2c3e50}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#2c3e50;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#2c3e50;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="criminal_view.php?id=<?php echo $c['id'];?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>
<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>CRIMINAL LAW CASE RECORD</h2>
  <p>R v <?php echo htmlspecialchars($c['defendant_name']);?></p>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?> | Printed: <?php echo date('d/m/Y H:i');?></p>
</div>
<div class="section"><div class="section-title">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('Court Reference',$c['court_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
<div class="section"><div class="section-title">2. DEFENDANT</div><table><?php row('Full Name',$c['defendant_name']);row('Date of Birth',$c['defendant_dob']);row('Nationality',$c['defendant_nationality']);row('Custody Status',$c['defendant_custody']);row('Email',$c['defendant_email']);row('Phone',$c['defendant_phone']);row('Address',$c['defendant_address']);?></table></div>
<div class="section"><div class="section-title">3. OFFENCE</div><table><?php row('Offence Date',$c['offence_date']);row('Location',$c['offence_location']);row('Description',$c['offence_description']);row('Charge Date',$c['charge_date']);row('Charges',$c['charges']);row('Plea',$c['plea']);?></table></div>
<div class="section"><div class="section-title">4. COURT</div><table><?php row('Court Name',$c['court_name']);row('Hearing Date',$c['hearing_date']);row('Trial Date',$c['trial_date']);row('Judge',$c['judge_name']);row('Prosecution',$c['prosecution_name']);?></table></div>
<div class="section"><div class="section-title">5. BAIL &amp; LEGAL AID</div><table><?php row('Bail Status',$c['bail_status']);row('Bail Conditions',$c['bail_conditions']);row('Legal Aid',$c['legal_aid']);row('Legal Aid Reference',$c['legal_aid_reference']);?></table></div>
<div class="section"><div class="section-title">6. LEGAL SUBMISSIONS &amp; OUTCOME</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);row('Sentence',$c['sentence']);row('Outcome',$c['outcome']);?></table></div>
</body>
</html>