<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [];

// ============================================================
// 1. USERS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    full_name TEXT,
    email TEXT,
    role TEXT DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'users';

// ============================================================
// 2. LEGAL ADVICE
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS legal_advice (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_name TEXT NOT NULL,
    client_address TEXT,
    client_email TEXT,
    client_phone TEXT,
    matter_type TEXT,
    subject TEXT,
    background TEXT,
    legal_issues TEXT,
    advice TEXT,
    recommendations TEXT,
    disclaimer TEXT,
    lawyer_name TEXT,
    status TEXT DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'legal_advice';

// ============================================================
// 3. DRAFT LETTERS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS draft_letters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    letter_type TEXT,
    recipient_name TEXT NOT NULL,
    recipient_address TEXT,
    recipient_email TEXT,
    sender_name TEXT,
    sender_address TEXT,
    subject TEXT,
    salutation TEXT,
    body TEXT,
    closing TEXT,
    enclosures TEXT,
    lawyer_name TEXT,
    status TEXT DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'draft_letters';

// ============================================================
// 4. WITNESS STATEMENTS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS witness_statements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_title TEXT NOT NULL,
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
)");
$tables[] = 'witness_statements';

// ============================================================
// 5. SKELETON ARGUMENTS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS skeleton_arguments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_title TEXT NOT NULL,
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
)");
$tables[] = 'skeleton_arguments';

// ============================================================
// 6. GROUNDS OF APPEAL
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS grounds_of_appeal (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_title TEXT NOT NULL,
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
)");
$tables[] = 'grounds_of_appeal';

// ============================================================
// 7. IMMIGRATION CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS immigration_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    -- CASE INFO
    visa_type TEXT NOT NULL,
    case_reference TEXT,
    ho_reference TEXT,
    status TEXT DEFAULT 'draft',

    -- APPLICANT
    applicant_name TEXT NOT NULL,
    date_of_birth TEXT,
    nationality TEXT,
    passport_number TEXT,
    passport_expiry TEXT,
    place_of_birth TEXT,

    -- CONTACT
    applicant_address TEXT,
    applicant_email TEXT,
    applicant_phone TEXT,

    -- RESIDENCY
    date_of_entry TEXT,
    port_of_entry TEXT,
    residency_type TEXT,
    continuous_residence_from TEXT,
    total_absences TEXT,
    breaks_in_residence TEXT,
    visa_history TEXT,

    -- FAMILY / DEPENDANTS
    marital_status TEXT,
    sponsor_name TEXT,
    sponsor_dob TEXT,
    sponsor_nationality TEXT,
    sponsor_status TEXT,
    dependants TEXT,

    -- EMPLOYMENT
    employment_history TEXT,
    current_employer TEXT,
    job_title TEXT,
    salary TEXT,

    -- LEGAL
    legal_basis TEXT,
    article8_grounds TEXT,
    evidence_available TEXT,
    representations TEXT,
    previous_refusals TEXT,
    refusal_reasons TEXT,

    -- COUNSEL
    lawyer_name TEXT,
    law_firm TEXT,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'immigration_cases';

// ============================================================
// 8. EMPLOYMENT CASES (RESERVED)
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS employment_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_title TEXT,
    claimant_name TEXT,
    respondent_name TEXT,
    tribunal TEXT,
    case_number TEXT,
    claim_details TEXT,
    remedy_sought TEXT,
    lawyer_name TEXT,
    status TEXT DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'employment_cases';

// ============================================================
// 9. PROPERTY CASES (RESERVED)
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS property_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_title TEXT,
    client_name TEXT,
    property_address TEXT,
    tenure TEXT,
    transaction_type TEXT,
    completion_date TEXT,
    purchase_price TEXT,
    details TEXT,
    lawyer_name TEXT,
    status TEXT DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'property_cases';

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>AEP Setup</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f4f6f9;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#fff;border-radius:12px;padding:40px;box-shadow:0 4px 20px rgba(0,0,0,0.1);max-width:600px;width:100%}
h2{color:#1a3c5e;margin-bottom:8px;font-size:1.4rem}
p{color:#666;font-size:0.9rem;margin-bottom:24px}
.table-list{list-style:none;display:flex;flex-direction:column;gap:10px}
.table-list li{display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0f4ff;border-radius:6px;font-size:0.88rem;color:#333;border-left:4px solid #1a3c5e}
.table-list li span.tick{color:#28a745;font-size:1.1rem;font-weight:bold}
.badge-reserved{background:#fff3cd;color:#856404;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:bold;margin-left:auto}
.badge-new{background:#d4edda;color:#155724;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:bold;margin-left:auto}
.footer{margin-top:24px;text-align:center;font-size:0.8rem;color:#aaa}
.btn{display:inline-block;margin-top:20px;padding:10px 28px;background:#1a3c5e;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9rem}
</style>
</head>
<body>
<div class="card">
  <h2>✅ AEP Database Setup Complete!</h2>
  <p>All tables have been created or verified successfully.</p>

  <ul class="table-list">
    <li><span class="tick">✓</span> users</li>
    <li><span class="tick">✓</span> legal_advice</li>
    <li><span class="tick">✓</span> draft_letters</li>
    <li><span class="tick">✓</span> witness_statements</li>
    <li><span class="tick">✓</span> skeleton_arguments</li>
    <li><span class="tick">✓</span> grounds_of_appeal</li>
    <li><span class="tick">✓</span> immigration_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> employment_cases <span class="badge-reserved">RESERVED</span></li>
    <li><span class="tick">✓</span> property_cases <span class="badge-reserved">RESERVED</span></li>
  </ul>

  <a href="dashboard.php" class="btn">→ Go to Dashboard</a>

  <div class="footer">AEP Legal Platform — Database v2.0</div>
</div>
</body>
</html>