<?php
require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/functions.php';
$pdo = aep_database();
$q = trim((string)($_GET['q'] ?? ''));
$sort = aep_valid_status((string)($_GET['sort'] ?? ''), ['name','created_at'], 'created_at');
$where = ''; $params = [];
if ($q !== '') { $where = 'WHERE c.name LIKE ? OR c.client_reference LIKE ?'; $params = ["%$q%", "%$q%"]; }
$perPage = 10; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page - 1) * $perPage;
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM clients c $where"); $countStmt->execute($params); $total = (int)$countStmt->fetchColumn();
$stmt = $pdo->prepare("SELECT c.*, COUNT(m.id) AS matter_count FROM clients c LEFT JOIN matters m ON m.client_id=c.id $where GROUP BY c.id ORDER BY c.$sort DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params); $clients = $stmt->fetchAll();
$pageTitle='Clients | AEP'; $activeNav='clients'; require __DIR__ . '/includes/header.php';
?>
<div class="actions" style="justify-content:space-between"><div><h1>Clients</h1><p class="muted"><?php echo (int)$total; ?> client record(s).</p></div><a class="btn" href="client_create.php">Create client</a></div>
<form method="get" class="grid grid-3" style="margin-top:18px">
<div><label>Search</label><input type="search" name="q" value="<?php echo aep_h($q); ?>" placeholder="Search by name or reference"></div>
<div><label>Sort by</label><select name="sort" onchange="this.form.submit()"><option value="created_at" <?php echo $sort==='created_at'?'selected':''; ?>>Created date</option><option value="name" <?php echo $sort==='name'?'selected':''; ?>>Name</option></select></div>
<div style="align-self:end"><button class="btn secondary">Apply</button></div>
</form>
<div class="card" style="margin-top:18px"><?php if (!$clients): ?><div class="empty"><?php echo $q!==''?'No clients match your search.':'Ready to create your first client record.'; ?><br><br><a class="btn" href="client_create.php">Create client</a></div><?php else: ?><table><thead><tr><th>Reference</th><th>Client</th><th>Contact</th><th>Matters</th><th></th></tr></thead><tbody><?php foreach($clients as $client): ?><tr><td><?php echo aep_h($client['client_reference']); ?></td><td><strong><?php echo aep_h($client['name']); ?></strong><br><span class="muted"><?php echo aep_h($client['address']); ?></span></td><td><?php echo aep_h($client['email']); ?><br><?php echo aep_h($client['phone']); ?></td><td><?php echo (int)$client['matter_count']; ?></td><td><a class="btn secondary" href="client_view.php?id=<?php echo (int)$client['id']; ?>">Open</a></td></tr><?php endforeach; ?></tbody></table>
<div class="actions" style="justify-content:space-between;margin-top:14px"><?php $qs=$_GET; ?>
<?php if ($page>1): $qs['page']=$page-1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
<span class="muted">Page <?php echo $page; ?> of <?php echo max(1,(int)ceil($total/$perPage)); ?></span>
<?php if ($offset+$perPage<$total): $qs['page']=$page+1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Next</a><?php else: ?><span></span><?php endif; ?>
</div>
<?php endif; ?></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
