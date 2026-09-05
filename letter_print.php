<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
$id = (int)($_GET['id'] ?? 0);
if (!$id) p2_redirect('letter_list.php');
$stmt = $pdo->prepare("SELECT l.*,c.correspondence_reference,c.bundle_reference,m.reference AS matter_reference,m.title AS matter_title
    FROM draft_letters l LEFT JOIN p5_correspondence c ON c.letter_id=l.id
    LEFT JOIN p2_matters m ON m.id=c.matter_id WHERE l.id=?");
$stmt->execute([$id]);
$letter = $stmt->fetch();
if (!$letter) p2_redirect('letter_list.php');
if (($_GET['format'] ?? '') === 'word') {
    $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', $letter['ref_no'] ?: 'correspondence-' . $id);
    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $name . '.doc"');
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title><?= p2_h($letter['subject']) ?></title>
<style>
body{font-family:"Times New Roman",serif;color:#111;margin:0;font-size:12pt}.screen{padding:14px 28px;background:#f4f6f9;font-family:Arial,sans-serif}.button{display:inline-block;padding:8px 14px;background:#1a3c5e;color:#fff;text-decoration:none;border:0;border-radius:3px;cursor:pointer}.page{max-width:210mm;min-height:297mm;margin:auto;padding:20mm 25mm}.letterhead{text-align:center;border-bottom:3px double #1a3c5e;padding-bottom:14px;margin-bottom:24px}.firm{font:bold 20pt Arial;color:#1a3c5e;letter-spacing:1px}.meta,.recipient,.signature{line-height:1.7;margin-bottom:20px}.subject{font-weight:bold;text-decoration:underline;margin:20px 0}.body{white-space:pre-wrap;line-height:1.8;text-align:justify}.signature{margin-top:32px}.footer{margin-top:42px;border-top:1px solid #ccc;padding-top:8px;text-align:center;font-size:9pt;color:#666}@media print{.screen{display:none}.page{padding:15mm 20mm}}
</style></head><body>
<div class="screen"><button class="button" onclick="window.print()">Print / Save as PDF</button> <a class="button" href="letter_view.php?id=<?= $id ?>">Back to record</a></div>
<main class="page"><div class="letterhead"><div class="firm">AEP Legal Consultancy</div><div>Private &amp; Confidential</div></div>
<div class="meta"><strong>Our Ref:</strong> <?= p2_h($letter['ref_no'] ?: 'N/A') ?><br><strong>Date:</strong> <?= p2_h(date('d F Y', strtotime($letter['created_at']))) ?><?php if ($letter['matter_reference']): ?><br><strong>Matter:</strong> <?= p2_h($letter['matter_reference'] . ' — ' . $letter['matter_title']) ?><?php endif; ?><?php if ($letter['bundle_reference']): ?><br><strong>Bundle ref:</strong> <?= p2_h($letter['bundle_reference']) ?><?php endif; ?></div>
<div class="recipient"><strong><?= p2_h($letter['recipient_name']) ?></strong><br><?= nl2br(p2_h($letter['recipient_address'] ?: '')) ?></div>
<div class="subject">RE: <?= p2_h($letter['subject']) ?></div><p><?= p2_h($letter['salutation'] ?: 'Dear Sir/Madam') ?>,</p>
<div class="body"><?= p2_h($letter['body']) ?></div>
<div class="signature"><strong><?= p2_h($letter['signatory_name'] ?: $letter['lawyer_name'] ?: 'Counsel') ?></strong><br><?= p2_h($letter['signatory_title'] ?: '') ?><br>For: AEP Legal Consultancy</div>
<div class="footer">AEP Legal Consultancy — This correspondence is confidential and intended solely for the addressee.</div></main></body></html>
