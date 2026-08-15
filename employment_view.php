<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM employment_cases WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) { header('Location: employment_list.php'); exit; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Employment Case — <?php echo htmlspecialchars($c['claimant_name']); ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;color:#333}
.topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center}
.topbar .brand{font-size:1.1rem;font-weight:bold}
.topbar a{color:#fff;text-decoration:none;font-size:0.88rem;margin-left:16px}
.topbar a:hover{text-decoration:underline}
.hero{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;padding:28px 40px;margin-bottom:30px}
.hero h1{font-size:1.5rem}
.hero p{font-size:0.88rem;opacity:0.85;margin-top:4px}
.hero .meta{display:flex;gap:20px;margin-top:12px;flex-wrap:wrap}
.hero .meta span{background:rgba(255,255,255,0.15);padding:4px 12px;border-radius:20px;font-size:0.82rem}
.container{max-width:1000px;margin:0 auto;padding:0 28px 40px}
.btn-row{display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap}
.btn{padding:9px 20px;border:none;border-radius:4px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block}
.btn-primary{background:#1a3c5e;color:#fff}
.btn-print{background:#27ae60;color:#fff}
.btn-delete{background:#e74c3c;color:#fff}
.btn-back{background:#6c757d;color:#fff}
.card{background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:24px}
.section-title{font-size:0.95rem;font-weight:bold;color:#fff;padding:10px 16px;border-radius:6px;margin-bottom:18px}
.section-title.blue{background:#1a3c5e}
.section-title.red{background:#c0392b}
.section-title.orange{background:#e67e22}
.section-title.green{background:#27ae60}
.section-title.purple{background:#8e44ad}
.section-title.teal{background:#16a085}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.info-item{display:flex;flex-direction:column;gap:4px}
.info-item.full{grid-column:1/-1}
.info-label{font-size:0.75rem;font-weight:bold;color:#888;text-transform:uppercase}
.info-value{font-size:0.9rem;color:#333;padding:8px 12px;background:#f8f9fa;border-radius:4px;min-height:36px}
.info-value.long{white-space:pre-wrap;min-height:60px}
.badge{display:inline-block;padding:4px 14px;border-radius:12px;font-size:0.8rem;font-weight:bold}
.badge-draft{background:#fff3cd;color:#856404}
.badge-active{background:#cce5ff;color:#004085}
.badge-tribunal{background:#d6d8f7;color:#2c2f8a}
.badge-settled{background:#d4edda;color:#155724}
.badge-won{background:#d4edda;color:#155724}
.badge-lost{background:#f8d7da;color:#721c24}
.badge-withdrawn{background:#e2e3e5;color:#383d41}
.badge-closed{background:#e2e3e5;color:#383d41}
.claims-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.claim-item{padding:8px 12px;border-radius:6px;font-size:0.82rem;text-align:center;font-weight:bold}
.claim-yes{background:#d4edda;color:#155724}
.claim-no{background:#f8f9fa;color:#aaa}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">⚖️ AEP Legal Platform</div>
  <div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="employment_list.php">💼 All Cases</a>
    <a href="employment_print.php?id=<?php echo $c['id']; ?>">🖨 Print</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="hero">
  <h1>💼 <?php echo htmlspecialchars($c['claimant_name']); ?> v <?php echo htmlspecialchars($c['respondent_name']); ?></h1>
  <p><?php echo htmlspecialchars($c['case_type']); ?> — <?php echo htmlspecialchars($c['case_reference'] ?: 'No Reference'); ?></p>
  <div class="meta">
    <span>📋 <?php echo htmlspecialchars($c['case_type']); ?></span>
    <span>📅 Created: <?php echo date('d M Y', strtotime($c['created_at'])); ?></span>
    <?php if ($c['hearing_date']): ?>
    <span>⚖️ Hearing: <?php echo date('d M Y', strtotime($c['hearing_date'])); ?></span>
    <?php endif; ?>
    <span>Status: <strong><?php echo ucfirst($c['status']); ?></strong></span>
  </div>
</div>

<div class="container">

  <div class="btn-row">
    <a href="employment_list.php" class="btn btn-back">← Back to List</a>
    <a href="employment_print.php?id=<?php echo $c['id']; ?>" class="btn btn-print">🖨 Print Case</a>
    <form method="POST" action="employment_list.php" style="display:inline" onsubmit="return confirm('Delete this case?')">
    <?php require_once __DIR__ . '/csrf.php'; echo csrf_input(); ?>
    <input type="hidden" name="delete_id" value="<?php echo (int)$c['id']; ?>"/>
    <button type="submit" class="btn btn-delete">🗑 Delete</button>
  </form>
  </div>

  <!-- CASE DETAILS -->
  <div class="card">
    <div class="section-title blue">1. CASE DETAILS</div>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Case Type</div>
        <div class="info-value"><?php echo htmlspecialchars($c['case_type']); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Status</div>
        <div class="info-value"><span class="badge badge-<?php echo htmlspecialchars($c['status']); ?>"><?php echo ucfirst(htmlspecialchars($c['status'])); ?></span></div>
      </div>
      <div class="info-item">
        <div class="info-label">Case Reference</div>
        <div class="info-value"><?php echo htmlspecialchars($c['case_reference'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Tribunal Reference</div>
        <div class="info-value"><?php echo htmlspecialchars($c['tribunal_reference'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Lawyer / Solicitor</div>
        <div class="info-value"><?php echo htmlspecialchars($c['lawyer_name'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Law Firm</div>
        <div class="info-value"><?php echo htmlspecialchars($c['law_firm'] ?: '—'); ?></div>
      </div>
    </div><!-- /.info-grid -->
  </div><!-- /.card CASE DETAILS -->

  <!-- PARTIES -->
  <div class="card">
    <div class="section-title red">2. PARTIES</div>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Claimant Name</div>
        <div class="info-value"><?php echo htmlspecialchars($c['claimant_name'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Respondent Name</div>
        <div class="info-value"><?php echo htmlspecialchars($c['respondent_name'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Claimant Email</div>
        <div class="info-value"><?php echo htmlspecialchars($c['claimant_email'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Claimant Phone</div>
        <div class="info-value"><?php echo htmlspecialchars($c['claimant_phone'] ?: '—'); ?></div>
      </div>
    </div>
  </div>

  <!-- EMPLOYMENT DETAILS -->
  <div class="card">
    <div class="section-title orange">3. EMPLOYMENT DETAILS</div>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Job Title</div>
        <div class="info-value"><?php echo htmlspecialchars($c['job_title'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Salary</div>
        <div class="info-value"><?php echo htmlspecialchars($c['salary'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Employment Start</div>
        <div class="info-value"><?php echo htmlspecialchars($c['employment_start'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Employment End</div>
        <div class="info-value"><?php echo htmlspecialchars($c['employment_end'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Tribunal</div>
        <div class="info-value"><?php echo htmlspecialchars($c['tribunal'] ?: '—'); ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Case Number</div>
        <div class="info-value"><?php echo htmlspecialchars($c['case_number'] ?: '—'); ?></div>
      </div>
    </div>
  </div>

  <!-- CLAIM DETAILS -->
  <div class="card">
    <div class="section-title purple">4. CLAIM DETAILS</div>
    <div class="info-grid">
      <div class="info-item full">
        <div class="info-label">Claim Details</div>
        <div class="info-value long"><?php echo htmlspecialchars($c['claim_details'] ?: '—'); ?></div>
      </div>
      <div class="info-item full">
        <div class="info-label">Remedy Sought</div>
        <div class="info-value long"><?php echo htmlspecialchars($c['remedy_sought'] ?: '—'); ?></div>
      </div>
    </div>
  </div>

</div><!-- /.container -->
</body>
</html>
