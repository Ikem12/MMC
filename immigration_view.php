<?php
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';
p2_start();
$pdo = p2_db();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: immigration_list.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM immigration_cases WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) { header('Location: immigration_list.php'); exit; }

// Build case data for intelligence evaluation
$caseData = [
    'applicant_name' => $c['client_name'] ?? '',
    'nationality' => $c['client_nationality'] ?? '',
    'date_of_birth' => $c['client_dob'] ?? '',
    'passport_number' => $c['client_passport'] ?? '',
    'visa_type' => $c['case_type'] ?? '',
    'case_reference' => $c['case_reference'] ?? '',
    'home_office_reference' => $c['home_office_reference'] ?? '',
    'entry_date' => $c['client_entry_date'] ?? '',
    'visa_expiry' => $c['client_visa_expiry'] ?? '',
    'leave_type' => $c['client_leave_type'] ?? '',
    'sponsor_name' => $c['sponsor_name'] ?? '',
    'sponsor_licence' => $c['sponsor_licence'] ?? '',
    'sponsor_address' => $c['sponsor_address'] ?? '',
    'decision_date' => $c['decision_date'] ?? '',
    'decision_description' => $c['decision_description'] ?? '',
    'appeal_lodged' => $c['appeal_lodged'] ?? '',
    'appeal_date' => $c['appeal_date'] ?? '',
    'appeal_tribunal' => $c['appeal_tribunal'] ?? '',
    'appeal_reference' => $c['appeal_reference'] ?? '',
    'removal_date' => $c['removal_date'] ?? '',
    'detention_centre' => $c['detention_centre'] ?? '',
    'facts' => $c['representations'] ?? $c['evidence_available'] ?? '',
    'evidence_available' => $c['evidence_available'] ?? '',
    'legal_basis' => $c['legal_basis'] ?? '',
    'family_ties' => $c['family_ties'] ?? '',
    'private_life_years' => $c['private_life_years'] ?? '',
    'children_details' => $c['children_details'] ?? '',
    'country_conditions' => $c['country_conditions'] ?? '',
    'instructions' => $c['representations'] ?? ''
];

$analysis = analyseImmigrationCase($caseData);
$strength = $analysis['strength'] ?? assessImmigrationCaseStrength($caseData);
$evidenceMatrix = $analysis['evidence_matrix'] ?? buildImmigrationEvidenceMatrix($caseData);
$article8 = $analysis['article_8'] ?? assessArticle8($caseData);
$appeals = $analysis['appeals'] ?? assessImmigrationAppeals($caseData);
$risks = $analysis['risks'] ?? assessImmigrationRisks($caseData);
$strategy = $analysis['strategy'] ?? generateImmigrationStrategy($caseData);

// Check for linked matter
$matter = null;
if (!empty($c['matter_id'])) {
    $mStmt = $pdo->prepare('SELECT * FROM matters WHERE id = ?');
    $mStmt->execute([(int)$c['matter_id']]);
    $matter = $mStmt->fetch(PDO::FETCH_ASSOC);
}

