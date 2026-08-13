<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO skeleton_arguments 
            (case_title, case_number, court, party, introduction,
             facts_summary, issues, submissions, conclusion,
             relief_sought, authorities, lawyer_name, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['case_title']),
            trim($_POST['case_number']),
            trim($_POST['court']),
            trim($_POST['party']),
            trim($_POST['introduction']),
            trim($_POST['facts_summary']),
            trim($_POST['issues']),
            trim($_POST['submissions']),
            trim($_POST['conclusion']),
            trim($_POST['relief_sought']),
            trim($_POST['authorities']),
            trim($_POST['lawyer_name']),
            trim($_POST['status'])
        ]);
        $success = 'Skeleton argument saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Skeleton Argument — AEP</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar a{color:#fff;text-decoration:none;font-size:0.9rem}
.topbar a:hover{text-decoration:underline}
.container{max-width:900px;margin:30px auto;padding:0 20px}
.card{background:#fff;border-radius:10px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
h2{color:#1a3c5e;margin-bottom:6px;font-size:1.3rem}
p.sub{color:#888;font-size:0.85rem;margin-bottom:24px}
.section-title{background:#1a3c5e;color:#fff;padding:8px 14px;border-radius:4px;font-size:0.85rem;font-weight:bold;margin:20px 0 12px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
label{font-size:0.82rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;font-family:Arial,sans-serif}
input:focus,select:focus,textarea:focus{outline:none;border-color:#1a3c5e}
textarea{resize:vertical}
.btn-row{display:flex;gap:12px;margin-top:24px}
.btn{padding:10px 28px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-secondary{background:#6c757d;color:#fff}
.btn-secondary:hover{background:#545b62}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
</style>
</head>
<body>

<div class="topbar">
  <strong>⚖️ AEP Legal Platform</strong>
  <div style="display:flex;gap:20px">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="skeleton_list.php">📋 All Skeletons</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>📜 Skeleton Argument</h2>
    <p class="sub">Complete all sections to prepare a formal skeleton argument</p>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="skeleton_list.php" style="margin-left:12px;color:#155724;font-weight:bold">→ View All</a>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- CASE DETAILS -->
      <div class="section-title">⚖️ CASE DETAILS</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Case Title *</label>
          <input type="text" name="case_title" required
            placeholder="e.g. JOHN DOE v. ATTORNEY GENERAL OF THE FEDERATION"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Case Number</label>
          <input type="text" name="case_number"
            placeholder="e.g. SUIT NO: CA/A/2026/001"/>
        </div>
        <div class="form-group">
          <label>Court</label>
          <input type="text" name="court"
            placeholder="e.g. Court of Appeal, Abuja Division"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Filing Party</label>
          <select name="party">
            <option value="">— Select Party —</option>
            <option>Claimant / Plaintiff</option>
            <option>Defendant / Respondent</option>
            <option>Appellant</option>
            <option>Respondent</option>
            <option>Petitioner</option>
            <option>Intervenor</option>
          </select>
        </div>
        <div class="form-group">
          <label>Counsel / Lawyer</label>
          <input type="text" name="lawyer_name"
            placeholder="Name of filing counsel"/>
        </div>
      </div>

      <!-- INTRODUCTION -->
      <div class="section-title">📌 INTRODUCTION</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Introduction & Background</label>
          <textarea name="introduction" rows="4"
            placeholder="This is a skeleton argument filed on behalf of the [Claimant/Defendant] in the above-referenced suit. The [Claimant/Defendant] respectfully urges this Honourable Court to..."></textarea>
        </div>
      </div>

      <!-- FACTS -->
      <div class="section-title">📄 SUMMARY OF FACTS</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Brief Summary of Relevant Facts</label>
          <textarea name="facts_summary" rows="5"
            placeholder="1. The Claimant is...&#10;2. On or about [date], the Defendant...&#10;3. The Claimant thereafter..."></textarea>
        </div>
      </div>

      <!-- ISSUES -->
      <div class="section-title">❓ ISSUES FOR DETERMINATION</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Issues distilled for the Court's determination</label>
          <textarea name="issues" rows="4"
            placeholder="1. Whether the Defendant breached the duty of care owed to the Claimant.&#10;2. Whether the Claimant is entitled to the reliefs sought.&#10;3. Whether..."></textarea>
        </div>
      </div>

      <!-- SUBMISSIONS -->
      <div class="section-title">⚖️ SUBMISSIONS</div>*
