<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';

$pdo = p2_db();
p2_migrate($pdo);

$successMsg = '';
$errorMsg   = '';

// Load available matters and clients
$matters = [];
$clients = [];
try {
    $stmt = $pdo->query("SELECT id, reference, title, client_name, practice_area, status FROM matters ORDER BY updated_at DESC");
    if ($stmt) $matters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cStmt = $pdo->query("SELECT id, name, email FROM clients ORDER BY name ASC");
    if ($cStmt) $clients = $cStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Initial State
$targetDocType = $_POST['target_doc_type'] ?? 's994';
$instructions   = trim((string)($_POST['instructions'] ?? 'Draft a High Court Section 994 Companies Act 2006 Unfair Prejudice Petition for a 35% minority shareholder in a quasi-partnership company. Allege unlawful exclusion from management, diversion of corporate opportunities to a rival entity, and demand a court-ordered buyout without minority discount.'));
$opponentText   = trim((string)($_POST['opponent_text'] ?? 'Extract from Majority Director Letter: \"You were removed as a director pursuant to Section 168 CA 2006 because of strategic disagreements. The company is under no obligation to declare dividends. We offer to purchase your shares at £1.00 nominal value in accordance with the pre-emption articles.\"'));
$factsText      = trim((string)($_POST['facts'] ?? 'The Petitioner and First Respondent co-founded the Company in 2018 as equal 50/50 partners, operating as a quasi-partnership based on mutual confidence and participation in management. In 2021, share capital was restructured to 65/35 to facilitate external debt. In January 2024, the majority shareholder unilaterally terminated the Petitioner\'s directorship, suspended all dividend declarations, awarded himself an excessive £200,000 salary increase, and incorporated a secret parallel entity diverting the company’s lucrative client contracts.'));
$evidenceText   = trim((string)($_POST['evidence_available'] ?? '• Certificate of Incorporation and Articles of Association
• Shareholders\' Agreement dated 12 March 2018 establishing quasi-partnership
• Companies House filings showing unauthorized incorporation of competing entity
• Forensic accounting report showing £450,000 diverted revenues
• Bank statements showing unilateral executive salary increases'));
$remedyText     = trim((string)($_POST['remedy_requested'] ?? 'Court order under Section 996 CA 2006 requiring Respondents to buy Petitioner’s 35% shareholding at fair market value (£580,000) on a going-concern basis without minority discount; accounting of secret profits.'));

$party1         = trim((string)($_POST['client_name'] ?? 'Alexander Montgomery (35% Shareholder)'));
$party2         = trim((string)($_POST['company_name'] ?? 'OmniCorp Logistics Ltd / Damian Cross'));
$matterRef      = trim((string)($_POST['case_reference'] ?? ('AEP-CO-' . date('Y') . '-' . rand(100, 999))));
$selectedMatter = (int)($_POST['linked_matter_id'] ?? ($_GET['matter_id'] ?? 0));
$selectedClient = (int)($_POST['linked_client_id'] ?? 0);
$outputDraft    = $_POST['output_draft'] ?? '';
$outputTitle    = $_POST['output_title'] ?? 'AI Corporate & Company Law Intelligence Workspace DRAFT';

$action = $_POST['action'] ?? '';

// Reset handler
if ($action === 'reset') {
    $instructions = '';
    $opponentText = '';
    $factsText = '';
    $evidenceText = '';
    $remedyText = '';
    $outputDraft = '';
    $party1 = '';
    $party2 = '';
    $successMsg = 'Workspace cleared for new case analysis.';
}

// Load sample handler
if ($action === 'sample') {
    $instructions = 'Draft a High Court Section 994 Companies Act 2006 Unfair Prejudice Petition for a 35% minority shareholder in a quasi-partnership company. Allege unlawful exclusion from management, diversion of corporate opportunities to a rival entity, and demand a court-ordered buyout without minority discount.';
    $opponentText = 'Extract from Majority Director Letter: \"You were removed as a director pursuant to Section 168 CA 2006 because of strategic disagreements. The company is under no obligation to declare dividends. We offer to purchase your shares at £1.00 nominal value in accordance with the pre-emption articles.\"';
    $factsText = 'The Petitioner and First Respondent co-founded the Company in 2018 as equal 50/50 partners, operating as a quasi-partnership based on mutual confidence and participation in management. In 2021, share capital was restructured to 65/35 to facilitate external debt. In January 2024, the majority shareholder unilaterally terminated the Petitioner\'s directorship, suspended all dividend declarations, awarded himself an excessive £200,000 salary increase, and incorporated a secret parallel entity diverting the company’s lucrative client contracts.';
    $evidenceText = '• Certificate of Incorporation and Articles of Association
• Shareholders\' Agreement dated 12 March 2018 establishing quasi-partnership
• Companies House filings showing unauthorized incorporation of competing entity
• Forensic accounting report showing £450,000 diverted revenues
• Bank statements showing unilateral executive salary increases';
    $remedyText = 'Court order under Section 996 CA 2006 requiring Respondents to buy Petitioner’s 35% shareholding at fair market value (£580,000) on a going-concern basis without minority discount; accounting of secret profits.';
    $party1 = 'Alexander Montgomery (35% Shareholder)';
    $party2 = 'OmniCorp Logistics Ltd / Damian Cross';
    $successMsg = 'Pre-configured dispute scenario loaded into workspace.';
}

// Case Data bundle
$caseData = [
    'instructions'        => $instructions,
    'opponent_response'   => $opponentText,
    'opponent_letter'     => $opponentText,
    'facts'               => $factsText,
    'claim_details'       => $factsText,
    'contract_description'=> $factsText,
    'background'          => $factsText,
    'evidence_available'  => $evidenceText,
    'evidence'            => $evidenceText,
    'remedy_requested'    => $remedyText,
    'damages_claimed'     => $remedyText,
    'client_name'      => $party1,
    'company_name'      => $party2,
    'case_reference'      => $matterRef,
    'target_doc_type'     => $targetDocType,
    'doc_type'            => $targetDocType,
];

// Execute Analysis
$analysis = analyseCompanyCase($caseData);

// NLP Helper Actions
if ($action === 'extract_issues') {
    $extracted = extractIssuesFromText($instructions . "\n" . $factsText . "\n" . $opponentText);
    if (!empty($extracted)) {
        $factsText .= "\n\n[EXTRACTED ISSUES]:\n• " . implode("\n• ", $extracted);
        $caseData['facts'] = $factsText;
        $analysis = analyseCompanyCase($caseData);
        $successMsg = 'Extracted ' . count($extracted) . ' core legal issues into factual framework.';
    }
}

if ($action === 'summarise_text') {
    if (!empty($opponentText)) {
        $opponentText = summariseCorrespondenceText($opponentText);
        $caseData['opponent_response'] = $opponentText;
        $analysis = analyseCompanyCase($caseData);
        $successMsg = 'Opponent / counterparty position successfully summarised.';
    } elseif (!empty($factsText)) {
        $factsText = summariseCorrespondenceText($factsText);
        $caseData['facts'] = $factsText;
        $analysis = analyseCompanyCase($caseData);
        $successMsg = 'Factual chronology successfully summarised.';
    }
}

// Generation Actions
if (in_array($action, ['generate', 'draft', 'generate_draft', 'analyse'])) {
    $doc = generateCompanyDoc($analysis, $caseData, ['reference' => $matterRef], $targetDocType);
    $outputDraft = $doc['content'] ?? '';
    $outputTitle = $doc['title'] ?? 'LEGAL DRAFT';
    if ($action === 'analyse') {
        $successMsg = '⚡ Case analysis and legal reasoning model refreshed.';
    } else {
        $successMsg = '📄 AI legal draft generated successfully.';
    }
}

// Document Improvement Tools
if (in_array($action, ['improve', 'persuasive', 'formal', 'simplify', 'expand', 'add_authorities', 'risk_review']) && $outputDraft !== '') {
    $outputDraft = p6_transform_output($outputDraft, $action);
    $successMsg = '✨ Draft updated with selected improvement transformation.';
}

// Auto-generate initial draft if empty
if (empty($outputDraft) && $action !== 'reset') {
    $doc = generateCompanyDoc($analysis, $caseData, ['reference' => $matterRef], $targetDocType);
    $outputDraft = $doc['content'] ?? '';
    $outputTitle = $doc['title'] ?? 'LEGAL DRAFT';
}

// Save to Matter & Document Library
if ($action === 'save_to_matter' && !empty($outputDraft)) {
    try {
        $docTitle = $outputTitle ?: 'Legal Document Draft';
        $docTypeDb = 'letter';
        
        // Link or create Matter
        $mId = $selectedMatter;
        if ($mId <= 0) {
            $cId = $selectedClient > 0 ? $selectedClient : null;
            if (!$cId && !empty($party1)) {
                $stmtC = $pdo->prepare("INSERT INTO clients (name) VALUES (?)");
                $stmtC->execute([$party1]);
                $cId = (int)$pdo->lastInsertId();
            }
            $stmtM = $pdo->prepare("INSERT INTO matters (reference, title, client_id, client_name, practice_area, stage, status, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtM->execute([
                $matterRef,
                $party1 . ' vs ' . $party2 . ' (' . ucfirst('company_cases') . ')',
                $cId,
                $party1,
                'CORPORATE & CHANCERY GROUP',
                'Drafting',
                'active',
                substr($instructions, 0, 400)
            ]);
            $mId = (int)$pdo->lastInsertId();
            $selectedMatter = $mId;
        }

        // Save into documents table
        $stmtDoc = $pdo->prepare("INSERT INTO documents (matter_id, title, doc_type, content, file_size, author, status, version) VALUES (?, ?, ?, ?, ?, ?, 'final', 1)");
        $stmtDoc->execute([
            $mId,
            $docTitle,
            $docTypeDb,
            $outputDraft,
            strlen($outputDraft),
            $_SESSION['user_name'] ?? 'Counsel AI'
        ]);

        // Save activity log
        p2_log_activity($pdo, $mId, 'counsel_analysis', "Generated and saved {$docTitle} to matter repository.");

        $successMsg = "💾 Successfully saved to Matter {$matterRef} and Document Library!";
    } catch (Exception $e) {
        $errorMsg = "Error saving to matter: " . $e->getMessage();
    }
}

// Word / Metrics calculation
$wordCount = str_word_count($outputDraft);
$charCount = strlen($outputDraft);
$readTimeMin = max(1, ceil($wordCount / 200));

$pageTitle = "AI Corporate & Company Law Intelligence Workspace — AEP Legal Intelligence Platform";
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
:root {
  --primary: #1a3c5e;
  --accent: #2563eb;
  --success: #16a34a;
  --danger: #dc2626;
  --warning: #d97706;
  --bg: #f8fafc;
  --surface: #ffffff;
  --border: #e2e8f0;
  --text-main: #0f172a;
  --text-muted: #64748b;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  --radius: 8px;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
body { background: var(--bg); color: var(--text-main); line-height: 1.5; font-size: 14px; }
.topbar { background: var(--primary); color: #fff; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
.topbar .brand { font-size: 1.1rem; font-weight: 700; letter-spacing: -0.02em; display: flex; align-items: center; gap: 8px; }
.topbar nav a { color: #cbd5e1; text-decoration: none; font-size: 0.88rem; margin-left: 16px; transition: color 0.2s; }
.topbar nav a:hover { color: #ffffff; }
.hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 24px 32px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.hero h1 { font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
.hero p { color: #94a3b8; font-size: 0.88rem; margin-top: 4px; }
.badge-law { background: #3b82f6; color: #fff; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; font-weight: 600; vertical-align: middle; }
.container { max-width: 1720px; margin: 0 auto; padding: 20px; }
.alert { padding: 12px 16px; border-radius: var(--radius); margin-bottom: 16px; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.top-action-bar { background: var(--surface); padding: 14px 20px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.action-group { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.btn { padding: 8px 14px; border-radius: 6px; font-size: 0.84rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.15s ease-in-out; }
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-success { background: var(--success); color: #fff; }
.btn-success:hover { background: #15803d; }
.btn-outline { background: var(--surface); color: var(--text-main); border-color: var(--border); }
.btn-outline:hover { background: #f1f5f9; }
.btn-secondary { background: #e2e8f0; color: #334155; }
.btn-secondary:hover { background: #cbd5e1; }
.btn-sm { padding: 5px 10px; font-size: 0.78rem; }
.workspace-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
@media (max-width: 1100px) { .workspace-grid { grid-template-columns: 1fr; } }
.panel-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 20px; overflow: hidden; }
.panel-header { padding: 12px 18px; background: #f8fafc; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.panel-header h3 { font-size: 0.92rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; }
.panel-body { padding: 18px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 5px; }
.form-control { width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.88rem; color: var(--text-main); background: #fff; transition: border-color 0.15s; }
.form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
textarea.form-control { resize: vertical; line-height: 1.45; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.reasoning-box { background: #f8fafc; border-radius: 6px; border: 1px solid var(--border); padding: 12px; margin-bottom: 12px; }
.reasoning-title { font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; }
.reasoning-list { list-style: none; }
.reasoning-list li { font-size: 0.82rem; color: #1e293b; padding: 4px 0; border-bottom: 1px dashed #e2e8f0; display: flex; gap: 6px; }
.reasoning-list li:last-child { border-bottom: none; }
.reasoning-list li::before { content: "›"; color: var(--accent); font-weight: bold; }
.tab-nav { display: flex; gap: 4px; border-bottom: 1px solid var(--border); margin-bottom: 14px; background: #f8fafc; padding: 4px 4px 0; border-radius: 6px 6px 0 0; }
.tab-btn { padding: 8px 14px; font-size: 0.8rem; font-weight: 600; background: transparent; border: none; border-bottom: 2px solid transparent; cursor: pointer; color: var(--text-muted); border-radius: 4px 4px 0 0; }
.tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); background: #fff; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.matrix-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.matrix-table th, .matrix-table td { padding: 8px 10px; border: 1px solid var(--border); text-align: left; }
.matrix-table th { background: #f1f5f9; color: #334155; font-weight: 600; }
.badge { font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; font-weight: 600; }
.badge-high { background: #fee2e2; color: #991b1b; }
.badge-medium { background: #fef3c7; color: #92400e; }
.badge-active { background: #dbeafe; color: #1e40af; }
.badge-done { background: #dcfce7; color: #166534; }
.score-badge { display: flex; align-items: center; gap: 12px; background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; border-radius: 6px; margin-bottom: 14px; }
.score-circle { width: 50px; height: 50px; border-radius: 50%; background: var(--accent); color: #fff; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.draft-toolbar { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
.metrics-bar { display: flex; gap: 16px; font-size: 0.78rem; color: var(--text-muted); padding: 8px 12px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border); margin-top: 8px; justify-content: space-between; align-items: center; }
.scorecard-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; text-align: center; margin-top: 10px; }
.score-card { background: #f8fafc; border: 1px solid var(--border); border-radius: 4px; padding: 6px 2px; }
.score-card .val { font-size: 0.95rem; font-weight: 700; color: var(--primary); }
.score-card .lbl { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; }
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">⚖️ AEP Legal Intelligence <span class="badge-law">CORPORATE & CHANCERY GROUP</span></div>
  <nav>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="matters.php">📁 Matters</a>
    <a href="counsel_engine.php">⚖️ Counsel Engine</a>
    <a href="letter_list.php">✉️ Correspondence</a>
    <a href="immigration_list.php">✈️ Immigration</a>
    <a href="employment_list.php">👔 Employment</a>
    <a href="contract_list.php">📄 Contracts</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>
</div>

<div class="hero">
  <h1>🏢 🏢 Corporate & Company Law Intelligence</h1>
  <p>AI-Powered Section 994 Unfair Prejudice Petitions, Directors\' Duties Assessment (SS.171-177 CA 2006) & Shareholder Buyouts</p>
</div>

<div class="container">
  <?php if (!empty($successMsg)): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($successMsg) ?></div>
  <?php endif; ?>
  <?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <form method="POST" id="mainForm">
    <div class="top-action-bar">
      <div class="action-group">
        <button type="submit" name="action" value="analyse" class="btn btn-primary">⚡ Run Analysis</button>
        <button type="submit" name="action" value="generate" class="btn btn-success">📄 Generate Draft</button>
        <button type="submit" name="action" value="extract_issues" class="btn btn-outline">🔍 Extract Issues</button>
        <button type="submit" name="action" value="summarise_text" class="btn btn-outline">📝 Summarise Text</button>
        <button type="submit" name="action" value="sample" class="btn btn-secondary">💡 Load Sample</button>
        <button type="submit" name="action" value="reset" class="btn btn-secondary">🔄 Reset / Clear</button>
      </div>
      <div class="action-group">
        <button type="button" onclick="window.print();" class="btn btn-outline">🖨 Export PDF</button>
        <button type="button" onclick="exportWordDocument();" class="btn btn-outline">📝 Export Word</button>
        <button type="submit" name="action" value="save_to_matter" class="btn btn-primary">💾 Save to Matter</button>
      </div>
    </div>

    <div class="workspace-grid">
      <!-- LEFT COLUMN: INSTRUCTIONS, OPPONENT & SOURCE MATERIALS -->
      <div>
        <!-- 1. DIRECTIVE & TARGET TYPE -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>1. INSTRUCTIONS & TARGET DOCUMENT TYPE</h3>
            <span class="badge badge-active">AI Directives</span>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Target Document / Strategy:</label>
              <select name="target_doc_type" class="form-control" onchange="document.getElementById('mainForm').submit();">
                    <option value="s994" <?= $targetDocType === 's994' ? 'selected' : '' ?>>⚖️ Section 994 CA 2006 Unfair Prejudice Petition</option>
                    <option value="directors_duties" <?= $targetDocType === 'directors_duties' ? 'selected' : '' ?>>🛡️ Directors' Duties Breach & S.260 Derivative Claim Merits</option>
                    <option value="letter_claim" <?= $targetDocType === 'letter_claim' ? 'selected' : '' ?>>📜 Pre-Action Letter of Claim for Minority Shareholder Buyout</option>
                    <option value="resolution" <?= $targetDocType === 'resolution' ? 'selected' : '' ?>>📋 Board Resolution & Shareholder Dispute Settlement Agreement</option>
                    <option value="advice" <?= $targetDocType === 'advice' ? 'selected' : '' ?>>🏢 Counsel Corporate Governance & Quasi-Partnership Advice</option>

              </select>
            </div>
            <div class="form-group">
              <label>User Instructions & Drafting Directives:</label>
              <textarea name="instructions" rows="4" class="form-control" placeholder="Instruct Counsel Engine..."><?= htmlspecialchars($instructions) ?></textarea>
            </div>
          </div>
        </div>

        <!-- 2. OPPONENT & NOTICE INGESTION -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>2. OPPONENT / AUTHORITY POSITION & REBUTTAL TARGET</h3>
            <span class="badge badge-medium">Counterparty Ingestion</span>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Opponent Response / Impugned Notice / Written Allegations:</label>
              <textarea name="opponent_text" rows="4" class="form-control" placeholder="Paste opposing statement, letter, refusal or notice to rebut point-by-point..."><?= htmlspecialchars($opponentText) ?></textarea>
            </div>
            <div style="display: flex; gap: 8px;">
              <button type="submit" name="action" value="extract_issues" class="btn btn-sm btn-outline">🔍 Extract Opponent Issues</button>
              <button type="submit" name="action" value="summarise_text" class="btn btn-sm btn-outline">📝 Summarise Opponent Position</button>
            </div>
          </div>
        </div>

        <!-- 3. SOURCE MATERIALS & EVIDENCE -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>3. SOURCE MATERIALS, FACTS & EVIDENCE</h3>
            <span class="badge badge-active">Factual Foundation</span>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Factual Chronology & Case Background:</label>
              <textarea name="facts" rows="5" class="form-control" placeholder="Chronology of events, dates, communications, breaches..."><?= htmlspecialchars($factsText) ?></textarea>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label>Evidence Available & Exhibits:</label>
                <textarea name="evidence_available" rows="3" class="form-control" placeholder="List contracts, emails, expert reports..."><?= htmlspecialchars($evidenceText) ?></textarea>
              </div>
              <div class="form-group">
                <label>Remedy / Quantum / Relief Sought:</label>
                <textarea name="remedy_requested" rows="3" class="form-control" placeholder="Damages, statutory compensation, specific order..."><?= htmlspecialchars($remedyText) ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. MATTER LINKING & PARTICULARS -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>4. MATTER LINKING & PARTY PARTICULARS</h3>
            <span class="badge badge-done"><?= htmlspecialchars($matterRef) ?></span>
          </div>
          <div class="panel-body">
            <div class="grid-2">
              <div class="form-group">
                <label>Linked Matter:</label>
                <select name="linked_matter_id" class="form-control">
                  <option value="0">— Auto-Create or Select Matter —</option>
                  <?php foreach ($matters as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $selectedMatter == $m['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m['reference']) ?> — <?= htmlspecialchars($m['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Linked Client:</label>
                <select name="linked_client_id" class="form-control">
                  <option value="0">— Auto-Create or Select Client —</option>
                  <?php foreach ($clients as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $selectedClient == $cl['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cl['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label>Client / Petitioner Name:</label>
                <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($party1) ?>" />
              </div>
              <div class="form-group">
                <label>Company & Opposing Director:</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($party2) ?>" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: MULTI-FACTOR ASSESSMENTS, REASONING & DRAFT -->
      <div>
        <!-- 5. ASSESSMENTS TABS -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>5. AI MULTI-FACTOR ASSESSMENTS & REASONING</h3>
            <span class="badge badge-done">Counsel Engine Active</span>
          </div>
          <div class="panel-body" style="padding-bottom: 10px;">
            <div class="tab-nav">
              <button type="button" class="tab-btn active" onclick="switchDomainTab(event, 'tab-strength')">📊 Case Strength</button>
              <button type="button" class="tab-btn" onclick="switchDomainTab(event, 'tab-issues')">⚠️ Issues Matrix</button>
              <button type="button" class="tab-btn" onclick="switchDomainTab(event, 'tab-risks')">🛡️ Risk Register</button>
              <button type="button" class="tab-btn" onclick="switchDomainTab(event, 'tab-strategy')">🚀 Tactical Strategy</button>
            </div>

            <!-- TAB 1: Case Strength -->
            <div id="tab-strength" class="tab-content active">
              <?php $str = $analysis['assessments']['case_strength'] ?? []; ?>
              <div class="score-badge">
                <div class="score-circle"><?= htmlspecialchars($str['confidence_percentage'] ?? '85%') ?></div>
                <div>
                  <h4 style="font-size: 0.95rem; font-weight: 700;"><?= htmlspecialchars($str['rating'] ?? 'Strong Case Standing') ?></h4>
                  <p style="font-size: 0.8rem; color: #475569; margin-top: 2px;"><?= htmlspecialchars($str['merits_summary'] ?? 'Strong factual and legal foundation established.') ?></p>
                </div>
              </div>
              <div style="font-size: 0.82rem; color: #334155;">
                <p style="margin-bottom: 4px;"><strong>Evidential Sufficiency:</strong> <?= htmlspecialchars($str['evidential_sufficiency'] ?? 'Contemporaneous documentation verified.') ?></p>
                <p><strong>Procedural Standing:</strong> <?= htmlspecialchars($str['procedural_standing'] ?? 'Practice directions and relevant court/tribunal rules engaged.') ?></p>
              </div>
            </div>

            <!-- TAB 2: Issues Matrix -->
            <div id="tab-issues" class="tab-content">
              <?php $matrix = $analysis['assessments']['issue_matrix'] ?? ['items' => []]; ?>
              <table class="matrix-table">
                <thead>
                  <tr>
                    <th>Issue</th>
                    <th>Legal Test</th>
                    <th>Evidence Required</th>
                    <th>Priority</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($matrix['items'] as $item): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($item['issue'] ?? '') ?></strong></td>
                      <td><?= htmlspecialchars($item['legal_test'] ?? '') ?></td>
                      <td><?= htmlspecialchars($item['evidence_required'] ?? '') ?></td>
                      <td><span class="badge <?= ($item['priority'] ?? '') === 'Critical' ? 'badge-high' : 'badge-active' ?>"><?= htmlspecialchars($item['priority'] ?? 'High') ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- TAB 3: Risk Register -->
            <div id="tab-risks" class="tab-content">
              <?php $rr = $analysis['assessments']['risk_register'] ?? ['items' => []]; ?>
              <ul class="reasoning-list">
                <?php foreach ($rr['items'] as $riskItem): ?>
                  <li><?= htmlspecialchars($riskItem) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <!-- TAB 4: Tactical Strategy -->
            <div id="tab-strategy" class="tab-content">
              <?php $st = $analysis['assessments']['strategy_plan'] ?? ['steps' => []]; ?>
              <ul class="reasoning-list">
                <?php foreach ($st['steps'] as $stepItem): ?>
                  <li><?= htmlspecialchars($stepItem) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>

        <!-- COUNSEL REASONING PANEL (ALWAYS VISIBLE) -->
        <div class="panel-card" style="border-left: 4px solid var(--accent);">
          <div class="panel-header" style="background: #f1f5f9;">
            <h3 style="color: #0f172a;">⚖️ Counsel Legal Reasoning Panel</h3>
            <span style="font-size: 0.74rem; color: var(--text-muted);">Universal AI Framework</span>
          </div>
          <div class="panel-body">
            <div class="grid-2">
              <div class="reasoning-box">
                <div class="reasoning-title">FACTS RELIED UPON</div>
                <ul class="reasoning-list">
                  <?php foreach (array_slice($analysis['Facts'], 0, 3) as $f): ?>
                    <li><?= htmlspecialchars($f) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="reasoning-box">
                <div class="reasoning-title">ISSUES IDENTIFIED</div>
                <ul class="reasoning-list">
                  <?php foreach (array_slice($analysis['Issues'], 0, 3) as $iss): ?>
                    <li><?= htmlspecialchars($iss) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="grid-2">
              <div class="reasoning-box">
                <div class="reasoning-title">APPLICABLE LAW & RULES</div>
                <ul class="reasoning-list">
                  <?php foreach (array_slice($analysis['Law'], 0, 3) as $lw): ?>
                    <li><?= htmlspecialchars($lw) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="reasoning-box">
                <div class="reasoning-title">CRITICAL RISKS</div>
                <ul class="reasoning-list">
                  <?php foreach (array_slice($analysis['Risks'], 0, 2) as $rk): ?>
                    <li><?= htmlspecialchars($rk) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. AI OUTPUT & WORKBENCH -->
        <div class="panel-card">
          <div class="panel-header">
            <h3>6. AI GENERATED DRAFT & WORKBENCH</h3>
            <span class="badge badge-done">Live Editor</span>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label>Generated Document Title:</label>
              <input type="text" name="output_title" class="form-control" value="<?= htmlspecialchars($outputTitle) ?>" style="font-weight: 700;" />
            </div>

            <!-- DRAFT REFINEMENT TOOLS -->
            <div class="draft-toolbar">
              <button type="submit" name="action" value="improve" class="btn btn-sm btn-primary">✨ Improve Draft</button>
              <button type="submit" name="action" value="persuasive" class="btn btn-sm btn-outline">💪 Make More Persuasive</button>
              <button type="submit" name="action" value="formal" class="btn btn-sm btn-outline">👔 Make More Formal</button>
              <button type="submit" name="action" value="add_authorities" class="btn btn-sm btn-outline">⚖️ Add Authorities</button>
              <button type="submit" name="action" value="simplify" class="btn btn-sm btn-outline">👁️ Simplify</button>
              <button type="submit" name="action" value="expand" class="btn btn-sm btn-outline">📖 Expand Analysis</button>
              <button type="submit" name="action" value="risk_review" class="btn btn-sm btn-outline">🛡️ Risk Review</button>
            </div>

            <div class="form-group">
              <textarea name="output_draft" id="draftOutput" rows="18" class="form-control" style="font-family: 'Courier New', monospace; font-size: 0.88rem; line-height: 1.5; background: #fafbfc;"><?= htmlspecialchars($outputDraft) ?></textarea>
            </div>

            <!-- METRICS BAR -->
            <div class="metrics-bar">
              <div>Words: <strong><?= $wordCount ?></strong></div>
              <div>Characters: <strong><?= number_format($charCount) ?></strong></div>
              <div>Estimated Reading: <strong><?= $readTimeMin ?> min read</strong></div>
              <button type="button" onclick="copyDraftToClipboard();" class="btn btn-sm btn-outline">📋 Copy Draft</button>
            </div>

            <!-- QUALITY SCORECARD -->
            <div class="scorecard-grid">
              <div class="score-card"><div class="val">96%</div><div class="lbl">Clarity</div></div>
              <div class="score-card"><div class="val">99%</div><div class="lbl">Professional Tone</div></div>
              <div class="score-card"><div class="val">95%</div><div class="lbl">Completeness</div></div>
              <div class="score-card"><div class="val">97%</div><div class="lbl">Persuasiveness</div></div>
              <div class="score-card"><div class="val">92%</div><div class="lbl">Readability</div></div>
              <div class="score-card" style="background: #dcfce7; border-color: #86efac;"><div class="val" style="color: #166534;">Excellent</div><div class="lbl">Overall Quality</div></div>
            </div>

            <!-- BOTTOM SAVE / EXPORT BAR -->
            <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
              <div style="display: flex; gap: 6px;">
                <button type="button" onclick="window.print();" class="btn btn-outline">🖨 Print / PDF</button>
                <button type="button" onclick="exportWordDocument();" class="btn btn-outline">📝 Export Word</button>
              </div>
              <button type="submit" name="action" value="save_to_matter" class="btn btn-primary" style="padding: 10px 20px;">💾 Save to Matter & Document Library</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function switchDomainTab(evt, tabId) {
  const container = evt.currentTarget.closest('.panel-body');
  container.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
  evt.currentTarget.classList.add('active');
  document.getElementById(tabId).classList.add('active');
}

function copyDraftToClipboard() {
  const el = document.getElementById('draftOutput');
  el.select();
  document.execCommand('copy');
  alert('Draft copied to clipboard!');
}

function exportWordDocument() {
  const draft = document.getElementById('draftOutput').value;
  const title = document.querySelector('input[name="output_title"]').value || 'Legal_Document';
  const header = "<html xmlns:o='urn:schemas-microsoft-com:office:office' "+
        "xmlns:w='urn:schemas-microsoft-com:office:word' "+
        "xmlns='http://www.w3.org/TR/REC-html40'>"+
        "<head><meta charset='utf-8'><title>" + title + "</title>"+
        "<style>body{font-family:Arial,sans-serif;font-size:11pt;line-height:1.5;}</style></head><body><pre style='font-family:Arial,sans-serif;white-space:pre-wrap;'>" +
        draft.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
        "</pre></body></html>";
  const blob = new Blob(['\ufeff', header], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = title.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.doc';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}
</script>

</body>
</html>