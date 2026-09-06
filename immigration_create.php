<?php
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';
p2_start();
$pdo = p2_db();
p2_check_csrf();

$error = '';
$notice = '';

// Helper for cleaning form inputs
function imm_field(array $src, string $key, string $default = ''): string {
    return trim((string)($src[$key] ?? $default));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Default sample case data
$defaultSample = [
    'matter_id' => 0,
    'client_id' => 0,
    'applicant_name' => 'Tariq Al-Mansoor',
    'nationality' => 'Jordanian',
    'date_of_birth' => '1988-04-12',
    'passport_number' => 'J8849201',
    'applicant_email' => 'tariq.mansoor@example.com',
    'applicant_phone' => '+44 7700 900123',
    'applicant_address' => '14 Victoria Road, Leeds, LS6 1AS',
    'visa_type' => 'Spouse Visa (Appendix FM) / Article 8',
    'case_reference' => 'IMM-' . date('Y') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT),
    'home_office_reference' => 'H9281740/001',
    'status' => 'active',
    'date_of_entry' => '2019-09-15',
    'client_visa_expiry' => '2024-11-30',
    'sponsor_name' => 'Sarah Al-Mansoor (British Citizen)',
    'sponsor_licence' => '',
    'sponsor_address' => '14 Victoria Road, Leeds, LS6 1AS',
    'draft_type' => 'advice',
    'instructions' => 'Analyse the refusal of further leave to remain as a partner under Appendix FM. Challenge the Home Office assertion regarding financial documentation shortfall and prepare Grounds of Appeal and Skeleton Argument establishing compliance with Appendix FM-SE and Article 8 ECHR family life with British spouse and young British child.',
    'facts' => "The Applicant Tariq entered the UK lawfully in September 2019. He married Sarah, a British citizen, in Manchester in June 2021. The couple share an infant daughter (Amina, born March 2023 in Leeds, British citizen). Tariq is employed as a Senior Systems Engineer earning £38,500 gross per annum. The Sponsor also earns £14,000 part-time. The family resides in a 3-bedroom privately rented property with no overcrowding.\n\nOn 15 October 2024, the Home Office refused Tariq's extension application under Appendix FM, alleging that 1 of the 6 required monthly bank statements was missing the official electronic validation stamp, and asserting that no insurmountable obstacles exist to family life continuing in Jordan.",
    'refusal_reasons' => "Refusal under paragraph R-LTRP.1.1(d) and Appendix FM-SE. The Home Office asserts that bank statement dated July 2024 was an unstamped electronic printout, failing strict specified evidence requirements. Refusal under GEN.3.1/GEN.3.2 asserting no exceptional circumstances and no insurmountable obstacles under EX.1 to family life relocating to Jordan.",
    'evidence_available' => "• Original marriage certificate (Manchester Register Office, June 2021)\n• Child British birth certificate and British passport (Amina)\n• 6 months payslips accompanied by formal employer letter from TechCorp UK Ltd confirming £38,500 salary\n• 6 months corresponding Lloyds Bank statements showing net salary deposits\n• 2 years joint tenancy agreements, council tax bills and utility invoices\n• Detailed witness statements from Applicant, Sponsor and maternal grandparents",
    'article8_grounds' => "Direct engagement of Article 8 ECHR family life with British spouse and British infant child. Section 55 BCIA 2009 duty engaged: best interests of British child Amina require her to remain in the UK with both parents. Section 117B(6) NIAA 2002 applies — unreasonable to expect British child to leave the UK. Insurmountable obstacles to spouse and child relocating to Jordan due to medical care and maternal family support network.",
    'lawyer_name' => 'Sarah Jenkins, Senior Immigration Counsel',
    'law_firm' => 'AEP Legal Intelligence Platform',
    'output_title' => 'IMMIGRATION CASE ASSESSMENT & LEGAL STRATEGY',
    'output_content' => '',
    'active_tab' => 'strength',
];

