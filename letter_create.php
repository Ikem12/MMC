<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO draft_letters 
            (ref_no, letter_type, recipient_name, recipient_address, 
             subject, salutation, body, signatory_name, signatory_title, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['ref_no']),
            trim($_POST['letter_type']),
            trim($_POST['recipient_name']),
            trim($_POST['recipient_address']),
            trim($_POST['subject']),
            trim($_POST['salutation']),
            trim($_POST['body']),
            trim($_POST['signatory_name']),
            trim($_POST['signatory_title']),
            trim($_POST['status'])
        ]);
        $success = 'Letter drafted and saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Draft Letter — AEP</title>
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
    <a href="letter_list.php">📋 All Letters</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>✉️ Draft Legal Letter</h2>
    <p class="sub">Complete all sections to draft a formal legal letter</p>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="letter_list.php" style="margin-left:12px;color:#155724;font-weight:bold">→ View All Letters</a>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

      <!-- LETTER DETAILS -->
      <div class="section-title">📁 LETTER DETAILS</div>
      <div class="form-row">
        <div class="form-group">
          <label>Reference Number</label>
          <input type="text" name="ref_no" placeholder="e.g. AEP/LTR/2026/001"/>
        </div>
        <div class="form-group">
          <label>Letter Type *</label>
          <select name="letter_type" required>
            <option value="">— Select Type —</option>
            <option>Letter Before Action</option>
            <option>Demand Letter</option>
            <option>Letter of Instruction</option>
            <option>Without Prejudice Letter</option>
            <option>Notice to Quit</option>
            <option>Letter of Advice</option>
            <option>Cease & Desist</option>
            <option>Letter of Undertaking</option>
            <option>General Correspondence</option>
          </select>
        </div>
      </div>

      <!-- RECIPIENT -->
      <div class="section-title">👤 RECIPIENT DETAILS</div>
      <div class="form-row">
        <div class="form-group">
          <label>Recipient Name *</label>
          <input type="text" name="recipient_name" required placeholder="Full name or organization"/>
        </div>
        <div class="form-group">
          <label>Salutation</label>
          <select name="salutation">
            <option>Dear Sir</option>
            <option>Dear Madam</option>
            <option>Dear Sir/Madam</option>
            <option>Dear Counsel</option>
            <option>Your Excellency</option>
            <option>Your Lordship</option>
            <option>Your Worship</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group full">
          <label>Recipient Address</label>
          <textarea name="recipient_address" rows="3" 
            placeholder="Street address&#10;City, State&#10;Country"></textarea>
        </div>
      </div>

      <!-- SUBJECT -->
      <div class="section-title">📌 SUBJECT</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Subject / Re: *</label>
          <input type="text" name="subject" required 
            placeholder="e.g. RE: DEMAND FOR PAYMENT OF OUTSTANDING DEBT"/>
        </div>
      </div>

      <!-- BODY -->
      <div class="section-title">📝 LETTER BODY</div>
      <div class="form-row">
        <div class="form-group full">
          <label>Body of Letter *</label>
          <textarea name="body" rows="12" required
            placeholder="We write on behalf of our client...&#10;&#10;The facts of this matter are as follows:&#10;&#10;1. ...&#10;2. ...&#10;&#10;In the circumstances, you are hereby put on notice that...&#10;&#10;TAKE NOTICE that unless you...within 7 days of receipt of this letter, our client shall have no option but to...&#10;&#10;We trust you will be guided accordingly.&#10;&#10;Yours faithfully,"></textarea>
        </div>
      </div>

      <!-- SIGNATORY -->
      <div class="section-title">✍️ SIGNATORY</div>
      <div class="form-row">
        <div class="form-group">
          <label>Signatory Name</label>
          <input type="text" name="signatory_name" placeholder="Name of signing lawyer"/>
        </div>
        <div class="form-group">
          <label>Designation / Title</label>
          <input type="text" name="signatory_title" 
            placeholder="e.g. Principal Partner, Associate"/>
        </div>
      </div>

      <!-- STATUS -->
      <div class="section-title">📊 STATUS</div>
      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="final">Final</option>
            <option value="sent">Sent</option>
          </select>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Save Letter</button>
        <a href="letter_list.php" class="btn btn-secondary">📋 View All</a>
        <a href="dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
      </div>

    </form>
  </div>
</div>
</body>
</html>