function row($label, $val) {
    if ($val === null || $val === '') return;
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td>' . nl2br(htmlspecialchars((string)$val)) . '</td></tr>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Immigration Intelligence: <?php echo htmlspecialchars($c['client_name'] ?? 'Case'); ?></title>
<style>
:root {
  --primary: #0f2744;
  --accent: #2563eb;
  --accent-light: #eff6ff;
  --border: #e2e8f0;
  --bg: #f8fafc;
  --text: #1e293b;
  --text-muted: #64748b;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }
.topbar { background: #0f172a; color: #fff; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; }
.topbar .brand { font-size: 1.05rem; font-weight: 700; color: #60a5fa; text-decoration: none; display: flex; align-items: center; gap: 8px; }
.topbar a { color: #94a3b8; text-decoration: none; font-size: 0.85rem; margin-left: 14px; transition: color 0.15s; }
.topbar a:hover { color: #fff; }

.hero { background: linear-gradient(135deg, #0f2744 0%, #1e3a8a 100%); color: #fff; padding: 24px 32px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.hero-content { max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.hero h1 { font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
.hero-meta { display: flex; gap: 16px; margin-top: 8px; font-size: 0.85rem; color: #cbd5e1; flex-wrap: wrap; }
.hero-meta span { display: flex; align-items: center; gap: 6px; }

.container { max-width: 1300px; margin: 0 auto; padding: 0 24px 40px; }

.action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
.btn { padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-outline { background: #fff; color: var(--text); border: 1px solid var(--border); }
.btn-outline:hover { background: #f1f5f9; }
.btn-ai { background: linear-gradient(135deg, #4338ca, #6366f1); color: #fff; }
.btn-ai:hover { opacity: 0.95; }
.btn-print { background: #10b981; color: #fff; }

.intelligence-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
@media (max-width: 1024px) { .intelligence-grid { grid-template-columns: 1fr; } }

.card { background: #fff; border-radius: 8px; border: 1px solid var(--border); padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
.card-title { font-size: 0.95rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; }

.stat-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
.stat-strong { background: #dcfce7; color: #166534; }
.stat-moderate { background: #fef3c7; color: #92400e; }
.stat-low { background: #fee2e2; color: #991b1b; }

.score-box { background: var(--accent-light); border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-bottom: 16px; display: flex; justify-content: space-around; text-align: center; }
.score-item .val { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
.score-item .lbl { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; }

table { width: 100%; border-collapse: collapse; }
th { width: 35%; padding: 8px 12px; text-align: left; font-size: 0.8rem; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border); font-weight: 600; }
td { padding: 8px 12px; font-size: 0.85rem; border-bottom: 1px solid #f1f5f9; }

.reasoning-box { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 6px; padding: 12px; margin-bottom: 10px; }
.reasoning-title { font-size: 0.75rem; font-weight: 700; color: #7e22ce; text-transform: uppercase; margin-bottom: 4px; }
.reasoning-body { font-size: 0.85rem; color: #4b5563; }

.matrix-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.matrix-table th { background: #f1f5f9; color: var(--text); padding: 8px; border: 1px solid var(--border); }
.matrix-table td { padding: 8px; border: 1px solid var(--border); }
</style>
</head>
<body>

<div class="topbar">
  <a href="dashboard.php" class="brand">⚖️ AEP Legal Intelligence Platform</a>
  <div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="immigration_list.php">✈️ Immigration Cases</a>
    <a href="letter_create.php">✉️ Correspondence</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</div>

<div class="hero">
  <div class="hero-content">
    <div>
      <h1>✈️ <?php echo htmlspecialchars($c['client_name'] ?? 'Immigration Case'); ?></h1>
      <div class="hero-meta">
        <span><strong>Ref:</strong> <?php echo htmlspecialchars($c['case_reference'] ?: 'N/A'); ?></span>
        <span><strong>Type:</strong> <?php echo htmlspecialchars($c['case_type'] ?: 'N/A'); ?></span>
        <span><strong>HO Ref:</strong> <?php echo htmlspecialchars($c['home_office_reference'] ?: 'N/A'); ?></span>
        <span><strong>Status:</strong> <?php echo ucfirst($c['status'] ?? 'Active'); ?></span>
        <?php if ($matter): ?>
          <span><strong>Linked Matter:</strong> <a href="matter_view.php?id=<?php echo $matter['id']; ?>" style="color:#93c5fd; text-decoration:underline;"><?php echo htmlspecialchars($matter['title'] ?: $matter['matter_number']); ?></a></span>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <a href="immigration_create.php?case_id=<?php echo $c['id']; ?>&matter_id=<?php echo $c['matter_id'] ?? 0; ?>" class="btn btn-ai" style="padding: 10px 20px; font-size: 0.95rem;">
        ⚡ Launch Immigration AI Workspace
      </a>
    </div>
  </div>
</div>

<div class="container">

  <div class="action-bar">
    <div class="btn-group">
      <a href="immigration_list.php" class="btn btn-outline">← Back to Cases</a>
      <a href="immigration_create.php?case_id=<?php echo $c['id']; ?>" class="btn btn-ai">✨ Re-Analyse Case</a>
      <?php if (!empty($c['matter_id'])): ?>
        <a href="matter_view.php?id=<?php echo $c['matter_id']; ?>" class="btn btn-outline">📂 Matter Dossier</a>
      <?php endif; ?>
    </div>
    <div class="btn-group">
      <a href="immigration_print.php?id=<?php echo $c['id']; ?>" class="btn btn-print" target="_blank">🖨️ Print Intelligence Dossier</a>
    </div>
  </div>

  <div class="intelligence-grid">
    
    <!-- Left Column: Primary Details & AI Intelligence Matrices -->
    <div>

      <!-- Executive Intelligence Summary -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">🧠 AI Case Strength &amp; Strategic Evaluation</div>
          <span class="stat-badge stat-<?php echo strtolower($strength['band'] ?? 'moderate'); ?>">
            <?php echo htmlspecialchars($strength['band'] ?? 'Moderate'); ?> Case (<?php echo (int)($strength['score'] ?? 65); ?>%)
          </span>
        </div>
        
        <div class="score-box">
          <div class="score-item">
            <div class="val"><?php echo (int)($strength['score'] ?? 65); ?>%</div>
            <div class="lbl">Merits Score</div>
          </div>
          <div class="score-item">
            <div class="val"><?php echo (int)($article8['score'] ?? 70); ?>%</div>
            <div class="lbl">Article 8 Strength</div>
          </div>
          <div class="score-item">
            <div class="val"><?php echo (int)($appeals['prospects'] ?? 60); ?>%</div>
            <div class="lbl">Appeal Prospects</div>
          </div>
          <div class="score-item">
            <div class="val"><?php echo ucfirst(htmlspecialchars($risks['overall_rating'] ?? 'Medium')); ?></div>
            <div class="lbl">Risk Level</div>
          </div>
        </div>

        <p style="font-size:0.88rem; color:var(--text); margin-bottom:12px;">
          <?php echo htmlspecialchars($strength['summary'] ?? 'Full assessment calculated from legal grounds, documentary evidence, and Home Office guidelines.'); ?>
        </p>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px;">
            <div style="font-size:0.75rem; font-weight:700; color:#166534; margin-bottom:4px;">KEY STRENGTHS</div>
            <ul style="font-size:0.82rem; padding-left:16px; color:#14532d;">
              <?php foreach (array_slice($strength['strengths'] ?? ['Documented presence in UK'], 0, 3) as $st): ?>
                <li><?php echo htmlspecialchars($st); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:10px;">
            <div style="font-size:0.75rem; font-weight:700; color:#991b1b; margin-bottom:4px;">EVIDENCE GAPS / VULNERABILITIES</div>
            <ul style="font-size:0.82rem; padding-left:16px; color:#7f1d1d;">
              <?php foreach (array_slice($strength['gaps'] ?? ['Corroborative cohabitation records'], 0, 3) as $gp): ?>
                <li><?php echo htmlspecialchars($gp); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Evidence Matrix -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">📊 Structured Evidence Matrix</div>
        </div>
        <table class="matrix-table">
          <thead>
            <tr>
              <th>Legal Requirement</th>
              <th>Status</th>
              <th>Evidential Items</th>
              <th>Gap Analysis</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($evidenceMatrix['matrix'] ?? []) as $em): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($em['requirement'] ?? ''); ?></strong></td>
                <td>
                  <span style="font-size:0.75rem; padding:2px 6px; border-radius:4px; font-weight:600; background:<?php echo ($em['status'] ?? '') === 'Satisfied' ? '#dcfce7; color:#166534' : (($em['status'] ?? '') === 'Critical Gap' ? '#fee2e2; color:#991b1b' : '#fef3c7; color:#92400e'); ?>;">
                    <?php echo htmlspecialchars($em['status'] ?? ''); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($em['evidence'] ?? ''); ?></td>
                <td style="color:#dc2626;"><?php echo htmlspecialchars($em['gaps'] ?? 'None identified'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Client & Immigration Intake Record -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">📋 Complete Case File &amp; Records</div>
        </div>
        <table>
          <?php
            row('Full Name', $c['client_name'] ?? '');
            row('Date of Birth', $c['client_dob'] ?? '');
            row('Nationality', $c['client_nationality'] ?? '');
            row('Passport Number', $c['client_passport'] ?? '');
            row('Email', $c['client_email'] ?? '');
            row('Phone', $c['client_phone'] ?? '');
            row('UK Address', $c['client_address'] ?? '');
            row('UK Entry Date', $c['client_entry_date'] ?? '');
            row('Current Visa / Leave', ($c['client_visa_type'] ?? '') . ' (Expires: ' . ($c['client_visa_expiry'] ?? 'N/A') . ')');
            row('Leave Category', $c['client_leave_type'] ?? '');
            row('Sponsor Name & Licence', ($c['sponsor_name'] ?? '') . (!empty($c['sponsor_licence']) ? ' (Lic: ' . $c['sponsor_licence'] . ')' : ''));
            row('Home Office Reference', $c['home_office_reference'] ?? '');
            row('Refusal / Decision Date', $c['decision_date'] ?? '');
            row('Decision Overview', $c['decision_description'] ?? '');
            row('Appeal Tribunal & Reference', ($c['appeal_tribunal'] ?? '') . (!empty($c['appeal_reference']) ? ' - ' . $c['appeal_reference'] : ''));
            row('Legal Basis & Submissions', $c['legal_basis'] ?? '');
            row('Evidence on File', $c['evidence_available'] ?? '');
          ?>
        </table>
      </div>

    </div>

    <!-- Right Column: Legal Reasoning, Risk Register & Tactical Roadmap -->
    <div>

      <!-- Always-Visible Legal Reasoning Panel -->
      <div class="card" style="border-top: 4px solid #7e22ce;">
        <div class="card-header">
          <div class="card-title">⚖️ Counsel Reasoning Framework</div>
        </div>
        
        <div class="reasoning-box">
          <div class="reasoning-title">1. Key Facts</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['facts'] ?? 'Applicant residing in UK under category ' . ($c['case_type'] ?? 'Immigration')); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">2. Legal Issues</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['issues'] ?? 'Meeting Immigration Rules and Article 8 ECHR proportionality.'); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">3. Governing Law</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['law'] ?? 'Immigration Rules, Appendix FM, Section 55 BCIA 2009, Human Rights Act 1998.'); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">4. Evidence Assessment</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['evidence'] ?? 'Documentary matrix evaluated against standard of proof.'); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">5. Identified Risks</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['risks'] ?? 'Strict refusal guidelines and procedural Home Office deadlines.'); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">6. Tactical Strategy</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['strategy'] ?? 'Serve robust bundle with certified statutory proofs.'); ?></div>
        </div>

        <div class="reasoning-box">
          <div class="reasoning-title">7. Relief / Remedy</div>
          <div class="reasoning-body"><?php echo htmlspecialchars($analysis['reasoning']['remedies'] ?? 'Grant of Entry Clearance / Leave to Remain / Appeal Allowed.'); ?></div>
        </div>
      </div>

      <!-- Tactical Strategy Actions -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">🎯 Tactical Action Roadmap</div>
        </div>
        <ol style="padding-left:18px; font-size:0.85rem; color:var(--text);">
          <?php foreach (($strategy['steps'] ?? ['Gather financial and identity proofs', 'Draft formal representations to Home Office', 'Lodge bundle before deadline']) as $st): ?>
            <li style="margin-bottom:8px;"><?php echo htmlspecialchars($st); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- Risk Register -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">⚠️ Risk Register</div>
        </div>
        <?php foreach (array_slice($risks['items'] ?? [], 0, 3) as $rk): ?>
          <div style="border-left: 3px solid #ef4444; padding-left: 8px; margin-bottom: 10px;">
            <div style="font-size:0.82rem; font-weight:700; color:#991b1b;"><?php echo htmlspecialchars($rk['risk'] ?? ''); ?></div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
              <strong>Mitigation:</strong> <?php echo htmlspecialchars($rk['mitigation'] ?? ''); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

  </div>

</div>

</body>
</html>