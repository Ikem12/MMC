<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
$c  = $pdo->prepare("SELECT * FROM employment_cases WHERE id = ?");
$c->execute([$id]);
$c  = $c->fetch(PDO::FETCH_ASSOC);

if (!$c) { header('Location: employment_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Employment Case — <?php echo htmlspecialchars($c['claimant_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:12px;color:#000;background:#fff;padding:20px}
@media print{
  body{padding:0}
  .no-print{display:none}
  .page-break{page-break-before:always}
}
.header{text-align:center;border-bottom:3px solid #1a3c5e;padding-bottom:16px;margin-bottom:20px}
.header h1{font-size:1.3rem;color:#1a3c5e}
.header p{font-size:0.85rem;color:#555;margin-top:4px}
.firm-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #ddd}
.firm-name{font-size:1rem;font-weight:bold;color:#1a3c5e}
.firm-sub{font-size:0.75rem;color:#888}
.doc-meta{text-align:right;font-size:0.78rem;color:#555}
.section{margin-bottom:18px}
.section-title{background:#1a3c5e;color:#fff;padding:6px 12px;font-size:0.82rem;font-weight:bold;margin-bottom:10px}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.green{background:#27ae60}
.section-title.purple{background:#8e44ad}
.section-title.teal{background:#16a085}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.field{margin-bottom:8px}
.field.full{grid-column:1/-1}
.field-label{font-size:0.7rem;font-weight:bold;color:#888;text-transform:uppercase;margin-bottom:2px}
.field-value{font-size:0.85rem;color:#222;padding:4px 8px;border:1px solid #e0e0e0;border-radius:2px;min-height:24px;background:#fafafa}
.field-value.long{min-height:50px;white-space:pre-wrap}
.claims-row{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:10px}
.claim-box{padding:6px;border:1px solid #ddd;border-radius:3px;text-align:center;font-size:0.75rem}
.claim-box.yes{background:#d4edda;border-color:#c3e6cb;color:#155724;font-weight:bold}
.claim-box.no{background:#f8f9fa;color:#aaa}
.footer{margin-top:30px;padding-top:12px;border-top:2px solid #1a3c5e;display:flex;justify-content:space-between;font-size:0.75rem;color:#888}
.sig-section{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:24px}
.sig-box{border-top:1px solid #333;padding-top:6px;font-size:0.78rem;color:#555}
.btn-row{display:flex;gap:10px;margin-bottom:20px}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-print{background:#1a3c5e;color:#fff}
.btn-back{background:#6c757d;color:#fff}
.badge{display:inline-block;padding:3px 10px;border-radius:10px;font-size:0.75rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-active{background:#cce5ff;color:#004085}
.badge-tribunal{background:#d6d8f7;color:#2c2f8a}
.badge-settled{background:#d4edda;color:#155724}
.badge-won{background:#d4edda;color:#155724}
.badge-lost{background:#f8d7da;color:#721c24}
.badge-withdrawn{background:#e2e3e5;color:#383d41}
.badge-closed{background:#e2e3e5;color:#383d41}
</style>
</head>
<body>

<!-- PRINT BUTTONS -->
<div class="btn-row no-print">
  <button onclick="window.print()" class="btn btn-print">🖨 Print / Save as PDF</button>
  <a href="employment_view.php?id=<?php echo $c['id']; ?>" class="btn btn-back">← Back to Case</a>
  <a href="employment_list.php" class="btn btn-back">📋 All Cases</a>
</div>

<!-- FIRM HEADER -->
<div class="firm-header">
  <div>
    <div class="firm-name">⚖️ AEP Legal Consultancy</div>
    <div class="firm-sub">Employment Law Division</div>
  </div>
  <div class="doc-meta">
    <div><strong>Case Ref:</strong> <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></div>
    <div><strong>Tribunal Ref:</strong> <?php echo htmlspecialchars($c['tribunal_reference'] ?: 'N/A'); ?></div>
    <div><strong>Date Printed:</strong> <?php echo date('d F Y'); ?></div>
    <div><strong>Status:</strong> <span class="badge badge-<?php echo $c['status']; ?>"><?php echo ucfirst($c['status']); ?></span></div>
  </div>
</div>

<!-- DOCUMENT TITLE -->
<div class="header">
  <h1>EMPLOYMENT TRIBUNAL CASE SUMMARY</h1>
  <p><?php echo htmlspecialchars($c['claimant_name']); ?> <strong>v</strong> <?php echo htmlspecialchars($c['respondent_name']); ?></p>
  <p><?php echo htmlspecialchars($c['case_type']); ?></p>
</div>

<!-- 1. CASE DETAILS -->
<div class="section">
  <div class="section-title">1. CASE DETAILS</div>
  <div class="grid">
    <div class="field">
      <div class="field-label">Case Type</div>
      <div class="field-value"><?php echo htmlspecialchars($c['case_type'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Status</div>
      <div class="field-value"><?php echo ucfirst($c['status']); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Case Reference</div>
      <div class="field-value"><?php echo htmlspecialchars($c['case_reference'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Tribunal Reference</div>
      <div class="field-value"><?php echo htmlspecialchars($c['tribunal_reference'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Lawyer / Solicitor</div>
      <div class="field-value"><?php echo htmlspecialchars($c['lawyer_name'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Law Firm</div>
      <div class="field-value"><?php echo htmlspecialchars($c['law_firm'] ?: '—'); ?></div>
    </div>
  </div>
</div>

<!-- 2. CLAIMANT -->
<div class="section">
  <div class="section-title red">2. CLAIMANT DETAILS</div>
  <div class="grid">
    <div class="field">
      <div class="field-label">Full Name</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_name'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Date of Birth</div>
      <div class="field-value"><?php echo $c['claimant_dob'] ? date('d M Y', strtotime($c['claimant_dob'])) : '—'; ?></div>
    </div>
    <div class="field">
      <div class="field-label">Email</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_email'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Phone</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_phone'] ?: '—'); ?></div>
    </div>
    <div class="field full">
      <div class="field-label">Address</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_address'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Job Title</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_job_title'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Annual Salary</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_salary'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Start Date</div>
      <div class="field-value"><?php echo $c['claimant_start_date'] ? date('d M Y', strtotime($c['claimant_start_date'])) : '—'; ?></div>
    </div>
    <div class="field">
      <div class="field-label">End Date</div>
      <div class="field-value"><?php echo $c['claimant_end_date'] ? date('d M Y', strtotime($c['claimant_end_date'])) : '—'; ?></div>
    </div>
    <div class="field">
      <div class="field-label">Notice Period</div>
      <div class="field-value"><?php echo htmlspecialchars($c['claimant_notice_period'] ?: '—'); ?></div>
    </div>
  </div>
</div>

<!-- 3. RESPONDENT -->
<div class="section">
  <div class="section-title orange">3. RESPONDENT / EMPLOYER</div>
  <div class="grid">
    <div class="field">
      <div class="field-label">Employer / Company</div>
      <div class="field-value"><?php echo htmlspecialchars($c['respondent_name'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Sector</div>
      <div class="field-value"><?php echo htmlspecialchars($c['respondent_sector'] ?: '—'); ?></div>
    </div>
    <div class="field">
      <div class="field-label">Contact Person</div>
      <div class="field-value"><?php echo htmlspecialchars($c['respondent_contact'] ?: '—'); ?></div>
    </div>
    <div class="field full">
      <div class="field-label">Address</div>
      <div class="field-value"><?php echo htmlspecialchars($c['respondent_address'] ?: '—'); ?></div>
    </div>