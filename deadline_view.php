<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT d.*,m.matter_reference,m.subject FROM deadlines d LEFT JOIN matters m ON m.id=d.matter_id WHERE d.id=?');$stmt->execute([$id]);$deadline=$stmt->fetch();
if(!$deadline){http_response_code(404);exit('Deadline not found.');}
$matters=$pdo->query('SELECT id,matter_reference,subject FROM matters ORDER BY updated_at DESC')->fetchAll();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  aep_validate_csrf();
  $action=$_POST['action']??'update';
  if($action==='delete'){
    $pdo->prepare('DELETE FROM deadlines WHERE id=?')->execute([$id]);
    aep_log_activity($pdo,'Deadlines','Deleted deadline: '.$deadline['title'],$id);
    header('Location:'.($deadline['matter_id']?'matter_view.php?id='.(int)$deadline['matter_id'].'&msg=Deadline+deleted':'deadline_list.php?msg=Deadline+deleted'));exit;
  } elseif($action==='complete'){
    $pdo->prepare('UPDATE deadlines SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute(['completed',$id]);
    aep_log_activity($pdo,'Deadlines','Completed deadline: '.$deadline['title'],$id);
    header('Location:deadline_view.php?id='.$id);exit;
  } else {
    $title=aep_post_string('title',300);$date=aep_post_string('due_date',10);$matterId=(int)($_POST['matter_id']??0);
    if($title===''||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)){$error='Title and a valid deadline date are required.';}
    elseif($matterId&&!array_filter($matters,fn($m)=>(int)$m['id']===$matterId)){$error='Select a valid matter.';}
    else{
      $pdo->prepare('UPDATE deadlines SET matter_id=?,title=?,due_date=?,priority=?,status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$matterId?:null,$title,$date,aep_valid_status(aep_post_string('priority',20),['low','normal','high','urgent'],'normal'),aep_valid_status(aep_post_string('status',20),['open','completed'],'open'),$id]);
      aep_log_activity($pdo,'Deadlines','Updated deadline: '.$title,$id);
      header('Location:deadline_view.php?id='.$id);exit;
    }
  }
}
$stmt=$pdo->prepare('SELECT * FROM activity_log WHERE entity=? AND entity_id=? ORDER BY created_at DESC LIMIT 20');$stmt->execute(['Deadlines',$id]);$activity=$stmt->fetchAll();
$days=(int)((strtotime($deadline['due_date'])-strtotime(date('Y-m-d')))/86400);
$pageTitle='Deadline | AEP';$activeNav='deadlines';require __DIR__.'/includes/header.php';
?>
<div class="card" style="max-width:820px;margin:auto">
<p class="muted">Deadlines &gt; <?php echo aep_h($deadline['title']);?></p>
<div class="actions" style="justify-content:space-between"><div><span class="badge"><?php echo aep_h($deadline['priority']);?></span> <span class="badge"><?php echo aep_h($deadline['status']);?></span><h1 style="margin:8px 0"><?php echo aep_h($deadline['title']);?></h1><p class="muted">Due <?php echo aep_h($deadline['due_date']);?> — <?php echo $deadline['status']==='completed'?'Completed':($days<0?abs($days).' day(s) overdue':($days===0?'Due today':$days.' day(s) remaining'));?><?php if($deadline['matter_reference']):?> · Matter: <a href="matter_view.php?id=<?php echo (int)$deadline['matter_id'];?>"><?php echo aep_h($deadline['matter_reference']);?></a><?php endif;?></p></div>
<div class="actions"><?php if($deadline['status']!=='completed'):?><form method="post" style="display:inline"><?php echo aep_csrf_field();?><input type="hidden" name="action" value="complete"><button class="btn">Mark as completed</button></form><?php endif;?>
<form method="post" style="display:inline" onsubmit="return confirm('Delete this deadline?');"><?php echo aep_csrf_field();?><input type="hidden" name="action" value="delete"><button class="btn danger">Delete</button></form></div></div>
<?php if($error):?><p style="color:#b42318"><?php echo aep_h($error);?></p><?php endif;?>
<h2 style="margin-top:24px">Edit Deadline</h2>
<form method="post" class="grid grid-2" style="margin-top:12px"><?php echo aep_csrf_field();?><input type="hidden" name="action" value="update">
<div><label>Deadline *</label><input required name="title" value="<?php echo aep_h($deadline['title']);?>"></div>
<div><label>Due date *</label><input required type="date" name="due_date" value="<?php echo aep_h($deadline['due_date']);?>"></div>
<div><label>Matter</label><select name="matter_id"><option value="">Unlinked deadline</option><?php foreach($matters as $matter):?><option value="<?php echo (int)$matter['id'];?>" <?php echo (int)$deadline['matter_id']===(int)$matter['id']?'selected':'';?>><?php echo aep_h($matter['matter_reference'].' — '.$matter['subject']);?></option><?php endforeach;?></select></div>
<div><label>Priority</label><select name="priority"><?php foreach(['low','normal','high','urgent'] as $p):?><option value="<?php echo $p;?>" <?php echo $deadline['priority']===$p?'selected':'';?>><?php echo ucfirst($p);?></option><?php endforeach;?></select></div>
<div><label>Status</label><select name="status"><?php foreach(['open'=>'Open','completed'=>'Completed'] as $v=>$label):?><option value="<?php echo $v;?>" <?php echo $deadline['status']===$v?'selected':'';?>><?php echo $label;?></option><?php endforeach;?></select></div>
<div class="actions"><button class="btn">Save changes</button></div>
</form>
<?php if($activity):?><h2 style="margin-top:24px">Activity</h2><ul><?php foreach($activity as $entry):?><li><span class="muted"><?php echo aep_h($entry['created_at']);?></span> — <?php echo aep_h($entry['action']);?></li><?php endforeach;?></ul><?php endif;?>
</div>
<?php require __DIR__.'/includes/footer.php';?>
