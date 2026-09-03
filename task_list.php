<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();
$q = trim((string)($_GET['q'] ?? ''));
$matterFilter = (int)($_GET['matter_id'] ?? 0);
$statusFilter = aep_valid_status((string)($_GET['status'] ?? ''), ['open','in_progress','completed'], '');
$overdueOnly = !empty($_GET['overdue']);
$sort = aep_valid_status((string)($_GET['sort'] ?? ''), ['due_date','created_at','status'], 'due_date');
$where = []; $params = [];
if ($q !== '') { $where[] = 't.title LIKE ?'; $params[] = "%$q%"; }
if ($matterFilter) { $where[] = 't.matter_id = ?'; $params[] = $matterFilter; }
if ($statusFilter !== '') { $where[] = 't.status = ?'; $params[] = $statusFilter; }
if ($overdueOnly) { $where[] = "t.due_date IS NOT NULL AND t.due_date < date('now') AND t.status != 'completed'"; }
$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';
$perPage = 20; $page = max(1, (int)($_GET['page'] ?? 1)); $offset = ($page - 1) * $perPage;
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t $whereSql"); $countStmt->execute($params); $total = (int)$countStmt->fetchColumn();
$orderSql = $sort === 'due_date' ? "COALESCE(t.due_date,'9999-12-31')" : "t.$sort";
$stmt = $pdo->prepare("SELECT t.*,m.matter_reference,m.subject FROM tasks t LEFT JOIN matters m ON m.id=t.matter_id $whereSql ORDER BY $orderSql, t.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params); $tasks = $stmt->fetchAll();
$matters = $pdo->query('SELECT id,matter_reference,subject FROM matters ORDER BY updated_at DESC')->fetchAll();
$pageTitle='Tasks | AEP';$activeNav='tasks';require __DIR__.'/includes/header.php';?>
<div class="actions" style="justify-content:space-between"><div><h1>Tasks</h1><p class="muted"><?php echo (int)$total; ?> task(s) across all matters.</p></div><a class="btn" href="task_create.php">Create task</a></div>
<form method="get" class="grid grid-3" style="margin-top:18px">
<div><label>Search</label><input type="search" name="q" value="<?php echo aep_h($q); ?>" placeholder="Search by title"></div>
<div><label>Matter</label><select name="matter_id" onchange="this.form.submit()"><option value="">All matters</option><?php foreach($matters as $matter): ?><option value="<?php echo (int)$matter['id']; ?>" <?php echo $matterFilter===(int)$matter['id']?'selected':''; ?>><?php echo aep_h($matter['matter_reference'].' — '.$matter['subject']); ?></option><?php endforeach; ?></select></div>
<div><label>Status</label><select name="status" onchange="this.form.submit()"><option value="">All statuses</option><?php foreach(['open'=>'Open','in_progress'=>'In progress','completed'=>'Completed'] as $v=>$label): ?><option value="<?php echo $v; ?>" <?php echo $statusFilter===$v?'selected':''; ?>><?php echo $label; ?></option><?php endforeach; ?></select></div>
<div><label>Sort by</label><select name="sort" onchange="this.form.submit()"><option value="due_date" <?php echo $sort==='due_date'?'selected':''; ?>>Due date</option><option value="created_at" <?php echo $sort==='created_at'?'selected':''; ?>>Created date</option><option value="status" <?php echo $sort==='status'?'selected':''; ?>>Status</option></select></div>
<div><label><input type="checkbox" name="overdue" value="1" style="width:auto;display:inline-block" <?php echo $overdueOnly?'checked':''; ?> onchange="this.form.submit()"> Overdue only</label></div>
<div style="align-self:end"><button class="btn secondary">Apply</button></div>
</form>
<div class="card" style="margin-top:18px"><?php if(!$tasks):?><div class="empty"><?php echo ($q!==''||$matterFilter||$statusFilter!==''||$overdueOnly)?'No tasks match these filters.':'Ready to create your first task.'; ?><br><br><a class="btn" href="task_create.php">Create task</a></div><?php else:?><table><thead><tr><th>Task</th><th>Matter</th><th>Due</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($tasks as $task):?><tr><td><strong><?php echo aep_h($task['title']);?></strong><br><span class="muted"><?php echo aep_h($task['description']);?></span></td><td><?php echo $task['matter_reference']?'<a href="matter_view.php?id='.(int)$task['matter_id'].'">'.aep_h($task['matter_reference']).'</a>':'—';?></td><td><?php echo aep_h($task['due_date']?:'—');?></td><td><span class="badge"><?php echo aep_h($task['status']);?></span></td><td><a class="btn secondary" href="task_view.php?id=<?php echo (int)$task['id'];?>">Open</a></td></tr><?php endforeach;?></tbody></table>
<div class="actions" style="justify-content:space-between;margin-top:14px"><?php $qs=$_GET; ?>
<?php if ($page>1): $qs['page']=$page-1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Previous</a><?php else: ?><span></span><?php endif; ?>
<span class="muted">Page <?php echo $page; ?> of <?php echo max(1,(int)ceil($total/$perPage)); ?></span>
<?php if ($offset+$perPage<$total): $qs['page']=$page+1; ?><a class="btn secondary" href="?<?php echo http_build_query($qs); ?>">Next</a><?php else: ?><span></span><?php endif; ?>
</div>
<?php endif;?></div><?php require __DIR__.'/includes/footer.php';?>
