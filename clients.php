<?php
require_once __DIR__ . '/phase2.php'; p2_start(); $pdo = p2_db(); p2_check_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') p2_redirect('clients.php', 'A client name is required.');
    $stmt = $pdo->prepare('INSERT INTO p2_clients (name,email,phone,address) VALUES (?,?,?,?)');
    $stmt->execute([$name, trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? '')]);
    $id=(int)$pdo->lastInsertId(); p2_log('client.created', "Created client {$name}", null, ['client_id'=>$id]); p2_redirect('clients.php', 'Client created.');
}
$clients=$pdo->query("SELECT c.*, COUNT(m.id) matter_count FROM p2_clients c LEFT JOIN p2_matters m ON m.client_id=c.id GROUP BY c.id ORDER BY c.created_at DESC")->fetchAll();
p2_page('Clients','clients.php',function() use($clients){ ?>
<div class="toolbar"><div><h1>Clients</h1><p class="subhead">A single client record can support multiple connected matters.</p></div></div>
<div class="grid grid-2"><section class="card"><h2>Add client</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><div class="full"><label>Name *</label><input required name="name"></div><div><label>Email</label><input type="email" name="email"></div><div><label>Phone</label><input name="phone"></div><div class="full"><label>Address</label><textarea name="address"></textarea></div><div><button>Add client</button></div></form></section>
<section class="card"><h2>Client directory</h2><?php if(!$clients):?><div class="empty">No clients have been added.</div><?php else:?><table><thead><tr><th>Client</th><th>Contact</th><th>Matters</th></tr></thead><tbody><?php foreach($clients as $client):?><tr><td><a href="client_view.php?id=<?=$client['id']?>"><?=p2_h($client['name'])?></a></td><td><?=p2_h($client['email'] ?: $client['phone'] ?: '—')?></td><td><a href="client_view.php?id=<?=$client['id']?>&tab=matters"><?=$client['matter_count']?> matter(s)</a></td></tr><?php endforeach;?></tbody></table><?php endif;?></section></div>
<?php }); ?>
