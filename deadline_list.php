<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();
$matterFilter=(int)($_GET['matter_id']??0);$priorityFilter=aep_valid_status((string)($_GET['priority']??''),['low','normal','high','urgent'],'');$statusFilter=aep_valid_status((string)($_GET['status']??''),['open','completed'],'');
$where=[];$params=[];
if($matterFilter){$where[]='d.matter_id=?';$params[]=$matterFilter;}
if($priorityFilter!==''){$where[]='d.priority=?';$params[]=$priorityFilter;}
if($statusFilter!==''){$where[]='d.status=?';$params[]=$statusFilter;}
$whereSql=$where?'WHERE '.implode(' AND ',$where):'';
$perPage=10;$page=max(1,(int)($_GET['page']??1));$offset=($page-1)*$perPage;
$countStmt=$pdo->prepare("SELECT COUNT(*) FROM deadlines d $whereSql");$countStmt->execute($params);$total=(int)$countStmt->fetchColumn();
$stmt=$pdo->prepare("SELECT d.*,m.matter_reference,m.subject FROM deadlines d LEFT JOIN matters m ON m.id=d.matter_id $whereSql ORDER BY d.status='completed',d.due_date LIMIT $perPage OFFSET $offset");
$stmt->execute($params);$deadlines=$stmt->fetchAll();
$matters=$pdo->query('SELECT id,matter_reference,subject FROM matters ORDER BY updated_at DESC')->fetchAll();
$pageTitle='Deadlines | AEP';$activeNav='deadlines';require __DIR__.'/includes/header.php';
?>
<div class="actions" style="justify-content:space-between"><div><h1>Deadlines</h1><p class="muted"><?php echo (int)$total;?> deadline(s) tracked.</p></div><a class="btn" href="deadline_create.php">Add deadline</a></div>
<form method="get" class="grid grid-3" style="margin-top:18px">
<div><label>Matter</label><select name="matter_id" onchange="this.form.submit()"><option value="">All matters</option><?php foreach($matters as $matter):?><option value="<?php echo (int)$matter['id'];?>" <?php echo $matterFilter===(int)$matter['id']?'selected':'';?>><?php echo aep_h($matter['matter_reference'].' — '.$matter['subject']);?></option><?php endforeach;?></select></div>
<div><label>Priority</label><select name="priority" onchange="this.form.submit()"><option value="">All priorities</option><?php foreach(['low','normal','high','urgent'] as $p):?><option value="<?php echo $p;?>" <?php echo $priorityFilter===$p?'selected':'';?>><?php echo ucfirst($p);?></option><?php endforeach;?></select></div>
<div><label>Status</label><select name="status" onchange="this.form.submit()"><option value="">All statuses</option><?php foreach(['open','completed'] as $s):?><option value="<?php echo $s;?>" <?php echo $statusFilter===$s?'selected':'';?>><?php echo ucfirst($s);?></option><?php endforeach;?></select></div>
</form>
<div class="card" style="margin-top:18px"><?php if(!$deadlines):?><div class="empty">No deadlines match these filters.<br><br><a class="btn" href="deadline_create.php">Add deadline</a></div><?php else:?><table><thead><tr><th>Title</th><th>Matter</th><th>Due date</th><th>Days until due</th><th>Priority</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($deadlines as $deadline):
  $days=(int)((strtotime($deadline['due_date'])-strtotime(date('Y-m-d')))/86400);
  $rowStyle='';
  if($deadline['status']!=='completed'){
    if($days<0){$rowStyle='background:#fdecea';}
    elseif($days<=7){$rowStyle='background:#fef7e0';}
  }
?><tr style="<?php echo $rowStyle;?>"><td><a href="deadline_view.php?id=<?php echo (int)$deadline['id'];?>"><?php echo aep_h($deadline['title']);?></a></td><td><?php echo $deadline['matter_reference']?'<a href="matter_view.php?id='.(int)$deadline['matter_id'].'">'.aep_h($deadline['matter_reference']).'</a>':'—';?></td><td><?php echo aep_h($deadline['due_date']);?></td><td><?php echo $deadline['status']==='completed'?'—':($days<0?abs($days).' day(s) overdue':($days===0?'Due today':$days.' day(s)')); ?></td><td><span class="badge"><?php echo aep_h($deadline['priority']);?></span></td><td><span class="badge"><?php echo aep_h($deadline['status']);?></span></td><td><a class="btn secondary" href="deadline_view.php?id=<?php echo (int)$deadline['id'];?>">Open</a></td></tr><?php endforeach;?></tbody></table>
<div class="actions" style="justify-content:space-between;margin-top:14px"><?php $qs=$_GET;?>
<?php if($page>1):$qs['page']=$page-1;?><a class="btn secondary" href="?<?php echo http_build_query($qs);?>">Previous</a><?php else:?><span></span><?php endif;?>
<span class="muted">Page <?php echo $page;?> of <?php echo max(1,(int)ceil($total/$perPage));?></span>
<?php if($offset+$perPage<$total):$qs['page']=$page+1;?><a class="btn secondary" href="?<?php echo http_build_query($qs);?>">Next</a><?php else:?><span></span><?php endif;?>
</div>
<?php endif;?></div>
<?php require __DIR__.'/includes/footer.php';?>
