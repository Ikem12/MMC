<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: immigration_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM immigration_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: immigration_list.php'); exit; }
function row($label,$val){if(!$val)return;echo '<tr><th>'.htmlspecialchars($label).'</th><td>'.nl2br(htmlspecialchars($val)).'</td></tr>';}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: <?php echo htmlspecialchars($c['client_name']);?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #2980b9;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#2980b9}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#2980b9;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#2980b9;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="immigration_view.php?id=<?php echo $c['id'];?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>
<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>IMMIGRATION LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['client_name']);?> | <?php echo htmlspecialchars($c['case_type']?:'N/A');?></p>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?> | Printed: <?php echo date('d/m/Y H:i');?></p>
</div>
<div class="section"><div class="section-title">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('HO Reference',$c['home_office_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
<div class="section"><div class="section-title">2. CLIENT DETAILS</div><table><?php row('Full Name',$c['client_name']);row('Date of Birth',$c['client_dob']);row('Nationality',$c['client_nationality']);row('Passport',$c['client_passport']);row('Email',$c['client_email']);row('Phone',$c['client_phone']);row('Address',$c['client_address']);row('Entry to UK',$c['client_entry_date']);row('Visa Type',$c['client_visa_type']);row('Visa Expiry',$c['client_visa_expiry']);row('Leave Type',$c['client_leave_type']);?></table></div>
<div class="section"><div class="section-title">3. SPONSOR</div><table><?php row('Sponsor Name',$c['sponsor_name']);row('Sponsor Licence',$c['sponsor_licence']);row('Sponsor Address',$c['sponsor_address']);?></table></div>
<div class="section"><div class="section-title">4. HOME OFFICE DECISION</div><table><?php row('Decision Date',$c['decision_date']);row('Decision',$c['decision_description']);?></table></div>
<div class="section"><div class="section-title">5. APPEAL DETAILS</div><table><?php row('Appeal Lodged',$c['appeal_lodged']);row('Appeal Date',$c['appeal_date']);row('Appeal Tribunal',$c['appeal_tribunal']);row('Appeal Reference',$c['appeal_reference']);row('Removal Date',$c['removal_date']);row('Detention Centre',$c['detention_centre']);?></table></div>
<div class="section"><div class="section-title">6. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);?></table></div>
</body>
</html>