<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/legal_library.php';

$pdo = aep_database();
$counts = [
    'clients' => (int)$pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn(),
    'matters' => (int)$pdo->query('SELECT COUNT(*) FROM matters WHERE status != "closed"')->fetchColumn(),
    'tasks' => (int)$pdo->query('SELECT COUNT(*) FROM tasks WHERE status != "completed"')->fetchColumn(),
    'deadlines' => (int)$pdo->query('SELECT COUNT(*) FROM deadlines WHERE status != "completed" AND due_date >= date("now") AND due_date <= date("now","+14 days")')->fetchColumn(),
];
$upcoming = $pdo->query('SELECT d.*, m.matter_reference, m.subject FROM deadlines d LEFT JOIN matters m ON m.id=d.matter_id WHERE d.status != "completed" AND d.due_date >= date("now") ORDER BY d.due_date LIMIT 6')->fetchAll();
$activity = $pdo->query('SELECT a.*, u.username FROM activity_log a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 8')->fetchAll();
$profiles = aep_domain_profiles();
$pageTitle = 'Workspace | AEP Legal Intelligence';
$activeNav = 'workspace';
require __DIR__ . '/includes/header.php';
?>
<style>
  .hero{background:linear-gradient(135deg,#163b62,#2469a3);border-radius:16px;color:#fff;padding:28px;margin-bottom:18px}.hero h1{margin:0 0 6px}.stat{padding:18px}.stat strong{display:block;font-size:1.8rem;color:#163b62}.stat span{color:#667085;font-size:.85rem}.area{display:flex;justify-content:space-between;gap:12px;align-items:center}.area h3{margin:0}.area p{margin:4px 0;color:#667085;font-size:.9rem}.deadline{padding:10px 0;border-bottom:1px solid #e4e7ec}.deadline:last-child{border:0}
</style>
<section class="hero"><h1>Today's Workspace</h1><p>Manage clients, matters, legal tasks, deadlines, and analysis from one place.</p></section>
<section class="grid grid-3">
  <div class="card stat"><strong><?php echo $counts['clients']; ?></strong><span>Clients</span></div>
  <div class="card stat"><strong><?php echo $counts['matters']; ?></strong><span>Open matters</span></div>
  <div class="card stat"><strong><?php echo $counts['tasks']; ?></strong><span>Open tasks</span></div>
</section>
<section class="grid grid-2" style="margin-top:18px">
  <div class="card"><div class="actions" style="justify-content:space-between"><div><h2>AI Counsel Engine</h2><p class="muted">Case analysis, appeal review, skeleton building, legal strategy, and drafting.</p></div><a class="btn" href="counsel_engine.php">Open engine</a></div></div>
  <div class="card"><div class="actions" style="justify-content:space-between"><div><h2>Quick Actions</h2><p class="muted">Start work without navigating through a module.</p></div><div class="actions"><a class="btn secondary" href="client_create.php">Client</a><a class="btn secondary" href="matter_create.php">Matter</a><a class="btn secondary" href="task_create.php">Task</a><a class="btn secondary" href="deadline_create.php">Deadline</a></div></div></div>
</section>
<section class="grid grid-2" style="margin-top:18px">
  <div class="card"><div class="actions" style="justify-content:space-between"><h2>Upcoming Deadlines <span class="badge"><?php echo $counts['deadlines']; ?> in 14 days</span></h2><a class="btn secondary" href="deadline_create.php">Add deadline</a></div>
  <?php if (!$upcoming): ?><div class="empty">No upcoming deadlines. Add the first deadline to keep the workspace on track.</div><?php else: foreach ($upcoming as $deadline): ?><div class="deadline"><strong><?php echo aep_h($deadline['due_date']); ?></strong> — <?php echo aep_h($deadline['title']); ?><br><span class="muted"><?php echo aep_h($deadline['matter_reference'] ? $deadline['matter_reference'] . ' — ' . $deadline['subject'] : 'Unlinked deadline'); ?></span></div><?php endforeach; endif; ?></div>
  <div class="card"><h2>Recent Activity</h2><?php if (!$activity): ?><div class="empty">Activity will appear as you create and update platform records.</div><?php else: ?><ul><?php foreach ($activity as $item): ?><li><strong><?php echo aep_h($item['module']); ?></strong> — <?php echo aep_h($item['action']); ?><br><span class="muted"><?php echo aep_h($item['username'] ?: 'System'); ?> · <?php echo aep_h($item['created_at']); ?></span></li><?php endforeach; ?></ul><?php endif; ?></div>
</section>
<section class="card" style="margin-top:18px"><div class="actions" style="justify-content:space-between"><div><h2>Practice Areas</h2><p class="muted">Open a domain workbench or use its existing legal module.</p></div><a class="btn secondary" href="domain_map.php">View full map</a></div><div class="grid grid-3" style="margin-top:14px"><?php foreach($profiles as $key=>$profile): ?><div class="card area"><div><h3><?php echo aep_h($profile['label']); ?></h3><p><?php echo aep_h(implode(' · ', array_slice($profile['subdomains'] ?? [],0,2))); ?></p></div><a class="btn secondary" href="domain_workbench.php?domain=<?php echo urlencode($key); ?>">Open</a></div><?php endforeach; ?></div></section>
<section class="card" style="margin-top:18px"><div class="actions" style="justify-content:space-between"><div><h2>Knowledge Centre</h2><p class="muted">Library, templates, phrase bank, Latin maxims, and complete domain map.</p></div><a class="btn" href="knowledge_centre.php">Open Knowledge Centre</a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
