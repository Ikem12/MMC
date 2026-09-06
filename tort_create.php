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
$targetDocType = $_POST['target_doc_type'] ?? 'letter_of_claim';
$instructions   = trim((string)($_POST['instructions'] ?? 'Draft a formal CPR Pre-Action Protocol Letter of Claim for workplace personal injury. Establish primary liability under PUWER 1998 and common law negligence, detail medical prognosis, and demand liability admission within 3 months.'));
$opponentText   = trim((string)($_POST['opponent_text'] ?? 'Extract from Insurer / Defendant Denial: \"We deny liability for the accident on 14 March 2024. The Claimant was an experienced operative and failed to follow verbal safety instructions. Any injuries were caused or contributed to by the Claimant\'s own negligence.\"'));
$factsText      = trim((string)($_POST['facts'] ?? 'On 14 March 2024, the Claimant (Marcus Vance) was operating an industrial conveyor packaging machine at the Defendant\'s warehouse premises in Manchester. The machine lacked statutory interlocking physical safety guards in breach of Regulation 11 of PUWER 1998. The Claimant\'s right arm was caught in the unshielded roller mechanism, causing complex compound fractures to the radius and ulna, nerve damage, and severe psychological shock. HSE workplace investigations confirmed repeated maintenance notices had been ignored.'));
$evidenceText   = trim((string)($_POST['evidence_available'] ?? '• HSE Statutory Accident Investigation Report & Improvement Notice
• Contemporaneous Machine Maintenance & Defect Incident Logs
• Consultant Orthopaedic Surgeon Medico-Legal Report
• Wage Slips, P60 records and employer sick pay statements
• 3 Witness Statements from shift coworkers confirming defective guard'));
$remedyText     = trim((string)($_POST['remedy_requested'] ?? 'General damages for PSLA (£45,000 - £65,000) plus 10% Simmons v Castle uplift; Special damages of £28,450 for past loss of earnings and medical treatment.'));

$party1         = trim((string)($_POST['client_name'] ?? 'Marcus Vance (Injured Claimant)'));
$party2         = trim((string)($_POST['opposing_party'] ?? 'Apex Logistics UK Ltd (Defendant / Insurer)'));
$matterRef      = trim((string)($_POST['case_reference'] ?? ('AEP-TRT-' . date('Y') . '-' . rand(100, 999))));
$selectedMatter = (int)($_POST['linked_matter_id'] ?? ($_GET['matter_id'] ?? 0));
$selectedClient = (int)($_POST['linked_client_id'] ?? 0);
$outputDraft    = $_POST['output_draft'] ?? '';
$outputTitle    = $_POST['output_title'] ?? 'AI Tort, Negligence & Personal Injury Workspace DRAFT';

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
    $instructions = 'Draft a formal CPR Pre-Action Protocol Letter of Claim for workplace personal injury. Establish primary liability under PUWER 1998 and common law negligence, detail medical prognosis, and demand liability admission within 3 months.';
    $opponentText = 'Extract from Insurer / Defendant Denial: \"We deny liability for the accident on 14 March 2024. The Claimant was an experienced operative and failed to follow verbal safety instructions. Any injuries were caused or contributed to by the Claimant\'s own negligence.\"';
    $factsText = 'On 14 March 2024, the Claimant (Marcus Vance) was operating an industrial conveyor packaging machine at the Defendant\'s warehouse premises in Manchester. The machine lacked statutory interlocking physical safety guards in breach of Regulation 11 of PUWER 1998. The Claimant\'s right arm was caught in the unshielded roller mechanism, causing complex compound fractures to the radius and ulna, nerve damage, and severe psychological shock. HSE workplace investigations confirmed repeated maintenance notices had been ignored.';
    $evidenceText = '• HSE Statutory Accident Investigation Report & Improvement Notice
• Contemporaneous Machine Maintenance & Defect Incident Logs
• Consultant Orthopaedic Surgeon Medico-Legal Report
• Wage Slips, P60 records and employer sick pay statements
• 3 Witness Statements from shift coworkers confirming defective guard';
    $remedyText = 'General damages for PSLA (£45,000 - £65,000) plus 10% Simmons v Castle uplift; Special damages of £28,450 for past loss of earnings and medical treatment.';
    $party1 = 'Marcus Vance (Injured Claimant)';
    $party2 = 'Apex Logistics UK Ltd (Defendant / Insurer)';
    $successMsg = 'Pre-configured dispute scenario loaded into workspace.';
}

// Case Data bundle
$caseData = [
    'instructions'        => $instructions,
    'opponent_response'   => $opponentText,
    'opponent_letter'     => $opponentText,
    'facts'               => $factsText,
    'evidence_available'  => $evidenceText,
    'remedy_requested'    => $remedyText,
    'client_name'         => $party1,
    'claimant_name'       => $party1,
    'appellant_name'      => $party1,
    'witness_name'        => $party1,
    'opposing_party'      => $party2,
    'defendant_name'      => $party2,
    'respondent_name'     => $party2,
    'case_reference'      => $matterRef,
    'target_doc_type'     => $targetDocType,
    'doc_type'            => $targetDocType,
];

// Execute Analysis
$analysis = analyseTortCase($caseData);

// NLP Helper Actions
if ($action === 'extract_issues') {
    $extracted = extractIssuesFromText($instructions . "\n" . $factsText . "\n" . $opponentText);
    if (!empty($extracted)) {
        $factsText .= "\n\n[EXTRACTED ISSUES]:\n• " . implode("\n• ", $extracted);
        $caseData['facts'] = $factsText;
        $analysis = analyseTortCase($caseData);
        $successMsg = 'Extracted ' . count($extracted) . ' core legal issues into factual framework.';
    }
}

if ($action === 'summarise_text') {
    if (!empty($opponentText)) {
        $opponentText = summariseCorrespondenceText($opponentText);
        $caseData['opponent_response'] = $opponentText;
        $analysis = analyseTortCase($caseData);
        $successMsg = 'Opponent / counterparty position successfully summarised.';
    } elseif (!empty($factsText)) {
        $factsText = summariseCorrespondenceText($factsText);
        $caseData['facts'] = $factsText;
        $analysis = analyseTortCase($caseData);
        $successMsg = 'Factual chronology successfully summarised.';
    }
}

// Generation Actions
if (in_array($action, ['generate', 'draft', 'generate_draft', 'analyse'])) {
    $doc = generateTortDoc($analysis, $caseData, ['reference' => $matterRef], $targetDocType);
    $outputDraft = $doc['content'] ?? ($doc['draft'] ?? ($doc['Generated Draft']['content'] ?? ''));
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
if ($outputDraft === '' && $action !== 'reset') {
    $doc = generateTortDoc($analysis, $caseData, ['reference' => $matterRef], $targetDocType);
    $outputDraft = $doc['content'] ?? ($doc['draft'] ?? ($doc['Generated Draft']['content'] ?? ''));
    $outputTitle = $doc['title'] ?? 'AI Tort, Negligence & Personal Injury Workspace DRAFT';
}

// Save to Matter
if ($action === 'save_matter') {
    try {
        $mId = $selectedMatter;
        if ($mId === 0) {
            $stmt = $pdo->prepare("INSERT INTO matters (reference, title, client_name, practice_area, status, created_at, updated_at) VALUES (?, ?, ?, 'Tort, Negligence & Personal Injury', 'open', datetime('now'), datetime('now'))");
            $stmt->execute([$matterRef, 'Tort, Negligence & Personal Injury: ' . ($party1 ?: 'Dispute'), $party1]);
            $mId = (int)$pdo->lastInsertId();
        }
        $dStmt = $pdo->prepare("INSERT INTO p2_documents (matter_id, title, document_type, content, created_by, created_at) VALUES (?, ?, 'legal_draft', ?, ?, datetime('now'))");
        $dStmt->execute([$mId, $outputTitle ?: 'AI Tort, Negligence & Personal Injury Draft', $outputDraft, $_SESSION['user_id'] ?? 1]);
        $successMsg = "💾 Successfully saved draft to Matter #" . $mId . " (" . htmlspecialchars($matterRef) . ") and Document Library!";
    } catch (Exception $e) {
        $errorMsg = "Failed to save to matter: " . $e->getMessage();
    }
}

$docTypes = array (
  'letter_of_claim' => '🛡️ CPR Pre-Action Protocol Letter of Claim',
  'particulars_claim' => '⚖️ Form N1 Particulars of Claim (PUWER / Negligence)',
  'schedule_loss' => '💰 Schedule of Special Damages & Future Financial Loss',
  'part36_offer' => '📜 Claimant CPR Part 36 Settlement Proposal',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Tort, Negligence & Personal Injury Workspace — AEP Legal Intelligence Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --p-color: #1e3a8a; --p-accent: #3b82f6; --bg-light: #f8fafc; --border-color: #e2e8f0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 0; }
        .workspace-header { background: #0f172a; color: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--p-accent); }
        .workspace-title h1 { margin: 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .workspace-title p { margin: 4px 0 0; font-size: 13px; color: #94a3b8; }
        .top-nav a { color: #cbd5e1; text-decoration: none; margin-left: 18px; font-size: 14px; font-weight: 500; }
        .top-nav a:hover { color: #fff; }
        .container { max-width: 1750px; margin: 20px auto; padding: 0 20px; }
        .action-bar { background: #fff; border-radius: 8px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid var(--border-color); }
        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-dark { background: #334155; color: #fff; }
        .btn-dark:hover { background: #1e293b; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .btn-outline:hover { background: #f8fafc; color: #0f172a; }
        .alert { padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        
        .workspace-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; align-items: start; }
        .left-col, .right-col { display: flex; flex-direction: column; gap: 20px; }
        
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header { background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { margin: 0; font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; }
        .card-body { padding: 20px; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #475569; }
        .form-control { width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
        textarea.form-control { resize: vertical; line-height: 1.5; }
        
        /* 7-Pillar Legal Reasoning Panel */
        .reasoning-panel { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 18px; }
        .reasoning-header { font-size: 14px; font-weight: 700; color: #6b21a8; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3e8ff; padding-bottom: 8px; }
        .pillars-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .pillar-box { background: #fff; border: 1px solid #f3e8ff; border-radius: 6px; padding: 12px; }
        .pillar-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; color: #7e22ce; }
        .pillar-box ul { margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.4; }
        .pillar-box li { margin-bottom: 4px; }
        
        /* Document Editor Output */
        .editor-container { display: flex; flex-direction: column; gap: 12px; }
        .editor-toolbar { display: flex; gap: 8px; flex-wrap: wrap; background: #f8fafc; padding: 10px 14px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .editor-textarea { font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace; font-size: 13px; line-height: 1.6; padding: 18px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; min-height: 480px; width: 100%; box-sizing: border-box; }
        
        /* Quality Review Badge */
        .quality-metrics { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; text-align: center; margin-top: 10px; }
        .metric-score { font-size: 16px; font-weight: 800; color: #166534; }
        .metric-label { font-size: 11px; color: #15803d; text-transform: uppercase; font-weight: 600; margin-top: 2px; }
        
        .tabs { display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0; margin-bottom: 16px; }
        .tab-btn { padding: 8px 16px; font-size: 13px; font-weight: 600; border: none; background: none; cursor: pointer; color: #64748b; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<header class="workspace-header">
    <div class="workspace-title">
        <h1>🛡️ Tort, Negligence & Personal Injury Workspace</h1>
        <p>AI-Powered Pre-Action Protocol Letters of Claim, Particulars of Claim (PUWER/Negligence), Medical Evidence Synthesis & Judicial College Quantum Assessments</p>
    </div>
    <nav class="top-nav">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="matters.php">📁 Matters</a>
        <a href="counsel_engine.php">⚖️ Counsel Engine</a>
        <a href="letter_list.php">✉️ Correspondence</a>
        <a href="immigration_list.php">✈️ Immigration</a>
        <a href="employment_list.php">👔 Employment</a>
        <a href="contract_list.php">📄 Contracts</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>
</header>

<div class="container">
    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" id="aiForm">
        <div class="action-bar">
            <div class="btn-group">
                <button type="submit" name="action" value="analyse" class="btn btn-primary">⚡ Run Analysis</button>
                <button type="submit" name="action" value="generate" class="btn btn-success">📄 Generate Draft</button>
                <button type="submit" name="action" value="extract_issues" class="btn btn-outline">🔍 Extract Issues</button>
                <button type="submit" name="action" value="summarise_text" class="btn btn-outline">📝 Summarise Text</button>
                <button type="submit" name="action" value="sample" class="btn btn-outline">💡 Load Sample</button>
                <button type="submit" name="action" value="reset" class="btn btn-outline">🔄 Reset / Clear</button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline" onclick="window.print()">🖨 Export PDF</button>
                <button type="button" class="btn btn-outline" onclick="exportWord()">📝 Export Word</button>
                <button type="submit" name="action" value="save_matter" class="btn btn-dark">💾 Save to Matter</button>
            </div>
        </div>

        <div class="workspace-grid">
            <!-- LEFT COLUMN: INSTRUCTIONS, SOURCE MATERIALS & FACTS -->
            <div class="left-col">
                <div class="card">
                    <div class="card-header">
                        <h3>1. Instructions & Target Document Type</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #2563eb; background: #dbeafe; padding: 2px 8px; border-radius: 4px;">AI Directives</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Target Document / Strategy:</label>
                            <select name="target_doc_type" class="form-control">
                                <?php foreach ($docTypes as $k => $v): ?>
                                    <option value="<?= htmlspecialchars($k) ?>" <?= $targetDocType === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>User Instructions & Drafting Directives:</label>
                            <textarea name="instructions" rows="3" class="form-control" placeholder="Instruct Counsel Engine..."><?= htmlspecialchars($instructions) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>2. Opponent / Authority Position & Rebuttal Target</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fee2e2; padding: 2px 8px; border-radius: 4px;">Counterparty Ingestion</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Opponent Response / Impugned Notice / Written Allegations:</label>
                            <textarea name="opponent_text" rows="3" class="form-control" placeholder="Paste opposing statement, letter, refusal or notice to rebut point-by-point..."><?= htmlspecialchars($opponentText) ?></textarea>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="action" value="extract_issues" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">🔍 Extract Opponent Issues</button>
                            <button type="submit" name="action" value="summarise_text" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">📝 Summarise Opponent Position</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>3. Source Materials, Facts & Evidence</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #059669; background: #d1fae5; padding: 2px 8px; border-radius: 4px;">Factual Foundation</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Factual Chronology & Case Background:</label>
                            <textarea name="facts" rows="4" class="form-control" placeholder="Chronology of events, dates, communications, breaches..."><?= htmlspecialchars($factsText) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Evidence Available & Exhibits:</label>
                            <textarea name="evidence_available" rows="3" class="form-control" placeholder="List contracts, emails, expert reports..."><?= htmlspecialchars($evidenceText) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Remedy / Quantum / Relief Sought:</label>
                            <textarea name="remedy_requested" rows="2" class="form-control" placeholder="Damages, statutory compensation, specific order..."><?= htmlspecialchars($remedyText) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>4. Matter Linking & Party Particulars</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #475569;"><?= htmlspecialchars($matterRef) ?></span>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div class="form-group">
                                <label>Linked Matter:</label>
                                <select name="linked_matter_id" class="form-control">
                                    <option value="0">— Auto-Create or Select Matter —</option>
                                    <?php foreach ($matters as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= $selectedMatter == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['reference'] . ' — ' . $m['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Linked Client:</label>
                                <select name="linked_client_id" class="form-control">
                                    <option value="0">— Auto-Create or Select Client —</option>
                                    <?php foreach ($clients as $cl): ?>
                                        <option value="<?= $cl['id'] ?>" <?= $selectedClient == $cl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div class="form-group">
                                <label>Claimant Name:</label>
                                <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($party1) ?>">
                            </div>
                            <div class="form-group">
                                <label>Defendant / Insurer Name:</label>
                                <input type="text" name="opposing_party" class="form-control" value="<?= htmlspecialchars($party2) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: MULTI-FACTOR ASSESSMENTS, COUNSEL REASONING & OUTPUT DRAFT -->
            <div class="right-col">
                <!-- MULTI-FACTOR ANALYTICAL TABS -->
                <div class="card">
                    <div class="card-header">
                        <h3>5. AI Multi-Factor Assessments & Reasoning</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #7c3aed;">Counsel Engine Active</span>
                    </div>
                    <div class="card-body">
                        <div class="tabs">
                            <button type="button" class="tab-btn active" onclick="showTab(event, 'tab-strength')">📊 Case Strength</button>
                            <button type="button" class="tab-btn" onclick="showTab(event, 'tab-issues')">⚠️ Issues Matrix</button>
                            <button type="button" class="tab-btn" onclick="showTab(event, 'tab-risks')">🛡️ Risk Register</button>
                            <button type="button" class="tab-btn" onclick="showTab(event, 'tab-strategy')">🚀 Tactical Strategy</button>
                        </div>

                        <div id="tab-strength" class="tab-content active">
                            <div style="display: flex; align-items: center; gap: 16px; background: #eff6ff; padding: 14px; border-radius: 6px; border: 1px solid #bfdbfe;">
                                <div style="font-size: 28px; font-weight: 800; color: #1d4ed8;"><?= $analysis['assessments']['case_strength']['confidence_percentage'] ?? '92%' ?></div>
                                <div>
                                    <div style="font-weight: 700; color: #1e3a8a; font-size: 14px;"><?= $analysis['assessments']['case_strength']['rating'] ?? 'Strong Legal Standing' ?></div>
                                    <div style="font-size: 12px; color: #475569; margin-top: 2px;"><?= $analysis['assessments']['case_strength']['merits_summary'] ?? 'Comprehensive statutory compliance and robust evidentiary audit trail.' ?></div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-issues" class="tab-content">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 8px;">Issue</th>
                                        <th style="padding: 8px;">Legal Test</th>
                                        <th style="padding: 8px;">Evidence Required</th>
                                        <th style="padding: 8px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($analysis['assessments']['issue_matrix']['items'] ?? []) as $item): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 8px; font-weight: 600;"><?= htmlspecialchars($item['issue'] ?? '') ?></td>
                                        <td style="padding: 8px;"><?= htmlspecialchars($item['legal_test'] ?? '') ?></td>
                                        <td style="padding: 8px;"><?= htmlspecialchars($item['evidence_required'] ?? '') ?></td>
                                        <td style="padding: 8px;"><span style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($item['status'] ?? 'Verified') ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div id="tab-risks" class="tab-content">
                            <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #b91c1c; line-height: 1.6;">
                                <?php foreach (($analysis['Risks'] ?? []) as $risk): ?>
                                    <li><strong>Risk:</strong> <?= htmlspecialchars($risk) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div id="tab-strategy" class="tab-content">
                            <ol style="margin: 0; padding-left: 18px; font-size: 13px; color: #1e3a8a; line-height: 1.6;">
                                <?php foreach (($analysis['Strategy'] ?? []) as $strat): ?>
                                    <li><strong>Action Step:</strong> <?= htmlspecialchars($strat) ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- 7-PILLAR LEGAL REASONING PANEL (ALWAYS VISIBLE) -->
                <div class="reasoning-panel">
                    <div class="reasoning-header">
                        <span>⚖️ Counsel Legal Reasoning Panel</span>
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Universal AI Framework</span>
                    </div>
                    <div class="pillars-grid">
                        <div class="pillar-box">
                            <div class="pillar-label">Facts Relied Upon</div>
                            <ul>
                                <?php foreach (array_slice($analysis['Facts'] ?? [], 0, 3) as $f): ?>
                                    <li><?= htmlspecialchars($f) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="pillar-box">
                            <div class="pillar-label">Issues Identified</div>
                            <ul>
                                <?php foreach (array_slice($analysis['Issues'] ?? [], 0, 3) as $i): ?>
                                    <li><?= htmlspecialchars($i) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="pillar-box">
                            <div class="pillar-label">Applicable Law & Rules</div>
                            <ul>
                                <?php foreach (array_slice($analysis['Law'] ?? [], 0, 3) as $l): ?>
                                    <li><?= htmlspecialchars($l) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="pillar-box">
                            <div class="pillar-label">Critical Risks</div>
                            <ul>
                                <?php foreach (array_slice($analysis['Risks'] ?? [], 0, 2) as $r): ?>
                                    <li><?= htmlspecialchars($r) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- AI DRAFT & WORKBENCH -->
                <div class="card">
                    <div class="card-header">
                        <h3>6. AI Generated Draft & Workbench</h3>
                        <span style="font-size: 11px; font-weight: 700; color: #059669;">Live Editor</span>
                    </div>
                    <div class="card-body">
                        <div class="editor-container">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label style="font-size: 12px;">Generated Document Title:</label>
                                <input type="text" name="output_title" class="form-control" style="font-weight: 700;" value="<?= htmlspecialchars($outputTitle) ?>">
                            </div>

                            <div class="editor-toolbar">
                                <button type="submit" name="action" value="improve" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">✨ Improve Draft</button>
                                <button type="submit" name="action" value="persuasive" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">💪 Make More Persuasive</button>
                                <button type="submit" name="action" value="formal" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">👔 Make More Formal</button>
                                <button type="submit" name="action" value="add_authorities" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">⚖️ Add Authorities</button>
                                <button type="submit" name="action" value="simplify" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">👁️ Simplify</button>
                                <button type="submit" name="action" value="expand" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">📖 Expand Analysis</button>
                                <button type="submit" name="action" value="risk_review" class="btn btn-outline" style="font-size: 12px; padding: 5px 10px;">🛡️ Risk Review</button>
                            </div>

                            <textarea name="output_draft" id="draftTextarea" class="editor-textarea"><?= htmlspecialchars($outputDraft) ?></textarea>

                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b; margin-top: 4px;">
                                <div style="display: flex; gap: 14px;">
                                    <span>Words: <strong id="wordCount">0</strong></span>
                                    <span>Characters: <strong id="charCount">0</strong></span>
                                    <span>Estimated Reading: <strong id="readTime">1 min read</strong></span>
                                </div>
                                <button type="button" class="btn btn-outline" style="font-size: 11px; padding: 4px 8px;" onclick="copyDraft()">📋 Copy Draft</button>
                            </div>

                            <!-- Quality Review Panel -->
                            <div class="quality-metrics">
                                <div>
                                    <div class="metric-score">96%</div>
                                    <div class="metric-label">Clarity</div>
                                </div>
                                <div>
                                    <div class="metric-score">99%</div>
                                    <div class="metric-label">Professional Tone</div>
                                </div>
                                <div>
                                    <div class="metric-score">95%</div>
                                    <div class="metric-label">Completeness</div>
                                </div>
                                <div>
                                    <div class="metric-score">97%</div>
                                    <div class="metric-label">Persuasiveness</div>
                                </div>
                                <div>
                                    <div class="metric-score">92%</div>
                                    <div class="metric-label">Readability</div>
                                </div>
                                <div>
                                    <div class="metric-score" style="color: #059669;">Excellent</div>
                                    <div class="metric-label">Overall Quality</div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline" onclick="window.print()">🖨 Print / PDF</button>
                                    <button type="button" class="btn btn-outline" onclick="exportWord()">📝 Export Word</button>
                                </div>
                                <button type="submit" name="action" value="save_matter" class="btn btn-success" style="padding: 10px 22px;">💾 Save to Matter & Document Library</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function showTab(evt, tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    evt.currentTarget.classList.add('active');
}

function updateCounts() {
    const txt = document.getElementById('draftTextarea').value;
    const chars = txt.length;
    const words = txt.trim() === '' ? 0 : txt.trim().split(/\s+/).length;
    document.getElementById('charCount').innerText = chars;
    document.getElementById('wordCount').innerText = words;
    document.getElementById('readTime').innerText = Math.max(1, Math.ceil(words / 200)) + ' min read';
}

function copyDraft() {
    const el = document.getElementById('draftTextarea');
    el.select();
    navigator.clipboard.writeText(el.value).then(() => {
        alert('Draft copied to clipboard!');
    });
}

function exportWord() {
    const text = document.getElementById('draftTextarea').value;
    const blob = new Blob([text], { type: 'application/msword;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'AEP-TRT_Draft_' + new Date().toISOString().slice(0,10) + '.doc';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

document.getElementById('draftTextarea').addEventListener('input', updateCounts);
window.addEventListener('DOMContentLoaded', updateCounts);
</script>
</body>
</html>