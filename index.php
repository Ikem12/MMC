<?php
// FILE: index.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
$loggedIn = !empty($_SESSION['user_id']);
$isAdmin  = !empty($_SESSION['is_admin']);
$username = $_SESSION['username'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#0b63a8;color:#fff;padding:28px 20px;text-align:center}
    header h1{font-size:2rem;margin-bottom:6px}
    header p{font-size:0.95rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#0b63a8;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#084d85;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:1100px;margin:30px auto;padding:0 18px}
    .domain-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:10px}
    .domain-card{background:#fff;border-radius:8px;padding:22px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:center;border-top:4px solid #0b63a8}
    .domain-card.coming{border-top-color:#aaa}
    .domain-card h3{margin-bottom:8px;font-size:1.05rem;color:#0b63a8}
    .domain-card.coming h3{color:#888}
    .domain-card p{font-size:0.82rem;color:#555;margin-bottom:14px;min-height:36px}
    .domain-card a{display:inline-block;padding:7px 16px;background:#0b63a8;color:#fff;border-radius:4px;text-decoration:none;font-size:0.88rem}
    .domain-card.coming a{background:#aaa;pointer-events:none;cursor:default}
    .section-title{font-size:1.1rem;font-weight:bold;margin:28px 0 12px;color:#084d85;border-bottom:2px solid #0b63a8;padding-bottom:6px}
    .info-box{background:#fff;border-radius:8px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.07);margin-top:18px;font-size:0.9rem;line-height:1.7}
    .info-box code{background:#f0f4f8;padding:2px 6px;border-radius:3px;font-size:0.85rem}
    .user-bar{background:#e8f0fa;padding:8px 20px;text-align:right;font-size:0.88rem}
    .user-bar a{margin-left:12px;color:#0b63a8;text-decoration:none;font-weight:bold}
  </style>
</head>
<body>

<header>
  <h1>AEP Legal Platform <span class="file-tag">FILE: index.php</span></h1>
  <p>Prototype (SQLite + PHP) — Multi-Domain Legal Case Management</p>
</header>

<div class="user-bar">
  <?php if ($loggedIn): ?>
    Logged in as <strong><?php echo htmlspecialchars($username); ?></strong>
    <?php if ($isAdmin): ?> &nbsp;[Admin]<?php endif; ?>
    <a href="logout.php">Logout</a>
  <?php else: ?>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
  <?php endif; ?>
</div>

<nav>
  <a href="index.php">Home</a>
  <a href="login.php">Login</a>
  <a href="register.php">Register</a>
  <a href="create.php">Create Case</a>
  <a href="list.php">List Cases</a>
  <?php if ($isAdmin): ?>
    <a href="admin.php">Admin</a>
    <a href="hr_reviews.php">HR Admin</a>
  <?php endif; ?>
</nav>

<div class="container">

  <div class="section-title">Legal Domains</div>

  <div class="domain-grid">

    <div class="domain-card">
      <h3>⚖️ Human Rights</h3>
      <p>Cases involving fundamental rights and freedoms under domestic and international law.</p>
      <a href="human-rights.php">Open</a>
    </div>

    <div class="domain-card">
      <h3>🏛️ Administrative Law</h3>
      <p>Challenges to decisions by public bodies exercising governmental, regulatory and tribunal functions.</p>
      <a href="admin_law.php">Open</a>
    </div>

    <div class="domain-card">
      <h3>🛢️ Oil &amp; Gas Law</h3>
      <p>Licensing, regulatory compliance, operator disputes and environmental issues in the oil &amp; gas sector.</p>
      <a href="oil_gas.php">Open</a>
    </div>

    <div class="domain-card">
      <h3>⚠️ Tort Law</h3>
      <p>Negligence, nuisance, defamation and other civil wrongs — duty, breach, causation and damage.</p>
      <a href="tort.php">Open</a>
    </div>

    <div class="domain-card coming">
      <h3>📄 Contract Law</h3>
      <p>Coming soon — formation, breach, remedies and enforcement of contracts.</p>
      <a href="contract.php">Coming Soon</a>
    </div>

    <div class="domain-card coming">
      <h3>⚖️ Criminal Law</h3>
      <p>Coming soon — offences, defences, procedure and sentencing in criminal matters.</p>
      <a href="criminal.php">Coming Soon</a>
    </div>

  </div>

  <div class="section-title">Quick Actions</div>
  <div class="domain-grid">
    <div class="domain-card">
      <h3>📁 Create Case</h3>
      <p>Open a new legal case on the platform.</p>
      <a href="create.php">Create</a>
    </div>
    <div class="domain-card">
      <h3>📋 List Cases</h3>
      <p>View and manage all existing cases.</p>
      <a href="list.php">View</a>
    </div>
    <?php if ($isAdmin): ?>
    <div class="domain-card">
      <h3>🔧 Admin Panel</h3>
      <p>Manage users, database and platform settings.</p>
      <a href="admin.php">Admin</a>
    </div>
    <div class="domain-card">
      <h3>👥 HR Admin</h3>
      <p>Review and manage HR review requests.</p>
      <a href="hr_reviews.php">HR Admin</a>
    </div>
    <?php endif; ?>
  </div>

  <div class="info-box" style="margin-top:28px">
    <strong>Dev Notes:</strong><br>
    Run <code>php init_db.php</code> once to initialise the database and seed admin user
    (username: <code>admin</code> / password: <code>change-me</code>).<br>
    Dev server: <code>php -S 0.0.0.0:8001</code> &nbsp;|&nbsp;
    Local: <code>http://localhost:8001</code> &nbsp;|&nbsp;
    Network: <code>http://192.168.1.91:8001</code>
  </div>

</div>
</body>
</html>