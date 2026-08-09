<?php
session_start();
require_once 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $result = loginUser($username, $password);
    if ($result === true) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Login — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1a3c5e,#2e6da4);min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#fff;border-radius:12px;padding:40px;width:100%;max-width:420px;box-shadow:0 10px 40px rgba(0,0,0,0.2)}
.logo{text-align:center;margin-bottom:28px}
.logo h1{font-size:1.4rem;color:#1a3c5e;margin-top:8px}
.logo p{font-size:0.85rem;color:#888;margin-top:4px}
.form-group{margin-bottom:18px}
label{display:block;font-size:0.8rem;font-weight:bold;color:#555;margin-bottom:6px}
input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem}
input:focus{outline:none;border-color:#1a3c5e}
.btn{width:100%;padding:12px;background:#1a3c5e;color:#fff;border:none;border-radius:4px;font-size:0.95rem;cursor:pointer;margin-top:8px}
.btn:hover{background:#122840}
.alert{background:#f8d7da;color:#721c24;padding:10px 14px;border-radius:4px;margin-bottom:18px;font-size:0.88rem}
.footer{text-align:center;margin-top:20px;font-size:0.8rem;color:#aaa}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div style="font-size:2.5rem">&#9878;&#65039;</div>
    <h1>AEP Legal Platform</h1>
    <p>Sign in to your account</p>
  </div>
  <?php if ($error): ?>
    <div class="alert">&#10060; <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="Enter username" required/>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required/>
    </div>
    <button type="submit" class="btn">&#128274; Sign In</button>
  </form>
  <div class="footer">AEP Legal Consultancy &copy; <?php echo date('Y'); ?></div>
</div>
</body>
</html>