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
<title>Admin Law Case: <?php echo htmlspecialchars($c['client_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#6c3483,#9b59b6);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.4rem}
.hero p{font-size:0.85rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:20px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#6c3483;padding:10px 16px;border-radius:6px;margin-bottom:14px}
.section-title.blue{background:#1a3c5e}
.section-title.green{background:#27ae60}
.section-title.red{background:#c0392b}
.section-title.teal{background:#16a085}
.section-title.orange{background:#e67e22}
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
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="admin_law_list.php">&#127963; Admin Law Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#127963; <?php echo htmlspecialchars($c['client_name']); ?> v <?php echo htmlspecialchars($c['public_body_name'] ?: 'Unknown Body'); ?></h1>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> &nbsp;|&nbsp; Status: <?php echo ucfirst($c['status']); ?></p>
</div>
<div class="container">
  <div class="btn-row">
    <a href="admin_law_list.php" class="btn btn-outline">&#8592; Back to List</a>
    <a href="admin_law_print.php?id=<?php echo $c['id']; ?>" class="btn btn-print">&#128424; Print / PDF</a>
  </div>
  <div class="card"><div class="section-title blue">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']); row('Case Reference',$c['case_reference']); row('Status',ucfirst($c['status'])); row('Lawyer',$c['lawyer_name']); row('Law Firm',$c['law_firm']); ?></table></div>
  <div class="card"><div class="section-title">2. CLIENT DETAILS</div><table><?php row('Full Name',$c['client_name']); row('Email',$c['client_email']); row('Phone',$c['client_phone']); row('Address',$c['client_address']); ?></table></div>
  <div class="card"><div class="section-title red">3. PUBLIC BODY</div><table><?php row('Public Body',$c['public_body_name']); row('Contact',$c['public_body_contact']); row('Address',$c['public_body_address']); ?></table></div>
  <div class="card"><div class="section-title orange">4. DECISION DETAILS</div><table><?php row('Decision Date',$c['decision_date']); row('Decision Description',$c['decision_description']); row('Grounds of Challenge',$c['grounds_of_challenge']); ?></table></div>
  <div class="card"><div class="section-title green">5. JUDICIAL REVIEW</div><table><?php row('JR Sought',$c['judicial_review']); row('JR Permission Date',$c['jr_permission_date']); row('JR Hearing Date',$c['jr_hearing_date']); row('JR Venue',$c['jr_venue']); ?></table></div>
  <div class="card"><div class="section-title teal">6. TRIBUNAL DETAILS</div><table><?php row('Tribunal Name',$c['tribunal_name']); row('Tribunal Reference',$c['tribunal_reference']); row('Tribunal Hearing',$c['tribunal_hearing_date']); row('Human Rights Article',$c['human_rights_article']); row('Regulatory Body',$c['regulatory_body']); row('Licence Reference',$c['licence_reference']); ?></table></div>
  <div class="card"><div class="section-title blue">7. LEGAL SUBMISSIONS</div><table><?php row('Legal Basis',$c['legal_basis']); row('Evidence Available',$c['evidence_available']); row('Representations',$c['representations']); row('Settlement Offers',$c['settlement_offers']); row('Outcome',$c['outcome']); ?></table></div>
</div>
</body>
</html>