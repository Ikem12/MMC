<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO witness_statements 
            (case_title, case_number, court, witness_name, witness_address,
             witness_occupation, relationship, statement, exhibits, 
             declaration, lawyer_name, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['case_title']),
            trim($_POST['case_number']),
            trim($_POST['court']),
            trim($_POST['witness_name']),
            trim($_POST['witness_address']),
            trim($_POST['witness_occupation']),
            trim($_POST['relationship']),
            trim($_POST['statement']),
            trim($_POST['exhibits']),
            trim($_POST['declaration']),
            trim($_POST['lawyer_name']),
            trim($_POST['status'])
        ]);
        $success = 'Witness statement saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Witness Statement — AEP</title>
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
    <a href="witness_list.php">📋 All Statements</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>👤 Witness Statement</h2>
    <p class="sub">Complete all sections to prepare a formal witness statement</p>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="witness_list.php" style="margin-left:12px;color:#155724;font-weight:bold">→ View All Statements</a>
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
            placeholder="e.g. JOHN DOE v. JANE DOE"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Case Number</label>
          <input type="text" name="case_number"
            placeholder="e.g. SUIT NO: FCT/HC/CV/2026/001"/>
        </div>
        <div class="form-group">
          <label>Court</label>
          <input type="text" name="court"
            placeholder="e.g. High Court of the FCT, Abuja"/>
        </div>
      </div>

      <!-- WITNESS DETAILS -->
      <div class="section-title">👤 WITNESS DETAILS</div>
      <div class="form-row">
        <div class="form-group">
          <label>Witness Full Name *</label>
          <input type="text" name="witness_name" required
            placeholder="Full legal name of witness"/>
        </div>
        <div class="form-group">
          <label>Occupation</label>
          <input type="text" name="witness_occupation"
            placeholder="e.g. Civil Servant, Trader, Lawyer"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group full">
          <label>Witness Address</label>
          <textarea name="witness_address" rows="2"
            placeholder="Full residential address of witness"></textarea>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Relationship to Party</label>
          <select name="relationship">
            <option value="">— Select —</option>
            <option>Claimant/Plaintiff</option>
            <option>Defendant/Respondent</option>
            <option>Independent Witness</option>
            <option>Expert Witness</option>
            <option>Character Witness</option>
          </select>
        </div>
        <div class="form-group">
          <label>Lawyer / Counsel</label>
          <input type="text" name="lawyer_name"
            placeholder="Name of counsel filing statement"/>
        </div>
      </div>

      <!-- STATEMENT -->
      <div class="section-title">📝 WITNESS STATEMENT</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Statement on Oath *</label>
          <textarea name="statement" rows="12" required
            placeholder="I, [Full Name], make oath and say as follows:&#10;&#10;1. I am the [claimant/defendant] in this suit and I have the authority to depose to this affidavit.&#10;&#10;2. I know the facts deposed to herein from my personal knowledge.&#10;&#10;3. ...&#10;&#10;4. ...&#10;&#10;5. I make this statement in good faith believing same to be true and correct."></textarea>
        </div>
      </div>

      <!-- EXHIBITS -->
      <div class="section-title">📎 EXHIBITS</div>
      <div class="form-row">
        <div class="form-group full">
          <label>List of Exhibits attached</label>
          <textarea name="exhibits" rows="4"
            placeholder="Exhibit A — Copy of Agreement dated...&#10;Exhibit B — Receipt of payment...&#10;Exhibit C — Photographs showing..."></textarea>
        </div>
      </div>

      <!-- DECLARATION -->
      <div class="section-title">✍️ DECLARATION</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Deponent's Declaration</label>
          <textarea name="declaration" rows="3"><?php echo htmlspecialchars(
            'I, the deponent, hereby make oath and swear that the contents of this witness statement are true and correct to the best of my knowledge, information and belief.'
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
            <option value="filed">Filed in Court</option>
          </select>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Save Statement</button>
        <a href="witness_list.php" class="btn btn-secondary">📋 View All</a>
        <a href="dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
      </div>

    </form>
  </div>
</div>
</body>
</html>