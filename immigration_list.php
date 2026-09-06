<?php
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';
p2_start();
$pdo = p2_db();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$sql = "SELECT c.*, m.title AS matter_title, m.matter_number 
        FROM immigration_cases c 
        LEFT JOIN matters m ON c.matter_id = m.id 
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (c.client_name LIKE ? OR c.case_reference LIKE ? OR c.client_nationality LIKE ? OR c.case_type LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}
$sql .= " ORDER BY c.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Immigration Intelligence Workspace &mdash; AEP Legal Platform</title>
<style>
:root {
  --primary: #0f2744;
  --accent: #2563eb;
  --accent-light: #eff6ff;
  --border: #e2e8f0;
  --bg: #f8fafc;
  --text: #1e293b;
  --text-muted: #64748b;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }
.topbar { background: #0f172a; color: #fff; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; }
.topbar .brand { font-size: 1.05rem; font-weight: 700; color: #60a5fa; text-decoration: none; display: flex; align-items: center; gap: 8px; }
.topbar a { color: #94a3b8; text-decoration: none; font-size: 0.85rem; margin-left: 14px; transition: color 0.15s; }
.topbar a:hover { color: #fff; }

.hero { background: linear-gradient(135deg, #0f2744 0%, #1e3a8a 100%); color: #fff; padding: 24px 32px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.hero-content { max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.hero h1 { font-size: 1.4rem; font-weight: 700; }
.hero p { font-size: 0.85rem; color: #cbd5e1; margin-top: 4px; }

.container { max-width: 1300px; margin: 0 auto; padding: 0 24px 40px; }

.toolbar { display: flex; gap: 12px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
.toolbar input, .toolbar select { padding: 9px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.88rem; background: #fff; }
.toolbar input { flex: 1; min-width: 220px; }

.btn { padding: 9px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.88rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: #1e3a8a; }
.btn-ai { background: linear-gradient(135deg, #4338ca, #6366f1); color: #fff; }
.btn-ai:hover { opacity: 0.95; }
.btn-outline { background: #fff; color: var(--text); border: 1px solid var(--border); }
.btn-outline:hover { background: #f1f5f9; }

.badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.badge-draft { background: #f1f5f9; color: #64748b; }
.badge-active { background: #dcfce7; color: #166534; }
.badge-appeal { background: #ffedd5; color: #c2410c; }
.badge-tribunal { background: #f3e8ff; color: #7e22ce; }
.badge-won { background: #dcfce7; color: #15803d; }
.badge-lost { background: #fee2e2; color: #b91c1c; }
.badge-withdrawn { background: #fef3c7; color: #b45309; }
.badge-closed { background: #f1f5f9; color: #64748b; }

table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); }
th { background: #0f2744; color: #fff; padding: 12px 16px; text-align: left; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 0.85rem; }
tr:hover td { background: #f8fafc; }

.action-links { display: flex; gap: 8px; align-items: center; }
.action-links a { text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 4px; }
.link-ai { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.link-ai:hover { background: #dbeafe; }
.link-view { background: #f1f5f9; color: #334155; border: 1px solid var(--border); }
.link-view:hover { background: #e2e8f0; }

.empty { text-align: center; padding: 60px 20px; color: var(--text-muted); background: #fff; border-radius: 8px; border: 1px solid var(--border); }
</style>
</head>
<body>

<div class="topbar">
  <a href="dashboard.php" class="brand">⚖️ AEP Legal Intelligence Platform</a>
  <div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="immigration_create.php">⚡ + AI Case Workspace</a>
    <a href="matter_list.php">📂 Matters</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="hero">
  <div class="hero-content">
    <div>
      <h1>✈️ Immigration Intelligence Registry</h1>
      <p>AI-Powered Multi-Factor Assessments, Evidence Matrices, Appeals &amp; Automated Representation Drafting</p>
    </div>
    <div>
      <a href="immigration_create.php" class="btn btn-ai" style="padding: 10px 20px; font-size: 0.95rem;">
        ⚡ Launch Immigration AI Workspace
      </a>
    </div>
  </div>
</div>

<div class="container">

  <div class="toolbar">
    <form method="GET" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
      <input type="text" name="search" placeholder="Search applicant, reference, nationality, or visa type..." value="<?php echo htmlspecialchars($search); ?>"/>
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach (['draft', 'active', 'appeal', 'tribunal', 'won', 'lost', 'withdrawn', 'closed'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">🔍 Search</button>
      <a href="immigration_list.php" class="btn btn-outline">✖ Clear</a>
    </form>
    <a href="immigration_create.php" class="btn btn-ai">+ New AI Case</a>
  </div>

  <?php if (empty($cases)): ?>
    <div class="empty">
      <div style="font-size:2rem; margin-bottom:12px;">✈️</div>
      <h3 style="font-size:1.1rem; color:var(--text); margin-bottom:6px;">No Immigration Cases Found</h3>
      <p style="font-size:0.85rem; margin-bottom:16px;">Create a new case in the AI Workspace to analyze merits, evidence, and generate legal documents.</p>
      <a href="immigration_create.php" class="btn btn-ai">⚡ Launch Immigration AI Workspace</a>
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Ref / Matter</th>
          <th>Applicant</th>
          <th>Nationality</th>
          <th>Visa / Case Type</th>
          <th>Status</th>
          <th>Home Office Ref</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $c): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($c['case_reference'] ?: ('IMM-' . $c['id'])); ?></strong>
              <?php if (!empty($c['matter_title'])): ?>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                  📂 <a href="matter_view.php?id=<?php echo $c['matter_id']; ?>" style="color:var(--accent); text-decoration:none;"><?php echo htmlspecialchars($c['matter_title']); ?></a>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($c['client_name']); ?></strong>
              <?php if (!empty($c['client_dob'])): ?>
                <div style="font-size:0.75rem; color:var(--text-muted);">DOB: <?php echo htmlspecialchars($c['client_dob']); ?></div>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($c['client_nationality'] ?: 'N/A'); ?></td>
            <td>
              <span><?php echo htmlspecialchars($c['case_type'] ?: 'Immigration / Human Rights'); ?></span>
              <?php if (!empty($c['client_visa_expiry'])): ?>
                <div style="font-size:0.75rem; color:var(--text-muted);">Expires: <?php echo date('d/m/Y', strtotime($c['client_visa_expiry'])); ?></div>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge badge-<?php echo htmlspecialchars($c['status'] ?? 'active'); ?>">
                <?php echo ucfirst($c['status'] ?? 'active'); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($c['home_office_reference'] ?: 'N/A'); ?></td>
            <td>
              <div class="action-links">
                <a href="immigration_create.php?case_id=<?php echo $c['id']; ?>&matter_id=<?php echo $c['matter_id'] ?? 0; ?>" class="link-ai">⚡ AI Workspace</a>
                <a href="immigration_view.php?id=<?php echo $c['id']; ?>" class="link-view">👁️ Details</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</div>

</body>
</html>