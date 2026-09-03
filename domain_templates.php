<?php
require_once __DIR__ . '/legal_library.php';

session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$profiles = aep_domain_profiles();
$jurisdictionOptions = aep_jurisdiction_options();
$jurisdiction = $_GET['jurisdiction'] ?? 'england_wales';
if (!isset($jurisdictionOptions[$jurisdiction])) {
    $jurisdiction = 'england_wales';
}
$trackOptions = aep_track_options();
$track = $_GET['track'] ?? 'general';
if (!isset($trackOptions[$track])) {
    $track = 'general';
}
$phraseCategories = aep_phrase_bank_categories();
$phraseCategory = aep_normalize_phrase_category((string)($_GET['phrase_category'] ?? ''), 'contract');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Domain Subdomains and Templates</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#1a3c5e;color:#fff;padding:24px 20px;text-align:center}
    nav{background:#084d85;padding:10px 20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;background:rgba(255,255,255,.15)}
    .container{max-width:1200px;margin:0 auto;padding:28px 18px 40px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px}
    .card{background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.07)}
    .card h3{color:#1a3c5e;margin-bottom:10px}
    .section-label{font-size:.8rem;font-weight:bold;color:#445;letter-spacing:.4px;text-transform:uppercase;margin:10px 0 6px}
    ul{margin-left:18px;line-height:1.5}
    li{margin-bottom:5px}
    .template{border:1px solid #e4e9f1;border-radius:8px;padding:10px;background:#f8fbff;margin-bottom:10px}
    .subdomain-link{display:block;color:#1a3c5e;margin-bottom:5px}
    .template strong{display:block;color:#1a3c5e;margin-bottom:5px}
    .btn{display:inline-block;padding:7px 12px;background:#1a3c5e;color:#fff;text-decoration:none;border-radius:4px;font-size:.85rem}
    .btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
    .action-row{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
    .filters{background:#fff;border-radius:10px;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:18px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end}
    .filters label{font-size:.8rem;font-weight:bold;color:#445;display:block;margin-bottom:5px}
    .filters select{width:100%;padding:8px 10px;border:1px solid #ccd;border-radius:4px}
  </style>
</head>
<body>
<header>
  <h1>Domain Subdomains and Templates</h1>
  <p>Reusable drafting and analysis starters across all legal domains</p>
</header>
<nav>
  <a href="index.php">Home</a>
  <a href="dashboard.php">Dashboard</a>
  <a href="library.php">Library</a>
  <a href="counsel_engine.php">Counsel Engine</a>
  <a href="phrase_bank.php">Phrase Bank</a>
</nav>
<div class="container">
  <form class="filters" method="GET">
    <div>
      <label>Jurisdiction</label>
      <select name="jurisdiction">
        <?php foreach ($jurisdictionOptions as $key => $label): ?>
          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $jurisdiction === $key ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Procedure Track</label>
      <select name="track">
        <?php foreach ($trackOptions as $key => $label): ?>
          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $track === $key ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Phrase Pack</label>
      <select name="phrase_category">
        <?php foreach ($phraseCategories as $key => $label): ?>
          <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $phraseCategory === $key ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <button class="btn" type="submit">Apply</button>
    </div>
  </form>
  <div class="grid">
    <?php foreach ($profiles as $key => $profile): ?>
      <div class="card">
        <h3><?php echo htmlspecialchars($profile['label']); ?></h3>
        <div class="section-label">Subdomains</div>
        <ul>
          <?php foreach (aep_domain_subdomain_links($key, ['jurisdiction' => $jurisdiction, 'track' => $track, 'phrase_category' => $phraseCategory]) as $subdomain): ?>
            <li><a class="subdomain-link" href="<?php echo htmlspecialchars($subdomain['workbench_href']); ?>"><?php echo htmlspecialchars($subdomain['label']); ?> — Open focused workbench</a></li>
          <?php endforeach; ?>
        </ul>

        <div class="section-label">Templates</div>
        <?php foreach (($profile['templates'] ?? []) as $templateKey => $template): ?>
          <div class="template">
            <strong><?php echo htmlspecialchars($template['label'] ?? $templateKey); ?></strong>
            <ul>
              <?php foreach (($template['sections'] ?? []) as $section): ?>
                <li><?php echo htmlspecialchars($section); ?></li>
              <?php endforeach; ?>
            </ul>
            <div class="action-row">
              <a class="btn" href="counsel_engine.php?domain=<?php echo urlencode($key); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>">Use Template</a>
              <a class="btn btn-outline" href="template_download.php?domain=<?php echo urlencode($key); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>&format=txt">Download TXT</a>
              <a class="btn btn-outline" href="template_download.php?domain=<?php echo urlencode($key); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>&format=pdf">Download PDF</a>
              <a class="btn btn-outline" href="template_download.php?domain=<?php echo urlencode($key); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>&format=html">Print View</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
