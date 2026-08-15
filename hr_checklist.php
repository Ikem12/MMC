<?php
// FILE: hr_checklist.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Optional: load a specific case if id is provided
$case = null;
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM human_rights_cases WHERE id = ?");
    $stmt->execute([$id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

$checklist = [
    'Initial Assessment' => [
        'Client identity and capacity confirmed',
        'Nature of human rights violation identified',
        'Relevant right(s) under ECHR / domestic law identified',
        'Limitation periods checked',
        'Urgency of the matter assessed',
    ],
    'Evidence Gathering' => [
        'Client witness statement obtained',
        'Supporting documentary evidence collected',
        'Medical evidence obtained (if applicable)',
        'Expert reports commissioned (if applicable)',
        'Third party witness statements gathered',
    ],
    'Legal Analysis' => [
        'Applicable articles / sections identified (e.g. ECHR Art. 6, CFRN s.33)',
        'Case law researched and relevant precedents identified',
        'Public authority / respondent identified',
        'Grounds of challenge clearly formulated',
        'Human rights impact assessment conducted',
    ],
    'Pre-Action Steps' => [
        'Letter before action / pre-action protocol letter sent',
        'Response from respondent received or deadline elapsed',
        'Internal complaints procedure exhausted',
        'Alternative dispute resolution (ADR) considered',
        'Funding and legal aid assessed',
    ],
    'Court / Tribunal Filing' => [
        'Claim / application form prepared',
        'Supporting evidence bundle compiled',
        'Skeleton argument / written submissions drafted',
        'Filing deadline met',
        'Service on respondent confirmed',
    ],
    'Relief & Remedy' => [
        'Injunctive relief considered',
        'Declaration of incompatibility assessed',
        'Damages / compensation calculated',
        'Costs position reviewed',
        'Enforcement mechanism identified',
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Human Rights Checklist &mdash; AEP Legal Platform</title>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
header{background:#b5451b;color:#fff;padding:22px 20px;text-align:center}
header h1{font-size:1.5rem}
header p{font-size:0.9rem;opacity:0.85;margin-top:4px}
nav{background:#7a2e0e;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
nav a:hover{background:rgba(255,255,255,0.3)}
.container{max-width:900px;margin:30px auto;padding:0 18px 40px}
.case-banner{background:#fff;border-left:4px solid #b5451b;border-radius:4px;padding:14px 18px;margin-bottom:24px;box-shadow:0 1px 6px rgba(0,0,0,0.07)}
.case-banner h2{font-size:1rem;color:#7a2e0e}
.case-banner p{font-size:0.88rem;color:#555;margin-top:4px}
.section-card{background:#fff;border-radius:8px;padding:20px 22px;box-shadow:0 2px 8px rgba(0,0,0,0.07);margin-bottom:18px}
.section-card h3{font-size:0.95rem;font-weight:bold;color:#fff;background:#b5451b;padding:8px 14px;border-radius:5px;margin-bottom:14px}
.checklist-item{display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5}
.checklist-item:last-child{border-bottom:none}
.checklist-item input[type=checkbox]{margin-top:2px;width:16px;height:16px;accent-color:#b5451b;flex-shrink:0}
.checklist-item label{font-size:0.9rem;color:#333;cursor:pointer}
.btn{display:inline-block;padding:9px 20px;background:#b5451b;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem;border:none;cursor:pointer}
.btn:hover{background:#7a2e0e}
.btn-secondary{background:#6c757d}
.btn-secondary:hover{background:#495057}
.no-print{margin-bottom:16px}
@media print{nav,.no-print,.btn{display:none}body{background:#fff}header{padding:10px}}
</style>
</head>
<body>
<header>
  <h1>🕊️ Human Rights Law Checklist</h1>
  <p>AEP Legal Platform &mdash; Case Preparation Checklist</p>
</header>
<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="hr_create.php">New Case</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="logout.php">Logout</a>
</nav>
<div class="container">

  <?php if ($case): ?>
  <div class="case-banner">
    <h2>📋 Checklist for: <?php echo htmlspecialchars($case['case_title'] ?? 'Case #' . $case['id']); ?></h2>
    <p>Applicant: <?php echo htmlspecialchars($case['applicant_name'] ?? '—'); ?> &nbsp;|&nbsp; Status: <?php echo ucfirst(htmlspecialchars($case['status'] ?? 'open')); ?> &nbsp;|&nbsp; <a href="hr_view.php?id=<?php echo $case['id']; ?>">View Case →</a></p>
  </div>
  <?php endif; ?>

  <div class="no-print" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap">
    <button onclick="window.print()" class="btn">🖨 Print Checklist</button>
    <a href="human-rights.php" class="btn btn-secondary">← Back to Cases</a>
  </div>

  <?php foreach ($checklist as $section => $items): ?>
  <div class="section-card">
    <h3><?php echo htmlspecialchars($section); ?></h3>
    <?php foreach ($items as $i => $item): ?>
    <div class="checklist-item">
      <input type="checkbox" id="item_<?php echo md5($section . $i); ?>"/>
      <label for="item_<?php echo md5($section . $i); ?>"><?php echo htmlspecialchars($item); ?></label>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

</div>
</body>
</html>
