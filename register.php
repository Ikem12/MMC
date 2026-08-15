<?php
// FILE: register.php
// This platform is private. Public self-registration is disabled.
// New users can only be created by an administrator via users.php.
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();

// If an admin is logged in, redirect them to the user management page
if (!empty($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header('Location: users.php');
    exit;
}

// All other visitors — deny access
http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Access Denied — AEP Legal Platform</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#fff;border-radius:12px;padding:40px;box-shadow:0 4px 20px rgba(0,0,0,0.1);width:100%;max-width:400px;text-align:center}
h1{color:#1a3c5e;font-size:1.4rem;margin-bottom:12px}
p{color:#555;font-size:0.92rem;margin-bottom:20px}
a{display:inline-block;padding:10px 22px;background:#1a3c5e;color:#fff;text-decoration:none;border-radius:4px;font-size:0.9rem}
a:hover{background:#0f2a45}
</style>
</head>
<body>
<div class="card">
  <h1>⚖️ AEP Legal Platform</h1>
  <p>&#128683; This platform is private. Self-registration is not permitted.<br>
  Contact your administrator to request access.</p>
  <a href="login.php">← Back to Login</a>
</div>
</body>
</html>