<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();$id=(int)($_GET['id']??$_POST['id']??0);$stmt=$pdo->prepare('SELECT * FROM matters WHERE id=?');$stmt->execute([$id]);$matter=$stmt->fetch();if(!$matter){http_response_code(404);exit('Matter not found.');}
$stmt=$pdo->prepare('SELECT COUNT(*) FROM tasks WHERE matter_id=?');$stmt->execute([$id]);$taskCount=(int)$stmt->fetchColumn();
$stmt=$pdo->prepare('SELECT COUNT(*) FROM deadlines WHERE matter_id=?');$stmt->execute([$id]);$deadlineCount=(int)$stmt->fetchColumn();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){aep_validate_csrf();
    $pdo->prepare('DELETE FROM tasks WHERE matter_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM deadlines WHERE matter_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM matters WHERE id=?')->execute([$id]);
    aep_log_activity($pdo,'Matters','Deleted '.$matter['matter_reference'],$id);
    header('Location:client_view.php?id='.(int)$matter['client_id'].'&msg=Matter+deleted');exit;
}
$pageTitle='Delete Matter | AEP';$activeNav='matters';require __DIR__.'/includes/header.php';?>
<div class="card" style="max-width:680px;margin:auto"><h1>Delete Matter</h1><p class="muted">Matters &gt; <a href="matter_view.php?id=<?php echo $id;?>"><?php echo aep_h($matter['matter_reference']);?></a> &gt; Delete</p>
<p>Are you sure you want to delete <strong><?php echo aep_h($matter['subject']);?></strong> (<?php echo aep_h($matter['matter_reference']);?>)? This cannot be undone.</p>
<?php if($taskCount>0||$deadlineCount>0):?><p style="color:#b42318">This will also delete <?php echo $taskCount;?> task(s) and <?php echo $deadlineCount;?> deadline(s) linked to this matter.</p><?php endif;?>
<form method="post"><?php echo aep_csrf_field();?><input type="hidden" name="id" value="<?php echo $id;?>"><div class="actions"><button class="btn danger">Delete matter</button><a class="btn secondary" href="matter_view.php?id=<?php echo $id;?>">Cancel</a></div></form></div>
<?php require __DIR__.'/includes/footer.php';?>
