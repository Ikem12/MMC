<?php
session_start();
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $success = 'Account created! You can now login.';
        } catch (Exception $e) {
            $error = 'Username already exists. Please choose another.';
        }
    } else {
        $error = 'Please fill in all fields.';
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
body{font-family:Arial,sans-serif;background:#f4f6f9;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#fff;border-radius:12px;padding:40px;box-shadow:0 4px 20px rgba(0,0,0,0.1);width:100%;max-width:400px}
.logo{text-align:center;margin-bottom:24px}
.logo h1{color:#1a3c5e;font-size:1.4rem}
.logo p{color:#888;font-size:0.85rem;margin-top:4px}
label{display:block;font-size:0.82rem;font-weight:bold;color:#555;margin-bottom:6px}
input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;margin-bottom:16px}
input:focus{outline:none;border-color:#1a3c5e}
.btn{width:100%;padding:11px;background:#28a745;color:#fff;border:none;border-radius:4px;font-size:0.95rem;cursor:pointer}
.btn:hover{background:#1e7e34}
.alert{background:#f8d7da;color:#721c24;padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:0.88rem}
.alert-success{background:#d4edda;color:#155724;padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:0.88rem}
.links{text-align:center;margin-top:16px;font-size:0.85rem;color:#888}
.links a{color:#1a3c5e;text-decoration:none}
.links a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <h1>⚖️ AEP Legal Platform</h1>
    <p>Create your account</p>
  </div>

  <?php if ($error): ?>
    <div class="alert">❌ <?php echo $error; ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert-success">✅ <?php echo $success; ?>
      <br/><a href="login.php">→ Click here to Login</a>
    </div>
  <?php endif; ?>

  <form method="POST">
    <label>Username</label>
    <input type="text" name="username" required autofocus placeholder="admin"/>

    <label>Password</label>
    <input type="password" name="password" required placeholder="123"/>

    <button type="submit" class="btn">✅ Create Account</button>
  </form>

  <div class="links">
    Already have an account? <a href="login.php">Login here</a>
  </div>
</div>
</body>
</html>