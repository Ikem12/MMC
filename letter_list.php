<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    p2_check_csrf();
    $id = (int)($_POST['letter_id'] ?? 0);
    if ((string)($_POST['action'] ?? '') === 'delete' && $id > 0) {
        $pdo->beginTransaction();
        foreach (['p5_letter_attachments', 'p5_letter_checklist', 'p5_letter_versions', 'p5_correspondence_timeline', 'p5_correspondence'] as $table) {
            $pdo->prepare('DELETE FROM ' . $table . ' WHERE letter_id=?')->execute([$id]);
        }
        $pdo->prepare('DELETE FROM draft_letters WHERE id=?')->execute([$id]);
        $pdo->commit();
        p2_redirect('letter_list.php', 'Correspondence deleted.');
    }
}
$search = trim((string)($_GET['search'] ?? ''));
$matterId = (int)($_GET['matter_id'] ?? 0);
$status = trim((string)($_GET['status'] ?? ''));
$direction = trim((string)($_GET['direction'] ?? ''));
$where = []; $params = [];
if ($search !== '') {
    $where[] = '(l.recipient_name LIKE ? OR l.ref_no LIKE ? OR l.subject LIKE ? OR l.letter_type LIKE ?)';
    for ($i = 0; $i < 4; $i++) $params[] = '%' . $search . '%';
}
if ($matterId) { $where[] = 'c.matter_id=?'; $params[] = $matterId; }
if (in_array($status, ['draft', 'final', 'sent'], true)) { $where[] = 'l.status=?'; $params[] = $status; }
if (in_array($direction, ['incoming', 'outgoing'], true)) { $where[] = 'c.direction=?'; $params[] = $direction; }
$sql = "SELECT l.*, c.matter_id,c.practice_area,c.direction,c.requires_response,c.response_due_on,c.response_received_on,c.quality_score,c.bundle_reference,m.reference,m.title AS matter_title,cl.name AS client_name
 FROM draft_letters l LEFT JOIN p5_correspondence c ON c.letter_id=l.id
 LEFT JOIN p2_matters m ON m.id=c.matter_id LEFT JOIN p2_clients cl ON cl.id=c.client_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY l.created_at DESC, l.id DESC';
$statement = $pdo->prepare($sql); $statement->execute($params); $records = $statement->fetchAll();
$summary = $pdo->query("SELECT COUNT(*) total,
 SUM(CASE WHEN l.status='draft' THEN 1 ELSE 0 END) drafts,
 SUM(CASE WHEN c.direction='incoming' AND c.requires_response=1 AND NOT EXISTS (SELECT 1 FROM p5_correspondence reply JOIN draft_letters reply_letter ON reply_letter.id=reply.letter_id WHERE reply.reply_to_letter_id=c.letter_id AND reply_letter.status='sent') THEN 1 ELSE 0 END) pending_responses,
 SUM(CASE WHEN c.direction='outgoing' AND l.status='sent' AND c.requires_response=1 AND c.response_received_on IS NULL THEN 1 ELSE 0 END) awaiting_replies
 FROM draft_letters l LEFT JOIN p5_correspondence c ON c.letter_id=l.id")->fetch() ?: [];
$matters = p2_matter_options($pdo);
p2_page('Correspondence workbench', 'letter_list.php', function () use ($records, $search, $matterId, $status, $direction, $summary, $matters) { ?>
<div class="toolbar"><div><h1>Correspondence workbench</h1><p class="subhead">Matter-linked letters, responses and delivery records.</p></div><a class="button" href="letter_create.php">+ New correspondence</a></div>
<section class="grid grid-4"><?php foreach (['All correspondence'=>$summary['total'] ?? 0, 'Drafts'=>$summary['drafts'] ?? 0, 'Pending responses'=>$summary['pending_responses'] ?? 0, 'Awaiting replies'=>$summary['awaiting_replies'] ?? 0] as $label=>$value): ?><div class="card"><div class="metric"><?= (int)$value ?></div><div class="metric-label"><?= p2_h($label) ?></div></div><?php endforeach; ?></section>
<form class="card form-grid" method="get" style="margin-top:18px">
 <label>Search <input name="search" value="<?= p2_h($search) ?>" placeholder="Reference, recipient, subject or type"></label>
 <label>Matter <select name="matter_id"><option value="">All matters</option><?php foreach ($matters as $matter): ?><option value="<?= $matter['id'] ?>" <?= $matterId===$matter['id']?'selected':'' ?>><?= p2_h($matter['reference'] . ' — ' . $matter['title']) ?></option><?php endforeach; ?></select></label>
 <label>Status <select name="status"><option value="">All statuses</option><?php foreach (['draft'=>'Draft','final'=>'Final','sent'=>'Sent'] as $key=>$label): ?><option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></label>
 <label>Direction <select name="direction"><option value="">Incoming and outgoing</option><option value="incoming" <?= $direction==='incoming'?'selected':'' ?>>Incoming</option><option value="outgoing" <?= $direction==='outgoing'?'selected':'' ?>>Outgoing</option></select></label>
 <p><button class="button">Apply filters</button> <a class="button secondary" href="letter_list.php">Clear</a></p>
</form>
<section class="card" style="margin-top:18px"><div class="split"><h2>Letters and correspondence</h2><span class="small"><?= count($records) ?> shown</span></div>
<?php if (!$records): ?><div class="empty">No correspondence matches these filters.<br><a href="letter_create.php">Draft the first item</a>.</div>
<?php else: ?><div style="overflow-x:auto"><table><thead><tr><th>Reference</th><th>Matter / client</th><th>Recipient and subject</th><th>Direction</th><th>Status</th><th>Response</th><th>Quality</th><th></th></tr></thead><tbody>
<?php foreach ($records as $record): ?><tr>
 <td><a href="letter_view.php?id=<?= $record['id'] ?>"><?= p2_h($record['ref_no'] ?: 'Unreferenced') ?></a><small><?= p2_h($record['letter_type'] ?: 'General correspondence') ?></small></td>
 <td><?= $record['matter_id'] ? '<a href="matters.php?id=' . (int)$record['matter_id'] . '">' . p2_h($record['reference'] ?: $record['matter_title']) . '</a>' : 'Unlinked' ?><small><?= p2_h($record['client_name'] ?: $record['practice_area'] ?: '') ?></small></td>
 <td><strong><?= p2_h($record['recipient_name']) ?></strong><small><?= p2_h(strlen((string)$record['subject']) > 58 ? substr((string)$record['subject'], 0, 58) . '…' : (string)$record['subject']) ?></small></td>
 <td><?= p2_status_badge($record['direction'] ?: 'outgoing') ?></td><td><?= p2_status_badge($record['status']) ?></td>
 <td><?php if ($record['requires_response']): ?><?= $record['response_received_on'] ? 'Received ' . p2_h($record['response_received_on']) : ($record['response_due_on'] ? p2_h($record['response_due_on']) : 'Required') ?><?php else: ?>—<?php endif; ?></td>
 <td><?= (int)$record['quality_score'] ?>/100</td>
 <td><a class="button secondary" href="letter_view.php?id=<?= $record['id'] ?>">Open</a>
 <form method="post" style="display:inline" onsubmit="return confirm('Delete this correspondence record?')"><input type="hidden" name="csrf" value="<?= p2_h(p2_csrf()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="letter_id" value="<?= $record['id'] ?>"><button class="button danger">Delete</button></form></td>
</tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php }); ?>
