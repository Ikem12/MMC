<?php
/**
 * FILE: includes/header.php
 * Shared responsive header, styles and navigation for the AEP Legal
 * Intelligence Platform. Expects $pageTitle and $activeNav to be set
 * by the including page, and (optionally) an authenticated session
 * from includes/auth.php.
 */

require_once __DIR__ . '/functions.php';

if (!isset($pageTitle)) {
    $pageTitle = 'AEP Legal Intelligence Platform';
}
if (!isset($activeNav)) {
    $activeNav = '';
}

$aepUser = function_exists('aep_current_user') ? aep_current_user() : null;
$aepUsername = $aepUser['username'] ?? ($_SESSION['username'] ?? 'User');

$aepNavLinks = [
    'workspace' => ['label' => 'Workspace', 'href' => 'dashboard.php'],
    'clients' => ['label' => 'Clients', 'href' => 'client_list.php'],
    'matters' => ['label' => 'Matters', 'href' => 'matter_list.php'],
    'tasks' => ['label' => 'Tasks', 'href' => 'task_list.php'],
    'knowledge' => ['label' => 'Knowledge Centre', 'href' => 'knowledge_centre.php'],
    'counsel' => ['label' => 'Counsel Engine', 'href' => 'counsel_engine.php'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= aep_h($pageTitle) ?></title>
<style>
:root{--aep-primary:#1a3c5e;--aep-primary-dark:#122840;--aep-accent:#2e6da4;--aep-border:#e2e6ea;--aep-muted:#6b7785;--aep-bg:#f4f6f9}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--aep-bg);color:#222}
.header{background:var(--aep-primary);color:#fff;padding:0 20px}
.header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;padding:12px 0}
.header-inner .brand{font-weight:bold;font-size:1.1rem;color:#fff;text-decoration:none}
.header-nav{display:flex;align-items:center;gap:4px;flex-wrap:wrap}
.header-nav a{color:#dce8f5;text-decoration:none;padding:8px 12px;border-radius:4px;font-size:0.92rem;white-space:nowrap}
.header-nav a:hover{background:rgba(255,255,255,0.12)}
.header-nav a.active{background:var(--aep-accent);color:#fff}
.header-nav .logout{color:#f8d7da}
.header-user{color:#cfe0f0;font-size:0.85rem;padding:8px 10px}
.hamburger{display:none;background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer}
.container{max-width:1200px;margin:24px auto;padding:0 20px}
.card{background:#fff;border:1px solid var(--aep-border);border-radius:8px;padding:20px}
.card.stat{text-align:center}
.grid{display:grid;gap:16px}
.grid-2{grid-template-columns:repeat(2,1fr)}
.grid-3{grid-template-columns:repeat(3,1fr)}
.actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn{display:inline-block;padding:9px 16px;background:var(--aep-primary);color:#fff;border:none;border-radius:4px;text-decoration:none;font-size:0.9rem;cursor:pointer}
.btn:hover{background:var(--aep-primary-dark)}
.btn.secondary{background:#fff;color:var(--aep-primary);border:1px solid var(--aep-primary)}
.btn.danger{background:#b42318}
.muted{color:var(--aep-muted)}
.empty{padding:24px;text-align:center;color:var(--aep-muted)}
.badge{display:inline-block;padding:2px 10px;border-radius:12px;background:#eef2f7;color:var(--aep-primary);font-size:0.78rem;text-transform:capitalize}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid var(--aep-border);text-align:left;font-size:0.92rem}
th{color:var(--aep-muted);font-weight:600}
label{display:block;font-size:0.82rem;font-weight:bold;color:#555;margin-bottom:6px}
input,select,textarea{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:4px;font-size:0.9rem;font-family:inherit}
textarea{min-height:90px}
h1{margin:0 0 6px}
@media (max-width:760px){
  .hamburger{display:block}
  .header-nav{display:none;width:100%;flex-direction:column;align-items:stretch}
  .header-nav.open{display:flex}
  .grid-2,.grid-3{grid-template-columns:1fr}
}
</style>
</head>
<body>
<header class="header">
  <div class="header-inner">
    <a class="brand" href="dashboard.php">&#9878;&#65039; AEP Legal Intelligence</a>
    <button class="hamburger" type="button" onclick="document.getElementById('aepNav').classList.toggle('open')" aria-label="Toggle navigation">&#9776;</button>
    <nav class="header-nav" id="aepNav">
      <?php foreach ($aepNavLinks as $key => $link): ?>
        <a href="<?= aep_h($link['href']) ?>" class="<?= $activeNav === $key ? 'active' : '' ?>"><?= aep_h($link['label']) ?></a>
      <?php endforeach; ?>
      <span class="header-user"><?= aep_h($aepUsername) ?></span>
      <a class="logout" href="logout.php">Logout</a>
    </nav>
  </div>
</header>
<main class="container">
