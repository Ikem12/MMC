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
    die("Invalid record ID.");
}

$stmt = $pdo->prepare("SELECT * FROM grounds_of_appeal WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("Record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print <?= htmlspecialchars($record['case_number'] ?? 'Document') ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 40px; color: #000; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 24px; }
        .header h1 { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .meta { margin-bottom: 20px; font-size: 11pt; }
        .content { white-space: pre-wrap; font-size: 11pt; line-height: 1.6; }
        .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 12px; font-size: 9pt; text-align: center; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 8px 16px; font-size: 13px; font-weight: bold; cursor: pointer;">🖨 Print / Save as PDF</button>
</div>

<div class="header">
    <h1>AEP Legal Intelligence Platform</h1>
    <div style="font-size: 11pt; font-weight: bold; margin-top: 4px;">Appellate Advocacy & Grounds of Appeal</div>
</div>

<div class="meta">
    <p><strong>DOCUMENT TITLE:</strong> <?= htmlspecialchars($record['case_title'] ?? 'Legal Record') ?><br>
    <strong>REFERENCE:</strong> <?= htmlspecialchars($record['case_number'] ?? 'N/A') ?><br>
    <strong>PARTY / CLIENT:</strong> <?= htmlspecialchars($record['party'] ?? 'N/A') ?><br>
    <strong>DATE:</strong> <?= date('d F Y', strtotime($record['created_at'] ?? 'now')) ?></p>
</div>

<div class="content"><?= htmlspecialchars($record['grounds'] ?? '') ?></div>

<div class="footer">
    AEP Legal Intelligence Platform — Certified Legal Document Record
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    // optional auto-print if wanted
});
</script>
</body>
</html>