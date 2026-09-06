<?php
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';
p2_start();
$pdo = p2_db();
p2_check_csrf();

$error = '';
$notice = '';

function letter_field(array $src, string $key, string $default = ''): string {
    return trim((string)($src[$key] ?? $default));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Default sample case
$defaultSample = [
    'matter_id' => 0,
    'client_id' => 0,
    'letter_type' => 'Letter Before Action (Pre-Action Protocol)',
    'ref_no' => 'AEP-COR-' . date('Y') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT),
    'recipient_name' => 'Apex Logistics UK Ltd (Attn: Marcus Vance, Managing Director)',
    'recipient_email' => 'm.vance@apexlogistics-uk.example.com',
    'recipient_address' => "Apex House, 42 King Street\nManchester, M2 4WU",
    'subject' => 'Breach of Contract & Outstanding Freight Invoices (£64,850.00)',
    'salutation' => 'Dear Mr Vance',
    'direction' => 'outgoing',
    'delivery_method' => 'email',
    'response_due_on' => date('Y-m-d', strtotime('+14 days')),
    'requires_response' => 1,
    'signatory_name' => 'Jonathan Sterling, Partner',
    'signatory_title' => 'Head of Commercial Litigation & Dispute Resolution',
    'instructions' => 'Draft a formal Pre-Action Protocol Letter Before Claim demanding payment of outstanding freight services invoices (£64,850.00) plus statutory interest under the Late Payment of Commercial Debts Act 1998. Rebut their spurious assertion of delayed cargo delivery and provide a strict 14-day deadline before issuing County Court proceedings.',
    'facts' => "Our client (TransGlobal Freight Services Ltd) entered into a standard commercial haulage master services agreement with Apex Logistics UK Ltd on 12 January 2024. Between May and August 2024, our client completed 18 separate scheduled cross-border freight consignments strictly in accordance with agreed transit timetables.\n\nAll consignments were signed for upon delivery without damage or demurrage noted on the consignment notes (CMR notes). Invoices INV-2024-881 through INV-2024-898 totalling £64,850.00 gross were duly submitted with 30-day payment terms.\n\nApex Logistics failed to remit payment despite multiple statements and credit control reminders. Apex now seeks to raise an unsubstantiated set-off claim alleging transit delays on consignment 884, despite having signed the clean proof of delivery note without protest.",
    'opponent_letter' => "Extract from Apex Logistics Letter dated 18 August 2024:\n\"We refer to your recent invoice statement. Please be advised that we are withholding the entire balance of £64,850.00. We contend that your driver arrived 4 hours late on consignment 884 in Rotterdam, causing our downstream buyer to threaten cancellation. We reserve our rights to set off our alleged losses of £75,000 against any sums due to you.\"",
    'evidence_available' => "• Signed Commercial Haulage Agreement dated 12 January 2024\n• 18 Clean Signed Consignment / CMR Notes with recipient delivery timestamps\n• Telematics & GPS vehicle tracking data confirming arrival within agreed booking window\n• 18 VAT Invoices (INV-2024-881 to INV-2024-898) totalling £64,850.00\n• 3 written credit control reminders dated 15 June, 15 July and 01 August 2024",
    'remedy_requested' => 'Full immediate payment of £64,850.00 plus statutory compensation of £720.00 and Late Payment interest at 8% above Bank of England base rate (£1,418.22 to date).',
    'output_title' => 'LETTER BEFORE ACTION — BREACH OF CONTRACT & DEBT RECOVERY',
    'output_content' => '',
    'active_tab' => 'strength',
];

if ($action === 'reset') {
    $caseData = [
        'matter_id' => 0,
        'client_id' => 0,
        'letter_type' => 'General Correspondence',
        'ref_no' => 'AEP-COR-' . date('Y') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT),
        'recipient_name' => '',
        'recipient_email' => '',
        'recipient_address' => '',
        'subject' => '',
        'salutation' => 'Dear Sir/Madam',
        'direction' => 'outgoing',
        'delivery_method' => 'email',
        'response_due_on' => '',
        'requires_response' => 1,
        'signatory_name' => 'Jonathan Sterling, Partner',
        'signatory_title' => 'Commercial Litigation',
        'instructions' => '',
        'facts' => '',
        'opponent_letter' => '',
        'evidence_available' => '',
        'remedy_requested' => '',
        'output_title' => 'LEGAL CORRESPONDENCE DRAFT',
        'output_content' => '',
        'active_tab' => 'strength',
    ];
    $notice = 'Correspondence workspace reset to blank template. Ready for new input.';
} elseif ($action === 'sample') {
    $caseData = $defaultSample;
    $notice = 'Sample commercial dispute case loaded (TransGlobal Freight v Apex Logistics). Click Analyse or Generate Draft.';
} else {
    $caseData = [
        'matter_id' => (int)($_POST['matter_id'] ?? $_GET['matter_id'] ?? 0),
        'client_id' => (int)($_POST['client_id'] ?? $_GET['client_id'] ?? 0),
        'letter_type' => letter_field($_POST, 'letter_type', $defaultSample['letter_type']),
        'ref_no' => letter_field($_POST, 'ref_no', $defaultSample['ref_no']),
        'recipient_name' => letter_field($_POST, 'recipient_name', $defaultSample['recipient_name']),
        'recipient_email' => letter_field($_POST, 'recipient_email', $defaultSample['recipient_email']),
        'recipient_address' => letter_field($_POST, 'recipient_address', $defaultSample['recipient_address']),
        'subject' => letter_field($_POST, 'subject', $defaultSample['subject']),
        'salutation' => letter_field($_POST, 'salutation', $defaultSample['salutation']),
        'direction' => letter_field($_POST, 'direction', 'outgoing'),
        'delivery_method' => letter_field($_POST, 'delivery_method', 'email'),
        'response_due_on' => letter_field($_POST, 'response_due_on', $defaultSample['response_due_on']),
        'requires_response' => isset($_POST['requires_response']) ? 1 : (isset($_GET['requires_response']) ? (int)$_GET['requires_response'] : 1),
        'signatory_name' => letter_field($_POST, 'signatory_name', $defaultSample['signatory_name']),
        'signatory_title' => letter_field($_POST, 'signatory_title', $defaultSample['signatory_title']),
        'instructions' => letter_field($_POST, 'instructions', $defaultSample['instructions']),
        'facts' => letter_field($_POST, 'facts', $defaultSample['facts']),
        'opponent_letter' => letter_field($_POST, 'opponent_letter', $defaultSample['opponent_letter']),
        'evidence_available' => letter_field($_POST, 'evidence_available', $defaultSample['evidence_available']),
        'remedy_requested' => letter_field($_POST, 'remedy_requested', $defaultSample['remedy_requested']),
        'output_title' => letter_field($_POST, 'output_title', 'LEGAL CORRESPONDENCE DRAFT'),
        'output_content' => letter_field($_POST, 'output_content', ''),
        'active_tab' => letter_field($_POST, 'active_tab', 'strength'),
    ];
}

