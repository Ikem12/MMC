<?php
// FILE: criminal.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Criminal Law — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#8b0000;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.7rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#8b0000;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#5c0000;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:860px;margin:60px auto;padding:0 18px;text-align:center}
    .coming-card{background:#fff;border-radius:12px;padding:50px 36px;box-shadow:0 4px 16px rgba(0,0,0,0.10);border-top:6px solid #8b0000}
    .coming-icon{font-size:4rem;margin-bottom:16px}
    .coming-card h2{font-size:1.8rem;color:#8b0000;margin-bottom:12px}
    .coming-card p{font-size:0.97rem;color:#555;line-height:1.7;max-width:520px;margin:0 auto 24px}
    .topics{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:28px}
    .topic-tag{background:#fdf0f0;color:#8b0000;padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:bold;border:1px solid #f5c0c0}
    .btn{display:inline-block;padding:10px 24px;background:#8b0000;color:#fff;border-radius:4px;text-decoration:none;font-size:0.95rem}
    .btn:hover{background:#5c0000}
    .notify-box{background:#fdf0f0;border-radius:8px;padding:18px 24px;margin-top:24px;font-size:0.88rem;color:#5c0000;border:1px solid #f5c0c0}
  </style>
</head>
<body>

<header>
  <h1>⚖️ Criminal Law <span class="file-tag">FILE: criminal.php</span></h1>
  <p>Offences, defences, procedure and sentencing in criminal matters</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="contract.php">Contract Law</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">
  <div class="coming-card">
    <div class="coming-icon">⚖️</div>
    <h2>Criminal Law — Coming Soon</h2>
    <p>
      This module is currently under development. It will cover all key areas of
      criminal law including offences, defences, criminal procedure, evidence
      and sentencing. Full case management, checklists and analysis tools
      will be available.
    </p>

    <div class="topics">
      <span class="topic-tag">🔪 Actus Reus</span>
      <span class="topic-tag">🧠 Mens Rea</span>
      <span class="topic-tag">🛡️ Defences</span>
      <span class="topic-tag">📋 Criminal Procedure</span>
      <span class="topic-tag">⚖️ Sentencing</span>
      <span class="topic-tag">🔍 Evidence</span>
      <span class="topic-tag">🏛️ Magistrates &amp; Crown Court</span>
      <span class="topic-tag">🚔 Arrest &amp; Detention</span>
      <span class="topic-tag">📜 Indictable Offences</span>
      <span class="topic-tag">⚠️ Summary Offences</span>
    </div>

    <a href="index.php" class="btn">← Back to Home</a>

    <div class="notify-box">
      🚧 <strong>Under Development</strong> — Criminal Law case management,
      pre-litigation checklist and case strength analysis engine coming in the next release.
    </div>
  </div>
</div>

</body>
</html>