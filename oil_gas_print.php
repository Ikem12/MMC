<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: oil_gas_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM oil_gas_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: oil_gas_list.php'); exit; }
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
.header{text-align:center;border-bottom:3px solid #16a085;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#16a085}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#16a085;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:6px 10px;text-align:left;font-size:0.82rem;background:#f5f5f5;border:1px solid #ddd;font-weight:bold}
td{padding:6px 10px;font-size:0.85rem;border:1px solid #ddd}
.no-print{margin-bottom:16px}
@media print{.no-print{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="no-print">
  <button onclick="window.print()" style="padding:8px 20px;background:#16a085;color:#fff;border:none;border-radius:4px;cursor:pointer;">&#128424; Print / Save as PDF</button>
  <a href="oil_gas_view.php?id=<?php echo $c['id'];?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>
<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>OIL &amp; GAS LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['client_name']);?> v <?php echo htmlspecialchars($c['opponent_name']?:'Unknown');?></p>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?> | Printed: <?php echo date('d/m/Y H:i');?></p>
</div>
<div class="section"><div class="section-title">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
<div class="section"><div class="section-title">2. CLIENT DETAILS</div><table><?php row('Name / Company',$c['client_name']);row('Email',$c['client_email']);row('Phone',$c['client_phone']);row('Address',$c['client_address']);?></table></div>
<div class="section"><div class="section-title">3. OPPONENT</div><table><?php row('Opponent',$c['opponent_name']);row('Contact',$c['opponent_contact']);row('Address',$c['opponent_address']);?></table></div>
<div class="section"><div class="section-title">4. LICENCE &amp; CONTRACT</div><table><?php row('Licence Number',$c['licence_number']);row('Licence Type',$c['licence_type']);row('Licence Area',$c['licence_area']);row('Contract Date',$c['contract_date']);row('Contract Value',$c['contract_value']);row('Contract Description',$c['contract_description']);?></table></div>
<div class="section"><div class="section-title">5. DISPUTE DETAILS</div><table><?php row('Dispute Date',$c['dispute_date']);row('Regulatory Body',$c['regulatory_body']);row('Dispute Description',$c['dispute_description']);row('Court / Arbitration Issued',$c['court_issued']);row('Court / Arbitration Name',$c['court_name']);row('Hearing Date',$c['court_date']);?></table></div>
<div class="section"><div class="section-title">6. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);row('Settlement Offers',$c['settlement_offers']);row('Without Prejudice',$c['without_prejudice']);row('Outcome',$c['outcome']);?></table></div>
</body>
</html>