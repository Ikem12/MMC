<?php
// FILE: analysis_engine.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$today   = date('d F Y');
$domains = [
    'human_rights' => 'Human Rights Cases',
    'tort'         => 'Tort Law Cases',
    'admin_law'    => 'Admin Law Cases',
    'oil_gas'      => 'Oil & Gas Cases',
];

$tables = [
    'human_rights' => 'human_rights_cases',
    'tort'         => 'tort_cases',
    'admin_law'    => 'admin_law_cases',
    'oil_gas'      => 'oil_gas_cases',
];

$domain  = $_GET['domain'] ?? '';
$case_id = (int)($_GET['id'] ?? 0);
$case    = null;

if ($domain && $case_id && isset($tables[$domain])) {
    $table = $tables[$domain];
    $stmt  = $pdo->prepare("SELECT * FROM {$table} WHERE id = :id");
    $stmt->execute([':id' => $case_id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Analysis logic ──────────────────────────────────────────────
$strengths       = [];
$weaknesses      = [];
$risks           = [];
$recommendations = [];
$score           = 0;

if ($case) {
    // Common checks
    if (!empty($case['title']))           { $strengths[] = 'Case has a clear title and reference.'; $score += 10; }
    if (!empty($case['claimant']))        { $strengths[] = 'Claimant / applicant is identified.'; $score += 10; }
    if (!empty($case['respondent']))      { $strengths[] = 'Respondent is identified.'; $score += 10; }
    if (!empty($case['summary']))         { $strengths[] = 'Case summary has been provided.'; $score += 10; }
    if (!empty($case['status']))          { $strengths[] = 'Case status is set to: ' . ucfirst($case['status']); $score += 5; }

    if (empty($case['claimant']))         { $weaknesses[] = 'Claimant name is missing.'; }
    if (empty($case['respondent']))       { $weaknesses[] = 'Respondent name is missing.'; }
    if (empty($case['summary']))          { $weaknesses[] = 'No case summary provided — this weakens the claim.'; }

    // Domain-specific checks
    if ($domain === 'human_rights') {
        if (!empty($case['right_violated']))   { $strengths[] = 'Specific right violated has been identified: ' . $case['right_violated']; $score += 15; }
        if (!empty($case['article_section']))  { $strengths[] = 'Legal provision cited: ' . $case['article_section']; $score += 10; }
        if (!empty($case['remedy']))           { $strengths[] = 'Remedy sought is specified: ' . $case['remedy']; $score += 10; }
        if (!empty($case['grounds']))          { $strengths[] = 'Grounds of claim are documented.'; $score += 10; }
        if (!empty($case['violation_date']))   { $strengths[] = 'Date of violation recorded.'; $score += 5; }

        if (empty($case['right_violated']))    { $weaknesses[] = 'No specific right violated identified — critical gap.'; }
        if (empty($case['article_section']))   { $weaknesses[] = 'No legal provision or article cited.'; }
        if (empty($case['remedy']))            { $weaknesses[] = 'No remedy sought specified.'; }
        if (empty($case['grounds']))           { $weaknesses[] = 'Grounds of claim not documented.'; }
        if (empty($case['violation_date']))    { $weaknesses[] = 'Date of violation not recorded — may affect limitation period.'; }

        $risks[] = 'Ensure the claim is within the 1-year limitation period under HRA 1998.';
        $risks[] = 'Verify respondent is a public authority — private entities may not be directly liable.';
        $risks[] = 'Proportionality of any state interference must be assessed carefully.';

        $recommendations[] = 'Send a formal pre-action letter to the respondent before filing.';
        $recommendations[] = 'Gather all documentary evidence of the violation immediately.';
        $recommendations[] = 'Consider whether domestic remedies have been exhausted before escalating.';
        $recommendations[] = 'Consult the relevant constitutional provisions alongside international instruments.';
    }

    if ($domain === 'tort') {
        if (!empty($case['tort_type']))        { $strengths[] = 'Tort type identified: ' . $case['tort_type']; $score += 15; }
        if (!empty($case['duty_of_care']))     { $strengths[] = 'Duty of care addressed.'; $score += 10; }
        if (!empty($case['damages']))          { $strengths[] = 'Damages quantified.'; $score += 10; }

        if (empty($case['tort_type']))         { $weaknesses[] = 'Tort type not specified.'; }
        if (empty($case['duty_of_care']))      { $weaknesses[] = 'Duty of care not established.'; }
        if (empty($case['damages']))           { $weaknesses[] = 'Damages not quantified.'; }

        $risks[] = 'Causation must be established on a balance of probabilities.';
        $risks[] = 'Consider contributory negligence which may reduce damages.';
        $risks[] = 'Remoteness of damage may be raised as a defence.';

        $recommendations[] = 'Obtain expert medical or technical reports to support damages claim.';
        $recommendations[] = 'Document all losses with receipts, records and witness statements.';
        $recommendations[] = 'Assess whether a pre-action protocol letter is required.';
    }

    if ($domain === 'admin_law') {
        if (!empty($case['decision_maker']))   { $strengths[] = 'Decision maker identified.'; $score += 15; }
        if (!empty($case['ground_of_review'])) { $strengths[] = 'Ground of judicial review identified.'; $score += 15; }
        if (!empty($case['relief_sought']))    { $strengths[] = 'Relief sought is specified.'; $score += 10; }

        if (empty($case['decision_maker']))    { $weaknesses[] = 'Decision maker not identified.'; }
        if (empty($case['ground_of_review']))  { $weaknesses[] = 'Ground of judicial review not specified.'; }
        if (empty($case['relief_sought']))     { $weaknesses[] = 'Relief sought not specified.'; }

        $risks[] = 'Judicial review must be filed promptly — typically within 3 months.';
        $risks[] = 'Permission stage may be refused if grounds are not arguable.';
        $risks[] = 'Alternative remedies must have been exhausted first.';

        $recommendations[] = 'File a pre-action protocol letter before commencing judicial review.';
        $recommendations[] = 'Obtain the full decision-making record from the public body.';
        $recommendations[] = 'Consider whether an urgent application for interim relief is needed.';
    }

    if ($domain === 'oil_gas') {
        if (!empty($case['licence_number']))   { $strengths[] = 'Licence number recorded.'; $score += 15; }
        if (!empty($case['field_location']))   { $strengths[] = 'Field location documented.'; $score += 10; }
        if (!empty($case['contract_type']))    { $strengths[] = 'Contract type identified.'; $score += 10; }

        if (empty($case['licence_number']))    { $weaknesses[] = 'Licence number not recorded.'; }
        if (empty($case['field_location']))    { $weaknesses[] = 'Field location not documented.'; }
        if (empty($case['contract_type']))     { $weaknesses[] = 'Contract type not identified.'; }

        $risks[] = 'Regulatory compliance with DPR/NUPRC requirements must be verified.';
        $risks[] = 'Environmental liability under NOSDRA and NESREA must be assessed.';
        $risks[] = 'Force majeure and stabilisation clauses should be reviewed carefully.';

        $recommendations[] = 'Ensure all licences and permits are current and valid.';
        $recommendations[] = 'Commission an independent environmental impact assessment.';
        $recommendations[] = 'Review all JOA and PSC provisions for dispute resolution clauses.';
    }

    $score = min($score, 100);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Analysis Engine — AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    header{background:#1a3c5e;color:#fff;padding:24px 20px;text-align:center}
    header h1{font-size:1.6rem;margin-bottom:4px}
    header p{font-size:0.9rem;opacity:0.85}
    .file-tag{font-size:11px;background:#fff;color:#1a3c5e;padding:3px 8px;border-radius:4px;margin-left:10px;vertical-align:middle}
    nav{background:#122840;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    nav a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:4px;font-size:0.9rem;background:rgba(255,255,255,0.15)}
    nav a:hover{background:rgba(255,255,255,0.3)}
    .container{max-width:1000px;margin:30px auto;padding:0 18px}
    .selector-card{background:#fff;border-radius:8px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:24px}
    .selector-card h2{font-size:1.1rem;color:#1a3c5e;margin-bottom:16px;border-bottom:2px solid #1a3c5e;padding-bottom:8px}
    .form-row{display:grid;grid-template-columns:1fr 1fr auto;gap:14px;align-items:end}
    .form-group label{display:block;font-size:0.82rem;font-weight:bold;margin-bottom:5px;color:#555;text-transform:uppercase;letter-spacing:0.5px}
    select,input{width:100%;padding:9px 12px;border:1px solid #ccd;border-radius:4px;font-size:0.9rem;font-family:inherit}
    select:focus,input:focus{outline:none;border-color:#1a3c5e}
    .btn{display:inline-block;padding:9px 20px;background:#1a3c5e;color:#fff;border:none;border-radius:4px;font-size:0.9rem;cursor:pointer;text-decoration:none;white-space:nowrap}
    .btn:hover{background:#122840}
    .btn-print{background:#28a745}
    .btn-print:hover{background:#1e7e34}
    .btn-secondary{background:#6c757d}
    .btn-secondary:hover{background:#495057}
    .action-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}

    /* Score */
    .score-section{background:#fff;border-radius:8px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:20px;text-align:center}
    .score-circle{display:inline-block;width:110px;height:110px;border-radius:50%;line-height:110px;font-size:2rem;font-weight:bold;color:#fff;margin-bottom:12px}
    .score-high{background:#28a745}
    .score-mid{background:#ffc107;color:#333}
    .score-low{background:#dc3545}
    .score-label{font-size:1rem;font-weight:bold;color:#333;margin-bottom:4px}
    .score-sub{font-size:0.85rem;color:#888}

    /* Analysis grid */
    .analysis-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
    .analysis-card{background:#fff;border-radius:8px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.07)}
    .analysis-card h3{font-size:0.95rem;font-weight:bold;margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid #eee}
    .analysis-card.strengths h3{color:#28a745;border-color:#d4edda}
    .analysis-card.weaknesses h3{color:#dc3545;border-color:#f8d7da}
    .analysis-card.risks h3{color:#ffc107;border-color:#fff3cd}
    .analysis-card.recommendations h3{color:#1a3c5e;border-color:#d0e4f7}
    .analysis-card ul{list-style:none;padding:0}
    .analysis-card ul li{font-size:0.88rem;line-height:1.7;padding:6px 0;border-bottom:1px solid #f5f5f5;color:#333}
    .analysis-card ul li:last-child{border-bottom:none}
    .analysis-card ul li::before{margin-right:8px}
    .strengths ul li::before{content:'✅'}
    .weaknesses ul li::before{content:'❌'}
    .risks ul li::before{content:'⚠️'}
    .recommendations ul li::before{content:'💡'}
    .empty-item{font-size:0.85rem;color:#aaa;font-style:italic}

    /* Case summary */
    .case-banner{background:#eef4fb;border-radius:6px;padding:14px 18px;margin-bottom:20px;border:1px solid #c8dff0}
    .case-banner h3{font-size:0.95rem;color:#1a3c5e;margin-bottom:8px}
    .case-banner p{font-size:0.87rem;color:#555;line-height:1.7}

    .no-analysis{text-align:center;padding:50px;color:#888;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.07)}

    @media print{
      header,nav,.selector-card,.action-bar{display:none!important}
      body{background:#fff}
      .container{max-width:100%;margin:0;padding:0}
      .analysis-grid{grid-template-columns:1fr 1fr}
    }
    @media(max-width:640px){
      .analysis-grid{grid-template-columns:1fr}
      .form-row{grid-template-columns:1fr}
    }
  </style>
</head>
<body>

<header>
  <h1>🔍 Analysis Engine <span class="file-tag">FILE: analysis_engine.php</span></h1>
  <p>Automated Case Strength Analysis — All Domains</p>
</header>

<nav>
  <a href="index.php">🏠 Home</a>
  <a href="human-rights.php">Human Rights</a>
  <a href="admin_law.php">Admin Law</a>
  <a href="oil_gas.php">Oil &amp; Gas</a>
  <a href="tort.php">Tort Law</a>
  <a href="draft_engine.php">Draft Engine</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">

  <!-- Selector -->
  <div class="selector-card">
    <h2>🔍 Analyse a Case</h2>
    <form method="GET">
      <div class="form-row">
        <div class="form-group">
          <label>Domain</label>
          <select name="domain">
            <option value="">— Select Domain —</option>
            <?php foreach ($domains as $key => $label): ?>
              <option value="<?php echo $key; ?>" <?php echo $domain===$key?'selected':''; ?>>
                <?php echo $label; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Case ID</label>
          <input type="number" name="id" value="<?php echo $case_id ?: ''; ?>"
                 placeholder="e.g. 1" min="1"/>
        </div>
        <div class="form-group">
          <button type="submit" class="btn">Analyse →</button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($case): ?>

  <div class="action-bar">
    <button onclick="window.print()" class="btn btn-print">🖨️ Print Report</button>
    <a href="draft_engine.php?domain=<?php echo $domain; ?>&id=<?php echo $case_id; ?>" class="btn">✍️ Draft Document</a>
    <a href="analysis_engine.php" class="btn btn-secondary">← New Analysis</a>
  </div>

  <!-- Case Banner -->
  <div class="case-banner">
    <h3>📁 <?php echo htmlspecialchars($case['title']); ?></h3>
    <p>
      <strong>Domain:</strong> <?php echo $domains[$domain]; ?> &nbsp;|&nbsp;
      <strong>Status:</strong> <?php echo ucfirst($case['status'] ?? '—'); ?> &nbsp;|&nbsp;
      <strong>Claimant:</strong> <?php echo htmlspecialchars($case['claimant'] ?? '—'); ?> &nbsp;|&nbsp;
      <strong>Respondent:</strong> <?php echo htmlspecialchars($case['respondent'] ?? '—'); ?>
    </p>
  </div>

  <!-- Score -->
  <?php
    $cls = $score >= 70 ? 'score-high' : ($score >= 40 ? 'score-mid' : 'score-low');
    $verdict = $score >= 70
      ? 'Strong Case — well documented and ready to proceed.'
      : ($score >= 40
        ? 'Moderate Case — several gaps need to be addressed.'
        : 'Weak Case — significant information is missing.');
  ?>
  <div class="score-section">
    <div class="score-circle <?php echo $cls; ?>"><?php echo $score; ?>%</div>
    <div class="score-label"><?php echo $verdict; ?></div>
    <div class="score-sub">Analysis generated on <?php echo $today; ?></div>
  </div>

  <!-- Analysis Grid -->
  <div class="analysis-grid">

    <div class="analysis-card strengths">
      <h3>✅ Strengths (<?php echo count($strengths); ?>)</h3>
      <ul>
        <?php if (empty($strengths)): ?>
          <li class="empty-item">No strengths identified yet.</li>
        <?php else: ?>
          <?php foreach ($strengths as $s): ?>
            <li><?php echo htmlspecialchars($s); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="analysis-card weaknesses">
      <h3>❌ Weaknesses (<?php echo count($weaknesses); ?>)</h3>
      <ul>
        <?php if (empty($weaknesses)): ?>
          <li class="empty-item">No weaknesses identified — great!</li>
        <?php else: ?>
          <?php foreach ($weaknesses as $w): ?>
            <li><?php echo htmlspecialchars($w); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="analysis-card risks">
      <h3>⚠️ Risks (<?php echo count($risks); ?>)</h3>
      <ul>
        <?php if (empty($risks)): ?>
          <li class="empty-item">No specific risks flagged.</li>
        <?php else: ?>
          <?php foreach ($risks as $r): ?>
            <li><?php echo htmlspecialchars($r); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="analysis-card recommendations">
      <h3>💡 Recommendations (<?php echo count($recommendations); ?>)</h3>
      <ul>
        <?php if (empty($recommendations)): ?>
          <li class="empty-item">No recommendations at this stage.</li>
        <?php else: ?>
          <?php foreach ($recommendations as $rec): ?>
            <li><?php echo htmlspecialchars($rec); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>

  </div>

  <?php else: ?>
    <div class="no-analysis">
      <p style="font-size:2rem;margin-bottom:16px">🔍</p>
      <p style="font-size:1.1rem;margin-bottom:10px;font-weight:bold">Select a domain and case ID above to begin analysis</p>
      <p style="font-size:0.9rem;color:#aaa">The engine will automatically assess case strength, identify gaps and provide recommendations.</p>
    </div>
  <?php endif; ?>

</div>
</body>
</html>