<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';

$pdo = p2_db();
p2_migrate($pdo);

// Ensure domain table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS employment_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            status TEXT DEFAULT \'draft\',
            claim_type TEXT,
            claimant_name TEXT,
            respondent_name TEXT,
            acas_certificate_number TEXT,
            employment_start_date TEXT,
            employment_end_date TEXT,
            job_title TEXT,
            gross_salary TEXT,
            claim_details TEXT,
            remedies_sought TEXT,
            lawyer_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
} catch (Exception $e) {}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM employment_cases WHERE id = ?");
        $stmt->execute([$delId]);
        header("Location: employment_list.php?msg=deleted");
        exit;
    }
}

$search = trim((string)($_GET['search'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));

// Query Domain Records
$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(case_reference LIKE ? OR claimant_name LIKE ? OR claim_type LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
}

$sql = "SELECT * FROM employment_cases WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Also query any saved AI Documents under this practice area
$aiDocs = [];
try {
    $dSql = "SELECT d.*, m.reference as matter_reference, m.title as matter_title 
             FROM p2_documents d 
             JOIN matters m ON d.matter_id = m.id 
             WHERE m.practice_area LIKE '%Employment Law%' OR m.practice_area LIKE '%Employment & Tribunal Intelligence%'
             ORDER BY d.id DESC LIMIT 20";
    $dStmt = $pdo->query($dSql);
    if ($dStmt) $aiDocs = $dStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$totalCount = count($records);
$draftCount = 0;
$finalCount = 0;
foreach ($records as $r) {
    if (($r['status'] ?? '') === 'final') $finalCount++;
    else $draftCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment & Tribunal Intelligence — AEP Legal Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --primary: #0f2744; --accent: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --text: #1e293b; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        .topbar { background: #0f172a; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent); }
        .topbar a { color: #cbd5e1; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .topbar a:hover { color: #fff; }
        .hero { background: linear-gradient(135deg, #0f2744 0%, #1e3a8a 100%); color: #fff; padding: 26px 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .hero-content { max-width: 1500px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .hero h1 { margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .hero p { margin: 6px 0 0; font-size: 13px; color: #cbd5e1; }
        .container { max-width: 1500px; margin: 24px auto; padding: 0 24px 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 8px; padding: 18px 24px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .stat-num { font-size: 28px; font-weight: 800; color: #1e3a8a; }
        .stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-top: 4px; }
        .toolbar { background: #fff; border-radius: 8px; padding: 14px 20px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; flex: 1; }
        .search-form input, .search-form select { padding: 9px 14px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .search-form input { flex: 1; min-width: 260px; }
        .btn { padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .btn-outline:hover { background: #f8fafc; }
        .btn-danger { background: #ef4444; color: #fff; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px; }
        .card-header { background: #f8fafc; padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 14px; color: #334155; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f8fafc; color: #475569; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border); }
        td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .badge-final { background: #d1fae5; color: #065f46; }
        .empty-state { text-align: center; padding: 50px 20px; color: #64748b; }
    </style>
</head>
<body>

<header class="topbar">
    <div style="font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px;">
        <span>⚖️ AEP Legal Intelligence Platform</span>
    </div>
    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="matters.php">📁 Matters</a>
        <a href="counsel_engine.php">⚖️ Counsel Engine</a>
        <a href="letter_list.php">✉️ Correspondence</a>
        <a href="immigration_list.php">✈️ Immigration</a>
        <a href="employment_list.php">👔 Employment</a>
        <a href="contract_list.php">📄 Contracts</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>
</header>

<div class="hero">
    <div class="hero-content">
        <div>
            <h1>👔 Employment & Tribunal Intelligence</h1>
            <p>ET1 Particulars of Claim, ET3 Responses, ACAS Early Conciliation, Schedule of Loss & Vento Band Assessments</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="employment_create.php" class="btn btn-success" style="font-size: 14px; padding: 11px 22px;">⚡ Open AI Workspace / New Draft</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num"><?= $totalCount ?></div>
            <div class="stat-label">Total Domain Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #059669;"><?= $finalCount ?></div>
            <div class="stat-label">Final Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #d97706;"><?= $draftCount ?></div>
            <div class="stat-label">Active Drafts</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #7c3aed;"><?= count($aiDocs) ?></div>
            <div class="stat-label">Saved Matter Documents</div>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by reference, party name, or case title..." value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">— All Statuses —</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="final" <?= $status === 'final' ? 'selected' : '' ?>>Final</option>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <?php if ($search || $status): ?>
                <a href="employment_list.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
        <div>
            <a href="employment_create.php" class="btn btn-primary">+ New Record</a>
        </div>
    </div>

    <!-- MAIN RECORD TABLE -->
    <div class="card">
        <div class="card-header">
            <span>📋 Employment & Tribunal Intelligence Records</span>
            <span style="font-size: 12px; color: #64748b;">Showing <?= count($records) ?> records</span>
        </div>
        <?php if (empty($records)): ?>
            <div class="empty-state">
                <div style="font-size: 40px; margin-bottom: 12px;">📂</div>
                <h3>No records found</h3>
                <p>Launch the Universal AI Workspace to generate your first document.</p>
                <a href="employment_create.php" class="btn btn-primary" style="margin-top: 14px;">⚡ Open Employment & Tribunal Intelligence Workspace</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Party / Client</th>
                            <th>Case Title / Subject</th>
                            <th>Date Created</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td style="font-weight: 700; color: #1e3a8a;">
                                    <a href="employment_view.php?id=<?= $r['id'] ?>" style="color: #1e3a8a; text-decoration: none;">
                                        <?= htmlspecialchars($r['case_reference'] ?? ('REF-' . $r['id'])) ?>
                                    </a>
                                </td>
                                <td style="font-weight: 600;">
                                    <?= htmlspecialchars($r['claimant_name'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['claim_type'] ?? 'Case Assessment') ?>
                                </td>
                                <td style="color: #64748b; font-size: 12px;">
                                    <?= !empty($r['created_at']) ? date('d M Y', strtotime($r['created_at'])) : date('d M Y') ?>
                                </td>
                                <td>
                                    <span class="badge <?= ($r['status'] ?? '') === 'final' ? 'badge-final' : 'badge-draft' ?>">
                                        <?= htmlspecialchars($r['status'] ?? 'draft') ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="employment_view.php?id=<?= $r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">👁️ View</a>
                                        <a href="employment_print.php?id=<?= $r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;" target="_blank">🖨 Print</a>
                                        <a href="employment_create.php?id=<?= $r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">⚡ AI Workspace</a>
                                        <a href="employment_list.php?delete=<?= $r['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 10px;" onclick="return confirm('Delete this record?');">🗑 Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- SAVED MATTER DOCUMENTS -->
    <?php if (!empty($aiDocs)): ?>
    <div class="card">
        <div class="card-header">
            <span>💾 Matter-Linked AI Documents & Briefs</span>
            <span style="font-size: 12px; color: #64748b;">Integrated Document Library</span>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Matter Ref</th>
                        <th>Document Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aiDocs as $doc): ?>
                    <tr>
                        <td style="font-weight: 700; color: #1e3a8a;">
                            <a href="matter_view.php?id=<?= $doc['matter_id'] ?>" style="color: #1e3a8a; text-decoration: none;">
                                <?= htmlspecialchars($doc['matter_reference'] ?? 'Matter #' . $doc['matter_id']) ?>
                            </a>
                        </td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($doc['title']) ?></td>
                        <td><span class="badge badge-final"><?= htmlspecialchars($doc['document_type']) ?></span></td>
                        <td style="color: #64748b; font-size: 12px;"><?= date('d M Y, H:i', strtotime($doc['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <a href="matter_view.php?id=<?= $doc['matter_id'] ?>#documents" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">📁 Open in Matter</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>