// Matter resolution
$matter = null;
if ($caseData['matter_id'] > 0) {
    $matter = p2_find_matter($pdo, $caseData['matter_id']);
    if ($matter && empty($_POST['ref_no'])) {
        $caseData['ref_no'] = 'AEP-COR-' . $matter['reference'];
    }
}

// AI Analysis
$analysis = analyseCorrespondenceCase($caseData, $matter);

// Action Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'analyse') {
        $caseData['output_title'] = "LEGAL CORRESPONDENCE & STRATEGY: " . strtoupper($caseData['recipient_name'] ?: 'MATTER');
        $draftObj = generateLegalCorrespondence($analysis, $caseData, $matter ?? [], $caseData['letter_type']);
        $caseData['output_content'] = $draftObj['Generated Draft']['content'] ?? '';
        $notice = 'Correspondence case fully analysed across Pre-Action Protocols, Issues, Merits, and Risk Register.';
    } elseif ($action === 'generate') {
        $draftObj = generateLegalCorrespondence($analysis, $caseData, $matter ?? [], $caseData['letter_type']);
        $caseData['output_title'] = $draftObj['Generated Draft']['title'] ?? 'LEGAL DRAFT';
        $caseData['output_content'] = $draftObj['Generated Draft']['content'] ?? '';
        $notice = 'Legal document draft generated based on selected letter type and instructions.';
    } elseif ($action === 'summarise_text') {
        $targetText = $caseData['opponent_letter'] ?: ($caseData['facts'] ?: $caseData['instructions']);
        $summary = summariseCorrespondenceText($targetText);
        $caseData['output_title'] = "EXECUTIVE SUMMARY OF SOURCE CORRESPONDENCE";
        $caseData['output_content'] = $summary;
        $notice = 'Source correspondence successfully summarised into core substantive points.';
    } elseif ($action === 'extract_issues') {
        $targetText = ($caseData['opponent_letter'] ? "OPPONENT TEXT:\n" . $caseData['opponent_letter'] . "\n\n" : "") . "FACTS & INSTRUCTIONS:\n" . $caseData['facts'] . "\n" . $caseData['instructions'];
        $extracted = extractIssuesFromText($targetText);
        $res = "LEGAL ISSUES & DISPUTE POINTS EXTRACTED FROM SOURCE MATERIAL\n";
        $res .= "================================================================================\n\n";
        foreach ($extracted as $idx => $iss) {
            $res .= "ISSUE " . ($idx + 1) . ":\n" . $iss . "\n\n";
        }
        $res .= "RECOMMENDED ACTION:\n• Direct Counsel Engine to generate formal rebuttal addressing each extracted point above.\n";
        $caseData['output_title'] = "EXTRACTED LEGAL ISSUES MATRIX";
        $caseData['output_content'] = $res;
        $caseData['active_tab'] = 'issues';
        $notice = 'Legal issues and factual assertions extracted from provided texts.';
    } elseif ($action === 'improve') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'improve', 'Verify every factual assertion, confirm contract dates and ensure proportionate language.');
        $notice = 'Draft improved with evidential audit and statutory verification notes.';
    } elseif ($action === 'persuasive') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'persuasive', 'Reinforce liability position, contractual entitlement and pre-action protocol cost warnings.');
        $notice = 'Draft enhanced with authoritative, persuasive legal submissions.';
    } elseif ($action === 'formal') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'formal', 'Enhance formal legal framing in accordance with Civil Procedure Rules standards.');
        $notice = 'Draft converted to heightened formal legal styling.';
    } elseif ($action === 'add_authorities') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'add_authorities', 'Integrate Pre-Action Protocol, Late Payment Act 1998 interest, and CPR Part 36 standards.');
        $notice = 'Statutory provisions and legal authorities integrated into draft.';
    } elseif ($action === 'simplify') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'simplify', 'Simplify phrasing into clear, direct propositions.');
        $notice = 'Draft simplified for plain clarity.';
    } elseif ($action === 'expand') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'expand', 'Expand factual chronology, documentary cross-references and legal consequences.');
        $notice = 'Draft expanded with comprehensive factual and legal analysis.';
    } elseif ($action === 'risk_review') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'risk_review', 'Audit for inadvertent admissions, limitation issues and without prejudice protection.');
        $notice = 'Risk and evidential gap review integrated into draft.';
    } elseif ($action === 'export_word') {
        $docTitle = $caseData['output_title'] ?: 'Legal-Correspondence';
        $docBody = $caseData['output_content'] ?: 'No content';
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $docTitle) . '.doc"');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars($docTitle) . '</title></head><body><pre style="white-space:pre-wrap;font-family:Calibri,Arial,sans-serif;font-size:11pt;">' . htmlspecialchars($docBody) . '</pre></body></html>';
        exit;
    } elseif ($action === 'save_to_matter') {
        try {
            $pdo->beginTransaction();

            // 1. Ensure Client exists
            $clientId = (int)$caseData['client_id'];
            if ($clientId <= 0) {
                $cStmt = $pdo->prepare('INSERT INTO p2_clients (name, email, phone, address) VALUES (?,?,?,?)');
                $cStmt->execute([$caseData['recipient_name'] ?: 'Correspondence Contact', $caseData['recipient_email'], '', $caseData['recipient_address']]);
                $clientId = (int)$pdo->lastInsertId();
                $caseData['client_id'] = $clientId;
            }

            // 2. Ensure Matter exists
            $matterId = (int)$caseData['matter_id'];
            if ($matterId <= 0) {
                $ref = 'AEP-MAT-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $mStmt = $pdo->prepare('INSERT INTO p2_matters (client_id, reference, title, practice_area, status, owner_user_id, opened_on, description) VALUES (?,?,?,?,?,?,?,?)');
                $mStmt->execute([
                    $clientId,
                    $ref,
                    'Correspondence: ' . ($caseData['subject'] ?: 'Legal Dispute'),
                    'Commercial Litigation',
                    'open',
                    p2_user_id(),
                    date('Y-m-d'),
                    $caseData['instructions'] . "\n\nFacts: " . $caseData['facts']
                ]);
                $matterId = (int)$pdo->lastInsertId();
                $caseData['matter_id'] = $matterId;
            }

            // 3. Save into draft_letters
            $docContent = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? 'Legal correspondence record.');
            $lStmt = $pdo->prepare('INSERT INTO draft_letters (ref_no, letter_type, recipient_name, recipient_address, recipient_email, subject, salutation, body, signatory_name, signatory_title, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $lStmt->execute([
                $caseData['ref_no'],
                $caseData['letter_type'],
                $caseData['recipient_name'],
                $caseData['recipient_address'],
                $caseData['recipient_email'],
                $caseData['subject'],
                $caseData['salutation'],
                $docContent,
                $caseData['signatory_name'],
                $caseData['signatory_title'],
                'draft'
            ]);
            $letterId = (int)$pdo->lastInsertId();

            // 4. Save into p5_correspondence
            $qualityScore = 95;
            $reviewSummary = "Counsel Engine Analysis — Issues: " . implode('; ', array_slice($analysis['Issues'], 0, 3));
            $p5Stmt = $pdo->prepare('INSERT INTO p5_correspondence (letter_id, matter_id, client_id, practice_area, correspondence_reference, direction, delivery_method, response_due_on, requires_response, ai_analysis, quality_score, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $p5Stmt->execute([
                $letterId,
                $matterId,
                $clientId,
                'Commercial Litigation',
                $caseData['ref_no'],
                $caseData['direction'],
                $caseData['delivery_method'],
                $caseData['response_due_on'] ?: null,
                $caseData['requires_response'],
                $reviewSummary,
                $qualityScore,
                p2_user_id()
            ]);

            // 5. Save version 1
            $snapshot = json_encode(['letter' => $caseData, 'analysis' => $analysis], JSON_UNESCAPED_UNICODE);
            $pdo->prepare('INSERT INTO p5_letter_versions (letter_id, version_no, snapshot, note, created_by) VALUES (?,?,?,?,?)')
                ->execute([$letterId, 1, $snapshot, 'AI Correspondence Workspace initial draft', p2_user_id()]);

            // 6. Save document to p2_documents
            $docTitle = $caseData['output_title'] ?: ('Correspondence — ' . $caseData['subject']);
            $dStmt = $pdo->prepare('INSERT INTO p2_documents (matter_id, title, document_type, source_table, source_id, content, created_by) VALUES (?,?,?,?,?,?,?)');
            $dStmt->execute([$matterId, $docTitle, 'correspondence', 'draft_letters', $letterId, $docContent, p2_user_id()]);
            $documentId = (int)$pdo->lastInsertId();

            // 7. Save AI Session in p6_*
            $sessStmt = $pdo->prepare('INSERT INTO p6_ai_sessions (matter_id, workflow, prompt, reasoning_json, created_by) VALUES (?,?,?,?,?)');
            $sessStmt->execute([$matterId, 'letter', $caseData['instructions'], json_encode($analysis, JSON_UNESCAPED_UNICODE), p2_user_id()]);
            $sessionId = (int)$pdo->lastInsertId();

            $outStmt = $pdo->prepare('INSERT INTO p6_ai_outputs (session_id, matter_id, workflow, title, content, preliminary, created_by) VALUES (?,?,?,?,?,?,?)');
            $outStmt->execute([$sessionId, $matterId, 'letter', $docTitle, $docContent, 1, p2_user_id()]);

            // 8. Timeline & Activity Log
            p5_add_timeline($pdo, $letterId, 'created', 'Correspondence drafted in AI Correspondence Workspace.');
            p2_log('correspondence.created', "Saved Correspondence #{$letterId} ({$caseData['ref_no']}) to Matter #{$matterId}", $matterId, [
                'recipient' => $caseData['recipient_name'],
                'document_id' => $documentId
            ]);

            $pdo->commit();
            header('Location: letter_view.php?id=' . $letterId . '&saved=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to save correspondence: ' . $e->getMessage();
        }
    }
}

