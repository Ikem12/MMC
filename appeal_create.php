<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO grounds_of_appeal 
            (case_title, case_number, lower_court, appeal_court, party,
             judgment_date, introduction, grounds, arguments,
             relief_sought, authorities, lawyer_name, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['case_title']),
            trim($_POST['case_number']),
            trim($_POST['lower_court']),
            trim($_POST['appeal_court']),
            trim($_POST['party']),
            trim($_POST['judgment_date']),
            trim($_POST['introduction']),
            trim($_POST['grounds']),
            trim($_POST['arguments']),
            trim($_POST['relief_sought']),
            trim($_POST['authorities']),
            trim($_POST['lawyer_name']),
            trim($_POST['status'])
        ]);
        $success = 'Grounds of appeal saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Grounds of Appeal — AEP</title>
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
    <a href="appeal_list.php">📋 All Appeals</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>🏛️ Grounds of Appeal</h2>
    <p class="sub">Complete all sections to prepare formal grounds of appeal</p>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="appeal_list.php" style="margin-left:12px;color:#155724;font-weight:bold">→ View All Appeals</a>
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
          <label>Appeal Case Number</label>
          <input type="text" name="case_number"
            placeholder="e.g. APPEAL NO: CA/A/2026/001"/>
        </div>
        <div class="form-group">
          <label>Date of Lower Court Judgment</label>
          <input type="date" name="judgment_date"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Lower Court</label>
          <input type="text" name="lower_court"
            placeholder="e.g. High Court of the FCT, Abuja"/>
        </div>
        <div class="form-group">
          <label>Appeal Court</label>
          <input type="text" name="appeal_court"
            placeholder="e.g. Court of Appeal, Abuja Division"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Appellant / Filing Party</label>
          <select name="party">
            <option value="">— Select —</option>
            <option>Appellant</option>
            <option>Cross-Appellant</option>
            <option>Petitioner</option>
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
          <label>Introduction & Background of Appeal</label>
          <textarea name="introduction" rows="4"
            placeholder="The Appellant being dissatisfied with the judgment of the [Lower Court] delivered on [date] hereby appeals to this Honourable Court on the following grounds..."></textarea>
        </div>
      </div>

      <!-- GROUNDS -->
      <div class="section-title">📋 GROUNDS OF APPEAL</div>
      <div class="form-row">
        <div class="form-group full">
          <label>State each Ground of Appeal clearly *</label>
          <textarea name="grounds" rows="10" required
            placeholder="GROUND 1 — ERROR IN LAW&#10;The learned trial Judge erred in law when he held that...&#10;PARTICULARS:&#10;(a) ...&#10;(b) ...&#10;&#10;GROUND 2 — MISCARRIAGE OF JUSTICE&#10;The learned trial Judge occasioned a miscarriage of justice when...&#10;PARTICULARS:&#10;(a) ...&#10;(b) ...&#10;&#10;GROUND 3 — AGAINST THE WEIGHT OF EVIDENCE&#10;The judgment is against the weight of evidence."></textarea>
        </div>
      </div>

      <!-- ARGUMENTS -->
      <div class="section-title">⚖️ ARGUMENTS IN SUPPORT</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Legal arguments in support of each ground</label>
          <textarea name="arguments" rows="10"
            placeholder="ON GROUND 1:&#10;It is submitted that the learned trial Judge erred when...&#10;In [case name] it was held that...&#10;Applying this to the facts...&#10;&#10;ON GROUND 2:&#10;It is further submitted that..."></textarea>
        </div>
      </div>

      <!-- AUTHORITIES -->
      <div class="section-title">📚 LIST OF AUTHORITIES</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Cases, Statutes & Texts relied upon</label>
          <textarea name="authorities" rows="5"
            placeholder="CASES:&#10;1. ...&#10;2. ...&#10;&#10;STATUTES:&#10;1. Constitution of the Federal Republic of Nigeria 1999&#10;2. ...&#10;&#10;TEXTS:&#10;1. ..."></textarea>
        </div>
      </div>

      <!-- RELIEF SOUGHT -->
      <div class="section-title">🙏 RELIEF SOUGHT</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Reliefs sought from the Appeal Court</label>
          <textarea name="relief_sought" rows="4"
            placeholder="WHEREFORE the Appellant respectfully urges this Honourable Court to:&#10;1. Allow this appeal.&#10;2. Set aside the judgment of the lower court delivered on [date].&#10;3. Enter judgment in favour of the Appellant.&#10;4. Award costs of this appeal to the Appellant."></textarea>
        </div>
      </div>

      <!-- STATUS -->
      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="final">Final</option>
            <option value="filed">Filed in Court</option>
          </select>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Save Grounds of Appeal</button>
        <a href="appeal_list.php" class="btn btn-secondary">📋 View All</a>
        <a href="dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
      </div>

    </form>
  </div>
</div>
</body>
</html>