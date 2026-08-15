<?php
// FILE: advice_create.php — Create new Legal Advice record
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name    = trim($_POST['client_name'] ?? '');
    $client_email   = trim($_POST['client_email'] ?? '');
    $client_phone   = trim($_POST['client_phone'] ?? '');
    $client_address = trim($_POST['client_address'] ?? '');
    $matter_type    = trim($_POST['matter_type'] ?? '');
    $subject        = trim($_POST['subject'] ?? '');
    $background     = trim($_POST['background'] ?? '');
    $legal_issues   = trim($_POST['legal_issues'] ?? '');
    $advice         = trim($_POST['advice'] ?? '');
    $recommendations= trim($_POST['recommendations'] ?? '');
    $disclaimer     = trim($_POST['disclaimer'] ?? '');
    $status         = trim($_POST['status'] ?? 'draft');
    $lawyer_name    = $_SESSION['username'] ?? '';

    if ($client_name === '' || $subject === '') {
        $error = 'Client name and subject are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO legal_advice
            (client_name, client_email, client_phone, client_address, matter_type,
             subject, background, legal_issues, advice, recommendations, disclaimer,
             status, lawyer_name, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $client_name, $client_email, $client_phone, $client_address, $matter_type,
            $subject, $background, $legal_issues, $advice, $recommendations, $disclaimer,
            $status, $lawyer_name, date('Y-m-d H:i:s'),
        ]);
        $newId = $pdo->lastInsertId();
        header('Location: advice_print.php?id=' . $newId);
        exit;
    }
}

$matterTypes = [
    'Contract Law', 'Family Law', 'Criminal Law', 'Employment Law', 'Property Law',
    'Immigration', 'Tort / Personal Injury', 'Administrative Law', 'Company Law',
    'Oil &amp; Gas', 'Human Rights', 'Intellectual Property', 'Debt Recovery',
    'Wills &amp; Probate', 'Other',
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>New Legal Advice &mdash; AEP Legal Platform</title>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.hero{background:linear-gradient(135deg,#1a3c5e,#2980b9);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.container{max-width:900px;margin:0 auto;padding:0 28px 40px}
.alert{padding:12px 16px;border-radius:4px;margin-bottom:20px;font-size:0.9rem}
.alert-error{background:#f8d7da;color:#721c24}
.alert-success{background:#d4edda;color:#155724}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.9rem;font-weight:bold;color:#fff;background:#1a3c5e;padding:9px 14px;border-radius:5px;margin-bottom:16px}
.section-title.blue{background:#2980b9}
.section-title.green{background:#27ae60}
.section-title.purple{background:#8e44ad}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:5px}
.form-group.full{grid-column:1/-1}
label{font-size:0.8rem;font-weight:bold;color:#555}
input,select,textarea{padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.88rem;width:100%;font-family:inherit}
textarea{resize:vertical;min-height:90px}
input:focus,select:focus,textarea:focus{outline:none;border-color:#2980b9;box-shadow:0 0 0 2px rgba(41,128,185,0.15)}
.btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:10px}
.btn{padding:10px 24px;border:none;border-radius:4px;cursor:pointer;font-size:0.9rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="advice_list.php">&#128196; All Advice</a>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>
<div class="hero">
  <h1>&#128196; New Legal Advice</h1>
  <p>Complete the form to create a new legal advice record.</p>
</div>
<div class="container">

  <?php if ($error): ?><div class="alert alert-error">&#10060; <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <form method="POST">

    <div class="card">
      <div class="section-title">1. CLIENT DETAILS</div>
      <div class="form-grid">
        <div class="form-group"><label>Client Name *</label><input type="text" name="client_name" placeholder="Full name of client" required/></div>
        <div class="form-group"><label>Matter Type</label>
          <select name="matter_type">
            <option value="">— Select area of law —</option>
            <?php foreach ($matterTypes as $m): ?><option value="<?php echo $m; ?>"><?php echo $m; ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Email</label><input type="email" name="client_email" placeholder="client@email.com"/></div>
        <div class="form-group"><label>Phone</label><input type="text" name="client_phone" placeholder="+44 ..."/></div>
        <div class="form-group full"><label>Address</label><textarea name="client_address" placeholder="Client's address"></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title blue">2. SUBJECT &amp; BACKGROUND</div>
      <div class="form-grid">
        <div class="form-group full"><label>Subject / Matter Title *</label><input type="text" name="subject" placeholder="e.g. Advice on termination of employment contract" required/></div>
        <div class="form-group full"><label>Background / Facts</label><textarea name="background" rows="5" placeholder="Describe the background facts..."></textarea></div>
        <div class="form-group full"><label>Legal Issues Raised</label><textarea name="legal_issues" rows="4" placeholder="What legal questions need to be addressed?"></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title green">3. ADVICE &amp; RECOMMENDATIONS</div>
      <div class="form-grid">
        <div class="form-group full"><label>Legal Advice</label><textarea name="advice" rows="7" placeholder="Set out the legal advice given..."></textarea></div>
        <div class="form-group full"><label>Recommendations</label><textarea name="recommendations" rows="4" placeholder="Recommended steps and next actions..."></textarea></div>
      </div>
    </div>

    <div class="card">
      <div class="section-title purple">4. STATUS &amp; DISCLAIMER</div>
      <div class="form-grid">
        <div class="form-group"><label>Status</label>
          <select name="status">
            <option value="draft">Draft</option>
            <option value="final">Final</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>
        <div class="form-group full"><label>Disclaimer</label><textarea name="disclaimer" rows="3" placeholder="e.g. This advice is given based on the facts presented and the law as at the date hereof..."></textarea></div>
      </div>
    </div>

    <div class="btn-row">
      <a href="advice_list.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">&#128190; Save Legal Advice</button>
    </div>

  </form>
</div>
</body>
</html>
