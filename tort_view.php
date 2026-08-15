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
<title>Tort Case: <?php echo htmlspecialchars($c['claimant_name'] ?: 'Case #' . $c['id']); ?> &mdash; AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#6a0572,#9b59b6);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.4rem}
.hero p{font-size:0.85rem;opacity:0.85;margin-top:4px}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:20px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;background:#6a0572;padding:10px 16px;border-radius:6px;margin-bottom:14px}
.section-title.navy{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.green{background:#27ae60}
.section-title.orange{background:#e67e22}
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
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="tort_list.php">&#9888;&#65039; Tort Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#9888;&#65039; <?php echo htmlspecialchars($c['claimant_name'] ?: 'Unknown Claimant'); ?> v <?php echo htmlspecialchars($c['defendant_name'] ?: 'Unknown Defendant'); ?></h1>
  <p>Case Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> &nbsp;|&nbsp; Status: <?php echo ucfirst($c['status'] ?? 'draft'); ?></p>
</div>
<div class="container">
  <div class="btn-row">
    <a href="tort_list.php" class="btn btn-outline">&#8592; Back to List</a>
    <a href="tort_print.php?id=<?php echo $c['id']; ?>" class="btn btn-print">&#128424; Print / PDF</a>
  </div>

  <div class="card">
    <div class="section-title navy">1. CASE DETAILS</div>
    <table>
      <?php row('Case Type', $c['case_type']); row('Case Reference', $c['case_reference']); row('Status', ucfirst($c['status'] ?? 'draft')); row('Lawyer', $c['lawyer_name']); row('Law Firm', $c['law_firm']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title">2. CLAIMANT</div>
    <table>
      <?php row('Full Name', $c['claimant_name']); row('Date of Birth', $c['claimant_dob']); row('Email', $c['claimant_email']); row('Phone', $c['claimant_phone']); row('Address', $c['claimant_address']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title red">3. DEFENDANT</div>
    <table>
      <?php row('Defendant Name', $c['defendant_name']); row('Contact', $c['defendant_contact']); row('Address', $c['defendant_address']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title orange">4. INCIDENT</div>
    <table>
      <?php row('Incident Date', $c['incident_date']); row('Location', $c['incident_location']); row('Description', $c['incident_description']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title">5. INJURY &amp; DAMAGES</div>
    <table>
      <?php row('Injury Description', $c['injury_description']); row('Medical Treatment', $c['medical_treatment']); row('Prognosis', $c['prognosis']); row('Total Damages Claimed', $c['damages_claimed']); row('Special Damages', $c['special_damages']); row('General Damages', $c['general_damages']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title green">6. LIABILITY &amp; COURT</div>
    <table>
      <?php row('Liability Admitted', $c['liability_admitted']); row('Contributory Negligence', $c['contributory_negligence']); row('Court Proceedings Issued', $c['court_issued']); row('Court Name', $c['court_name']); row('Court Date', $c['court_date']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title teal">7. LEGAL SUBMISSIONS &amp; OUTCOME</div>
    <table>
      <?php row('Legal Basis', $c['legal_basis']); row('Evidence Available', $c['evidence_available']); row('Representations', $c['representations']); row('Settlement Offers', $c['settlement_offers']); row('Without Prejudice', $c['without_prejudice']); row('Outcome / Notes', $c['outcome']); ?>
    </table>
  </div>
</div>
</body>
</html>
