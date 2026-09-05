<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
$id = (int)($_GET['id'] ?? 0);
$tab = $_GET['tab'] ?? 'overview';
$tabs = ['overview' => 'Overview', 'matters' => 'Matters', 'documents' => 'Documents', 'activity' => 'Recent activity'];
if (!array_key_exists($tab, $tabs)) $tab = 'overview';
$stmt = $pdo->prepare('SELECT * FROM p2_clients WHERE id = ?');
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) {
    http_response_code(404);
    exit('Client not found.');
}
$matters = $documents = $activity = [];
if ($tab === 'matters' || $tab === 'overview') {
    $stmt = $pdo->prepare('SELECT * FROM p2_matters WHERE client_id = ? ORDER BY created_at DESC');
    $stmt->execute([$id]);
    $matters = $stmt->fetchAll();
}
if ($tab === 'documents') {
    $stmt = $pdo->prepare('SELECT d.*, m.reference FROM p2_documents d JOIN p2_matters m ON m.id=d.matter_id WHERE m.client_id = ? ORDER BY d.created_at DESC');
    $stmt->execute([$id]);
    $documents = $stmt->fetchAll();
}
if ($tab === 'activity') {
    $stmt = $pdo->prepare('SELECT a.*, m.reference, u.username FROM p2_activity a JOIN p2_matters m ON m.id=a.matter_id LEFT JOIN users u ON u.id=a.actor_id WHERE m.client_id=? ORDER BY a.created_at DESC LIMIT 30');
    $stmt->execute([$id]);
    $activity = $stmt->fetchAll();
}
$stmt = $pdo->prepare("SELECT COUNT(*) total, SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) open, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) closed FROM p2_matters WHERE client_id=?");
$stmt->execute([$id]);
$statistics = $stmt->fetch() ?: ['total' => 0, 'open' => 0, 'closed' => 0];
$stmt = $pdo->prepare('SELECT COUNT(*) FROM p2_documents d JOIN p2_matters m ON m.id=d.matter_id WHERE m.client_id=?');
$stmt->execute([$id]);
$statistics['documents'] = (int)$stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT a.*,m.reference FROM p2_activity a JOIN p2_matters m ON m.id=a.matter_id WHERE m.client_id=? ORDER BY a.created_at DESC LIMIT 5');
$stmt->execute([$id]);
$recentActivity = $stmt->fetchAll();
p2_page('Client workspace', 'clients.php', function () use ($client, $id, $tab, $tabs, $matters, $documents, $activity, $statistics, $recentActivity) { ?>
<div class="toolbar"><div><h1><?= p2_h($client['name']) ?></h1><p class="subhead">Client workspace and connected records.</p></div><div><a class="button" href="matters.php?action=new&client_id=<?= $id ?>">+ New matter</a> <a class="button secondary" href="clients.php">Back to clients</a></div></div>
<?php p2_workspace_tabs('client_view.php', $id, $tab, $tabs); ?>
<?php if ($tab === 'overview'): ?><section class="grid grid-4"><div class="card"><h2>Client profile</h2><p><?= p2_h($client['email'] ?: 'No email recorded') ?><br><?= p2_h($client['phone'] ?: 'No phone recorded') ?><br><?= nl2br(p2_h($client['address'] ?: 'No address recorded')) ?></p></div><div class="card"><div class="metric"><?= (int)$statistics['open'] ?></div><div class="metric-label">Open matters</div></div><div class="card"><div class="metric"><?= (int)$statistics['closed'] ?></div><div class="metric-label">Closed matters</div></div><div class="card"><div class="metric"><?= (int)$statistics['documents'] ?></div><div class="metric-label">Linked documents</div></div></section><section class="grid grid-2" style="margin-top:18px"><div class="card"><h2>Matters</h2><?php if (!$matters): ?><div class="empty">No matters linked to this client. <a href="matters.php?action=new&client_id=<?= $id ?>">Create one</a>.</div><?php else: ?><ul class="compact-list"><?php foreach (array_slice($matters, 0, 5) as $matter): ?><li><a href="matters.php?id=<?= $matter['id'] ?>"><?= p2_h($matter['reference'] ?: $matter['title']) ?></a><?= p2_status_badge($matter['status']) ?></li><?php endforeach; ?></ul><?php endif; ?></div><div class="card"><h2>Recent activity</h2><?php if (!$recentActivity): ?><div class="empty">No linked matter activity yet.</div><?php else: ?><ol class="activity-stream"><?php foreach ($recentActivity as $event): ?><li><strong><?= p2_h(p2_event_label($event['event_type'])) ?></strong><span><?= p2_h($event['reference']) ?> · <?= p2_h(date('d M H:i', strtotime($event['created_at']))) ?></span></li><?php endforeach; ?></ol><?php endif; ?></div></section>
<?php elseif ($tab === 'matters'): ?><section class="card"><h2>Client matters</h2><?php if (!$matters): ?><div class="empty">No matters linked to this client.</div><?php else: ?><table><thead><tr><th>Reference</th><th>Title</th><th>Status</th></tr></thead><tbody><?php foreach ($matters as $matter): ?><tr><td><a href="matters.php?id=<?= $matter['id'] ?>"><?= p2_h($matter['reference']) ?></a></td><td><?= p2_h($matter['title']) ?></td><td><?= p2_status_badge($matter['status']) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
<?php elseif ($tab === 'documents'): ?><section class="card"><h2>Client documents</h2><?php if (!$documents): ?><div class="empty">No documents linked.</div><?php else: ?><table><thead><tr><th>Document</th><th>Matter</th><th>Type</th><th>Added</th></tr></thead><tbody><?php foreach ($documents as $document): ?><tr><td><?= p2_h($document['title']) ?></td><td><a href="matters.php?id=<?= $document['matter_id'] ?>"><?= p2_h($document['reference']) ?></a></td><td><?= p2_h($document['document_type']) ?></td><td><?= p2_h(date('d M Y', strtotime($document['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
<?php else: ?><section class="card"><h2>Client activity stream</h2><?php if (!$activity): ?><div class="empty">No linked matter activity recorded.</div><?php else: ?><ol class="activity-stream"><?php foreach ($activity as $event): ?><li><strong><?= p2_h($event['description']) ?></strong><span><?= p2_h($event['reference']) ?> · <?= p2_h($event['username'] ?: 'Workspace user') ?> · <?= p2_h(date('d M Y H:i', strtotime($event['created_at']))) ?></span></li><?php endforeach; ?></ol><?php endif; ?></section><?php endif; ?>
<?php }); ?>