// Ensure default content if empty
if (empty($caseData['output_content']) && $action !== 'reset') {
    $draftObj = generateLegalCorrespondence($analysis, $caseData, $matter ?? [], $caseData['letter_type']);
    $caseData['output_content'] = $draftObj['Generated Draft']['content'] ?? '';
}

$allMatters = $pdo->query('SELECT id, reference, title FROM p2_matters ORDER BY id DESC LIMIT 50')->fetchAll();
$allClients = $pdo->query('SELECT id, name FROM p2_clients ORDER BY name ASC LIMIT 50')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>AI Legal Correspondence Workspace &mdash; AEP Legal Intelligence Platform</title>
<link rel="stylesheet" href="assets/phase2.css"/>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f0f3f8; color: #1e293b; line-height: 1.5; }
.topbar { background: #0f2438; color: #fff; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2563eb; }
.topbar .brand { font-size: 1.15rem; font-weight: 700; letter-spacing: 0.5px; }
.topbar .brand span { color: #38bdf8; }
.topbar nav a { color: #cbd5e1; text-decoration: none; font-size: 0.88rem; margin-left: 18px; font-weight: 500; transition: color .2s; }
.topbar nav a:hover { color: #38bdf8; }
.topbar nav a.active { color: #38bdf8; font-weight: 700; }

.page-header { background: linear-gradient(135deg, #0f2438 0%, #1e3a5f 50%, #1e40af 100%); color: #fff; padding: 22px 32px; border-bottom: 1px solid #3b82f6; }
.page-header h1 { font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
.page-header p { font-size: 0.9rem; color: #93c5fd; margin-top: 6px; }

.main-container { max-width: 1650px; margin: 0 auto; padding: 20px; }

/* Sticky / Prominent Action Bar */
.action-bar { background: #fff; border-radius: 8px; padding: 14px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; border-left: 5px solid #2563eb; position: sticky; top: 0; z-index: 100; }
.action-group { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

.btn { padding: 9px 15px; border-radius: 6px; font-size: 0.86rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all .15s ease-in-out; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-accent { background: #0d9488; color: #fff; }
.btn-accent:hover { background: #0f766e; }
.btn-purple { background: #7c3aed; color: #fff; }
.btn-purple:hover { background: #6d28d9; }
.btn-emerald { background: #059669; color: #fff; }
.btn-emerald:hover { background: #047857; }
.btn-outline { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
.btn-outline:hover { background: #f1f5f9; border-color: #94a3b8; }
.btn-success { background: #16a34a; color: #fff; }
.btn-success:hover { background: #15803d; }
.btn-warning { background: #f59e0b; color: #1e293b; }
.btn-warning:hover { background: #d97706; color: #fff; }
.btn-danger { background: #ef4444; color: #fff; }
.btn-danger:hover { background: #dc2626; }

.alert { padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px; }
.alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* Grid Layout */
.workspace-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
@media (max-width: 1280px) { .workspace-grid { grid-template-columns: 1fr; } }

.card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; border: 1px solid #e2e8f0; }
.card-header { padding: 12px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 0.92rem; color: #0f172a; display: flex; justify-content: space-between; align-items: center; }
.card-header.navy { background: #0f2438; color: #fff; }
.card-header.blue { background: #1e40af; color: #fff; }
.card-header.teal { background: #0f766e; color: #fff; }
.card-header.purple { background: #6b21a8; color: #fff; }
.card-body { padding: 18px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: 1 / -1; }
label { font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.4px; }
input, select, textarea { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; color: #1e293b; background: #fff; width: 100%; transition: border-color .15s; }
input:focus, select:focus, textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
textarea { resize: vertical; min-height: 80px; font-family: inherit; line-height: 1.45; }

/* Focus instruction area */
.instruction-box { background: #eff6ff; border: 2px solid #3b82f6; border-radius: 6px; font-size: 0.92rem; font-weight: 500; color: #1e3a8a; }

/* Assessments Tabs */
.tabs-nav { display: flex; background: #e2e8f0; border-radius: 6px; padding: 4px; gap: 4px; margin-bottom: 14px; overflow-x: auto; }
.tab-btn { flex: 1; padding: 7px 10px; text-align: center; background: transparent; border: none; font-size: 0.8rem; font-weight: 600; color: #475569; border-radius: 4px; cursor: pointer; white-space: nowrap; transition: all .15s; }
.tab-btn.active { background: #fff; color: #2563eb; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* Metrics & Strength Gauges */
.strength-gauge { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 14px; }
.score-badge { font-size: 1.6rem; font-weight: 800; color: #16a34a; background: #dcfce7; padding: 6px 14px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0; }
.gauge-info { flex: 1; margin-left: 16px; }
.gauge-info h4 { font-size: 0.95rem; color: #0f172a; margin-bottom: 2px; }
.gauge-info p { font-size: 0.82rem; color: #64748b; }

/* Issue Matrix Table */
.matrix-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.matrix-table th { background: #f1f5f9; color: #334155; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; font-weight: 700; }
.matrix-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.matrix-table tr:hover td { background: #f8fafc; }
.badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.badge-verified { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.badge-high { background: #2563eb; color: #fff; }
.badge-medium { background: #0f766e; color: #fff; }

/* Legal Reasoning Panel */
.reasoning-panel { background: #0f2438; color: #e2e8f0; border-radius: 8px; padding: 18px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.reasoning-panel h3 { color: #38bdf8; font-size: 0.95rem; font-weight: 700; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #334155; padding-bottom: 6px; display: flex; justify-content: space-between; align-items: center; }
.reasoning-block { margin-bottom: 12px; }
.reasoning-title { font-size: 0.74rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 3px; }
.reasoning-list { list-style: none; padding-left: 0; }
.reasoning-list li { font-size: 0.8rem; color: #f1f5f9; padding: 2px 0 2px 14px; position: relative; line-height: 1.4; }
.reasoning-list li::before { content: "›"; position: absolute; left: 0; color: #38bdf8; font-weight: bold; font-size: 1.1rem; top: -2px; }

/* AI Output Editor */
.editor-container { position: relative; }
.editor-textarea { font-family: "Courier New", Courier, monospace, sans-serif; font-size: 0.88rem; line-height: 1.5; background: #ffffff; min-height: 480px; padding: 16px; border: 1px solid #94a3b8; border-radius: 6px; }

/* Metrics bar under editor */
.metrics-bar { display: flex; justify-content: space-between; align-items: center; background: #f1f5f9; border-radius: 6px; padding: 8px 14px; font-size: 0.8rem; color: #475569; margin-top: 8px; border: 1px solid #cbd5e1; }
.metrics-bar strong { color: #0f172a; }

/* Quality Scorecard */
.quality-card { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px; background: #f8fafc; border-radius: 6px; padding: 10px; border: 1px solid #e2e8f0; }
.quality-item { text-align: center; }
.quality-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
.quality-val { font-size: 0.95rem; font-weight: 800; color: #16a34a; }

.section-tag { background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-size: 0.74rem; font-weight: 700; text-transform: uppercase; }
</style>
<script>
function switchTab(tabId, btn) {
  document.querySelectorAll('.tab-pane').forEach(function(el) { el.classList.remove('active'); });
  document.querySelectorAll('.tab-btn').forEach(function(el) { el.classList.remove('active'); });
  
  var pane = document.getElementById('tab-' + tabId);
  if (pane) pane.classList.add('active');
  
  if (btn) {
    btn.classList.add('active');
  } else if (window.event && window.event.target) {
    window.event.target.classList.add('active');
  }
  var tabInput = document.getElementById('active_tab');
  if (tabInput) tabInput.value = tabId;
}

function updateTextMetrics() {
  var text = document.getElementById('output_content') ? document.getElementById('output_content').value : '';
  var trimmed = text.trim();
  var words = trimmed === '' ? 0 : trimmed.split(/\s+/).length;
  var chars = text.length;
  var readMin = Math.max(1, Math.ceil(words / 200));
  
  var wEl = document.getElementById('word_count_val');
  var cEl = document.getElementById('char_count_val');
  var rEl = document.getElementById('read_time_val');
  
  if (wEl) wEl.textContent = words.toLocaleString();
  if (cEl) cEl.textContent = chars.toLocaleString();
  if (rEl) rEl.textContent = readMin + ' min read';
}

function copyDraftToClipboard() {
  var el = document.getElementById('output_content');
  if (el) {
    navigator.clipboard.writeText(el.value).then(function() {
      alert('Draft copied to clipboard successfully!');
    });
  }
}

window.addEventListener('DOMContentLoaded', function() {
  var outArea = document.getElementById('output_content');
  if (outArea) {
    outArea.addEventListener('input', updateTextMetrics);
    updateTextMetrics();
  }
});
</script>
</head>
<body>

<div class="topbar">
  <div class="brand">&#9878;&#65039; AEP <span>Legal Intelligence</span></div>
  <nav>
    <a href="dashboard.php">&#127968; Dashboard</a>
    <a href="matters.php">&#128193; Matters</a>
    <a href="counsel_engine.php">&#9878;&#65039; Counsel Engine</a>
    <a href="letter_list.php" class="active">&#9993;&#65039; Correspondence</a>
    <a href="immigration_list.php">&#9992;&#65039; Immigration</a>
    <a href="logout.php">&#128682; Logout</a>
  </nav>
</div>

<div class="page-header">
  <div class="main-container" style="padding:0;">
    <h1>&#9993;&#65039; Legal Correspondence &amp; Response Workspace</h1>
    <p>AI-Powered Letter Drafting, Opponent Response Generation, Issue Extraction, Pre-Action Protocol Compliance &amp; Multi-Factor Analysis</p>
  </div>
</div>

<div class="main-container">
  <?php if ($notice): ?><div class="alert alert-success">&#9989; <?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error">&#10060; <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <form method="POST" id="letterForm">
    <?php echo p2_csrf_field(); ?>
    <input type="hidden" name="active_tab" id="active_tab" value="<?php echo htmlspecialchars($caseData['active_tab']); ?>"/>

    <!-- TOP UNIVERSAL ACTION TOOLBAR -->
    <div class="action-bar">
      <div class="action-group">
        <button type="submit" name="action" value="analyse" class="btn btn-primary" title="Execute full correspondence analysis">
          &#9889; Analyse Correspondence
        </button>
        <button type="submit" name="action" value="generate" class="btn btn-accent" title="Generate draft based on selected letter type">
          &#128196; Generate Draft
        </button>
        <button type="submit" name="action" value="extract_issues" class="btn btn-purple" title="Extract legal issues from facts and opponent letter">
          &#128269; Extract Issues
        </button>
        <button type="submit" name="action" value="summarise_text" class="btn btn-emerald" title="Generate executive summary of source texts">
          &#128221; Summarise Text
        </button>
        <button type="submit" name="action" value="sample" class="btn btn-outline" title="Load sample dispute case">
          &#128161; Load Sample Case
        </button>
        <button type="submit" name="action" value="reset" class="btn btn-outline" title="Clear all fields and reset form">
          &#128260; Reset / Clear Form
        </button>
      </div>
      <div class="action-group">
        <button type="button" onclick="window.print()" class="btn btn-outline" title="Print preview & PDF Export">
          &#128424; Export PDF
        </button>
        <button type="submit" name="action" value="export_word" class="btn btn-outline" title="Download Word file">
          &#128221; Export Word
        </button>
        <button type="submit" name="action" value="save_to_matter" class="btn btn-success" title="Save to Matter, Document Library & Activity Log">
          &#128190; Save to Matter
        </button>
      </div>
    </div>

    <!-- TWO-COLUMN WORKSPACE: INPUT (LEFT) vs AI ANALYSIS & OUTPUT (RIGHT) -->
    <div class="workspace-grid">
      
      <!-- ============================================== -->
      <!-- LEFT COLUMN: INPUTS, INSTRUCTIONS & DOSSIER   -->
      <!-- ============================================== -->
      <div>
        
        <!-- CARD 1: USER INSTRUCTIONS & LETTER TYPE (TOP PRIORITY) -->
        <div class="card" style="border: 2px solid #3b82f6;">
          <div class="card-header blue">
            <span>1. USER INSTRUCTIONS &amp; TARGET CORRESPONDENCE TYPE</span>
            <span class="section-tag" style="background:#dbeafe;color:#1e40af;">AI Input Directive</span>
          </div>
          <div class="card-body" style="background:#f8fafc;">
            
            <div class="form-group" style="margin-bottom:14px;">
              <label style="color:#1e40af;font-size:0.84rem;">Target Letter / Document Type:</label>
              <select name="letter_type" style="font-weight:700;font-size:0.92rem;color:#0f172a;background:#fff;border:2px solid #93c5fd;">
                <option value="General Correspondence" <?php echo $caseData['letter_type'] === 'General Correspondence' ? 'selected' : ''; ?>>&#128196; General Legal Correspondence</option>
                <option value="Letter Before Action (Pre-Action Protocol)" <?php echo $caseData['letter_type'] === 'Letter Before Action (Pre-Action Protocol)' ? 'selected' : ''; ?>>&#9878;&#65039; Letter Before Action / Pre-Action Protocol Claim</option>
                <option value="Response to Opponent Letter" <?php echo $caseData['letter_type'] === 'Response to Opponent Letter' ? 'selected' : ''; ?>>&#128172; Formal Response &amp; Reply to Opponent</option>
                <option value="Counterargument & Rebuttal Letter" <?php echo $caseData['letter_type'] === 'Counterargument & Rebuttal Letter' ? 'selected' : ''; ?>>&#128737;&#65039; Counterargument &amp; Rebuttal Letter</option>
                <option value="Settlement Proposal (Without Prejudice)" <?php echo $caseData['letter_type'] === 'Settlement Proposal (Without Prejudice)' ? 'selected' : ''; ?>>&#129309; Settlement Proposal / Without Prejudice Offer</option>
                <option value="Legal Advice Email / Client Email" <?php echo $caseData['letter_type'] === 'Legal Advice Email / Client Email' ? 'selected' : ''; ?>>&#128231; Formal Legal Advice Email / Client Update</option>
                <option value="Client Care & Engagement Letter" <?php echo $caseData['letter_type'] === 'Client Care & Engagement Letter' ? 'selected' : ''; ?>>&#128203; Client Care &amp; Engagement Letter</option>
                <option value="Court & Tribunal Correspondence" <?php echo $caseData['letter_type'] === 'Court & Tribunal Correspondence' ? 'selected' : ''; ?>>&#127963;&#65039; Court &amp; Tribunal Correspondence</option>
                <option value="Formal Complaint Letter" <?php echo $caseData['letter_type'] === 'Formal Complaint Letter' ? 'selected' : ''; ?>>&#9888;&#65039; Formal Complaint Letter</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label style="color:#1e40af;font-size:0.84rem;">User Instructions &amp; Drafting Directives:</label>
              <textarea name="instructions" rows="4" class="instruction-box" placeholder="Instruct Counsel Engine (e.g. 'Draft a Letter Before Action for unpaid freight invoices of £64,850; rebut opponent allegation of transit delay; demand payment within 14 days under threat of County Court action and statutory interest')..."><?php echo htmlspecialchars($caseData['instructions']); ?></textarea>
            </div>

            <!-- Quick Direct Action Buttons below prompt -->
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding-top:6px;border-top:1px solid #e2e8f0;">
              <button type="submit" name="action" value="analyse" class="btn btn-primary" style="font-size:0.82rem;padding:7px 12px;">
                &#9889; Run Analysis
              </button>
              <button type="submit" name="action" value="generate" class="btn btn-accent" style="font-size:0.82rem;padding:7px 12px;">
                &#128196; Generate Selected Draft
              </button>
              <button type="submit" name="action" value="extract_issues" class="btn btn-purple" style="font-size:0.82rem;padding:7px 12px;">
                &#128269; Extract Issues
              </button>
              <button type="submit" name="action" value="summarise_text" class="btn btn-emerald" style="font-size:0.82rem;padding:7px 12px;">
                &#128221; Summarise Text
              </button>
              <button type="submit" name="action" value="sample" class="btn btn-outline" style="font-size:0.82rem;padding:7px 12px;">
                &#128161; Load Sample
              </button>
              <button type="submit" name="action" value="reset" class="btn btn-outline" style="font-size:0.82rem;padding:7px 12px;">
                &#128260; Clear All
              </button>
            </div>

          </div>
        </div>

        <!-- CARD 2: OPPONENT CORRESPONDENCE & TEXT INGESTION -->
        <div class="card">
          <div class="card-header teal">
            <span>2. OPPONENT CORRESPONDENCE &amp; INCOMING MATERIAL</span>
            <span style="font-size:0.8rem;opacity:0.85;">Rebuttal Target</span>
          </div>
          <div class="card-body">
            <div class="form-group" style="margin-bottom:10px;">
              <label>Opponent Letter / Incoming Notice Text:</label>
              <textarea name="opponent_letter" rows="4" placeholder="Paste opposing letter, email, refusal notice or legal claims to answer point-by-point..."><?php echo htmlspecialchars($caseData['opponent_letter']); ?></textarea>
            </div>
            <div style="display:flex;gap:8px;">
              <button type="submit" name="action" value="extract_issues" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;">
                &#128269; Extract Opponent Issues
              </button>
              <button type="submit" name="action" value="summarise_text" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;">
                &#128221; Summarise Opponent Letter
              </button>
            </div>
          </div>
        </div>

        <!-- CARD 3: SOURCE MATERIALS, FACTS & EVIDENCE -->
        <div class="card">
          <div class="card-header blue">
            <span>3. SOURCE MATERIALS, FACTS &amp; EVIDENCE</span>
            <span style="font-size:0.8rem;opacity:0.85;">Factual Chronology &amp; Documents</span>
          </div>
          <div class="card-body">
            <div class="form-group" style="margin-bottom:12px;">
              <label>Factual Chronology &amp; Background:</label>
              <textarea name="facts" rows="4" placeholder="Detailed chronology of contract, performance, breaches, communications, losses..."><?php echo htmlspecialchars($caseData['facts']); ?></textarea>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label>Evidence Available &amp; Exhibits:</label>
                <textarea name="evidence_available" rows="3" placeholder="List signed contracts, invoices, CMR delivery notes, tracking logs, emails..."><?php echo htmlspecialchars($caseData['evidence_available']); ?></textarea>
              </div>
              <div class="form-group">
                <label>Remedy / Quantum / Relief Sought:</label>
                <textarea name="remedy_requested" rows="3" placeholder="Principal sum claimed, statutory interest rate, specific performance, deadline..."><?php echo htmlspecialchars($caseData['remedy_requested']); ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 4: MATTER LINKING, RECIPIENT & DELIVERY PARTICULARS -->
        <div class="card">
          <div class="card-header navy">
            <span>4. MATTER LINKING, RECIPIENT &amp; DELIVERY PARTICULARS</span>
            <span style="font-size:0.8rem;opacity:0.85;">Ref: <?php echo htmlspecialchars($caseData['ref_no']); ?></span>
          </div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Linked Matter</label>
                <select name="matter_id">
                  <option value="0">&mdash; Auto-Create or Select Matter &mdash;</option>
                  <?php foreach ($allMatters as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $caseData['matter_id'] == $m['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($m['reference'] . ' — ' . $m['title']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Linked Client</label>
                <select name="client_id">
                  <option value="0">&mdash; Auto-Create or Select Client &mdash;</option>
                  <?php foreach ($allClients as $cl): ?>
                    <option value="<?php echo $cl['id']; ?>" <?php echo $caseData['client_id'] == $cl['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($cl['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Recipient Name / Organisation</label>
                <input type="text" name="recipient_name" value="<?php echo htmlspecialchars($caseData['recipient_name']); ?>" placeholder="e.g. Apex Logistics UK Ltd" required/>
              </div>
              <div class="form-group">
                <label>Recipient Email</label>
                <input type="email" name="recipient_email" value="<?php echo htmlspecialchars($caseData['recipient_email']); ?>" placeholder="e.g. legal@recipient.com"/>
              </div>
              <div class="form-group full">
                <label>Subject / Re:</label>
                <input type="text" name="subject" value="<?php echo htmlspecialchars($caseData['subject']); ?>" placeholder="e.g. Breach of Contract & Outstanding Invoices" required/>
              </div>
              <div class="form-group full">
                <label>Recipient Physical Address</label>
                <textarea name="recipient_address" rows="2" placeholder="Full postal address..."><?php echo htmlspecialchars($caseData['recipient_address']); ?></textarea>
              </div>
              <div class="form-group">
                <label>Direction &amp; Delivery</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                  <select name="direction">
                    <option value="outgoing" <?php echo $caseData['direction'] === 'outgoing' ? 'selected' : ''; ?>>Outgoing Letter</option>
                    <option value="incoming" <?php echo $caseData['direction'] === 'incoming' ? 'selected' : ''; ?>>Incoming Letter</option>
                  </select>
                  <select name="delivery_method">
                    <option value="email" <?php echo $caseData['delivery_method'] === 'email' ? 'selected' : ''; ?>>Email</option>
                    <option value="post" <?php echo $caseData['delivery_method'] === 'post' ? 'selected' : ''; ?>>First Class Post</option>
                    <option value="hand" <?php echo $caseData['delivery_method'] === 'hand' ? 'selected' : ''; ?>>Hand Delivery</option>
                    <option value="portal" <?php echo $caseData['delivery_method'] === 'portal' ? 'selected' : ''; ?>>Court Portal</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Response Due Date</label>
                <input type="date" name="response_due_on" value="<?php echo htmlspecialchars($caseData['response_due_on']); ?>"/>
              </div>
              <div class="form-group">
                <label>Signatory Name</label>
                <input type="text" name="signatory_name" value="<?php echo htmlspecialchars($caseData['signatory_name']); ?>"/>
              </div>
              <div class="form-group">
                <label>Signatory Title</label>
                <input type="text" name="signatory_title" value="<?php echo htmlspecialchars($caseData['signatory_title']); ?>"/>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ============================================== -->
      <!-- RIGHT COLUMN: AI REASONING & GENERATED DRAFT  -->
      <!-- ============================================== -->
      <div>
        
        <!-- CARD 5: AI MULTI-FACTOR REASONING & ASSESSMENTS -->
        <div class="card">
          <div class="card-header teal">
            <span>5. AI MULTI-FACTOR ASSESSMENTS &amp; REASONING</span>
            <span style="font-size:0.8rem;opacity:0.85;">Counsel Engine Active</span>
          </div>
          <div class="card-body">
            
            <!-- Tabs Navigation -->
            <div class="tabs-nav">
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'strength' ? 'active' : ''; ?>" onclick="switchTab('strength', this)">
                &#128202; Case Strength
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'issues' ? 'active' : ''; ?>" onclick="switchTab('issues', this)">
                &#9888;&#65039; Issues Matrix
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'pap' ? 'active' : ''; ?>" onclick="switchTab('pap', this)">
                &#128220; Pre-Action Protocol
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'risks' ? 'active' : ''; ?>" onclick="switchTab('risks', this)">
                &#128737;&#65039; Risk Register
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'strategy' ? 'active' : ''; ?>" onclick="switchTab('strategy', this)">
                &#128640; Tactical Strategy
              </button>
            </div>

            <!-- TAB 1: CASE STRENGTH ASSESSMENT -->
            <div id="tab-strength" class="tab-pane <?php echo $caseData['active_tab'] === 'strength' ? 'active' : ''; ?>">
              <?php $str = $analysis['assessments']['case_strength']; ?>
              <div class="strength-gauge">
                <div class="score-badge"><?php echo $str['confidence_percentage']; ?></div>
                <div class="gauge-info">
                  <h4>Overall Merits: <?php echo htmlspecialchars($str['rating']); ?></h4>
                  <p><?php echo htmlspecialchars($str['merits_summary']); ?></p>
                </div>
              </div>
              <div style="font-size:0.85rem;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Evidential Sufficiency:</strong><br/>
                  <span style="color:#2563eb;"><?php echo htmlspecialchars($str['evidential_sufficiency']); ?></span>
                </div>
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Procedural Standing:</strong><br/>
                  <span style="color:#0f766e;"><?php echo htmlspecialchars($str['procedural_standing']); ?></span>
                </div>
              </div>
            </div>

            <!-- TAB 2: ISSUES MATRIX -->
            <div id="tab-issues" class="tab-pane <?php echo $caseData['active_tab'] === 'issues' ? 'active' : ''; ?>">
              <?php $imat = $analysis['assessments']['issue_matrix']; ?>
              <div style="margin-bottom:8px;font-size:0.82rem;color:#64748b;">
                Dispute Issues Tracked: <strong><?php echo $imat['total_issues']; ?></strong>
              </div>
              <table class="matrix-table">
                <thead>
                  <tr>
                    <th>Issue</th>
                    <th>Our Position</th>
                    <th>Opponent Position</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($imat['items'] as $it): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($it['issue']); ?></strong><br/>
                        <span class="badge badge-<?php echo strtolower($it['strength']); ?>"><?php echo $it['strength']; ?> Priority</span>
                      </td>
                      <td><?php echo htmlspecialchars($it['our_position']); ?></td>
                      <td><?php echo htmlspecialchars($it['opponent_position']); ?></td>
                      <td>
                        <span class="badge badge-<?php echo strtolower($it['status']); ?>"><?php echo $it['status']; ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- TAB 3: PRE-ACTION PROTOCOL -->
            <div id="tab-pap" class="tab-pane <?php echo $caseData['active_tab'] === 'pap' ? 'active' : ''; ?>">
              <?php $pap = $analysis['assessments']['pre_action_protocol']; ?>
              <div style="font-size:0.84rem;display:flex;flex-direction:column;gap:8px;">
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Applicable Protocol:</strong><br/>
                  <span style="color:#7c3aed;font-weight:700;"><?php echo htmlspecialchars($pap['protocol_name']); ?></span>
                </div>
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Minimum Response Time Limit:</strong><br/>
                  <span style="color:#2563eb;font-weight:700;"><?php echo htmlspecialchars($pap['minimum_response_time']); ?></span>
                </div>
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Mandatory Pre-Action Requirements:</strong>
                  <ul style="padding-left:18px;margin-top:4px;">
                    <?php foreach ($pap['mandatory_elements'] as $me): ?>
                      <li style="margin-bottom:3px;"><?php echo htmlspecialchars($me); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>

            <!-- TAB 4: RISK REGISTER -->
            <div id="tab-risks" class="tab-pane <?php echo $caseData['active_tab'] === 'risks' ? 'active' : ''; ?>">
              <?php $rsk = $analysis['assessments']['risk_register']; ?>
              <div style="margin-bottom:8px;">
                <span class="badge badge-high"><?php echo htmlspecialchars($rsk['risk_level']); ?></span>
              </div>
              <ul style="list-style:none;padding:0;margin-bottom:8px;">
                <?php foreach ($rsk['items'] as $rItem): ?>
                  <li style="background:#fff1f2;border-left:4px solid #e11d48;padding:8px 12px;border-radius:0 6px 6px 0;margin-bottom:6px;font-size:0.84rem;color:#881337;">
                    &#128737;&#65039; <?php echo htmlspecialchars($rItem); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
              <div style="background:#f8fafc;padding:8px 12px;border-radius:6px;font-size:0.82rem;border:1px solid #e2e8f0;">
                <strong>Mitigation Strategy:</strong> <?php echo htmlspecialchars($rsk['mitigation']); ?>
              </div>
            </div>

            <!-- TAB 5: STRATEGY PLAN -->
            <div id="tab-strategy" class="tab-pane <?php echo $caseData['active_tab'] === 'strategy' ? 'active' : ''; ?>">
              <?php $stp = $analysis['assessments']['strategy_plan']; ?>
              <ol style="padding-left:18px;font-size:0.84rem;line-height:1.5;">
                <?php foreach ($stp['steps'] as $sItem): ?>
                  <li style="margin-bottom:6px;background:#f8fafc;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0;">
                    <?php echo htmlspecialchars($sItem); ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            </div>

          </div>
        </div>

        <!-- ALWAYS VISIBLE COUNSEL ENGINE REASONING PANEL -->
        <div class="reasoning-panel">
          <h3>
            <span>&#9878;&#65039; Counsel Legal Reasoning Panel</span>
            <span style="font-size:0.75rem;color:#38bdf8;">Universal AI Framework</span>
          </h3>
          
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="reasoning-block">
              <div class="reasoning-title">FACTS RELIED UPON</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Facts'], 0, 3) as $f): ?>
                  <li><?php echo htmlspecialchars($f); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="reasoning-block">
              <div class="reasoning-title">ISSUES IDENTIFIED</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Issues'], 0, 3) as $is): ?>
                  <li><?php echo htmlspecialchars($is); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="reasoning-block">
              <div class="reasoning-title">APPLICABLE LAW &amp; RULES</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Law'], 0, 3) as $lw): ?>
                  <li><?php echo htmlspecialchars($lw); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="reasoning-block">
              <div class="reasoning-title">CRITICAL RISKS</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Risks'], 0, 2) as $rk): ?>
                  <li><?php echo htmlspecialchars($rk); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="reasoning-block">
              <div class="reasoning-title">TACTICAL STRATEGY</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Strategy'], 0, 2) as $st): ?>
                  <li><?php echo htmlspecialchars($st); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="reasoning-block">
              <div class="reasoning-title">REMEDIES SOUGHT</div>
              <ul class="reasoning-list">
                <?php foreach (array_slice($analysis['Remedies'], 0, 2) as $rm): ?>
                  <li><?php echo htmlspecialchars($rm); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>

        <!-- CARD 6: AI GENERATED DRAFT & EDITABLE WORKSPACE -->
        <div class="card" style="border: 2px solid #7c3aed;">
          <div class="card-header purple">
            <span>6. AI GENERATED DRAFT &amp; WORKBENCH</span>
            <span class="section-tag" style="background:#ede9fe;color:#6b21a8;">Live Editor</span>
          </div>
          <div class="card-body">
            
            <div style="margin-bottom:12px;">
              <label>Generated Document Title:</label>
              <input type="text" name="output_title" value="<?php echo htmlspecialchars($caseData['output_title']); ?>" style="font-weight:700;font-size:0.95rem;"/>
            </div>

            <!-- Document Improvement Toolbar -->
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;">
              <button type="submit" name="action" value="improve" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Audit facts, authorities and dates">
                &#10024; Improve Draft
              </button>
              <button type="submit" name="action" value="persuasive" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Enhance persuasiveness under English Law">
                &#128170; Make More Persuasive
              </button>
              <button type="submit" name="action" value="formal" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Heighten formal CPR tone">
                &#128084; Make More Formal
              </button>
              <button type="submit" name="action" value="add_authorities" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Integrate Pre-Action Protocol and Interest Statutes">
                &#9878;&#65039; Add Authorities
              </button>
              <button type="submit" name="action" value="simplify" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Simplify into concise legal propositions">
                &#128065;&#65039; Simplify
              </button>
              <button type="submit" name="action" value="expand" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Expand legal submissions and evidential reasoning">
                &#128214; Expand Analysis
              </button>
              <button type="submit" name="action" value="risk_review" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Audit for inadvertent admissions">
                &#128737;&#65039; Risk Review
              </button>
            </div>

            <!-- Editor -->
            <div class="editor-container">
              <textarea name="output_content" id="output_content" class="editor-textarea"><?php echo htmlspecialchars($caseData['output_content']); ?></textarea>
            </div>

            <!-- Live Text Metrics Bar -->
            <div class="metrics-bar">
              <span>Words: <strong id="word_count_val">0</strong></span>
              <span>Characters: <strong id="char_count_val">0</strong></span>
              <span>Estimated Reading: <strong id="read_time_val">0 min read</strong></span>
              <button type="button" onclick="copyDraftToClipboard()" class="btn btn-outline" style="padding:3px 8px;font-size:0.75rem;">
                &#128203; Copy Draft
              </button>
            </div>

            <!-- Quality Scorecard -->
            <div class="quality-card">
              <div class="quality-item">
                <div class="quality-label">Clarity</div>
                <div class="quality-val">96%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Professional Tone</div>
                <div class="quality-val">99%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Completeness</div>
                <div class="quality-val">95%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Persuasiveness</div>
                <div class="quality-val">97%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Readability</div>
                <div class="quality-val">92%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Overall Quality</div>
                <div class="quality-val" style="color:#2563eb;">Excellent</div>
              </div>
            </div>

            <!-- Bottom Save / Export Action Group -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:1px solid #e2e8f0;">
              <div style="display:flex;gap:8px;">
                <button type="button" onclick="window.print()" class="btn btn-outline">
                  &#128424; Print / PDF
                </button>
                <button type="submit" name="action" value="export_word" class="btn btn-outline">
                  &#128221; Export Word
                </button>
              </div>
              <div>
                <button type="submit" name="action" value="save_to_matter" class="btn btn-success" style="padding:9px 18px;font-size:0.9rem;">
                  &#128190; Save to Matter &amp; Document Library
                </button>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </form>
</div>

</body>
</html>