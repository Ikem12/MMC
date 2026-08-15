<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();

// Already logged in — go to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            $return = $_GET['return'] ?? 'dashboard.php';
            // Only allow local relative paths to prevent open-redirect
            if (preg_match('#^https?://#i', $return) || strpos($return, '//') === 0) {
                $return = 'dashboard.php';
            }
            header('Location: ' . $return);
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
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