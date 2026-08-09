<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function countTable($pdo, $table) {
    try { return $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(); }
    catch (Exception $e) { return 0; }
}

$counts = [
    'employment'  => countTable($pdo, 'employment_cases'),
    'immigration' => countTable($pdo, 'immigration_cases'),
    'criminal'    => countTable($pdo, 'criminal_cases'),
    'tort'        => countTable($pdo, 'tort_cases'),
    'contract'    => countTable($pdo, 'contract_cases'),
    'oil_gas'     => countTable($pdo, 'oil_gas_cases'),
    'advice'      => countTable($pdo, 'legal_advice'),
    'letters'     => countTable($pdo, 'letters'),
    'witness'     => countTable($pdo, 'witness_statements'),
    'skeleton'    => countTable($pdo, 'skeleton_arguments'),
    'appeals'     => countTable($pdo, 'appeals'),
    'company'     => countTable($pdo, 'company_cases'),
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Dashboard &mdash; AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.topbar a:hover{text-decoration:underline}
.hero{background:linear-gradient(135deg,#1a3c5e,#2e6da4);color:#fff;padding:36px 40px;margin-bottom:30px}
.hero h1{font-size:1.8rem}
.hero p{font-size:0.9rem;opacity:0.85;margin-top:6px}
.hero .meta{margin-top:14px;font-size:0.85rem;opacity:0.75}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.section-heading{font-size:1rem;font-weight:bold;color:#1a3c5e;margin:28px 0 14px;padding-bottom:6px;border-bottom:2px solid #1a3c5e}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;margin-bottom:10px}
.card{background:#fff;border-radius:10px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,0.07);text-decoration:none;color:#333;display:flex;flex-direction:column;gap:10px;transition:transform .15s,box-shadow .15s;border-top:4px solid #1a3c5e}
.card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,0.12)}
.card .icon{font-size:2rem}
.card .title{font-size:0.95rem;font-weight:bold;color:#1a3c5e}
.card .count{font-size:1.5rem;font-weight:bold}
.card .label{font-size:0.75rem;color:#888}
.card .actions{display:flex;gap:8px;margin-top:6px}
.card .actions a{font-size:0.78rem;padding:5px 10px;border-radius:4px;text-decoration:none;font-weight:bold}
.btn-view{background:#e8f0fe;color:#1a3c5e}
.btn-new{background:#1a3c5e;color:#fff}
.emp{border-top-color:#c0392b}.emp .title{color:#c0392b}
.imm{border-top-color:#2980b9}.imm .title{color:#2980b9}
.crim{border-top-color:#2c3e50}.crim .title{color:#2c3e50}
.tort{border-top-color:#8e44ad}.tort .title{color:#8e44ad}
.cont{border-top-color:#e67e22}.cont .title{color:#e67e22}
.oil{border-top-color:#16a085}.oil .title{color:#16a085}
.adv{border-top-color:#27ae60}.adv .title{color:#27ae60}
.let{border-top-color:#f39c12}.let .title{color:#f39c12}
.wit{border-top-color:#d35400}.wit .title{color:#d35400}
.ske{border-top-color:#7f8c8d}.ske .title{color:#7f8c8d}
.app{border-top-color:#c0392b}.app .title{color:#c0392b}
.com{border-top-color:#1abc9c}.com .title{color:#1abc9c}
.stat-bar{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
.stat{background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 2px 8px rgba(0,0,0,0.07);text-align:center}
.stat .num{font-size:2rem;font-weight:bold;color:#1a3c5e}
.stat .lbl{font-size:0.78rem;color:#888;margin-top:4px}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="admin.php">&#9881;&#65039; Admin</a>
    <a href="users.php">&#128100; Users</a>
    <a href="logout.php">&#128682; Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
  </div>
</div>

<div class="hero">
  <h1>&#9878;&#65039; AEP Legal Platform</h1>
  <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Manage all your legal cases from one place.</p>
  <div class="meta">&#128197; <?php echo date('l, d F Y'); ?> &nbsp;|&nbsp; &#128336; <?php echo date('H:i'); ?></div>
</div>

<div class="container">

  <!-- STATS -->
  <div class="stat-bar">
    <div class="stat"><div class="num"><?php echo array_sum($counts); ?></div><div class="lbl">Total Cases</div></div>
    <div class="stat"><div class="num"><?php echo $counts['employment'] + $counts['immigration'] + $counts['criminal']; ?></div><div class="lbl">Active Law Cases</div></div>
    <div class="stat"><div class="num"><?php echo $counts['advice'] + $counts['letters']; ?></div><div class="lbl">Advice &amp; Letters</div></div>
    <div class="stat"><div class="num"><?php echo $counts['appeals'] + $counts['skeleton'] + $counts['witness']; ?></div><div class="lbl">Court Documents</div></div>
  </div>

  <!-- LAW DOMAINS -->
  <div class="section-heading">&#128188; Law Domains</div>
  <div class="grid">

    <div class="card emp">
      <div class="icon">&#128188;</div>
      <div class="title">Employment Law</div>
      <div class="count"><?php echo $counts['employment']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="employment_list.php" class="btn-view">&#128065; View All</a>
        <a href="employment_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card imm">
      <div class="icon">&#9992;&#65039;</div>
      <div class="title">Immigration Law</div>
      <div class="count"><?php echo $counts['immigration']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="immigration_list.php" class="btn-view">&#128065; View All</a>
        <a href="immigration_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card crim">
      <div class="icon">&#9878;</div>
      <div class="title">Criminal Law</div>
      <div class="count"><?php echo $counts['criminal']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="criminal_list.php" class="btn-view">&#128065; View All</a>
        <a href="criminal_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card tort">
      <div class="icon">&#9878;&#65039;</div>
      <div class="title">Tort Law</div>
      <div class="count"><?php echo $counts['tort']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="tort_list.php" class="btn-view">&#128065; View All</a>
        <a href="tort_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card cont">
      <div class="icon">&#128221;</div>
      <div class="title">Contract Law</div>
      <div class="count"><?php echo $counts['contract']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="contract_list.php" class="btn-view">&#128065; View All</a>
        <a href="contract_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card oil">
      <div class="icon">&#128167;</div>
      <div class="title">Oil &amp; Gas Law</div>
      <div class="count"><?php echo $counts['oil_gas']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="oil_gas_list.php" class="btn-view">&#128065; View All</a>
        <a href="oil_gas_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card com">
      <div class="icon">&#127970;</div>
      <div class="title">Company Law</div>
      <div class="count"><?php echo $counts['company']; ?></div>
      <div class="label">Cases on record</div>
      <div class="actions">
        <a href="company_list.php" class="btn-view">&#128065; View All</a>
        <a href="company_create.php" class="btn-new">+ New</a>
      </div>
    </div>

  </div>

  <!-- COURT DOCUMENTS -->
  <div class="section-heading">&#128196; Court Documents &amp; Legal Tools</div>
  <div class="grid">

    <div class="card adv">
      <div class="icon">&#128172;</div>
      <div class="title">Legal Advice</div>
      <div class="count"><?php echo $counts['advice']; ?></div>
      <div class="label">Advice records</div>
      <div class="actions">
        <a href="advice_list.php" class="btn-view">&#128065; View All</a>
        <a href="advice_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card let">
      <div class="icon">&#9993;&#65039;</div>
      <div class="title">Letters</div>
      <div class="count"><?php echo $counts['letters']; ?></div>
      <div class="label">Letters on record</div>
      <div class="actions">
        <a href="letter_list.php" class="btn-view">&#128065; View All</a>
        <a href="letter_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card wit">
      <div class="icon">&#128100;</div>
      <div class="title">Witness Statements</div>
      <div class="count"><?php echo $counts['witness']; ?></div>
      <div class="label">Statements on record</div>
      <div class="actions">
        <a href="witness_list.php" class="btn-view">&#128065; View All</a>
        <a href="witness_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card ske">
      <div class="icon">&#128196;</div>
      <div class="title">Skeleton Arguments</div>
      <div class="count"><?php echo $counts['skeleton']; ?></div>
      <div class="label">Arguments on record</div>
      <div class="actions">
        <a href="skeleton_list.php" class="btn-view">&#128065; View All</a>
        <a href="skeleton_create.php" class="btn-new">+ New</a>
      </div>
    </div>

    <div class="card app">
      <div class="icon">&#128209;</div>
      <div class="title">Appeals</div>
      <div class="count"><?php echo $counts['appeals']; ?></div>
      <div class="label">Appeals on record</div>
      <div class="actions">
        <a href="appeal_list.php" class="btn-view">&#128065; View All</a>
        <a href="appeal_create.php" class="btn-new">+ New</a>
      </div>
    </div>

  </div>

  <!-- ADMIN -->
  <div class="section-heading">&#9881;&#65039; Administration</div>
  <div class="grid">
    <a href="admin.php" class="card" style="border-top-color:#e74c3c">
      <div class="icon">&#9881;&#65039;</div>
      <div class="title" style="color:#e74c3c">Admin Panel</div>
      <div class="label">Manage platform settings</div>
    </a>
    <a href="users.php" class="card" style="border-top-color:#3498db">
      <div class="icon">&#128101;</div>
      <div class="title" style="color:#3498db">User Management</div>
      <div class="label">Manage platform users</div>
    </a>
    <a href="admin_law.php" class="card" style="border-top-color:#9b59b6">
      <div class="icon">&#128218;</div>
      <div class="title" style="color:#9b59b6">Law Library</div>
      <div class="label">Manage legal references</div>
    </a>
    <a href="setup_db.php" class="card" style="border-top-color:#27ae60">
      <div class="icon">&#128190;</div>
      <div class="title" style="color:#27ae60">Database Setup</div>
      <div class="label">Initialise / repair database</div>
    </a>
  </div>

</div>
</body>
</html>
