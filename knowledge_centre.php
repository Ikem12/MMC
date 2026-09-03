<?php
require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/functions.php';
$pageTitle='Knowledge Centre | AEP';$activeNav='knowledge';require __DIR__.'/includes/header.php';
?>
<section class="card"><h1>Knowledge Centre</h1><p class="muted">Central access to reusable legal knowledge, templates, drafting language, and domain coverage.</p></section>
<section class="grid grid-2" style="margin-top:18px">
  <div class="card"><h2>Legal Library</h2><p class="muted">Browse legal resources and reference materials.</p><a class="btn" href="library.php">Open library</a></div>
  <div class="card"><h2>Domain Templates</h2><p class="muted">Choose an analysis, drafting, procedural, or form template by practice area.</p><a class="btn" href="domain_templates.php">Browse templates</a></div>
  <div class="card"><h2>Phrase Bank</h2><p class="muted">Use reusable legal language by drafting purpose.</p><a class="btn" href="phrase_bank.php">Open phrase bank</a></div>
  <div class="card"><h2>Latin Maxims</h2><p class="muted">Search and apply legal maxims with supporting context.</p><a class="btn" href="latin_maxims.php">Open Latin maxims</a></div>
  <div class="card"><h2>Domain Map</h2><p class="muted">See practice areas, subdomains, essentials, templates, and engines.</p><a class="btn" href="domain_map.php">Open domain map</a></div>
  <div class="card"><h2>Counsel Engine</h2><p class="muted">Turn records and instructions into legal analysis and drafts.</p><a class="btn" href="counsel_engine.php">Open engine</a></div>
</section>
<?php require __DIR__.'/includes/footer.php'; ?>
