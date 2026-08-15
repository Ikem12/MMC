<?php
// FILE: hr_view.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?return=' . urlencode($_SERVER['REQUEST_URI'] ?? '/hr_view.php'));
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: human-rights.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM human_rights_cases WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: human-rights.php'); exit; }

$title          = $c['case_title']       ?? $c['title']           ?? '—';
$applicant      = $c['applicant_name']   ?? $c['claimant']        ?? '—';
$rights         = $c['rights_violated']  ?? $c['right_violated']  ?? '—';
$article        = $c['article_relied_on']?? $c['article_section'] ?? '—';
$relief         = $c['relief_sought']    ?? $c['remedy']          ?? '—';
$incidentDate   = $c['incident_date']    ?? $c['violation_date']  ?? '—';
$background     = $c['background']       ?? $c['summary']         ?? '—';
$notes          = $c['notes']            ?? $c['grounds']         ?? '—';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title><?php echo htmlspecialchars($title); ?> — Human Rights — AEP</title>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
header{background:#b5451b;color:#fff;padding:22px 20px;text-align:center}
header h1{font-size:1.5rem}
nav{background:#7a2e0e;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
nav a:hover{background:rgba(255,255,255,0.3)}
.container{max-width:900px;margin:30px auto;padding:0 18px}
.card{background:#fff;border-radius:8px;padding:28px 24px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
.card h2{font-size:1.3rem;color:#7a2e0e;margin-bottom:20px;border-bottom:2px solid #b5451b;padding-bottom:8px}
.field{margin-bottom:14px}
.field label{font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;color:#888;display:block;margin-bottom:3px}
.field .val{font-size:0.95rem;color:#222}
.badge{display:inline-block;padding:3px 12px;border-radius:12px;font-size:0.82rem;font-weight:bold}
.badge-open{background:#d4edda;color:#155724}
.badge-pending{background:#fff3cd;color:#856404}
.badge-closed{background:#f8d7da;color:#721c24}
.badge-draft{background:#e2e3e5;color:#383d41}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.btn{display:inline-block;padding:8px 18px;background:#b5451b;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem}
.btn:hover{background:#7a2e0e}
.btn-secondary{background:#6c757d}
.btn-secondary:hover{background:#495057}
pre{white-space:pre-wrap;background:#fafafa;border:1px solid #eee;border-radius:4px;padding:12px;font-size:0.9rem;font-family:inherit}
</style>
</head>
<body>
<header><h1>🕊️ Human Rights Case</h1></header>
<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="hr_create.php">New Case</a>
  <a href="logout.php">Logout</a>
</nav>
<div class="container">
  <div class="card">
    <h2><?php echo htmlspecialchars($title); ?></h2>

    <div class="grid">
      <div class="field"><label>Case ID</label><div class="val">#<?php echo $c['id']; ?></div></div>
      <div class="field"><label>Status</label><div class="val">
        <span class="badge badge-<?php echo htmlspecialchars($c['status']); ?>">
          <?php echo ucfirst(htmlspecialchars($c['status'])); ?>
        </span>
      </div></div>
      <div class="field"><label>Applicant / Claimant</label><div class="val"><?php echo htmlspecialchars($applicant); ?></div></div>
      <div class="field"><label>Respondent</label><div class="val"><?php echo htmlspecialchars($c['respondent'] ?? '—'); ?></div></div>
      <div class="field"><label>Rights Violated</label><div class="val"><?php echo htmlspecialchars($rights); ?></div></div>
      <div class="field"><label>Article / Section</label><div class="val"><?php echo htmlspecialchars($article); ?></div></div>
      <div class="field"><label>Incident Date</label><div class="val"><?php echo htmlspecialchars($incidentDate); ?></div></div>
      <div class="field"><label>Relief Sought</label><div class="val"><?php echo htmlspecialchars($relief); ?></div></div>
      <?php if (!empty($c['lawyer_name'])): ?>
      <div class="field"><label>Lawyer</label><div class="val"><?php echo htmlspecialchars($c['lawyer_name']); ?></div></div>
      <?php endif; ?>
      <div class="field"><label>Created</label><div class="val"><?php echo substr($c['created_at'], 0, 10); ?></div></div>
    </div>

    <?php if ($background !== '—'): ?>
    <div class="field" style="margin-top:14px">
      <label>Background / Summary</label>
      <pre><?php echo htmlspecialchars($background); ?></pre>
    </div>
    <?php endif; ?>

    <?php if ($notes !== '—'): ?>
    <div class="field" style="margin-top:14px">
      <label>Grounds / Notes</label>
      <pre><?php echo htmlspecialchars($notes); ?></pre>
    </div>
    <?php endif; ?>

    <div style="margin-top:22px;display:flex;gap:12px;flex-wrap:wrap">
      <a href="human-rights.php" class="btn btn-secondary">← Back to List</a>
      <a href="hr_print.php?id=<?php echo $c['id']; ?>" class="btn">🖨 Print</a>
    </div>
  </div>
</div>
</body>
</html>
