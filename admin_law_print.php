<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: admin_law_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM admin_law_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: admin_law_list.php'); exit; }
function row($label, $val) { if (!$val) return; echo '<tr><th>' . htmlspecialchars($label) . '</th><td>' . nl2br(htmlspecialchars($val)) . '</td></tr>'; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Print: <?php echo htmlspecialchars($c['client_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #6c3483;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#6c3483}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#6c3483;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#6c3483;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="admin_law_view.php?id=<?php echo $c['id']; ?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back to Case</a>
</div>
<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>ADMINISTRATIVE LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['client_name']); ?> v <?php echo htmlspecialchars($c['public_body_name'] ?: 'Unknown Body'); ?></p>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> &nbsp;|&nbsp; Status: <?php echo ucfirst($c['status']); ?> &nbsp;|&nbsp; Printed: <?php echo date('d/m/Y H:i'); ?></p>
</div>
<div class="section"><div class="section-title">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']); row('Case Reference',$c['case_reference']); row('Status',ucfirst($c['status'])); row('Lawyer',$c['lawyer_name']); row('Law Firm',$c['law_firm']); ?></table></div>
<div class="section"><div class="section-title">2. CLIENT DETAILS</div><table><?php row('Full Name',$c['client_name']); row('Email',$c['client_email']); row('Phone',$c['client_phone']); row('Address',$c['client_address']); ?></table></div>
<div class="section"><div class="section-title">3. PUBLIC BODY</div><table><?php row('Public Body',$c['public_body_name']); row('Contact',$c['public_body_contact']); row('Address',$c['public_body_address']); ?></table></div>
<div class="section"><div class="section-title">4. DECISION DETAILS</div><table><?php row('Decision Date',$c['decision_date']); row('Decision Description',$c['decision_description']); row('Grounds of Challenge',$c['grounds_of_challenge']); ?></table></div>
<div class="section"><div class="section-title">5. JUDICIAL REVIEW</div><table><?php row('JR Sought',$c['judicial_review']); row('JR Permission Date',$c['jr_permission_date']); row('JR Hearing Date',$c['jr_hearing_date']); row('JR Venue',$c['jr_venue']); ?></table></div>
<div class="section"><div class="section-title">6. TRIBUNAL DETAILS</div><table><?php row('Tribunal Name',$c['tribunal_name']); row('Tribunal Reference',$c['tribunal_reference']); row('Tribunal Hearing',$c['tribunal_hearing_date']); row('Human Rights Article',$c['human_rights_article']); row('Regulatory Body',$c['regulatory_body']); row('Licence Reference',$c['licence_reference']); ?></table></div>
<div class="section"><div class="section-title">7. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']); row('Evidence Available',$c['evidence_available']); row('Representations',$c['representations']); row('Settlement Offers',$c['settlement_offers']); row('Outcome',$c['outcome']); ?></table></div>
</body>
</html>