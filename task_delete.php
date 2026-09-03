<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();$id=(int)($_GET['id']??$_POST['id']??0);$stmt=$pdo->prepare('SELECT t.*,m.matter_reference FROM tasks t LEFT JOIN matters m ON m.id=t.matter_id WHERE t.id=?');$stmt->execute([$id]);$task=$stmt->fetch();if(!$task){http_response_code(404);exit('Task not found.');}
if($_SERVER['REQUEST_METHOD']==='POST'){aep_validate_csrf();$pdo->prepare('DELETE FROM tasks WHERE id=?')->execute([$id]);aep_log_activity($pdo,'Tasks','Deleted task: '.$task['title'],$id);header('Location:'.($task['matter_id']?'matter_view.php?id='.(int)$task['matter_id'].'&msg=Task+deleted':'task_list.php?msg=Task+deleted'));exit;}
$pageTitle='Delete Task | AEP';$activeNav='tasks';require __DIR__.'/includes/header.php';?>
<div class="card" style="max-width:680px;margin:auto"><h1>Delete Task</h1><p class="muted">Tasks &gt; <a href="task_view.php?id=<?php echo $id;?>"><?php echo aep_h($task['title']);?></a> &gt; Delete</p>
<p>Are you sure you want to delete <strong><?php echo aep_h($task['title']);?></strong>? This cannot be undone.</p>
<form method="post"><?php echo aep_csrf_field();?><input type="hidden" name="id" value="<?php echo $id;?>"><div class="actions"><button class="btn danger">Delete task</button><a class="btn secondary" href="task_view.php?id=<?php echo $id;?>">Cancel</a></div></form></div>
<?php require __DIR__.'/includes/footer.php';?>