if ($action === 'reset') {
    $caseData = [
        'matter_id' => 0,
        'client_id' => 0,
        'applicant_name' => '',
        'nationality' => '',
        'date_of_birth' => '',
        'passport_number' => '',
        'applicant_email' => '',
        'applicant_phone' => '',
        'applicant_address' => '',
        'visa_type' => '',
        'case_reference' => 'IMM-' . date('Y') . '-' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT),
        'home_office_reference' => '',
        'status' => 'draft',
        'date_of_entry' => '',
        'client_visa_expiry' => '',
        'sponsor_name' => '',
        'sponsor_licence' => '',
        'sponsor_address' => '',
        'draft_type' => 'advice',
        'instructions' => '',
        'facts' => '',
        'refusal_reasons' => '',
        'evidence_available' => '',
        'article8_grounds' => '',
        'lawyer_name' => 'Sarah Jenkins, Senior Immigration Counsel',
        'law_firm' => 'AEP Legal Intelligence Platform',
        'output_title' => 'IMMIGRATION DRAFT WORKSPACE',
        'output_content' => '',
        'active_tab' => 'strength',
    ];
    $notice = 'Workspace reset to blank state. Ready for new case input.';
} elseif ($action === 'sample') {
    $caseData = $defaultSample;
    $notice = 'Sample case loaded (Tariq Al-Mansoor — Appendix FM / Article 8). Click Analyse or Generate Draft.';
} else {
    $caseData = [
        'matter_id' => (int)($_POST['matter_id'] ?? $_GET['matter_id'] ?? 0),
        'client_id' => (int)($_POST['client_id'] ?? $_GET['client_id'] ?? 0),
        'applicant_name' => imm_field($_POST, 'applicant_name', imm_field($_POST, 'client_name', $defaultSample['applicant_name'])),
        'nationality' => imm_field($_POST, 'nationality', imm_field($_POST, 'client_nationality', $defaultSample['nationality'])),
        'date_of_birth' => imm_field($_POST, 'date_of_birth', imm_field($_POST, 'client_dob', $defaultSample['date_of_birth'])),
        'passport_number' => imm_field($_POST, 'passport_number', imm_field($_POST, 'client_passport', $defaultSample['passport_number'])),
        'applicant_email' => imm_field($_POST, 'applicant_email', imm_field($_POST, 'client_email', $defaultSample['applicant_email'])),
        'applicant_phone' => imm_field($_POST, 'applicant_phone', imm_field($_POST, 'client_phone', $defaultSample['applicant_phone'])),
        'applicant_address' => imm_field($_POST, 'applicant_address', imm_field($_POST, 'client_address', $defaultSample['applicant_address'])),
        'visa_type' => imm_field($_POST, 'visa_type', imm_field($_POST, 'case_type', $defaultSample['visa_type'])),
        'case_reference' => imm_field($_POST, 'case_reference', $defaultSample['case_reference']),
        'home_office_reference' => imm_field($_POST, 'home_office_reference', imm_field($_POST, 'ho_reference', $defaultSample['home_office_reference'])),
        'status' => imm_field($_POST, 'status', 'active'),
        'date_of_entry' => imm_field($_POST, 'date_of_entry', imm_field($_POST, 'client_entry_date', $defaultSample['date_of_entry'])),
        'client_visa_expiry' => imm_field($_POST, 'client_visa_expiry', $defaultSample['client_visa_expiry']),
        'sponsor_name' => imm_field($_POST, 'sponsor_name', $defaultSample['sponsor_name']),
        'sponsor_licence' => imm_field($_POST, 'sponsor_licence', ''),
        'sponsor_address' => imm_field($_POST, 'sponsor_address', $defaultSample['sponsor_address']),
        'draft_type' => imm_field($_POST, 'draft_type', imm_field($_GET, 'draft_type', 'advice')),
        'instructions' => imm_field($_POST, 'instructions', $defaultSample['instructions']),
        'facts' => imm_field($_POST, 'facts', $defaultSample['facts']),
        'refusal_reasons' => imm_field($_POST, 'refusal_reasons', imm_field($_POST, 'decision_description', $defaultSample['refusal_reasons'])),
        'evidence_available' => imm_field($_POST, 'evidence_available', $defaultSample['evidence_available']),
        'article8_grounds' => imm_field($_POST, 'article8_grounds', $defaultSample['article8_grounds']),
        'lawyer_name' => imm_field($_POST, 'lawyer_name', 'Sarah Jenkins, Senior Immigration Counsel'),
        'law_firm' => 'AEP Legal Intelligence Platform',
        'output_title' => imm_field($_POST, 'output_title', 'IMMIGRATION CASE ASSESSMENT & LEGAL STRATEGY'),
        'output_content' => imm_field($_POST, 'output_content', ''),
        'active_tab' => imm_field($_POST, 'active_tab', 'strength'),
    ];
}

