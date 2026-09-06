<?php
/**
 * Builder script to generate modern, complete list, view, and print pages for all legal domains.
 */

$domainConfigs = [
    'witness' => [
        'title' => 'Witness Statement Intelligence',
        'badge' => 'EVIDENCE & LITIGATION GROUP',
        'icon' => '✍️',
        'subheading' => 'CPR Practice Direction 57AC Compliant Witness Statements, Factual Chronologies & Statements of Truth',
        'table' => 'witness_statements',
        'create_file' => 'witness_create.php',
        'list_file' => 'witness_list.php',
        'view_file' => 'witness_view.php',
        'print_file' => 'witness_print.php',
        'ref_field' => 'case_number',
        'title_field' => 'case_title',
        'client_field' => 'witness_name',
        'summary_field' => 'statement',
        'practice_area' => 'Witness Statement Intelligence',
        'schema' => "CREATE TABLE IF NOT EXISTS witness_statements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_title TEXT,
            case_number TEXT,
            court TEXT,
            witness_name TEXT,
            witness_address TEXT,
            witness_occupation TEXT,
            relationship TEXT,
            statement TEXT,
            exhibits TEXT,
            declaration TEXT,
            lawyer_name TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'skeleton' => [
        'title' => 'Skeleton Argument Intelligence',
        'badge' => 'ADVOCACY & TRIAL SKELETON GROUP',
        'icon' => '🏛️',
        'subheading' => 'High Court & Tribunal Skeleton Arguments, Argument Mapping, Proposition Hierarchy & CPR Part 24 Summary Judgments',
        'table' => 'skeleton_arguments',
        'create_file' => 'skeleton_create.php',
        'list_file' => 'skeleton_list.php',
        'view_file' => 'skeleton_view.php',
        'print_file' => 'skeleton_print.php',
        'ref_field' => 'case_number',
        'title_field' => 'case_title',
        'client_field' => 'party',
        'summary_field' => 'submissions',
        'practice_area' => 'Skeleton Argument Intelligence',
        'schema' => "CREATE TABLE IF NOT EXISTS skeleton_arguments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_title TEXT,
            case_number TEXT,
            court TEXT,
            party TEXT,
            introduction TEXT,
            facts_summary TEXT,
            issues TEXT,
            submissions TEXT,
            conclusion TEXT,
            relief_sought TEXT,
            authorities TEXT,
            lawyer_name TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'appeal' => [
        'title' => 'Appellate Advocacy & Grounds of Appeal',
        'badge' => 'APPELLATE ADVOCACY GROUP',
        'icon' => '📜',
        'subheading' => 'Form N161 Grounds of Appeal, Permission to Appeal Skeleton Arguments (CPR Part 52) & Appellate Review',
        'table' => 'grounds_of_appeal',
        'create_file' => 'appeal_create.php',
        'list_file' => 'appeal_list.php',
        'view_file' => 'appeal_view.php',
        'print_file' => 'appeal_print.php',
        'ref_field' => 'case_number',
        'title_field' => 'case_title',
        'client_field' => 'party',
        'summary_field' => 'grounds',
        'practice_area' => 'Appellate Advocacy',
        'schema' => "CREATE TABLE IF NOT EXISTS grounds_of_appeal (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_title TEXT,
            case_number TEXT,
            lower_court TEXT,
            appeal_court TEXT,
            party TEXT,
            judgment_date TEXT,
            introduction TEXT,
            grounds TEXT,
            arguments TEXT,
            relief_sought TEXT,
            authorities TEXT,
            lawyer_name TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'advice' => [
        'title' => 'Legal Advice & Merits Assessment',
        'badge' => 'STRATEGIC COUNSEL GROUP',
        'icon' => '💡',
        'subheading' => 'Formal Legal Opinions, Merits Prospects Rating (95%+), Risk Evaluation & Step-by-Step Strategic Advice',
        'table' => 'legal_advice',
        'create_file' => 'advice_create.php',
        'list_file' => 'advice_list.php',
        'view_file' => 'advice_view.php',
        'print_file' => 'advice_print.php',
        'ref_field' => 'file_no',
        'title_field' => 'subject',
        'client_field' => 'client_name',
        'summary_field' => 'advice',
        'practice_area' => 'Legal Advice',
        'schema' => "CREATE TABLE IF NOT EXISTS legal_advice (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            file_no TEXT,
            client_name TEXT,
            client_address TEXT,
            client_email TEXT,
            client_phone TEXT,
            matter_type TEXT,
            legal_domain TEXT,
            subject TEXT,
            background TEXT,
            facts TEXT,
            legal_issues TEXT,
            applicable_law TEXT,
            diagnosis TEXT,
            advice TEXT,
            action_plan TEXT,
            recommendations TEXT,
            disclaimer TEXT,
            lawyer_name TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'tort' => [
        'title' => 'Tort, Negligence & Personal Injury',
        'badge' => 'TORT & PERSONAL INJURY GROUP',
        'icon' => '🛡️',
        'subheading' => 'CPR Pre-Action Protocol Letters of Claim, Particulars of Claim (PUWER/Negligence) & Judicial College Quantum',
        'table' => 'tort_cases',
        'create_file' => 'tort_create.php',
        'list_file' => 'tort_list.php',
        'view_file' => 'tort_view.php',
        'print_file' => 'tort_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'case_type',
        'client_field' => 'claimant_name',
        'summary_field' => 'incident_description',
        'practice_area' => 'Tort & Personal Injury',
        'schema' => "CREATE TABLE IF NOT EXISTS tort_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            status TEXT DEFAULT 'draft',
            case_type TEXT,
            claimant_name TEXT,
            defendant_name TEXT,
            incident_date TEXT,
            incident_location TEXT,
            incident_description TEXT,
            injury_description TEXT,
            damages_claimed TEXT,
            lawyer_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'employment' => [
        'title' => 'Employment & Tribunal Intelligence',
        'badge' => 'EMPLOYMENT LAW PRACTICE GROUP',
        'icon' => '👔',
        'subheading' => 'ET1 Particulars of Claim, ET3 Responses, ACAS Early Conciliation, Schedule of Loss & Vento Band Assessments',
        'table' => 'employment_cases',
        'create_file' => 'employment_create.php',
        'list_file' => 'employment_list.php',
        'view_file' => 'employment_view.php',
        'print_file' => 'employment_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'claim_type',
        'client_field' => 'claimant_name',
        'summary_field' => 'claim_details',
        'practice_area' => 'Employment Law',
        'schema' => "CREATE TABLE IF NOT EXISTS employment_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            status TEXT DEFAULT 'draft',
            claim_type TEXT,
            claimant_name TEXT,
            respondent_name TEXT,
            acas_certificate_number TEXT,
            employment_start_date TEXT,
            employment_end_date TEXT,
            job_title TEXT,
            gross_salary TEXT,
            claim_details TEXT,
            remedies_sought TEXT,
            lawyer_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'contract' => [
        'title' => 'Commercial Contracts & Dispute Intelligence',
        'badge' => 'COMMERCIAL CONTRACTS GROUP',
        'icon' => '📄',
        'subheading' => 'Commercial Breach of Contract Claims, Particulars of Claim, Defences, UCTA 1977 Review & Part 36 Offers',
        'table' => 'commercial_contracts',
        'create_file' => 'contract_create.php',
        'list_file' => 'contract_list.php',
        'view_file' => 'contract_view.php',
        'print_file' => 'contract_print.php',
        'ref_field' => 'contract_ref',
        'title_field' => 'contract_title',
        'client_field' => 'party_a',
        'summary_field' => 'contract_description',
        'practice_area' => 'Commercial Contracts',
        'schema' => "CREATE TABLE IF NOT EXISTS commercial_contracts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            contract_ref TEXT,
            contract_title TEXT,
            contract_type TEXT,
            party_a TEXT,
            party_b TEXT,
            contract_date TEXT,
            governing_law TEXT,
            contract_value TEXT,
            contract_description TEXT,
            status TEXT DEFAULT 'draft',
            lawyer_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'company' => [
        'title' => 'Corporate Law & Shareholder Intelligence',
        'badge' => 'CORPORATE LAW PRACTICE GROUP',
        'icon' => '🏢',
        'subheading' => 'Section 994 Unfair Prejudice Petitions, Directors Duties Enforcement, Shareholder Buyout Remedies & Articles of Association',
        'table' => 'company_cases',
        'create_file' => 'company_create.php',
        'list_file' => 'company_list.php',
        'view_file' => 'company_view.php',
        'print_file' => 'company_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'company_name',
        'client_field' => 'petitioner_name',
        'summary_field' => 'prejudice_details',
        'practice_area' => 'Corporate Law',
        'schema' => "CREATE TABLE IF NOT EXISTS company_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            company_name TEXT,
            company_number TEXT,
            petitioner_name TEXT,
            respondent_name TEXT,
            shareholding_percentage TEXT,
            prejudice_details TEXT,
            remedy_sought TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'criminal' => [
        'title' => 'Criminal Defence & Trial Intelligence',
        'badge' => 'CRIMINAL DEFENCE GROUP',
        'icon' => '⚖️',
        'subheading' => 'Section 5 CPIA Defence Case Statements, Section 78 PACE Evidence Exclusion, Bail Packages & Turnbull Identification Challenges',
        'table' => 'criminal_cases',
        'create_file' => 'criminal_create.php',
        'list_file' => 'criminal_list.php',
        'view_file' => 'criminal_view.php',
        'print_file' => 'criminal_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'offence_charged',
        'client_field' => 'defendant_name',
        'summary_field' => 'defence_basis',
        'practice_area' => 'Criminal Defence',
        'schema' => "CREATE TABLE IF NOT EXISTS criminal_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            court_name TEXT,
            defendant_name TEXT,
            offence_charged TEXT,
            offence_date TEXT,
            prosecution_case TEXT,
            defence_basis TEXT,
            alibi_details TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'family' => [
        'title' => 'Matrimonial & Family Law Intelligence',
        'badge' => 'FAMILY & MATRIMONIAL GROUP',
        'icon' => '👨‍👩‍👧',
        'subheading' => 'Section 25 MCA 1973 FDR Statements, Child Welfare Assessment (Section 1 Children Act), Pension Sharing & Clean Break Strategy',
        'table' => 'family_cases',
        'create_file' => 'family_create.php',
        'list_file' => 'family_list.php',
        'view_file' => 'family_view.php',
        'print_file' => 'family_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'matter_type',
        'client_field' => 'applicant_name',
        'summary_field' => 'financial_overview',
        'practice_area' => 'Family Law',
        'schema' => "CREATE TABLE IF NOT EXISTS family_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            court_name TEXT,
            applicant_name TEXT,
            respondent_name TEXT,
            marriage_date TEXT,
            separation_date TEXT,
            children_details TEXT,
            financial_overview TEXT,
            pension_assets TEXT,
            orders_sought TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'admin_law' => [
        'title' => 'Judicial Review & Public Law Intelligence',
        'badge' => 'PUBLIC LAW & JUDICIAL REVIEW GROUP',
        'icon' => '🏛️',
        'subheading' => 'Pre-Action Protocol for JR (PAP-JR), Form N461 Grounds of Judicial Review, Wednesbury Unreasonableness & Section 149 Equality Act PSED',
        'table' => 'admin_law_cases',
        'create_file' => 'admin_law_create.php',
        'list_file' => 'admin_law_list.php',
        'view_file' => 'admin_law_view.php',
        'print_file' => 'admin_law_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'decision_challenged',
        'client_field' => 'claimant_name',
        'summary_field' => 'grounds_summary',
        'practice_area' => 'Public Law & Judicial Review',
        'schema' => "CREATE TABLE IF NOT EXISTS admin_law_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            claimant_name TEXT,
            defendant_public_body TEXT,
            decision_challenged TEXT,
            decision_date TEXT,
            grounds_summary TEXT,
            remedy_sought TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    'oil_gas' => [
        'title' => 'Energy, Oil & Gas Intelligence',
        'badge' => 'ENERGY & NATURAL RESOURCES GROUP',
        'icon' => '⚡',
        'subheading' => 'JOA Cash Call Default Notices, Section 29 Petroleum Act 1998 Decommissioning Liabilities & Energy Arbitration',
        'table' => 'oil_gas_cases',
        'create_file' => 'oil_gas_create.php',
        'list_file' => 'oil_gas_list.php',
        'view_file' => 'oil_gas_view.php',
        'print_file' => 'oil_gas_print.php',
        'ref_field' => 'case_reference',
        'title_field' => 'licence_block',
        'client_field' => 'operator_name',
        'summary_field' => 'dispute_summary',
        'practice_area' => 'Energy & Natural Resources',
        'schema' => "CREATE TABLE IF NOT EXISTS oil_gas_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matter_id INTEGER DEFAULT 0,
            case_reference TEXT,
            licence_block TEXT,
            operator_name TEXT,
            non_operator_name TEXT,
            dispute_summary TEXT,
            quantum_in_dispute TEXT,
            status TEXT DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ],
];

function generateListPage(array $cfg): string {
    $title = addslashes($cfg['title']);
    $badge = addslashes($cfg['badge']);
    $icon = $cfg['icon'];
    $subheading = addslashes($cfg['subheading']);
    $table = $cfg['table'];
    $createFile = $cfg['create_file'];
    $viewFile = $cfg['view_file'];
    $printFile = $cfg['print_file'];
    $listFile = $cfg['list_file'];
    $refField = $cfg['ref_field'];
    $titleField = $cfg['title_field'];
    $clientField = $cfg['client_field'];
    $summaryField = $cfg['summary_field'];
    $practiceArea = addslashes($cfg['practice_area']);
    $schema = addslashes($cfg['schema']);

    $code = <<<PHP
<?php
session_start();
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';
require_once __DIR__ . '/counsel_engine_service.php';

\$pdo = p2_db();
p2_migrate(\$pdo);

// Ensure domain table exists
try {
    \$pdo->exec("{$schema}");
} catch (Exception \$e) {}

// Handle Delete
if (isset(\$_GET['delete'])) {
    \$delId = (int)\$_GET['delete'];
    if (\$delId > 0) {
        \$stmt = \$pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        \$stmt->execute([\$delId]);
        header("Location: {$listFile}?msg=deleted");
        exit;
    }
}

\$search = trim((string)(\$_GET['search'] ?? ''));
\$status = trim((string)(\$_GET['status'] ?? ''));

// Query Domain Records
\$where = ["1=1"];
\$params = [];

if (\$search !== '') {
    \$where[] = "({$refField} LIKE ? OR {$clientField} LIKE ? OR {$titleField} LIKE ?)";
    \$params[] = "%{\$search}%";
    \$params[] = "%{\$search}%";
    \$params[] = "%{\$search}%";
}

if (\$status !== '') {
    \$where[] = "status = ?";
    \$params[] = \$status;
}

\$sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', \$where) . " ORDER BY id DESC";
\$stmt = \$pdo->prepare(\$sql);
\$stmt->execute(\$params);
\$records = \$stmt->fetchAll(PDO::FETCH_ASSOC);

// Also query any saved AI Documents under this practice area
\$aiDocs = [];
try {
    \$dSql = "SELECT d.*, m.reference as matter_reference, m.title as matter_title 
             FROM p2_documents d 
             JOIN matters m ON d.matter_id = m.id 
             WHERE m.practice_area LIKE '%{$practiceArea}%' OR m.practice_area LIKE '%{$title}%'
             ORDER BY d.id DESC LIMIT 20";
    \$dStmt = \$pdo->query(\$dSql);
    if (\$dStmt) \$aiDocs = \$dStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception \$e) {}

\$totalCount = count(\$records);
\$draftCount = 0;
\$finalCount = 0;
foreach (\$records as \$r) {
    if ((\$r['status'] ?? '') === 'final') \$finalCount++;
    else \$draftCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} — AEP Legal Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --primary: #0f2744; --accent: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --text: #1e293b; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        .topbar { background: #0f172a; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent); }
        .topbar a { color: #cbd5e1; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .topbar a:hover { color: #fff; }
        .hero { background: linear-gradient(135deg, #0f2744 0%, #1e3a8a 100%); color: #fff; padding: 26px 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .hero-content { max-width: 1500px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .hero h1 { margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .hero p { margin: 6px 0 0; font-size: 13px; color: #cbd5e1; }
        .container { max-width: 1500px; margin: 24px auto; padding: 0 24px 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 8px; padding: 18px 24px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .stat-num { font-size: 28px; font-weight: 800; color: #1e3a8a; }
        .stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-top: 4px; }
        .toolbar { background: #fff; border-radius: 8px; padding: 14px 20px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; flex: 1; }
        .search-form input, .search-form select { padding: 9px 14px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .search-form input { flex: 1; min-width: 260px; }
        .btn { padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .btn-outline:hover { background: #f8fafc; }
        .btn-danger { background: #ef4444; color: #fff; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px; }
        .card-header { background: #f8fafc; padding: 14px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 14px; color: #334155; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f8fafc; color: #475569; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border); }
        td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .badge-final { background: #d1fae5; color: #065f46; }
        .empty-state { text-align: center; padding: 50px 20px; color: #64748b; }
    </style>
</head>
<body>

<header class="topbar">
    <div style="font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px;">
        <span>⚖️ AEP Legal Intelligence Platform</span>
    </div>
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
</header>

<div class="hero">
    <div class="hero-content">
        <div>
            <h1>{$icon} {$title}</h1>
            <p>{$subheading}</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{$createFile}" class="btn btn-success" style="font-size: 14px; padding: 11px 22px;">⚡ Open AI Workspace / New Draft</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num"><?= \$totalCount ?></div>
            <div class="stat-label">Total Domain Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #059669;"><?= \$finalCount ?></div>
            <div class="stat-label">Final Submissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #d97706;"><?= \$draftCount ?></div>
            <div class="stat-label">Active Drafts</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #7c3aed;"><?= count(\$aiDocs) ?></div>
            <div class="stat-label">Saved Matter Documents</div>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by reference, party name, or case title..." value="<?= htmlspecialchars(\$search) ?>">
            <select name="status">
                <option value="">— All Statuses —</option>
                <option value="draft" <?= \$status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="final" <?= \$status === 'final' ? 'selected' : '' ?>>Final</option>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <?php if (\$search || \$status): ?>
                <a href="{$listFile}" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
        <div>
            <a href="{$createFile}" class="btn btn-primary">+ New Record</a>
        </div>
    </div>

    <!-- MAIN RECORD TABLE -->
    <div class="card">
        <div class="card-header">
            <span>📋 {$title} Records</span>
            <span style="font-size: 12px; color: #64748b;">Showing <?= count(\$records) ?> records</span>
        </div>
        <?php if (empty(\$records)): ?>
            <div class="empty-state">
                <div style="font-size: 40px; margin-bottom: 12px;">📂</div>
                <h3>No records found</h3>
                <p>Launch the Universal AI Workspace to generate your first document.</p>
                <a href="{$createFile}" class="btn btn-primary" style="margin-top: 14px;">⚡ Open {$title} Workspace</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Party / Client</th>
                            <th>Case Title / Subject</th>
                            <th>Date Created</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (\$records as \$r): ?>
                            <tr>
                                <td style="font-weight: 700; color: #1e3a8a;">
                                    <a href="{$viewFile}?id=<?= \$r['id'] ?>" style="color: #1e3a8a; text-decoration: none;">
                                        <?= htmlspecialchars(\$r['{$refField}'] ?? ('REF-' . \$r['id'])) ?>
                                    </a>
                                </td>
                                <td style="font-weight: 600;">
                                    <?= htmlspecialchars(\$r['{$clientField}'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(\$r['{$titleField}'] ?? 'Case Assessment') ?>
                                </td>
                                <td style="color: #64748b; font-size: 12px;">
                                    <?= !empty(\$r['created_at']) ? date('d M Y', strtotime(\$r['created_at'])) : date('d M Y') ?>
                                </td>
                                <td>
                                    <span class="badge <?= (\$r['status'] ?? '') === 'final' ? 'badge-final' : 'badge-draft' ?>">
                                        <?= htmlspecialchars(\$r['status'] ?? 'draft') ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="{$viewFile}?id=<?= \$r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">👁️ View</a>
                                        <a href="{$printFile}?id=<?= \$r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;" target="_blank">🖨 Print</a>
                                        <a href="{$createFile}?id=<?= \$r['id'] ?>" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">⚡ AI Workspace</a>
                                        <a href="{$listFile}?delete=<?= \$r['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 10px;" onclick="return confirm('Delete this record?');">🗑 Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- SAVED MATTER DOCUMENTS -->
    <?php if (!empty(\$aiDocs)): ?>
    <div class="card">
        <div class="card-header">
            <span>💾 Matter-Linked AI Documents & Briefs</span>
            <span style="font-size: 12px; color: #64748b;">Integrated Document Library</span>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Matter Ref</th>
                        <th>Document Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (\$aiDocs as \$doc): ?>
                    <tr>
                        <td style="font-weight: 700; color: #1e3a8a;">
                            <a href="matter_view.php?id=<?= \$doc['matter_id'] ?>" style="color: #1e3a8a; text-decoration: none;">
                                <?= htmlspecialchars(\$doc['matter_reference'] ?? 'Matter #' . \$doc['matter_id']) ?>
                            </a>
                        </td>
                        <td style="font-weight: 600;"><?= htmlspecialchars(\$doc['title']) ?></td>
                        <td><span class="badge badge-final"><?= htmlspecialchars(\$doc['document_type']) ?></span></td>
                        <td style="color: #64748b; font-size: 12px;"><?= date('d M Y, H:i', strtotime(\$doc['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <a href="matter_view.php?id=<?= \$doc['matter_id'] ?>#documents" class="btn btn-outline" style="font-size: 11px; padding: 5px 10px;">📁 Open in Matter</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
PHP;

    return $code;
}

function generateViewPage(array $cfg): string {
    $title = addslashes($cfg['title']);
    $icon = $cfg['icon'];
    $table = $cfg['table'];
    $createFile = $cfg['create_file'];
    $listFile = $cfg['list_file'];
    $printFile = $cfg['print_file'];
    $refField = $cfg['ref_field'];
    $titleField = $cfg['title_field'];
    $clientField = $cfg['client_field'];
    $summaryField = $cfg['summary_field'];

    $code = <<<PHP
<?php
session_start();
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';

\$pdo = p2_db();
\$id = (int)(\$_GET['id'] ?? 0);

if (\$id <= 0) {
    header("Location: {$listFile}");
    exit;
}

\$stmt = \$pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
\$stmt->execute([\$id]);
\$record = \$stmt->fetch(PDO::FETCH_ASSOC);

if (!\$record) {
    header("Location: {$listFile}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View <?= htmlspecialchars(\$record['{$refField}'] ?? 'Record') ?> — {$title}</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --primary: #0f2744; --accent: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --text: #1e293b; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        .topbar { background: #0f172a; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent); }
        .topbar a { color: #cbd5e1; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 24px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .btn { padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-outline { background: #fff; color: #475569; border: 1px solid #cbd5e1; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .field-group { background: #f8fafc; padding: 14px; border-radius: 6px; border-left: 3px solid var(--accent); }
        .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .field-val { font-size: 14px; font-weight: 600; color: #1e293b; }
        .full-content { background: #ffffff; border: 1px solid var(--border); border-radius: 6px; padding: 20px; line-height: 1.7; font-family: "SFMono-Regular", Consolas, monospace; font-size: 13px; white-space: pre-wrap; }
    </style>
</head>
<body>

<header class="topbar">
    <div style="font-weight: 700; font-size: 15px;">⚖️ AEP Legal Platform — {$title}</div>
    <nav>
        <a href="{$listFile}">📋 Back to List</a>
        <a href="{$createFile}">⚡ AI Workspace</a>
        <a href="dashboard.php">🏠 Dashboard</a>
    </nav>
</header>

<div class="container">
    <div class="card">
        <div class="header-actions">
            <div>
                <h1 style="margin: 0; font-size: 20px; color: #0f2744;">{$icon} <?= htmlspecialchars(\$record['{$titleField}'] ?? 'Record Details') ?></h1>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 13px;">Ref: <strong><?= htmlspecialchars(\$record['{$refField}'] ?? 'REF-' . \$record['id']) ?></strong> | Created: <?= date('d F Y', strtotime(\$record['created_at'] ?? 'now')) ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{$printFile}?id=<?= \$record['id'] ?>" target="_blank" class="btn btn-outline">🖨 Print / PDF</a>
                <a href="{$createFile}?id=<?= \$record['id'] ?>" class="btn btn-primary">⚡ Open in AI Workspace</a>
            </div>
        </div>

        <div class="grid-2">
            <div class="field-group">
                <div class="field-label">Party / Client</div>
                <div class="field-val"><?= htmlspecialchars(\$record['{$clientField}'] ?? 'N/A') ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Status</div>
                <div class="field-val"><?= htmlspecialchars(strtoupper(\$record['status'] ?? 'DRAFT')) ?></div>
            </div>
        </div>

        <h3 style="font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; margin-bottom: 8px;">Substantive Record / Statement Content</h3>
        <div class="full-content"><?= htmlspecialchars(\$record['{$summaryField}'] ?? 'No extended content recorded.') ?></div>
    </div>
</div>

</body>
</html>
PHP;

    return $code;
}

function generatePrintPage(array $cfg): string {
    $title = addslashes($cfg['title']);
    $table = $cfg['table'];
    $refField = $cfg['ref_field'];
    $titleField = $cfg['title_field'];
    $clientField = $cfg['client_field'];
    $summaryField = $cfg['summary_field'];

    $code = <<<PHP
<?php
session_start();
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phase2.php';

\$pdo = p2_db();
\$id = (int)(\$_GET['id'] ?? 0);

if (\$id <= 0) {
    die("Invalid record ID.");
}

\$stmt = \$pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
\$stmt->execute([\$id]);
\$record = \$stmt->fetch(PDO::FETCH_ASSOC);

if (!\$record) {
    die("Record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print <?= htmlspecialchars(\$record['{$refField}'] ?? 'Document') ?></title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 40px; color: #000; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 24px; }
        .header h1 { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .meta { margin-bottom: 20px; font-size: 11pt; }
        .content { white-space: pre-wrap; font-size: 11pt; line-height: 1.6; }
        .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 12px; font-size: 9pt; text-align: center; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 8px 16px; font-size: 13px; font-weight: bold; cursor: pointer;">🖨 Print / Save as PDF</button>
</div>

<div class="header">
    <h1>AEP Legal Intelligence Platform</h1>
    <div style="font-size: 11pt; font-weight: bold; margin-top: 4px;">{$title}</div>
</div>

<div class="meta">
    <p><strong>DOCUMENT TITLE:</strong> <?= htmlspecialchars(\$record['{$titleField}'] ?? 'Legal Record') ?><br>
    <strong>REFERENCE:</strong> <?= htmlspecialchars(\$record['{$refField}'] ?? 'N/A') ?><br>
    <strong>PARTY / CLIENT:</strong> <?= htmlspecialchars(\$record['{$clientField}'] ?? 'N/A') ?><br>
    <strong>DATE:</strong> <?= date('d F Y', strtotime(\$record['created_at'] ?? 'now')) ?></p>
</div>

<div class="content"><?= htmlspecialchars(\$record['{$summaryField}'] ?? '') ?></div>

<div class="footer">
    AEP Legal Intelligence Platform — Certified Legal Document Record
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    // optional auto-print if wanted
});
</script>
</body>
</html>
PHP;

    return $code;
}

foreach ($domainConfigs as $key => $cfg) {
    $listCode = generateListPage($cfg);
    $viewCode = generateViewPage($cfg);
    $printCode = generatePrintPage($cfg);

    file_put_contents(__DIR__ . '/' . $cfg['list_file'], $listCode);
    file_put_contents(__DIR__ . '/' . $cfg['view_file'], $viewCode);
    file_put_contents(__DIR__ . '/' . $cfg['print_file'], $printCode);

    echo "Generated complete suite for {$key}: {$cfg['list_file']}, {$cfg['view_file']}, {$cfg['print_file']}\n";
}
