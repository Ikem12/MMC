<?php
require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/functions.php';
$pdo = aep_database(); $clients = $pdo->query('SELECT c.*, COUNT(m.id) AS matter_count FROM clients c LEFT JOIN matters m ON m.client_id=c.id GROUP BY c.id ORDER BY c.created_at DESC')->fetchAll();
$pageTitle='Clients | AEP'; $activeNav='clients'; require __DIR__ . '/includes/header.php';
?>
<div class="actions" style="justify-content:space-between"><div><h1>Clients</h1><p class="muted">Client records and linked matters.</p></div><a class="btn" href="client_create.php">Create client</a></div>
<div class="card" style="margin-top:18px"><?php if (!$clients): ?><div class="empty">Ready to create your first client record.<br><br><a class="btn" href="client_create.php">Create client</a></div><?php else: ?><table><thead><tr><th>Reference</th><th>Client</th><th>Contact</th><th>Matters</th><th></th></tr></thead><tbody><?php foreach($clients as $client): ?><tr><td><?php echo aep_h($client['client_reference']); ?></td><td><strong><?php echo aep_h($client['name']); ?></strong><br><span class="muted"><?php echo aep_h($client['address']); ?></span></td><td><?php echo aep_h($client['email']); ?><br><?php echo aep_h($client['phone']); ?></td><td><?php echo (int)$client['matter_count']; ?></td><td><a class="btn secondary" href="client_view.php?id=<?php echo (int)$client['id']; ?>">Open</a></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
