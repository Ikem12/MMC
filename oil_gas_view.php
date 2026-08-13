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
<title>Oil &amp; Gas: <?php echo htmlspecialchars($c['client_name']);?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#16a085,#1abc9c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.4rem}
.hero p{font-size:0.85rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:20px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#16a085;padding:10px 16px;border-radius:6px;margin-bottom:14px}
.section-title.navy{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.purple{background:#8e44ad}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:9px 12px;text-align:left;font-size:0.8rem;color:#555;background:#f8f9fa;border-bottom:1px solid #eee;font-weight:bold}
td{padding:9px 12px;font-size:0.85rem;border-bottom:1px solid #f0f0f0}
.btn-row{display:flex;gap:12px;margin-bottom:20px}
.btn{padding:9px 20px;border-radius:4px;text-decoration:none;font-size:0.88rem;font-weight:bold;border:none;cursor:pointer;display:inline-block}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.btn-print{background:#27ae60;color:#fff}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="oil_gas_list.php">&#128167; Oil &amp; Gas</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero">
  <h1>&#128167; <?php echo htmlspecialchars($c['client_name']);?> v <?php echo htmlspecialchars($c['opponent_name']?:'Unknown');?></h1>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | <?php echo htmlspecialchars($c['case_type']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?></p>
</div>
<div class="container">
  <div class="btn-row">
    <a href="oil_gas_list.php" class="btn btn-outline">&#8592; Back</a>
    <a href="oil_gas_print.php?id=<?php echo $c['id'];?>" class="btn btn-print">&#128424; Print</a>
  </div>
  <div class="card"><div class="section-title navy">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
  <div class="card"><div class="section-title">2. CLIENT DETAILS</div><table><?php row('Name / Company',$c['client_name']);row('Email',$c['client_email']);row('Phone',$c['client_phone']);row('Address',$c['client_address']);?></table></div>
  <div class="card"><div class="section-title red">3. OPPONENT DETAILS</div><table><?php row('Opponent',$c['opponent_name']);row('Contact',$c['opponent_contact']);row('Address',$c['opponent_address']);?></table></div>
  <div class="card"><div class="section-title orange">4. LICENCE &amp; CONTRACT</div><table><?php row('Licence Number',$c['licence_number']);row('Licence Type',$c['licence_type']);row('Licence Area',$c['licence_area']);row('Contract Date',$c['contract_date']);row('Contract Value',$c['contract_value']);row('Contract Description',$c['contract_description']);?></table></div>
  <div class="card"><div class="section-title blue">5. DISPUTE DETAILS</div><table><?php row('Dispute Date',$c['dispute_date']);row('Regulatory Body',$c['regulatory_body']);row('Dispute Description',$c['dispute_description']);row('Court / Arbitration Issued',$c['court_issued']);row('Court / Arbitration Name',$c['court_name']);row('Hearing Date',$c['court_date']);?></table></div>
  <div class="card"><div class="section-title purple">6. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);row('Settlement Offers',$c['settlement_offers']);row('Without Prejudice',$c['without_prejudice']);row('Outcome',$c['outcome']);?></table></div>
</div>
</body>
</html>