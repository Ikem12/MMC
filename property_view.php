<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: property_list.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM property_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: property_list.php'); exit; }

function row($label, $val) {
    if (!$val) return;
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td>' . nl2br(htmlspecialchars($val)) . '</td></tr>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Property Case: <?php echo htmlspecialchars($c['case_title'] ?: 'Case #' . $c['id']); ?> &mdash; AEP</title>
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
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.purple{background:#8e44ad}
.section-title.red{background:#c0392b}
.section-title.gray{background:#7f8c8d}
table{width:100%;border-collapse:collapse}
th{width:35%;padding:9px 12px;text-align:left;font-size:0.8rem;color:#555;background:#f8f9fa;border-bottom:1px solid #eee;font-weight:bold}
td{padding:9px 12px;font-size:0.85rem;border-bottom:1px solid #f0f0f0}
.btn-row{display:flex;gap:12px;margin-bottom:20px}
.btn{padding:9px 20px;border-radius:4px;text-decoration:none;font-size:0.88rem;font-weight:bold;border:none;cursor:pointer;display:inline-block}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.btn-print{background:#27ae60;color:#fff}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.75rem;font-weight:bold}
.badge-draft{background:#ecf0f1;color:#7f8c8d}
.badge-active{background:#d5f5e3;color:#27ae60}
.badge-court{background:#fde8d8;color:#e67e22}
.badge-settled{background:#d6eaf8;color:#2980b9}
.badge-won{background:#d5f5e3;color:#27ae60}
.badge-lost{background:#fadbd8;color:#c0392b}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="property_list.php">&#127968; Property Cases</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#127968; <?php echo htmlspecialchars($c['case_title'] ?: 'Property Case #' . $c['id']); ?></h1>
  <p>Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> &nbsp;|&nbsp; Type: <?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?> &nbsp;|&nbsp; Status: <?php echo ucfirst($c['status'] ?? 'draft'); ?></p>
</div>
<div class="container">
  <div class="btn-row">
    <a href="property_list.php" class="btn btn-outline">&#8592; Back to List</a>
    <a href="property_print.php?id=<?php echo $c['id']; ?>" class="btn btn-print">&#128424; Print / PDF</a>
  </div>

  <div class="card">
    <div class="section-title navy">1. CASE OVERVIEW</div>
    <table>
      <?php row('Case Type', $c['case_type']); row('Case Title', $c['case_title']); row('Case Reference', $c['case_reference']); ?>
      <tr><th>Status</th><td><span class="badge badge-<?php echo htmlspecialchars($c['status']??'draft'); ?>"><?php echo ucfirst(htmlspecialchars($c['status']??'draft')); ?></span></td></tr>
      <?php row('Lawyer', $c['lawyer_name']); row('Law Firm', $c['law_firm']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title">2. CLIENT</div>
    <table><?php row('Client Name', $c['client_name']); row('Email', $c['client_email']); row('Phone', $c['client_phone']); row('Address', $c['client_address']); ?></table>
  </div>

  <div class="card">
    <div class="section-title orange">3. PROPERTY</div>
    <table><?php row('Property Address', $c['property_address']); row('Property Type', $c['property_type']); row('Tenure', $c['tenure']); ?></table>
  </div>

  <div class="card">
    <div class="section-title blue">4. PARTY A</div>
    <table><?php row('Name', $c['party_a_name']); row('Role', $c['party_a_role']); row('Address', $c['party_a_address']); row('Contact', $c['party_a_contact']); ?></table>
  </div>

  <div class="card">
    <div class="section-title purple">5. PARTY B</div>
    <table><?php row('Name', $c['party_b_name']); row('Role', $c['party_b_role']); row('Address', $c['party_b_address']); row('Contact', $c['party_b_contact']); ?></table>
  </div>

  <div class="card">
    <div class="section-title">6. TENANCY / LEASE TERMS</div>
    <table><?php row('Rent Amount', $c['rent_amount']); row('Deposit Amount', $c['deposit_amount']); row('Tenancy Start', $c['tenancy_start']); row('Tenancy End', $c['tenancy_end']); ?></table>
  </div>

  <div class="card">
    <div class="section-title red">7. NOTICE &amp; PROCEEDINGS</div>
    <table>
      <?php row('Notice Type', $c['notice_type']); row('Notice Date', $c['notice_date']); row('Notice Period', $c['notice_period']); row('Ground for Possession', $c['possession_ground']); row('Claim Details', $c['claim_details']); row('Defence / Response', $c['defence']); row('Repairs / Disrepair', $c['repairs_issues']); row('Covenant Breach', $c['covenant_breach']); ?>
    </table>
  </div>

  <div class="card">
    <div class="section-title gray">8. COURT &amp; LEGAL</div>
    <table>
      <?php row('Court Name', $c['court_name']); row('Court Date', $c['court_date']); row('Court Case Number', $c['court_case_number']); row('Applicable Laws', $c['applicable_laws']); row('Evidence', $c['evidence']); row('Outcome', $c['outcome']); row('Notes', $c['notes']); ?>
    </table>
  </div>
</div>
</body>
</html>
