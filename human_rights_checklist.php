<?php
// FILE: hr_checklist.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id   = (int)($_GET['id'] ?? 0);
$case = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM human_rights_cases WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

$checklist = [
    ['q' => 'Has a specific right or freedom been identified as violated?',                 'hint' => 'e.g. Right to life, liberty, fair trial, privacy, expression, religion or freedom from torture.'],
    ['q' => 'Is the respondent a public authority or state actor?',                         'hint' => 'Human rights claims generally lie against public authorities — government, police, prison service etc.'],
    ['q' => 'Has the relevant article, section or constitutional provision been identified?','hint' => 'e.g. Article 6 ECHR (fair trial), Section 34 CFRN (dignity), Article 3 ECHR (torture).'],
    ['q' => 'Has the violation been clearly documented with evidence?',                     'hint' => 'Gather witness statements, medical reports, correspondence, photographs or official records.'],
    ['q' => 'Is the claim within the relevant time limit?',                                 'hint' => 'Under the HRA 1998 claims must be brought within 1 year. Constitutional claims vary by jurisdiction.'],
    ['q' => 'Has the claimant exhausted all domestic remedies?',                            'hint' => 'Before going to international bodies, all local court options must typically be exhausted first.'],
    ['q' => 'Is the claimant a victim with standing to bring the claim?',                   'hint' => 'The claimant must be directly affected by the violation — actual or potential victim test applies.'],
    ['q' => 'Has a pre-action letter / letter before claim been sent?',                     'hint' => 'A formal letter notifying the respondent of the claim and remedy sought before issuing proceedings.'],
    ['q' => 'What remedy is being sought?',                                                 'hint' => 'Declaration, damages, injunction, release from detention, reinstatement or policy change.'],
    ['q' => 'Has the proportionality of any interference been assessed?',                   'hint' => 'Was the interference with the right necessary, proportionate and prescribed by law?'],
];

$results   = [];
$submitted = false;
$score     = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    foreach ($checklist as $i => $item) {
        $ans = $_POST['q' . $i] ?? 'no';
        $results[$i] = $ans;
        if ($ans === 'yes') $score++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Human Rights Checklist — AEP Legal Platform</title>
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
    .container{max-width:860px;margin:30px auto;padding:0 18px}
    .card{background:#fff;border-radius:8px;padding:28px 24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px}
    h2{font-size:1.2rem;color:#7a2e0e;margin-bottom:18px;border-bottom:2px solid #b5451b;padding-bottom:8px}
    .checklist-item{border:1px solid #e0e0e0;border-radius:6px;padding:14px 16px;margin-bottom:12px;background:#fafafa}
    .checklist-item.yes{border-left:4px solid #28a745;background:#f0fff4}
    .checklist-item.no{border-left:4px solid #dc3545;background:#fff5f5}
    .checklist-item.na{border-left:4px solid #ffc107;background:#fffdf0}
    .q-text{font-size:0.95rem;font-weight:bold;margin-bottom:6px;color:#222}
    .q-hint{font-size:0.82rem;color:#666;margin-bottom:10px;font-style:italic}
    .radio-group{display:flex;gap:16px;flex-wrap:wrap}
    .radio-group label{font-size:0.88rem;display:flex;align-items:center;gap:5px;cursor:pointer}
    .score-box{text-align:center;padding:24px;border-radius:8px;margin-bottom:20px}
    .score-box.good{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
    .score-box.mid{background:#fff3cd;color:#856404;border:1px solid #ffeeba}
    .score-box.low{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
    .score-box h3{font-size:1.5rem;margin-bottom:6px}
    .score-box p{font-size:0.92rem}
    .btn{display:inline-block;padding:10px 24px;background:#b5451b;color:#fff;border:none;border-radius:4px;font-size:0.95rem;cursor:pointer;text-decoration:none}
    .btn:hover{background:#7a2e0e}
    .btn-secondary{background:#6c757d;margin-left:10px;border:none;cursor:pointer}
    .btn-secondary:hover{background:#495057}
    .case-banner{background:#fdf2ee;border-radius:6px;padding:10px 16px;margin-bottom:18px;font-size:0.9rem;color:#7a2e0e;border:1px solid #f5cdb8}
  </style>
</head>
<body>

<header>
  <h1>🕊️ Human Rights Law <span class="file-tag">FILE: hr_checklist.php</span></h1>
  <p>Pre-litigation Checklist for Human Rights Cases</p>
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

  <?php if ($case): ?>
    <div class="case-banner">
      📁 Running checklist for case: <strong><?php echo htmlspecialchars($case['title']); ?></strong>
      &nbsp;|&nbsp; Right Violated: <strong><?php echo htmlspecialchars($case['right_violated'] ?? '—'); ?></strong>
      &nbsp;|&nbsp; Claimant: <strong><?php echo htmlspecialchars($case['claimant'] ?? '—'); ?></strong>
    </div>
  <?php endif; ?>

  <?php if ($submitted): ?>
    <?php
      $pct = round(($score / count($checklist)) * 100);
      $cls = $pct >= 70 ? 'good' : ($pct >= 40 ? 'mid' : 'low');
      $msg = $pct >= 70
        ? 'Strong position — most pre-litigation requirements are satisfied.'
        : ($pct >= 40
          ? 'Moderate position — several items need attention before proceeding.'
          : 'Weak position — significant gaps identified. Review carefully before filing.');
    ?>
    <div class="score-box <?php echo $cls; ?>">
      <h3>Score: <?php echo $score; ?> / <?php echo count($checklist); ?> (<?php echo $pct; ?>%)</h3>
      <p><?php echo $msg; ?></p>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2>📋 Human Rights Pre-Litigation Checklist</h2>

    <form method="POST" action="hr_checklist.php<?php echo $id ? '?id='.$id : ''; ?>">
      <?php foreach ($checklist as $i => $item): ?>
        <?php $ans = $results[$i] ?? ''; ?>
        <div class="checklist-item <?php echo $submitted ? $ans : ''; ?>">
          <div class="q-text"><?php echo ($i+1) . '. ' . htmlspecialchars($item['q']); ?></div>
          <div class="q-hint">💡 <?php echo htmlspecialchars($item['hint']); ?></div>
          <div class="radio-group">
            <label>
              <input type="radio" name="q<?php echo $i; ?>" value="yes"
                     <?php echo $ans==='yes'?'checked':''; ?> required/> ✅ Yes
            </label>
            <label>
              <input type="radio" name="q<?php echo $i; ?>" value="no"
                     <?php echo $ans==='no'?'checked':''; ?>/> ❌ No
            </label>
            <label>
              <input type="radio" name="q<?php echo $i; ?>" value="na"
                     <?php echo $ans==='na'?'checked':''; ?>/> ➖ N/A
            </label>
          </div>
        </div>
      <?php endforeach; ?>

      <div style="margin-top:20px">
        <button type="submit" class="btn">📊 Submit Checklist</button>
        <a href="human-rights.php" class="btn btn-secondary">← Back</a>
      </div>
    </form>
  </div>

</div>
</body>
</html>