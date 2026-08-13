<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO legal_advice 
            (file_no, client_name, legal_domain, facts, legal_issues, 
             applicable_law, diagnosis, advice, action_plan, disclaimer, 
             lawyer_name, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['file_no']),
            trim($_POST['client_name']),
            trim($_POST['legal_domain']),
            trim($_POST['facts']),
            trim($_POST['legal_issues']),
            trim($_POST['applicable_law']),
            trim($_POST['diagnosis']),
            trim($_POST['advice']),
            trim($_POST['action_plan']),
            trim($_POST['disclaimer']),
            trim($_POST['lawyer_name']),
            trim($_POST['status'])
        ]);
        $success = 'Legal advice record saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Legal Advice — AEP</title>
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
textarea{resize:vertical;min-height:100px}
.btn-row{display:flex;gap:12px;margin-top:24px}
.btn{padding:10px 28px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-secondary{background:#6c757d;color:#fff}
.btn-secondary:hover{background:#545b62}
.btn-success{background:#28a745;color:#fff}
.btn-success:hover{background:#1e7e34}
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
    <a href="advice_list.php">📋 All Advice</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>📝 Legal Diagnosis & Advice</h2>
    <p class="sub">Complete all sections to generate a formal legal advice record</p>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="advice_list.php" style="margin-left:12px;color:#155724;font-weight:bold">→ View All Advice</a>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- CLIENT DETAILS -->
      <div class="section-title">📁 CLIENT DETAILS</div>
      <div class="form-row">
        <div class="form-group">
          <label>File Number</label>
          <input type="text" name="file_no" placeholder="e.g. AEP/2026/001"/>
        </div>
        <div class="form-group">
          <label>Client Name *</label>
          <input type="text" name="client_name" required placeholder="Full name of client"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Legal Domain *</label>
          <select name="legal_domain" required>
            <option value="">— Select Domain —</option>
            <option>Criminal Law</option>
            <option>Family Law</option>
            <option>Company Law</option>
            <option>Human Rights</option>
            <option>Tort Law</option>
            <option>Administrative Law</option>
            <option>Oil & Gas Law</option>
            <option>General</option>
          </select>
        </div>
        <div class="form-group">
          <label>Advising Lawyer</label>
          <input type="text" name="lawyer_name" placeholder="Name of advising lawyer"/>
        </div>
      </div>

      <!-- FACTS -->
      <div class="section-title">📄 FACTS PRESENTED BY CLIENT</div>
      <div class="form-row">
        <div class="form-group full">
          <label>State the facts as presented by the client</label>
          <textarea name="facts" rows="5" placeholder="Set out the material facts chronologically..."></textarea>
        </div>
      </div>

      <!-- LEGAL ISSUES -->
      <div class="section-title">⚖️ LEGAL ISSUES FOR DETERMINATION</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Identify the legal issues arising from the facts</label>
          <textarea name="legal_issues" rows="4" 
            placeholder="1. Whether...&#10;2. Whether...&#10;3. Whether..."></textarea>
        </div>
      </div>

      <!-- APPLICABLE LAW -->
      <div class="section-title">📚 APPLICABLE LAW</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Statutes, Case Laws & Latin Maxims applicable</label>
          <textarea name="applicable_law" rows="4" 
            placeholder="e.g. Section 36 CFRN 1999; Donoghue v Stevenson [1932]; Audi Alteram Partem..."></textarea>
        </div>
      </div>

      <!-- DIAGNOSIS -->
      <div class="section-title">🔍 LEGAL DIAGNOSIS</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Legal analysis — application of law to the facts</label>
          <textarea name="diagnosis" rows="5" 
            placeholder="Having regard to the facts presented and the applicable law..."></textarea>
        </div>
      </div>

      <!-- ADVICE -->
      <div class="section-title">💡 FORMAL LEGAL ADVICE</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Our formal advice and recommendation to the client</label>
          <textarea name="advice" rows="5" 
            placeholder="It is our considered opinion that..."></textarea>
        </div>
      </div>

      <!-- ACTION PLAN -->
      <div class="section-title">📋 ACTION PLAN</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Steps to be taken going forward</label>
          <textarea name="action_plan" rows="4" 
            placeholder="1. File originating summons at...&#10;2. Serve the respondent...&#10;3. Attend hearing on..."></textarea>
        </div>
      </div>

      <!-- DISCLAIMER -->
      <div class="section-title">⚠️ DISCLAIMER</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Disclaimer</label>
          <textarea name="disclaimer" rows="3"><?php echo htmlspecialchars(
            'This legal advice is based solely on the facts as presented by the client and the applicable law at the time of this opinion. It does not constitute a guarantee of outcome. AEP Legal Consultancy accepts no liability for decisions taken solely on the basis of this advice without further legal consultation.'
          ); ?></textarea>
        </div>
      </div>

      <!-- STATUS -->
      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="final">Final</option>
            <option value="delivered">Delivered to Client</option>
          </select>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Save Legal Advice</button>
        <a href="advice_list.php" class="btn btn-secondary">📋 View All</a>
        <a href="dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
      </div>

    </form>
  </div>
</div>
</body>
</html>