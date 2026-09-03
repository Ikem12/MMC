<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';require_once __DIR__.'/legal_library.php';$pdo=aep_database();
$q = trim((string)($_GET['q'] ?? ''));
$practiceFilter = trim((string)($_GET['practice_area'] ?? ''));
$statusFilter = aep_valid_status((string)($_GET['status'] ?? ''), ['open','active','closed'], '');
$clientFilter = (int)($_GET['client_id'] ?? 0);
$sort = aep_valid_status((string)($_GET['sort'] ?? ''), ['created_at','status'], 'created_at');
$where = []; $params = [];
if ($q !== '') { $where[] = '(m.matter_reference LIKE ? OR m.subject LIKE ? OR c.name LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($practiceFilter !== '') { $where[] = 'm.practice_area = ?'; $params[] = $practiceFilter; }
if ($statusFilter !== '') { $where[] = 'm.status = ?'; $params[] = $statusFilter; }
if ($clientFilter) { $where[] = 'm.client_id = ?'; $params[] = $clientFilter; }
$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';
$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page - 1) * $perPage;
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM matters m JOIN clients c ON c.id=m.client_id $whereSql"); $countStmt->execute($params); $total = (int)$countStmt->fetchColumn();
$stmt = $pdo->prepare("SELECT m.*,c.name client_name,COUNT(t.id) task_count FROM matters m JOIN clients c ON c.id=m.client_id LEFT JOIN tasks t ON t.matter_id=m.id $whereSql GROUP BY m.id ORDER BY m.$sort DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params); $matters = $stmt->fetchAll();
$clients = $pdo->query('SELECT id,name FROM clients ORDER BY name')->fetchAll();
$pageTitle='Matters | AEP';$activeNav='matters';require __DIR__.'/includes/header.php';?>
<div class="actions" style="justify-content:space-between"><div><h1>Matters</h1><p class="muted"><?php echo (int)$total; ?> matter(s) tracked.</p></div><a class="btn" href="matter_create.php">Create matter</a></div>
<form method="get" class="grid grid-3" style="margin-top:18px">
<div><label>Search</label><input type="search" name="q" value="<?php echo aep_h($q); ?>" placeholder="Reference, subject, or client"></div>
<div><label>Client</label><select name="client_id" onchange="this.form.submit()"><option value="">All clients</option><?php foreach($clients as $client): ?><option value="<?php echo (int)$client['id']; ?>" <?php echo $clientFilter===(int)$client['id']?'selected':''; ?>><?php echo aep_h($client['name']); ?></option><?php endforeach; ?></select></div>
<div><label>Practice area</label><select name="practice_area" onchange="this.form.submit()"><option value="">All areas</option><?php foreach(aep_domain_profiles() as $profile): ?><option value="<?php echo aep_h($profile['label']); ?>" <?php echo $practiceFilter===$profile['label']?'selected':''; ?>><?php echo aep_h($profile['label']); ?></option><?php endforeach; ?></select></div>
<div><label>Status</label><select name="status" onchange="this.form.submit()"><option value="">All statuses</option><?php foreach(['open','active','closed'] as $s): ?><option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option><?php endforeach; ?></select></div>
<div><label>Sort by</label><select name="sort" onchange="this.form.submit()"><option value="created_at" <?php echo $sort==='created_at'?'selected':''; ?>>Created date</option><option value="status" <?php echo $sort==='status'?'selected':''; ?>>Status</option></select></div>
<div style="align-self:end"><button class="btn secondary">Apply</button></div>
</form>
<div class="card" style="margin-top:18px"><?php if(!$matters):?><div class="empty"><?php echo ($q!==''||$practiceFilter!==''||$statusFilter!==''||$clientFilter)?'No matters match these filters.':'Ready to create your first matter.'; ?><br><br><a class="btn" href="matter_create.php">Create matter</a></div><?php else:?><table><thead><tr><th>Reference</th><th>Client</th><th>Practice area</th><th>Subject</th><th>Tasks</th><th>Status</th></tr></thead><tbody><?php foreach($matters as $matter):?><tr><td><a href="matter_view.php?id=<?php echo (int)$matter['id'];?>"><?php echo aep_h($matter['matter_reference']);?></a></td><td><?php echo aep_h($matter['client_name']);?></td><td><?php echo aep_h($matter['practice_area']);?></td><td><?php echo aep_h($matter['subject']);?></td><td><?php echo (int)$matter['task_count'];?></td><td><span class="badge"><?php echo aep_h($matter['status']);?></span></td></tr><?php endforeach;?></tbody></table>
<div class="actions" style="justify-content:space-between;margin-top:14px"><?php $qs=$_GET; ?>
<?php if ($page>1): $qs['page']=$page-1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
<span class="muted">Page <?php echo $page; ?> of <?php echo max(1,(int)ceil($total/$perPage)); ?></span>
<?php if ($offset+$perPage<$total): $qs['page']=$page+1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Next</a><?php else: ?><span></span><?php endif; ?>
</div>
<?php endif;?></div><?php require __DIR__.'/includes/footer.php';?>
