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
<title>Criminal: <?php echo htmlspecialchars($c['defendant_name']);?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#2c3e50,#4a4a4a);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.4rem}
.hero p{font-size:0.85rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:20px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#2c3e50;padding:10px 16px;border-radius:6px;margin-bottom:14px}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.green{background:#27ae60}
.section-title.teal{background:#16a085}
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
  <div><a href="dashboard.php">&#127968; Dashboard</a><a href="criminal_list.php">&#9878; Criminal</a><a href="logout.php">&#128682; Logout</a></div>
</div>
<div class="hero">
  <h1>&#9878; R v <?php echo htmlspecialchars($c['defendant_name']);?></h1>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference']?:'N/A');?> | <?php echo htmlspecialchars($c['case_type']?:'N/A');?> | Status: <?php echo ucfirst($c['status']);?></p>
</div>
<div class="container">
  <div class="btn-row">
    <a href="criminal_list.php" class="btn btn-outline">&#8592; Back</a>
    <a href="criminal_print.php?id=<?php echo $c['id'];?>" class="btn btn-print">&#128424; Print</a>
  </div>
  <div class="card"><div class="section-title">1. CASE DETAILS</div><table><?php row('Case Type',$c['case_type']);row('Case Reference',$c['case_reference']);row('Court Reference',$c['court_reference']);row('Status',ucfirst($c['status']));row('Lawyer',$c['lawyer_name']);row('Law Firm',$c['law_firm']);?></table></div>
  <div class="card"><div class="section-title red">2. DEFENDANT</div><table><?php row('Full Name',$c['defendant_name']);row('Date of Birth',$c['defendant_dob']);row('Nationality',$c['defendant_nationality']);row('Custody Status',$c['defendant_custody']);row('Email',$c['defendant_email']);row('Phone',$c['defendant_phone']);row('Address',$c['defendant_address']);?></table></div>
  <div class="card"><div class="section-title orange">3. OFFENCE</div><table><?php row('Offence Date',$c['offence_date']);row('Location',$c['offence_location']);row('Description',$c['offence_description']);row('Charge Date',$c['charge_date']);row('Charges',$c['charges']);row('Plea',$c['plea']);?></table></div>
  <div class="card"><div class="section-title blue">4. COURT</div><table><?php row('Court Name',$c['court_name']);row('Hearing Date',$c['hearing_date']);row('Trial Date',$c['trial_date']);row('Judge',$c['judge_name']);row('Prosecution',$c['prosecution_name']);?></table></div>
  <div class="card"><div class="section-title green">5. BAIL &amp; LEGAL AID</div><table><?php row('Bail Status',$c['bail_status']);row('Bail Conditions',$c['bail_conditions']);row('Legal Aid',$c['legal_aid']);row('Legal Aid Reference',$c['legal_aid_reference']);?></table></div>
  <div class="card"><div class="section-title teal">6. LEGAL SUBMISSIONS &amp; OUTCOME</div><table><?php row('Legal Basis',$c['legal_basis']);row('Evidence',$c['evidence_available']);row('Representations',$c['representations']);row('Sentence',$c['sentence']);row('Outcome',$c['outcome']);?></table></div>
</div>
</body>
</html>