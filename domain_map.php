<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/legal_library.php';

$pdo = aep_db();
$domainMap = aep_domain_map();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Domain Map and Essentials</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    .top{background:#1a3c5e;color:#fff;padding:14px 22px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    .top a{color:#fff;text-decoration:none}
    .container{max-width:1400px;margin:20px auto;padding:0 18px 40px}
    .panel{background:#fff;border-radius:10px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:14px}
    h1{font-size:1.35rem;color:#1a3c5e;margin-bottom:8px}
    h2{font-size:1rem;color:#1a3c5e;margin-bottom:8px}
    .muted{color:#666}
    .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
    .cols{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
    .chip{display:inline-block;padding:4px 9px;border-radius:999px;background:#eef3fb;color:#1a3c5e;font-size:.78rem;margin:0 6px 6px 0}
    a.chip{text-decoration:none}a.chip:hover{background:#d8e8fa}
    ul{margin:8px 0 0 18px}
    li{margin-bottom:5px}
    .ok{font-size:.78rem;font-weight:bold;color:#155724;background:#d4edda;padding:3px 8px;border-radius:999px}
    .warn{font-size:.78rem;font-weight:bold;color:#7a1d1d;background:#f8d7da;padding:3px 8px;border-radius:999px}
    @media (max-width: 1100px){.grid,.cols{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="top">
  <strong>AEP Domain Map</strong>
  <div><a href="dashboard.php">Dashboard</a> | <a href="domain_workbench.php">Domain Workbench</a> | <a href="counsel_engine.php">Counsel Engine</a></div>
</div>
<div class="container">
  <div class="panel">
    <h1>Complete Domain, Subdomain, and Essentials Map</h1>
    <p class="muted">This page maps each domain to its operational subdomains, core essentials, template bank, and engine stack coverage.</p>
  </div>

  <div class="grid">
    <?php foreach ($domainMap as $entry): ?>
      <?php $tableExists = $entry['table'] !== '' ? aep_table_exists($pdo, $entry['table']) : false; ?>
      <div class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
          <h2><?php echo htmlspecialchars($entry['label']); ?></h2>
          <span class="<?php echo $tableExists ? 'ok' : 'warn'; ?>">
            <?php echo $tableExists ? 'Table ready' : 'Table missing'; ?><?php echo $entry['table'] !== '' ? ': ' . htmlspecialchars($entry['table']) : ''; ?>
          </span>
        </div>

        <div style="margin:6px 0 10px 0">
          <?php foreach (($entry['subdomain_links'] ?? []) as $sub): ?>
            <a class="chip" href="<?php echo htmlspecialchars($sub['workbench_href']); ?>"><?php echo htmlspecialchars($sub['label']); ?></a>
          <?php endforeach; ?>
        </div>

        <div class="cols">
          <div>
            <strong>Essentials</strong>
            <ul>
              <?php foreach (array_slice($entry['essentials'] ?? [], 0, 10) as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong>Required fields</strong>
            <ul>
              <?php foreach ($entry['required_fields'] as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <strong>Template bank highlights</strong>
            <ul>
              <?php foreach (array_slice($entry['template_labels'] ?? [], 0, 8) as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <div style="margin-top:10px">
          <strong>Engine stack</strong>
          <div style="margin-top:6px">
            <?php foreach (($entry['engine_stack'] ?? []) as $engine): ?>
              <span class="chip"><?php echo htmlspecialchars($engine); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
