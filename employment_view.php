<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';

$pdo = p2_db();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: employment_list.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM employment_cases WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header("Location: employment_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View <?= htmlspecialchars($record['case_reference'] ?? 'Record') ?> — Employment & Tribunal Intelligence</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --primary: #0f2744; --accent: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --text: #1e293b; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        .topbar { background: #0f172a; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent); }
        .topbar a { color: #cbd5e1; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 24px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .btn { padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .field-group { background: #f8fafc; padding: 14px; border-radius: 6px; border-left: 3px solid var(--accent); }
        .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .field-val { font-size: 14px; font-weight: 600; color: #1e293b; }
        .full-content { background: #ffffff; border: 1px solid var(--border); border-radius: 6px; padding: 20px; line-height: 1.7; font-family: "SFMono-Regular", Consolas, monospace; font-size: 13px; white-space: pre-wrap; }
    </style>
</head>
<body>

<header class="topbar">
    <div style="font-weight: 700; font-size: 15px;">⚖️ AEP Legal Platform — Employment & Tribunal Intelligence</div>
    <nav>
        <a href="employment_list.php">📋 Back to List</a>
        <a href="employment_create.php">⚡ AI Workspace</a>
        <a href="dashboard.php">🏠 Dashboard</a>
    </nav>
</header>

<div class="container">
    <div class="card">
        <div class="header-actions">
            <div>
                <h1 style="margin: 0; font-size: 20px; color: #0f2744;">👔 <?= htmlspecialchars($record['claim_type'] ?? 'Record Details') ?></h1>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 13px;">Ref: <strong><?= htmlspecialchars($record['case_reference'] ?? 'REF-' . $record['id']) ?></strong> | Created: <?= date('d F Y', strtotime($record['created_at'] ?? 'now')) ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="employment_print.php?id=<?= $record['id'] ?>" target="_blank" class="btn btn-outline">🖨 Print / PDF</a>
                <a href="employment_create.php?id=<?= $record['id'] ?>" class="btn btn-primary">⚡ Open in AI Workspace</a>
            </div>
        </div>

        <div class="grid-2">
            <div class="field-group">
                <div class="field-label">Party / Client</div>
                <div class="field-val"><?= htmlspecialchars($record['claimant_name'] ?? 'N/A') ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Status</div>
                <div class="field-val"><?= htmlspecialchars(strtoupper($record['status'] ?? 'DRAFT')) ?></div>
            </div>
        </div>

        <h3 style="font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">Substantive Record / Statement Content</h3>
        <div class="full-content"><?= htmlspecialchars($record['claim_details'] ?? 'No extended content recorded.') ?></div>
    </div>
</div>

</body>
</html>