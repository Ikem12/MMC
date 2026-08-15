<?php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo "403 Forbidden — admin access required. <a href='login.php'>Login</a>";
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [];

// ============================================================
// 1. USERS
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    full_name TEXT,
    email TEXT,
    is_admin INTEGER NOT NULL DEFAULT 0,
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
    visa_type TEXT NOT NULL,
    case_reference TEXT,
    ho_reference TEXT,
    status TEXT DEFAULT 'draft',
    applicant_name TEXT NOT NULL,
    date_of_birth TEXT,
    nationality TEXT,
    passport_number TEXT,
    passport_expiry TEXT,
    place_of_birth TEXT,
    applicant_address TEXT,
    applicant_email TEXT,
    applicant_phone TEXT,
    date_of_entry TEXT,
    port_of_entry TEXT,
    residency_type TEXT,
    continuous_residence_from TEXT,
    total_absences TEXT,
    breaks_in_residence TEXT,
    visa_history TEXT,
    marital_status TEXT,
    sponsor_name TEXT,
    sponsor_dob TEXT,
    sponsor_nationality TEXT,
    sponsor_status TEXT,
    dependants TEXT,
    employment_history TEXT,
    current_employer TEXT,
    job_title TEXT,
    salary TEXT,
    legal_basis TEXT,
    article8_grounds TEXT,
    evidence_available TEXT,
    representations TEXT,
    previous_refusals TEXT,
    refusal_reasons TEXT,
    lawyer_name TEXT,
    law_firm TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'immigration_cases';

// ============================================================
// 8. ADMIN LAW CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS admin_law_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    client_name TEXT,
    client_email TEXT,
    client_phone TEXT,
    client_address TEXT,
    opposing_party TEXT,
    court TEXT,
    judge TEXT,
    case_title TEXT,
    date_filed TEXT,
    background TEXT,
    legal_issues TEXT,
    grounds TEXT,
    relief_sought TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    representations TEXT,
    outcome TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'admin_law_cases';

// ============================================================
// 9. CRIMINAL CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS criminal_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    defendant_name TEXT,
    defendant_email TEXT,
    defendant_phone TEXT,
    defendant_address TEXT,
    prosecution TEXT,
    court TEXT,
    judge TEXT,
    charge TEXT,
    date_of_offence TEXT,
    plea TEXT,
    bail_status TEXT,
    facts TEXT,
    defence TEXT,
    mitigation TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    witnesses TEXT,
    outcome TEXT,
    sentence TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'criminal_cases';

// ============================================================
// 10. TORT CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS tort_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    claimant_name TEXT,
    claimant_email TEXT,
    claimant_phone TEXT,
    claimant_address TEXT,
    defendant_name TEXT,
    court TEXT,
    judge TEXT,
    tort_type TEXT,
    incident_date TEXT,
    incident_location TEXT,
    facts TEXT,
    duty_of_care TEXT,
    breach TEXT,
    causation TEXT,
    damage TEXT,
    relief_sought TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    outcome TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'tort_cases';

// ============================================================
// 11. COMPANY LAW CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS company_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    company_name TEXT,
    company_rc TEXT,
    client_name TEXT,
    client_email TEXT,
    client_phone TEXT,
    client_address TEXT,
    opposing_party TEXT,
    court TEXT,
    judge TEXT,
    matter_title TEXT,
    date_filed TEXT,
    background TEXT,
    legal_issues TEXT,
    relief_sought TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    outcome TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'company_cases';

// ============================================================
// 12. OIL & GAS CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS oil_gas_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    client_name TEXT,
    client_email TEXT,
    client_phone TEXT,
    client_address TEXT,
    opposing_party TEXT,
    regulatory_body TEXT,
    court TEXT,
    licence_number TEXT,
    field_location TEXT,
    matter_title TEXT,
    date_filed TEXT,
    background TEXT,
    legal_issues TEXT,
    relief_sought TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    outcome TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'oil_gas_cases';

// ============================================================
// 13. HUMAN RIGHTS CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS human_rights_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_reference TEXT,
    status TEXT DEFAULT 'draft',
    lawyer_name TEXT,
    law_firm TEXT,
    applicant_name TEXT,
    applicant_email TEXT,
    applicant_phone TEXT,
    applicant_address TEXT,
    respondent TEXT,
    court TEXT,
    judge TEXT,
    rights_violated TEXT,
    incident_date TEXT,
    background TEXT,
    legal_issues TEXT,
    article_relied_on TEXT,
    relief_sought TEXT,
    applicable_laws TEXT,
    evidence TEXT,
    outcome TEXT,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'human_rights_cases';

// ============================================================
// 14. EMPLOYMENT CASES
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS employment_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_title TEXT,
    case_reference TEXT,
    claimant_name TEXT,
    claimant_email TEXT,
    claimant_phone TEXT,
    respondent_name TEXT,
    tribunal TEXT,
    case_number TEXT,
    employment_start TEXT,
    employment_end TEXT,
    job_title TEXT,
    salary TEXT,
    claim_details TEXT,
    remedy_sought TEXT,
    lawyer_name TEXT,
    law_firm TEXT,
    status TEXT DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$tables[] = 'employment_cases';

// ============================================================
// 15. PROPERTY CASES (RESERVED)
// ============================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS property_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_type TEXT,
    case_title TEXT,
    case_reference TEXT,
    client_name TEXT,
    property_address TEXT,
    tenure TEXT,
    transaction_type TEXT,
    completion_date TEXT,
    purchase_price TEXT,
    details TEXT,
    lawyer_name TEXT,
    law_firm TEXT,
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
    <li><span class="tick">✓</span> admin_law_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> criminal_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> tort_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> company_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> oil_gas_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> human_rights_cases <span class="badge-new">NEW</span></li>
    <li><span class="tick">✓</span> employment_cases</li>
    <li><span class="tick">✓</span> property_cases <span class="badge-reserved">RESERVED</span></li>
  </ul>

  <a href="dashboard.php" class="btn">→ Go to Dashboard</a>

  <div class="footer">AEP Legal Platform — Database v3.0</div>
</div>
</body>
</html>
