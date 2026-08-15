<?php
// FILE: hr_create.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title'] ?? '');
    $right_violated  = trim($_POST['right_violated'] ?? '');
    $claimant        = trim($_POST['claimant'] ?? '');
    $respondent      = trim($_POST['respondent'] ?? '');
    $article_section = trim($_POST['article_section'] ?? '');
    $violation_date  = trim($_POST['violation_date'] ?? '');
    $remedy          = trim($_POST['remedy'] ?? '');
    $summary         = trim($_POST['summary'] ?? '');
    $grounds         = trim($_POST['grounds'] ?? '');
    $status          = trim($_POST['status'] ?? 'open');

    if ($title === '') {
        $error = 'Case title is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO human_rights_cases
            (case_title, rights_violated, applicant_name, respondent, article_relied_on,
             incident_date, relief_sought, background, notes, status, lawyer_name, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $title,
            $right_violated,
            $claimant,
            $respondent,
            $article_section,
            $violation_date,
            $remedy,
            $summary,
            $grounds,
            $status,
            $_SESSION['username'] ?? 'unknown',
            date('Y-m-d H:i:s'),
        ]);
        $success = 'Case created successfully!';
    }
}

$rightsViolated = [
    'Right to Life',
    'Freedom from Torture & Inhuman Treatment',
    'Right to Liberty & Security',
    'Right to Fair Trial',
    'Right to Privacy',
    'Freedom of Expression',
    'Freedom of Assembly & Association',
    'Freedom of Religion & Belief',
    'Right to Education',
    'Right to Free Elections',
    'Protection from Discrimination',
    'Right to Property',
    'Right to Health',
    'Right to Housing',
];

$remedies = [
    'Declaration of Rights Violation',
    'Injunction',
    'Damages / Compensation',
    'Release from Detention',
    'Reinstatement',
    'Public Apology',
    'Policy Change',
    'Constitutional Relief',
];

$statusOptions = ['open','pending','closed'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>New Human Rights Case — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#b5451b;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.6rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#b5451b;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#7a2e0e;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:780px;margin:30px auto;padding:0 18px}
    .card{background:#fff;border-radius:8px;padding:28px 24px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
    h2{font-size:1.2rem;color:#7a2e0e;margin-bottom:20px;border-bottom:2px solid #b5451b;padding-bottom:8px}
    .form-group{margin-bottom:16px}
    label{display:block;font-size:0.88rem;font-weight:bold;margin-bottom:5px;color:#333}
    input[type=text],input[type=date],select,textarea{width:100%;padding:9px 12px;border:1px solid #ccd;border-radius:4px;font-size:0.9rem;font-family:inherit}
    textarea{resize:vertical;min-height:90px}
    input:focus,select:focus,textarea:focus{outline:none;border-color:#b5451b;box-shadow:0 0 0 2px rgba(181,69,27,0.15)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .btn{display:inline-block;padding:10px 24px;background:#b5451b;color:#fff;border:none;border-radius:4px;font-size:0.95rem;cursor:pointer;text-decoration:none}
    .btn:hover{background:#7a2e0e}
    .btn-secondary{background:#6c757d;margin-left:10px}
    .btn-secondary:hover{background:#495057}
    .alert{padding:10px 16px;border-radius:4px;margin-bottom:16px;font-size:0.9rem}
    .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
    .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
    .section-label{font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;color:#b5451b;font-weight:bold;margin:20px 0 10px;border-top:1px solid #e0e0e0;padding-top:14px}
  </style>
</head>
<body>

<header>
  <h1>🕊️ Human Rights Law <span class="file-tag">FILE: hr_create.php</span></h1>
  <p>Create a new Human Rights case</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="hr_create.php">New Case</a>
  <a href="hr_checklist.php">Checklist</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">
  <div class="card">
    <h2>New Human Rights Case</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?php echo $success; ?>
        <a href="human-rights.php" style="margin-left:12px;color:#155724;font-weight:bold">View All Cases →</a>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="section-label">Case Information</div>

      <div class="form-group">
        <label for="title">Case Title *</label>
        <input type="text" id="title" name="title"
               placeholder="e.g. Okafor v Federal Republic of Nigeria" required/>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="right_violated">Right Violated</label>
          <select id="right_violated" name="right_violated">
            <option value="">— Select —</option>
            <?php foreach ($rightsViolated as $r): ?>
              <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statusOptions as $s): ?>
              <option value="<?php echo $s; ?>"><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="section-label">Parties</div>

      <div class="form-row">
        <div class="form-group">
          <label for="claimant">Claimant / Applicant</label>
          <input type="text" id="claimant" name="claimant"
                 placeholder="Name of claimant or applicant"/>
        </div>
        <div class="form-group">
          <label for="respondent">Respondent</label>
          <input type="text" id="respondent" name="respondent"
                 placeholder="e.g. State, Government Body, Authority"/>
        </div>
      </div>

      <div class="section-label">Legal Details</div>

      <div class="form-row">
        <div class="form-group">
          <label for="article_section">Article / Section / Provision</label>
          <input type="text" id="article_section" name="article_section"
                 placeholder="e.g. Article 6 ECHR, Section 33 CFRN"/>
        </div>
        <div class="form-group">
          <label for="violation_date">Date of Violation</label>
          <input type="date" id="violation_date" name="violation_date"/>
        </div>
      </div>

      <div class="form-group">
        <label for="grounds">Grounds of Claim</label>
        <input type="text" id="grounds" name="grounds"
               placeholder="e.g. Unlawful detention, denial of fair hearing, torture"/>
      </div>

      <div class="form-group">
        <label for="remedy">Remedy Sought</label>
        <select id="remedy" name="remedy">
          <option value="">— Select —</option>
          <?php foreach ($remedies as $r): ?>
            <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="section-label">Summary</div>

      <div class="form-group">
        <label for="summary">Case Summary</label>
        <textarea id="summary" name="summary"
                  placeholder="Brief description of the violation, facts and issues..."></textarea>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="btn">💾 Save Case</button>
        <a href="human-rights.php" class="btn btn-secondary">Cancel</a>
      </div>

    </form>
  </div>
</div>
</body>
</html>