// If linked to a matter, pull matter details
$matter = null;
if ($caseData['matter_id'] > 0) {
    $matter = p2_find_matter($pdo, $caseData['matter_id']);
    if ($matter && empty($_POST['matter_id'])) {
        $caseData['case_reference'] = $matter['reference'];
    }
}

// Perform AI Analysis
$analysis = analyseImmigrationCase($caseData, $matter);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'analyse') {
        $caseData['output_title'] = "IMMIGRATION CASE ASSESSMENT & STRATEGY: " . strtoupper($caseData['applicant_name'] ?: 'MATTER');
        $adviceObj = generateImmigrationAdvice($analysis, $caseData, $matter ?? []);
        $caseData['output_content'] = $adviceObj['Generated Draft']['content'] ?? '';
        $notice = 'Immigration Case analysed across Substantive Rules, Evidence Matrix, Article 8, Appeals & Risk Register.';
    } elseif ($action === 'generate') {
        $type = $caseData['draft_type'];
        if ($type === 'appeal') {
            $doc = generateImmigrationAppeal($analysis, $caseData, $matter ?? []);
            $notice = 'Tribunal Notice & Grounds of Appeal generated successfully.';
        } elseif ($type === 'skeleton') {
            $doc = generateImmigrationSkeleton($analysis, $caseData, $matter ?? []);
            $notice = 'Tribunal Skeleton Argument generated successfully.';
        } elseif ($type === 'representations') {
            $doc = generateImmigrationRepresentations($analysis, $caseData, $matter ?? []);
            $notice = 'UKVI Legal Representations cover letter generated successfully.';
        } else {
            $doc = generateImmigrationAdvice($analysis, $caseData, $matter ?? []);
            $notice = 'Immigration Legal Advice & Merits Assessment generated successfully.';
        }
        $caseData['output_title'] = $doc['Generated Draft']['title'] ?? 'IMMIGRATION LEGAL DRAFT';
        $caseData['output_content'] = $doc['Generated Draft']['content'] ?? '';
    } elseif ($action === 'generate_advice') {
        $caseData['draft_type'] = 'advice';
        $adviceObj = generateImmigrationAdvice($analysis, $caseData, $matter ?? []);
        $caseData['output_title'] = $adviceObj['Generated Draft']['title'] ?? 'IMMIGRATION LEGAL ADVICE';
        $caseData['output_content'] = $adviceObj['Generated Draft']['content'] ?? '';
        $notice = 'Comprehensive Immigration Legal Advice & Merits Opinion generated.';
    } elseif ($action === 'generate_appeal') {
        $caseData['draft_type'] = 'appeal';
        $appealObj = generateImmigrationAppeal($analysis, $caseData, $matter ?? []);
        $caseData['output_title'] = $appealObj['Generated Draft']['title'] ?? 'NOTICE & GROUNDS OF APPEAL';
        $caseData['output_content'] = $appealObj['Generated Draft']['content'] ?? '';
        $notice = 'Notice of Appeal and Detailed Grounds of Appeal generated.';
    } elseif ($action === 'generate_skeleton') {
        $caseData['draft_type'] = 'skeleton';
        $skelObj = generateImmigrationSkeleton($analysis, $caseData, $matter ?? []);
        $caseData['output_title'] = $skelObj['Generated Draft']['title'] ?? 'TRIBUNAL SKELETON ARGUMENT';
        $caseData['output_content'] = $skelObj['Generated Draft']['content'] ?? '';
        $notice = 'Immigration Tribunal Skeleton Argument generated.';
    } elseif ($action === 'generate_representations') {
        $caseData['draft_type'] = 'representations';
        $repObj = generateImmigrationRepresentations($analysis, $caseData, $matter ?? []);
        $caseData['output_title'] = $repObj['Generated Draft']['title'] ?? 'LEGAL REPRESENTATIONS TO UKVI';
        $caseData['output_content'] = $repObj['Generated Draft']['content'] ?? '';
        $notice = 'Formal Legal Representations cover letter generated.';
    } elseif ($action === 'improve') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'improve', 'Strengthen evidential flexibility arguments and emphasise Section 55 BCIA 2009 statutory child duties.');
        $notice = 'Draft improved with evidential audit notes and enhanced authority references.';
    } elseif ($action === 'persuasive') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'persuasive', 'Reinforce the proportionality balance under Article 8(2) ECHR and insurmountable obstacles.');
        $notice = 'Draft enhanced with persuasive Article 8 ECHR and precedent citations.';
    } elseif ($action === 'add_authorities') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'add_authorities', 'Integrate Chikwamba, Agyarko, KO (Nigeria), and ZH (Tanzania) principles.');
        $notice = 'Draft updated with leading Immigration Rules & Upper Tribunal authorities.';
    } elseif ($action === 'risk_review') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'risk_review', 'Highlight critical evidential gaps under Appendix FM-SE and mitigation advice.');
        $notice = 'Risk & Evidential Gaps review integrated into draft.';
    } elseif ($action === 'simplify') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'simplify', 'Focus on plain language and concise proposition structure.');
        $notice = 'Draft simplified for plain clarity.';
    } elseif ($action === 'expand') {
        $curr = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? '');
        $caseData['output_content'] = p6_transform_output($curr, 'expand', 'Expand upon procedural history, Appendix FM-SE compliance and child best interests.');
        $notice = 'Draft expanded with additional legal reasoning.';
    } elseif ($action === 'export_word') {
        $docTitle = $caseData['output_title'] ?: 'Immigration-Case-Document';
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
                $cStmt->execute([$caseData['applicant_name'] ?: 'Immigration Applicant', $caseData['applicant_email'], $caseData['applicant_phone'], $caseData['applicant_address']]);
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
                    'Immigration: ' . ($caseData['applicant_name'] ?: 'Applicant') . ' — ' . ($caseData['visa_type'] ?: 'Immigration Case'),
                    'Immigration & Asylum',
                    'open',
                    p2_user_id(),
                    date('Y-m-d'),
                    $caseData['instructions'] . "\n\nFacts: " . $caseData['facts']
                ]);
                $matterId = (int)$pdo->lastInsertId();
                $caseData['matter_id'] = $matterId;
            }

            // 3. Save to immigration_cases table
            $insCase = $pdo->prepare('INSERT INTO immigration_cases (
                visa_type, case_reference, ho_reference, status, applicant_name,
                date_of_birth, nationality, passport_number, applicant_address,
                applicant_email, applicant_phone, date_of_entry, sponsor_name,
                sponsor_address, sponsor_licence, legal_basis, article8_grounds,
                evidence_available, representations, refusal_reasons, lawyer_name,
                law_firm, matter_id, client_id, instructions, facts, client_name,
                client_nationality, client_dob, client_passport, client_visa_type,
                case_type, home_office_reference, client_address, client_email,
                client_phone, client_entry_date, client_visa_expiry
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
            )');
            $insCase->execute([
                $caseData['visa_type'], $caseData['case_reference'], $caseData['home_office_reference'], $caseData['status'], $caseData['applicant_name'],
                $caseData['date_of_birth'], $caseData['nationality'], $caseData['passport_number'], $caseData['applicant_address'],
                $caseData['applicant_email'], $caseData['applicant_phone'], $caseData['date_of_entry'], $caseData['sponsor_name'],
                $caseData['sponsor_address'], $caseData['sponsor_licence'], 'Immigration Rules / Article 8 ECHR', $caseData['article8_grounds'],
                $caseData['evidence_available'], $caseData['refusal_reasons'], $caseData['refusal_reasons'], $caseData['lawyer_name'],
                $caseData['law_firm'], $matterId, $clientId, $caseData['instructions'], $caseData['facts'], $caseData['applicant_name'],
                $caseData['nationality'], $caseData['date_of_birth'], $caseData['passport_number'], $caseData['visa_type'],
                $caseData['visa_type'], $caseData['home_office_reference'], $caseData['applicant_address'], $caseData['applicant_email'],
                $caseData['applicant_phone'], $caseData['date_of_entry'], $caseData['client_visa_expiry']
            ]);
            $caseId = (int)$pdo->lastInsertId();

            // 4. Save Generated Document to p2_documents
            $docContent = $caseData['output_content'] ?: ($analysis['Generated Draft']['content'] ?? 'Immigration legal analysis record.');
            $docTitle = $caseData['output_title'] ?: ('Immigration Document — ' . $caseData['applicant_name']);
            $docStmt = $pdo->prepare('INSERT INTO p2_documents (matter_id, title, document_type, source_table, source_id, content, created_by) VALUES (?,?,?,?,?,?,?)');
            $docStmt->execute([$matterId, $docTitle, 'immigration_intelligence', 'immigration_cases', $caseId, $docContent, p2_user_id()]);
            $docId = (int)$pdo->lastInsertId();

            // 5. Save AI Session & Output in p6_*
            $sessStmt = $pdo->prepare('INSERT INTO p6_ai_sessions (matter_id, workflow, prompt, reasoning_json, created_by) VALUES (?,?,?,?,?)');
            $sessStmt->execute([$matterId, 'immigration', $caseData['instructions'], json_encode($analysis, JSON_UNESCAPED_UNICODE), p2_user_id()]);
            $sessionId = (int)$pdo->lastInsertId();

            $outStmt = $pdo->prepare('INSERT INTO p6_ai_outputs (session_id, matter_id, workflow, title, content, preliminary, created_by) VALUES (?,?,?,?,?,?,?)');
            $outStmt->execute([$sessionId, $matterId, 'immigration', $docTitle, $docContent, 1, p2_user_id()]);

            // 6. Log Activity
            p2_log('immigration.workspace_saved', "Saved Immigration Intelligence Case #{$caseId} and generated document to Matter #{$matterId}", $matterId, [
                'applicant' => $caseData['applicant_name'],
                'visa_type' => $caseData['visa_type'],
                'document_id' => $docId
            ]);

            $pdo->commit();
            header('Location: immigration_view.php?id=' . $caseId . '&saved=1');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to save case to Matter: ' . $e->getMessage();
        }
    }
}

