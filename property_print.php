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
<title>Print: Property Case &mdash; <?php echo htmlspecialchars($c['case_title'] ?: 'Case #' . $c['id']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:11pt;color:#000;background:#fff;padding:20px}
.header{text-align:center;border-bottom:3px solid #16a085;padding-bottom:14px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header h2{font-size:1rem;margin-top:6px;color:#16a085}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.section{margin-bottom:18px}
.section-title{background:#16a085;color:#fff;padding:6px 12px;font-size:0.9rem;font-weight:bold;margin-bottom:8px}
.section-title.navy{background:#1a3c5e}
.section-title.orange{background:#e67e22}
.section-title.blue{background:#2980b9}
.section-title.purple{background:#8e44ad}
.section-title.red{background:#c0392b}
.section-title.gray{background:#7f8c8d}
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
  <a href="property_view.php?id=<?php echo $c['id']; ?>" style="margin-left:12px;font-size:0.88rem;">&#8592; Back</a>
</div>

<div class="header">
  <h1>AEP Legal Consultancy</h1>
  <h2>PROPERTY LAW CASE RECORD</h2>
  <p><?php echo htmlspecialchars($c['case_title'] ?: 'Case #' . $c['id']); ?></p>
  <p>Type: <?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?> | Ref: <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?> | Status: <?php echo ucfirst($c['status'] ?? 'draft'); ?> | Printed: <?php echo date('d/m/Y H:i'); ?></p>
</div>

<div class="section">
  <div class="section-title navy">1. CASE OVERVIEW</div>
  <table><?php row('Case Type', $c['case_type']); row('Case Title', $c['case_title']); row('Case Reference', $c['case_reference']); row('Status', ucfirst($c['status'] ?? 'draft')); row('Lawyer', $c['lawyer_name']); row('Law Firm', $c['law_firm']); ?></table>
</div>

<div class="section">
  <div class="section-title">2. CLIENT</div>
  <table><?php row('Client Name', $c['client_name']); row('Email', $c['client_email']); row('Phone', $c['client_phone']); row('Address', $c['client_address']); ?></table>
</div>

<div class="section">
  <div class="section-title orange">3. PROPERTY</div>
  <table><?php row('Property Address', $c['property_address']); row('Property Type', $c['property_type']); row('Tenure', $c['tenure']); ?></table>
</div>

<div class="section">
  <div class="section-title blue">4. PARTY A</div>
  <table><?php row('Name', $c['party_a_name']); row('Role', $c['party_a_role']); row('Address', $c['party_a_address']); row('Contact', $c['party_a_contact']); ?></table>
</div>

<div class="section">
  <div class="section-title purple">5. PARTY B</div>
  <table><?php row('Name', $c['party_b_name']); row('Role', $c['party_b_role']); row('Address', $c['party_b_address']); row('Contact', $c['party_b_contact']); ?></table>
</div>

<div class="section">
  <div class="section-title">6. TENANCY / LEASE TERMS</div>
  <table><?php row('Rent Amount', $c['rent_amount']); row('Deposit Amount', $c['deposit_amount']); row('Tenancy Start', $c['tenancy_start']); row('Tenancy End', $c['tenancy_end']); ?></table>
</div>

<div class="section">
  <div class="section-title red">7. NOTICE &amp; PROCEEDINGS</div>
  <table><?php row('Notice Type', $c['notice_type']); row('Notice Date', $c['notice_date']); row('Notice Period', $c['notice_period']); row('Ground for Possession', $c['possession_ground']); row('Claim Details', $c['claim_details']); row('Defence / Response', $c['defence']); row('Repairs / Disrepair', $c['repairs_issues']); row('Covenant Breach', $c['covenant_breach']); ?></table>
</div>

<div class="section">
  <div class="section-title gray">8. COURT &amp; LEGAL</div>
  <table><?php row('Court Name', $c['court_name']); row('Court Date', $c['court_date']); row('Court Case Number', $c['court_case_number']); row('Applicable Laws', $c['applicable_laws']); row('Evidence', $c['evidence']); row('Outcome', $c['outcome']); row('Notes', $c['notes']); ?></table>
</div>
</body>
</html>
