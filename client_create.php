<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = aep_database();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    aep_validate_csrf();
    $name = aep_post_string('name', 160);
    $email = aep_post_string('email', 160);
    $phone = aep_post_string('phone', 60);
    $address = aep_post_string('address', 1000);
    if ($name === '') {
        $error = 'Client name is required.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address or leave it blank.';
    } else {
        $reference = aep_reference('CLI');
        $pdo->prepare('INSERT INTO clients (client_reference,name,email,phone,address) VALUES (?,?,?,?,?)')->execute([$reference,$name,$email,$phone,$address]);
        aep_log_activity($pdo, 'Clients', 'Created ' . $reference);
        header('Location: client_view.php?id=' . (int)$pdo->lastInsertId());
        exit;
    }
}
$pageTitle = 'Create Client | AEP'; $activeNav = 'clients'; require __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:820px;margin:auto"><h1>Create Client</h1><p class="muted">Create a client record before opening linked matters.</p>
<?php if ($error): ?><p style="color:#b42318"><?php echo aep_h($error); ?></p><?php endif; ?>
<form method="post" class="grid grid-2" style="margin-top:18px"><?php echo aep_csrf_field(); ?>
<div><label>Client name *</label><input required name="name" value="<?php echo aep_h($_POST['name'] ?? ''); ?>"></div>
<div><label>Email</label><input type="email" name="email" value="<?php echo aep_h($_POST['email'] ?? ''); ?>"></div>
<div><label>Phone</label><input name="phone" value="<?php echo aep_h($_POST['phone'] ?? ''); ?>"></div>
<div><label>Address</label><textarea name="address"><?php echo aep_h($_POST['address'] ?? ''); ?></textarea></div>
<div class="actions"><button class="btn">Create client</button><a class="btn secondary" href="client_list.php">Cancel</a></div></form></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