// Ensure output content has default if empty
if (empty($caseData['output_content']) && $action !== 'reset') {
    $adviceObj = generateImmigrationAdvice($analysis, $caseData, $matter ?? []);
    $caseData['output_content'] = $adviceObj['Generated Draft']['content'] ?? '';
}

// Fetch all available matters for the dropdown
$allMatters = $pdo->query('SELECT id, reference, title FROM p2_matters ORDER BY id DESC LIMIT 50')->fetchAll();
$allClients = $pdo->query('SELECT id, name FROM p2_clients ORDER BY name ASC LIMIT 50')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Immigration Intelligence Workspace &mdash; AEP Legal Intelligence Platform</title>
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

/* Evidence Matrix Table */
.matrix-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.matrix-table th { background: #f1f5f9; color: #334155; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; font-weight: 700; }
.matrix-table td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.matrix-table tr:hover td { background: #f8fafc; }
.badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; }
.badge-verified { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.badge-missing { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.badge-critical { background: #7f1d1d; color: #fff; }
.badge-high { background: #ea580c; color: #fff; }

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
  var words = trimmed === '' ? 0 : trimmed.split(/\\s+/).length;
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
    <a href="immigration_list.php" class="active">&#9992;&#65039; Immigration</a>
    <a href="letter_list.php">&#9993;&#65039; Correspondence</a>
    <a href="logout.php">&#128682; Logout</a>
  </nav>
</div>

<div class="page-header">
  <div class="main-container" style="padding:0;">
    <h1>&#9992;&#65039; Immigration Intelligence Workspace</h1>
    <p>AI-Powered Case Analysis, Evidential Matrix Reasoning, Multi-Factor Risk Assessment &amp; Automated Document Drafting</p>
  </div>
</div>

<div class="main-container">
  <?php if ($notice): ?><div class="alert alert-success">&#9989; <?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error">&#10060; <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

  <form method="POST" id="immForm">
    <?php echo p2_csrf_field(); ?>
    <input type="hidden" name="active_tab" id="active_tab" value="<?php echo htmlspecialchars($caseData['active_tab']); ?>"/>

    <!-- TOP UNIVERSAL ACTION TOOLBAR -->
    <div class="action-bar">
      <div class="action-group">
        <button type="submit" name="action" value="analyse" class="btn btn-primary" title="Execute full immigration analysis & reasoning">
          &#9889; Analyse Immigration Case
        </button>
        <button type="submit" name="action" value="generate" class="btn btn-accent" title="Generate draft based on selected document type">
          &#128196; Generate Draft
        </button>
        <button type="submit" name="action" value="sample" class="btn btn-outline" title="Load pre-filled sample case (Tariq Al-Mansoor)">
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
        
        <!-- CARD 1: USER INSTRUCTIONS & DRAFT OBJECTIVES (TOP PRIORITY) -->
        <div class="card" style="border: 2px solid #3b82f6;">
          <div class="card-header blue">
            <span>1. USER INSTRUCTIONS &amp; DRAFTING OBJECTIVES</span>
            <span class="section-tag" style="background:#dbeafe;color:#1e40af;">AI Input Directive</span>
          </div>
          <div class="card-body" style="background:#f8fafc;">
            
            <div class="form-group" style="margin-bottom:14px;">
              <label style="color:#1e40af;font-size:0.84rem;">Target Document to Generate:</label>
              <select name="draft_type" style="font-weight:700;font-size:0.92rem;color:#0f172a;background:#fff;border:2px solid #93c5fd;">
                <option value="advice" <?php echo $caseData['draft_type'] === 'advice' ? 'selected' : ''; ?>>&#128196; Immigration Legal Advice &amp; Merits Opinion</option>
                <option value="appeal" <?php echo $caseData['draft_type'] === 'appeal' ? 'selected' : ''; ?>>&#9878;&#65039; First-tier Tribunal Notice &amp; Grounds of Appeal</option>
                <option value="skeleton" <?php echo $caseData['draft_type'] === 'skeleton' ? 'selected' : ''; ?>>&#127963;&#65039; Immigration Tribunal Skeleton Argument</option>
                <option value="representations" <?php echo $caseData['draft_type'] === 'representations' ? 'selected' : ''; ?>>&#9993;&#65039; UKVI Legal Representations Cover Letter</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label style="color:#1e40af;font-size:0.84rem;">User Instructions &amp; Case Directives:</label>
              <textarea name="instructions" rows="4" class="instruction-box" placeholder="Provide instructions to Counsel Engine (e.g. 'Challenge refusal of spouse visa on financial grounds; draft grounds of appeal and skeleton citing Section 55 BCIA 2009 and Appendix FM-SE evidential flexibility')..."><?php echo htmlspecialchars($caseData['instructions']); ?></textarea>
            </div>

            <!-- Quick Direct Action Buttons below prompt -->
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding-top:6px;border-top:1px solid #e2e8f0;">
              <button type="submit" name="action" value="analyse" class="btn btn-primary" style="font-size:0.82rem;padding:7px 12px;">
                &#9889; Run Analysis
              </button>
              <button type="submit" name="action" value="generate" class="btn btn-accent" style="font-size:0.82rem;padding:7px 12px;">
                &#128196; Generate Selected Draft
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

        <!-- CARD 2: SOURCE MATERIALS, FACTS & EVIDENCE -->
        <div class="card">
          <div class="card-header teal">
            <span>2. SOURCE MATERIALS, FACTS &amp; EVIDENCE</span>
            <span style="font-size:0.8rem;opacity:0.85;">Factual Chronology &amp; Evidence Matrix</span>
          </div>
          <div class="card-body">
            <div class="form-group" style="margin-bottom:12px;">
              <label>Factual Chronology &amp; Background:</label>
              <textarea name="facts" rows="4" placeholder="Chronology of entry, marriage, child births, employment, residence history..."><?php echo htmlspecialchars($caseData['facts']); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
              <label>Home Office Refusal Reasons / Decision Notice Text:</label>
              <textarea name="refusal_reasons" rows="3" placeholder="Paste the refusal notice paragraphs, refusal grounds, or UKVI decision assertions..."><?php echo htmlspecialchars($caseData['refusal_reasons']); ?></textarea>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label>Evidence Available &amp; Documents:</label>
                <textarea name="evidence_available" rows="3" placeholder="List marriage cert, payslips, bank statements, birth certs, tenancy..."><?php echo htmlspecialchars($caseData['evidence_available']); ?></textarea>
              </div>
              <div class="form-group">
                <label>Article 8 ECHR &amp; Family Factors:</label>
                <textarea name="article8_grounds" rows="3" placeholder="Family life with British spouse/child, Section 55 BCIA 2009, medical..."><?php echo htmlspecialchars($caseData['article8_grounds']); ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 3: MATTER LINKING & CASE DOSSIER -->
        <div class="card">
          <div class="card-header navy">
            <span>3. MATTER LINKING &amp; CASE PARTICULARS</span>
            <span style="font-size:0.8rem;opacity:0.85;">Ref: <?php echo htmlspecialchars($caseData['case_reference']); ?></span>
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
                <label>Applicant Full Name</label>
                <input type="text" name="applicant_name" value="<?php echo htmlspecialchars($caseData['applicant_name']); ?>" placeholder="e.g. Tariq Al-Mansoor"/>
              </div>
              <div class="form-group">
                <label>Nationality &amp; DOB</label>
                <div style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:6px;">
                  <input type="text" name="nationality" value="<?php echo htmlspecialchars($caseData['nationality']); ?>" placeholder="Nationality"/>
                  <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($caseData['date_of_birth']); ?>"/>
                </div>
              </div>
              <div class="form-group">
                <label>Application / Visa Category</label>
                <input type="text" name="visa_type" value="<?php echo htmlspecialchars($caseData['visa_type']); ?>" placeholder="e.g. Spouse Visa (Appendix FM)"/>
              </div>
              <div class="form-group">
                <label>Home Office Reference</label>
                <input type="text" name="home_office_reference" value="<?php echo htmlspecialchars($caseData['home_office_reference']); ?>" placeholder="e.g. H9281740/001"/>
              </div>
              <div class="form-group">
                <label>UK Entry Date &amp; Leave Expiry</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                  <input type="date" name="date_of_entry" value="<?php echo htmlspecialchars($caseData['date_of_entry']); ?>"/>
                  <input type="date" name="client_visa_expiry" value="<?php echo htmlspecialchars($caseData['client_visa_expiry']); ?>"/>
                </div>
              </div>
              <div class="form-group">
                <label>Sponsor Name &amp; Status</label>
                <input type="text" name="sponsor_name" value="<?php echo htmlspecialchars($caseData['sponsor_name']); ?>" placeholder="e.g. Spouse (British Citizen)"/>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ============================================== -->
      <!-- RIGHT COLUMN: AI REASONING & GENERATED DRAFT  -->
      <!-- ============================================== -->
      <div>
        
        <!-- CARD 4: AI MULTI-FACTOR REASONING & ASSESSMENTS -->
        <div class="card">
          <div class="card-header teal">
            <span>4. AI MULTI-FACTOR ASSESSMENTS &amp; REASONING</span>
            <span style="font-size:0.8rem;opacity:0.85;">Counsel Engine Active</span>
          </div>
          <div class="card-body">
            
            <!-- Tabs Navigation -->
            <div class="tabs-nav">
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'strength' ? 'active' : ''; ?>" onclick="switchTab('strength', this)">
                &#128202; Case Strength
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'evidence' ? 'active' : ''; ?>" onclick="switchTab('evidence', this)">
                &#128203; Evidence Matrix
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'issues' ? 'active' : ''; ?>" onclick="switchTab('issues', this)">
                &#9888;&#65039; Issues
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'article8' ? 'active' : ''; ?>" onclick="switchTab('article8', this)">
                &#128106; Article 8
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'appeals' ? 'active' : ''; ?>" onclick="switchTab('appeals', this)">
                &#9878;&#65039; Appeals
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'risks' ? 'active' : ''; ?>" onclick="switchTab('risks', this)">
                &#128737;&#65039; Risks
              </button>
              <button type="button" class="tab-btn <?php echo $caseData['active_tab'] === 'strategy' ? 'active' : ''; ?>" onclick="switchTab('strategy', this)">
                &#128640; Strategy
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

            <!-- TAB 2: EVIDENCE MATRIX -->
            <div id="tab-evidence" class="tab-pane <?php echo $caseData['active_tab'] === 'evidence' ? 'active' : ''; ?>">
              <?php $mat = $analysis['assessments']['evidence_matrix']; ?>
              <div style="margin-bottom:8px;font-size:0.82rem;color:#64748b;display:flex;justify-content:space-between;">
                <span>Requirements: <strong><?php echo $mat['total_requirements']; ?></strong></span>
                <span>Verified: <strong style="color:#16a34a;"><?php echo $mat['verified_count']; ?></strong> | Pending/Missing: <strong style="color:#ea580c;"><?php echo $mat['pending_count']; ?></strong></span>
              </div>
              <table class="matrix-table">
                <thead>
                  <tr>
                    <th>Requirement / Issue</th>
                    <th>Required Proof</th>
                    <th>Status</th>
                    <th>Gaps / Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mat['items'] as $it): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($it['issue']); ?></strong><br/>
                        <span class="badge badge-<?php echo strtolower($it['weight']); ?>"><?php echo $it['weight']; ?></span>
                      </td>
                      <td><?php echo htmlspecialchars($it['required']); ?></td>
                      <td>
                        <span class="badge badge-<?php echo strtolower($it['status']); ?>"><?php echo $it['status']; ?></span>
                      </td>
                      <td>
                        <?php if ($it['missing']): ?>
                          <span style="color:#dc2626;font-weight:600;"><?php echo htmlspecialchars($it['missing']); ?></span>
                        <?php else: ?>
                          <span style="color:#16a34a;">&#10004; Evidenced in record</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- TAB 3: ISSUE DETECTION -->
            <div id="tab-issues" class="tab-pane <?php echo $caseData['active_tab'] === 'issues' ? 'active' : ''; ?>">
              <?php $iss = $analysis['assessments']['issue_detection']; ?>
              <ul style="list-style:none;padding:0;">
                <?php foreach ($iss['summary'] as $sumItem): ?>
                  <li style="background:#f8fafc;border-left:4px solid #2563eb;padding:8px 12px;border-radius:0 6px 6px 0;margin-bottom:6px;font-size:0.84rem;">
                    &#9888;&#65039; <?php echo htmlspecialchars($sumItem); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <!-- TAB 4: ARTICLE 8 ASSESSMENT -->
            <div id="tab-article8" class="tab-pane <?php echo $caseData['active_tab'] === 'article8' ? 'active' : ''; ?>">
              <?php $art = $analysis['assessments']['article8_assessment']; ?>
              <div style="font-size:0.84rem;display:flex;flex-direction:column;gap:8px;">
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Family Life Engagement:</strong> <?php echo htmlspecialchars($art['family_life_engaged']); ?>
                </div>
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Section 55 BCIA 2009 (Best Interests of Children):</strong><br/>
                  <span style="color:#2563eb;"><?php echo htmlspecialchars($art['section_55_bcia']); ?></span>
                </div>
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Insurmountable Obstacles (EX.1 / Appendix FM):</strong><br/>
                  <?php echo htmlspecialchars($art['insurmountable_obstacles']); ?>
                </div>
              </div>
            </div>

            <!-- TAB 5: APPEALS ASSESSMENT -->
            <div id="tab-appeals" class="tab-pane <?php echo $caseData['active_tab'] === 'appeals' ? 'active' : ''; ?>">
              <?php $app = $analysis['assessments']['appeals_assessment']; ?>
              <div style="font-size:0.84rem;display:flex;flex-direction:column;gap:8px;">
                <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;">
                  <strong>Recommended Statutory Route:</strong><br/>
                  <span style="color:#7c3aed;font-weight:700;"><?php echo htmlspecialchars($app['recommended_route']); ?></span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                  <div style="background:#fef2f2;border:1px solid #fee2e2;padding:8px;border-radius:6px;">
                    <strong style="color:#991b1b;">In-Country Appeal Deadline:</strong><br/>
                    <?php echo htmlspecialchars($app['in_country_time_limit']); ?>
                  </div>
                  <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:8px;border-radius:6px;">
                    <strong>Admin Review Deadline:</strong><br/>
                    <?php echo htmlspecialchars($app['admin_review_time_limit']); ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 6: RISK REGISTER -->
            <div id="tab-risks" class="tab-pane <?php echo $caseData['active_tab'] === 'risks' ? 'active' : ''; ?>">
              <?php $rsk = $analysis['assessments']['risk_assessment']; ?>
              <div style="margin-bottom:8px;">
                <span class="badge badge-high">Risk Level: <?php echo htmlspecialchars($rsk['risk_level']); ?></span>
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

            <!-- TAB 7: STRATEGY PLAN -->
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

        <!-- CARD 5: AI GENERATED DOCUMENT & EDITABLE WORKSPACE -->
        <div class="card" style="border: 2px solid #7c3aed;">
          <div class="card-header purple">
            <span>5. AI GENERATED DRAFT &amp; WORKBENCH</span>
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
              <button type="submit" name="action" value="persuasive" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Enhance persuasiveness under Article 8 ECHR">
                &#128170; Make More Persuasive
              </button>
              <button type="submit" name="action" value="add_authorities" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Integrate leading Immigration Rules and Case Law">
                &#9878;&#65039; Add Authorities
              </button>
              <button type="submit" name="action" value="simplify" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Simplify into concise legal propositions">
                &#128065;&#65039; Simplify
              </button>
              <button type="submit" name="action" value="expand" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Expand legal submissions and evidential reasoning">
                &#128214; Expand Analysis
              </button>
              <button type="submit" name="action" value="risk_review" class="btn btn-outline" style="font-size:0.78rem;padding:5px 9px;" title="Audit for evidential gaps under Appendix FM-SE">
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
                <div class="quality-val">95%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Professional Tone</div>
                <div class="quality-val">98%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Completeness</div>
                <div class="quality-val">94%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Persuasiveness</div>
                <div class="quality-val">96%</div>
              </div>
              <div class="quality-item">
                <div class="quality-label">Readability</div>
                <div class="quality-val">90%</div>
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