<?php
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/functions.php';$pdo=aep_database();$id=(int)($_GET['id']??$_POST['id']??0);$stmt=$pdo->prepare('SELECT * FROM clients WHERE id=?');$stmt->execute([$id]);$client=$stmt->fetch();if(!$client){http_response_code(404);exit('Client not found.');}
$stmt=$pdo->prepare('SELECT COUNT(*) FROM matters WHERE client_id=?');$stmt->execute([$id]);$matterCount=(int)$stmt->fetchColumn();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){aep_validate_csrf();if($matterCount>0){$error='This client has linked matters and cannot be deleted until they are removed or reassigned.';}else{$pdo->prepare('DELETE FROM clients WHERE id=?')->execute([$id]);aep_log_activity($pdo,'Clients','Deleted '.$client['client_reference'],$id);header('Location:client_list.php?msg=Client+deleted');exit;}}
$pageTitle='Delete Client | AEP';$activeNav='clients';require __DIR__.'/includes/header.php';?>
<div class="card" style="max-width:680px;margin:auto"><h1>Delete Client</h1><p class="muted">Clients &gt; <a href="client_view.php?id=<?php echo $id;?>"><?php echo aep_h($client['client_reference']);?></a> &gt; Delete</p>
<?php if($error):?><p style="color:#b42318"><?php echo aep_h($error);?></p><?php endif;?>
<p>Are you sure you want to delete <strong><?php echo aep_h($client['name']);?></strong> (<?php echo aep_h($client['client_reference']);?>)? This cannot be undone.</p>
<?php if($matterCount>0):?><p style="color:#b42318"><?php echo $matterCount;?> matter(s) are linked to this client. Remove or reassign them before deleting.</p><?php endif;?>
<form method="post"><?php echo aep_csrf_field();?><input type="hidden" name="id" value="<?php echo $id;?>"><div class="actions"><button class="btn danger" <?php echo $matterCount>0?'disabled':'';?>>Delete client</button><a class="btn secondary" href="client_view.php?id=<?php echo $id;?>">Cancel</a></div></form></div>
<?php require __DIR__.'/includes/footer.php';?>
