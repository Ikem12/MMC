<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure new tables exist for installations that haven't re-run setup
$pdo->exec("CREATE TABLE IF NOT EXISTS property_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_type TEXT, case_title TEXT, case_reference TEXT, status TEXT DEFAULT 'draft',
  client_name TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS latin_maxims (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  maxim TEXT NOT NULL, meaning TEXT NOT NULL, category TEXT DEFAULT 'General',
  details TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$counts = [
  'advice'      => $pdo->query("SELECT COUNT(*) FROM legal_advice")->fetchColumn(),
  'letters'     => $pdo->query("SELECT COUNT(*) FROM draft_letters")->fetchColumn(),
  'witness'     => $pdo->query("SELECT COUNT(*) FROM witness_statements")->fetchColumn(),
  'skeleton'    => $pdo->query("SELECT COUNT(*) FROM skeleton_arguments")->fetchColumn(),
  'appeal'      => $pdo->query("SELECT COUNT(*) FROM grounds_of_appeal")->fetchColumn(),
  'immigration' => $pdo->query("SELECT COUNT(*) FROM immigration_cases")->fetchColumn(),
  'employment'  => $pdo->query("SELECT COUNT(*) FROM employment_cases")->fetchColumn(),
  'property'    => $pdo->query("SELECT COUNT(*) FROM property_cases")->fetchColumn(),
  'latin'       => $pdo->query("SELECT COUNT(*) FROM latin_maxims")->fetchColumn(),
];
$total = array_sum($counts);

$recent = [];
foreach ([
  "SELECT 'Legal Advice' as module, client_name as title, status, created_at FROM legal_advice ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Letter' as module, recipient_name as title, status, created_at FROM draft_letters ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Witness Statement' as module, case_title as title, status, created_at FROM witness_statements ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Skeleton Argument' as module, case_title as title, status, created_at FROM skeleton_arguments ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Grounds of Appeal' as module, case_title as title, status, created_at FROM grounds_of_appeal ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Immigration' as module, applicant_name as title, status, created_at FROM immigration_cases ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Employment' as module, claimant_name as title, status, created_at FROM employment_cases ORDER BY created_at DESC LIMIT 2",
  "SELECT 'Property' as module, COALESCE(case_title, client_name) as title, status, created_at FROM property_cases ORDER BY created_at DESC LIMIT 2",
] as $sql) {
  $recent = array_merge($recent, $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
}
usort($recent, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
$recent = array_slice($recent, 0, 10);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Dashboard — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar .nav{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem}
.topbar a:hover{text-decoration:underline}
.topbar .user{background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:20px;font-size:0.85rem}
.hero{background:linear-gradient(135deg,#1a3c5e,#2e6da4);color:#fff;padding:36px 40px;margin-bottom:30px}
.hero h1{font-size:1.8rem;margin-bottom:6px}
.hero p{font-size:0.95rem;opacity:0.85}
.hero .date{font-size:0.85rem;opacity:0.7;margin-top:8px}
.container{max-width:1200px;margin:0 auto;padding:0 28px 40px}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:14px;margin-bottom:30px}
.stat-card{background:#fff;border-radius:10px;padding:18px 12px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.07);border-top:4px solid #1a3c5e;transition:transform 0.2s}
.stat-card:hover{transform:translateY(-3px)}
.stat-card.advice{border-top-color:#28a745}
.stat-card.letters{border-top-color:#17a2b8}
.stat-card.witness{border-top-color:#fd7e14}
.stat-card.skeleton{border-top-color:#6f42c1}
.stat-card.appeal{border-top-color:#dc3545}
.stat-card.immigration{border-top-color:#003366}
.stat-card.employment{border-top-color:#c0392b}
.stat-card.property{border-top-color:#16a085}
.stat-card.latin{border-top-color:#2c3e50}
.stat-number{font-size:1.8rem;font-weight:bold;color:#1a3c5e}
.stat-label{font-size:0.72rem;color:#888;margin-top:4px}
.stat-icon{font-size:1.3rem;margin-bottom:4px}
.section-heading{font-size:1rem;font-weight:bold;color:#1a3c5e;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #e0e6ef}
.quick-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:12px;margin-bottom:30px}
.quick-btn{background:#fff;border-radius:8px;padding:14px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);text-decoration:none;color:#1a3c5e;transition:all 0.2s;border:1px solid #e0e6ef;display:block}
.quick-btn:hover{background:#1a3c5e;color:#fff;transform:translateY(-2px)}
.quick-btn .q-icon{font-size:1.4rem;margin-bottom:6px}
.quick-btn .q-label{font-size:0.75rem;font-weight:bold}
.modules-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:30px}
.module-card{background:#fff;border-radius:10px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,0.07);display:flex;flex-direction:column;gap:10px}
.module-card.reserved{opacity:0.65;border:2px dashed #ccc}
.mod-header{display:flex;align-items:center;gap:12px}
.mod-icon{font-size:1.8rem}
.mod-title{font-size:0.95rem;font-weight:bold;color:#1a3c5e}
.mod-count{font-size:0.82rem;color:#888}
.mod-desc{font-size:0.82rem;color:#666;line-height:1.5}
.mod-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.btn{padding:7px 14px;border:none;border-radius:4px;cursor:pointer;font-size:0.8rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-primary:hover{background:#122840}
.btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
.btn-outline:hover{background:#f0f4ff}
.btn-reserved{background:#f8f9fa;color:#aaa;border:1px solid #ddd;cursor:not-allowed}
.badge-reserved{background:#fff3cd;color:#856404;padding:2px 8px;border-radius:4px;font-size:0.7rem;font-weight:bold}
.badge-new{background:#d4edda;color:#155724;padding:2px 8px;border-radius:4px;font-size:0.7rem;font-weight:bold}
.recent-card{background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:30px}
table{width:100%;border-collapse:collapse;font-size:0.87rem}
th{background:#f4f6f9;color:#555;padding:10px 12px;text-align:left;font-size:0.78rem;text-transform:uppercase}
td{padding:10px 12px;border-bottom:1px solid #f0f0f0}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f8f9ff}
.badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.72rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-final{background:#d4edda;color:#155724}
.badge-filed{background:#cce5ff;color:#004085}
.badge-sent{background:#cce5ff;color:#004085}
.badge-active{background:#cce5ff;color:#004085}
.badge-won{background:#d4edda;color:#155724}
.badge-lost{background:#f8d7da;color:#721c24}
.badge-settled{background:#d4edda;color:#155724}
.badge-tribunal{background:#d6d8f7;color:#2c2f8a}
.badge-withdrawn{background:#e2e3e5;color:#383d41}
.badge-closed{background:#e2e3e5;color:#383d41}
.mod-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:0.72rem;background:#e8eef5;color:#1a3c5e;font-weight:bold}
.dash-footer{text-align:center;padding:20px;color:#aaa;font-size:0.8rem;border-top:1px solid #e0e6ef}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP Legal Platform</div>
  <div class="nav">
    <a href="advice_list.php">Legal Advice</a>
    <a href="letter_list.php">Letters</a>
    <a href="witness_list.php">Witness</a>
    <a href="skeleton_list.php">Skeleton</a>
    <a href="appeal_list.php">Appeals</a>
    <a href="immigration_list.php">&#127468;&#127463; Immigration</a>
    <a href="employment_list.php">&#128188; Employment</a>
    <a href="property_law.php">&#127968; Property</a>
    <a href="latin_list.php">&#9878;&#65039; Latin</a>
    <span class="user">&#128100; <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
    <a href="logout.php">&#128682; Logout</a>
  </div>
</div>

<div class="hero">
  <h1>Welcome to AEP Legal Platform &#128075;</h1>
  <p>Manage all your legal documents, court processes and client matters in one place.</p>
  <div class="date">&#128197; <?php echo date('l, d F Y'); ?></div>
</div>

<div class="container">

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">&#128202;</div>
      <div class="stat-number"><?php echo $total; ?></div>
      <div class="stat-label">Total Documents</div>
    </div>
    <div class="stat-card advice">
      <div class="stat-icon">&#128221;</div>
      <div class="stat-number"><?php echo $counts['advice']; ?></div>
      <div class="stat-label">Legal Advice</div>
    </div>
    <div class="stat-card letters">
      <div class="stat-icon">&#9993;&#65039;</div>
      <div class="stat-number"><?php echo $counts['letters']; ?></div>
      <div class="stat-label">Letters</div>
    </div>
    <div class="stat-card witness">
      <div class="stat-icon">&#128100;</div>
      <div class="stat-number"><?php echo $counts['witness']; ?></div>
      <div class="stat-label">Witness</div>
    </div>
    <div class="stat-card skeleton">
      <div class="stat-icon">&#128220;</div>
      <div class="stat-number"><?php echo $counts['skeleton']; ?></div>
      <div class="stat-label">Skeleton Args</div>
    </div>
    <div class="stat-card appeal">
      <div class="stat-icon">&#127963;&#65039;</div>
      <div class="stat-number"><?php echo $counts['appeal']; ?></div>
      <div class="stat-label">Appeals</div>
    </div>
    <div class="stat-card immigration">
      <div class="stat-icon">&#127468;&#127463;</div>
      <div class="stat-number"><?php echo $counts['immigration']; ?></div>
      <div class="stat-label">Immigration</div>
    </div>
    <div class="stat-card employment">
      <div class="stat-icon">&#128188;</div>
      <div class="stat-number"><?php echo $counts['employment']; ?></div>
      <div class="stat-label">Employment</div>
    </div>
    <div class="stat-card property">
      <div class="stat-icon">&#127968;</div>
      <div class="stat-number"><?php echo $counts['property']; ?></div>
      <div class="stat-label">Property</div>
    </div>
    <div class="stat-card latin">
      <div class="stat-icon">&#9878;&#65039;</div>
      <div class="stat-number"><?php echo $counts['latin']; ?></div>
      <div class="stat-label">Latin Maxims</div>
    </div>
  </div>

  <div class="section-heading">&#9889; Quick Actions</div>
  <div class="quick-grid">
    <a href="advice_create.php" class="quick-btn">
      <div class="q-icon">&#128221;</div>
      <div class="q-label">New Legal Advice</div>
    </a>
    <a href="letter_create.php" class="quick-btn">
      <div class="q-icon">&#9993;&#65039;</div>
      <div class="q-label">Draft Letter</div>
    </a>
    <a href="witness_create.php" class="quick-btn">
      <div class="q-icon">&#128100;</div>
      <div class="q-label">Witness Statement</div>
    </a>
    <a href="skeleton_create.php" class="quick-btn">
      <div class="q-icon">&#128220;</div>
      <div class="q-label">Skeleton Argument</div>
    </a>
    <a href="appeal_create.php" class="quick-btn">
      <div class="q-icon">&#127963;&#65039;</div>
      <div class="q-label">Grounds of Appeal</div>
    </a>
    <a href="immigration_create.php" class="quick-btn">
      <div class="q-icon">&#127468;&#127463;</div>
      <div class="q-label">Immigration Case</div>
    </a>
    <a href="employment_create.php" class="quick-btn">
      <div class="q-icon">&#128188;</div>
      <div class="q-label">Employment Case</div>
    </a>
    <a href="property_create.php" class="quick-btn">
      <div class="q-icon">&#127968;</div>
      <div class="q-label">Property Case</div>
    </a>
    <a href="latin_create.php" class="quick-btn">
      <div class="q-icon">&#9878;&#65039;</div>
      <div class="q-label">Add Latin Maxim</div>
    </a>
  </div>

  <div class="section-heading">&#128194; Modules</div>
  <div class="modules-grid">

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#128221;</div>
        <div>
          <div class="mod-title">Legal Advice</div>
          <div class="mod-count"><?php echo $counts['advice']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Prepare formal legal opinions and advice letters for clients.</div>
      <div class="mod-actions">
        <a href="advice_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="advice_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#9993;&#65039;</div>
        <div>
          <div class="mod-title">Draft Letters</div>
          <div class="mod-count"><?php echo $counts['letters']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Draft demand letters, notices and legal correspondence.</div>
      <div class="mod-actions">
        <a href="letter_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="letter_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#128100;</div>
        <div>
          <div class="mod-title">Witness Statements</div>
          <div class="mod-count"><?php echo $counts['witness']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Prepare sworn witness statements for filing in court.</div>
      <div class="mod-actions">
        <a href="witness_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="witness_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#128220;</div>
        <div>
          <div class="mod-title">Skeleton Arguments</div>
          <div class="mod-count"><?php echo $counts['skeleton']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Prepare detailed skeleton arguments with legal authorities.</div>
      <div class="mod-actions">
        <a href="skeleton_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="skeleton_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#127963;&#65039;</div>
        <div>
          <div class="mod-title">Grounds of Appeal</div>
          <div class="mod-count"><?php echo $counts['appeal']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Prepare and file grounds of appeal against lower court judgments.</div>
      <div class="mod-actions">
        <a href="appeal_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="appeal_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#127468;&#127463;</div>
        <div>
          <div class="mod-title">UK Immigration <span class="badge-new">NEW</span></div>
          <div class="mod-count"><?php echo $counts['immigration']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Handle visa applications, long residency, ILR, appeals and all immigration matters.</div>
      <div class="mod-actions">
        <a href="immigration_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="immigration_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#128188;</div>
        <div>
          <div class="mod-title">Employment Law <span class="badge-new">NEW</span></div>
          <div class="mod-count"><?php echo $counts['employment']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Unfair dismissal, discrimination, tribunal claims and employment contracts.</div>
      <div class="mod-actions">
        <a href="employment_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="employment_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card reserved">
      <div class="mod-header">
        <div class="mod-icon">&#127968;</div>
        <div>
          <div class="mod-title">Property Law <span class="badge-new">NEW</span></div>
          <div class="mod-count"><?php echo $counts['property']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Landlord &amp; Tenant disputes, Lodger agreements, and Commercial lease matters.</div>
      <div class="mod-actions">
        <a href="property_create.php" class="btn btn-primary">&#10133; New</a>
        <a href="property_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

    <div class="module-card">
      <div class="mod-header">
        <div class="mod-icon">&#9878;&#65039;</div>
        <div>
          <div class="mod-title">Latin Maxims <span class="badge-new">NEW</span></div>
          <div class="mod-count"><?php echo $counts['latin']; ?> records</div>
        </div>
      </div>
      <div class="mod-desc">Browse and manage the legal Latin maxims and principles library.</div>
      <div class="mod-actions">
        <a href="latin_create.php" class="btn btn-primary">&#10133; Add</a>
        <a href="latin_list.php" class="btn btn-outline">&#128203; View All</a>
      </div>
    </div>

  </div>

  <div class="section-heading">&#128336; Recent Activity</div>
  <div class="recent-card">
    <?php if (empty($recent)): ?>
      <p style="text-align:center;color:#888;padding:30px">No activity yet. Start by creating a document above!</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Module</th>
            <th>Title / Name</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $i => $row): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><span class="mod-badge"><?php echo htmlspecialchars($row['module']); ?></span></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<div class="dash-footer">
  AEP Legal Platform &mdash; &copy; <?php echo date('Y'); ?> AEP Legal Consultancy. All rights reserved.
&nbsp;|&nbsp; Database v4.0 &nbsp;|&nbsp; 9 Active Modules
</div>

</body>
</html>