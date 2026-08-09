<?php
session_start();
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)')->execute([$username, $hash]);
            $success = 'Account created! You can now log in.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Register — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1a3c5e,#2e6da4);min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:420px;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
.logo{text-align:center;margin-bottom:28px}
.logo h1{font-size:1.4rem;color:#1a3c5e;margin-top:8px}
.form-group{margin-bottom:18px}
label{display:block;font-size:0.8rem;font-weight:bold;color:#555;margin-bottom:6px}
input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem}
input:focus{outline:none;border-color:#1a3c5e}
.btn{width:100%;padding:12px;background:#1a3c5e;color:#fff;border:none;border-radius:4px;font-size:0.95rem;cursor:pointer;margin-top:8px}
.btn:hover{background:#122840}
.alert{padding:10px 14px;border-radius:4px;margin-bottom:18px;font-size:0.88rem}
.alert-error{background:#f8d7da;color:#721c24}
.alert-success{background:#d4edda;color:#155724}
.footer{text-align:center;margin-top:20px;font-size:0.8rem;color:#aaa}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div style="font-size:2.5rem">&#9878;&#65039;</div>
    <h1>Create Account</h1>
  </div>
  <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="login.php">Login here</a></div><?php endif; ?>
  <form method="POST">
    <div class="form-group"><label>Username</label><input type="text" name="username" required/></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required/></div>
    <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm" required/></div>
    <button type="submit" class="btn">Create Account</button>
  </form>
  <div class="footer"><a href="login.php">Back to Login</a></div>
</div>
</body>
</html>