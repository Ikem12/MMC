<?php
// init_db.php - create SQLite DB (data/aep.sqlite) and seed admin user
// Run once: php init_db.php

$dbFile = __DIR__ . '/data/aep.sqlite';
@mkdir(dirname($dbFile), 0755, true);

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ── Users ──────────────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  username      TEXT    UNIQUE NOT NULL,
  password_hash TEXT    NOT NULL,
  full_name     TEXT,
  email         TEXT,
  is_admin      INTEGER NOT NULL DEFAULT 0,
  role          TEXT    DEFAULT 'user',
  created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

// ── Generic cases (legacy) ─────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, client_name TEXT,
  address TEXT, date TEXT, case_type TEXT, facts TEXT, instructions TEXT, created_at TEXT
)");

// ── HR review requests (legacy) ────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS hr_requests (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_id INTEGER, title TEXT,
  note TEXT, requested_by TEXT, requested_at TEXT, status TEXT DEFAULT 'pending'
)");

// ── Legal Advice ───────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS legal_advice (
  id INTEGER PRIMARY KEY AUTOINCREMENT, client_name TEXT NOT NULL,
  client_address TEXT, client_email TEXT, client_phone TEXT, matter_type TEXT,
  subject TEXT, background TEXT, legal_issues TEXT, advice TEXT,
  recommendations TEXT, disclaimer TEXT, lawyer_name TEXT,
  status TEXT DEFAULT 'draft', created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Draft Letters ──────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS draft_letters (
  id INTEGER PRIMARY KEY AUTOINCREMENT, letter_type TEXT,
  recipient_name TEXT NOT NULL, recipient_address TEXT, recipient_email TEXT,
  sender_name TEXT, sender_address TEXT, subject TEXT, salutation TEXT,
  body TEXT, closing TEXT, enclosures TEXT, lawyer_name TEXT,
  status TEXT DEFAULT 'draft', created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Witness Statements ─────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS witness_statements (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_title TEXT NOT NULL,
  case_number TEXT, court TEXT, witness_name TEXT, witness_address TEXT,
  witness_occupation TEXT, relationship TEXT, statement TEXT, exhibits TEXT,
  declaration TEXT, lawyer_name TEXT, status TEXT DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Skeleton Arguments ─────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS skeleton_arguments (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_title TEXT NOT NULL,
  case_number TEXT, court TEXT, party TEXT, introduction TEXT,
  facts_summary TEXT, issues TEXT, submissions TEXT, conclusion TEXT,
  relief_sought TEXT, authorities TEXT, lawyer_name TEXT,
  status TEXT DEFAULT 'draft', created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Grounds of Appeal ──────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS grounds_of_appeal (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_title TEXT NOT NULL,
  case_number TEXT, lower_court TEXT, appeal_court TEXT, party TEXT,
  judgment_date TEXT, introduction TEXT, grounds TEXT, arguments TEXT,
  relief_sought TEXT, authorities TEXT, lawyer_name TEXT,
  status TEXT DEFAULT 'draft', created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Immigration Cases ──────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS immigration_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, visa_type TEXT NOT NULL,
  case_reference TEXT, ho_reference TEXT, status TEXT DEFAULT 'draft',
  applicant_name TEXT NOT NULL, date_of_birth TEXT, nationality TEXT,
  passport_number TEXT, passport_expiry TEXT, place_of_birth TEXT,
  applicant_address TEXT, applicant_email TEXT, applicant_phone TEXT,
  date_of_entry TEXT, port_of_entry TEXT, residency_type TEXT,
  continuous_residence_from TEXT, total_absences TEXT, breaks_in_residence TEXT,
  visa_history TEXT, marital_status TEXT, sponsor_name TEXT, sponsor_dob TEXT,
  sponsor_nationality TEXT, sponsor_status TEXT, dependants TEXT,
  employment_history TEXT, current_employer TEXT, job_title TEXT, salary TEXT,
  legal_basis TEXT, article8_grounds TEXT, evidence_available TEXT,
  representations TEXT, previous_refusals TEXT, refusal_reasons TEXT,
  lawyer_name TEXT, law_firm TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Admin Law Cases ────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS admin_law_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT,
  client_name TEXT, client_email TEXT, client_phone TEXT, client_address TEXT,
  opposing_party TEXT, court TEXT, judge TEXT, case_title TEXT, date_filed TEXT,
  background TEXT, legal_issues TEXT, grounds TEXT, relief_sought TEXT,
  applicable_laws TEXT, evidence TEXT, representations TEXT, outcome TEXT,
  notes TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Criminal Cases ─────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS criminal_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT,
  defendant_name TEXT, defendant_email TEXT, defendant_phone TEXT,
  defendant_address TEXT, prosecution TEXT, court TEXT, judge TEXT, charge TEXT,
  date_of_offence TEXT, plea TEXT, bail_status TEXT, facts TEXT, defence TEXT,
  mitigation TEXT, applicable_laws TEXT, evidence TEXT, witnesses TEXT,
  outcome TEXT, sentence TEXT, notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Tort Cases ─────────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS tort_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT,
  claimant_name TEXT, claimant_email TEXT, claimant_phone TEXT, claimant_address TEXT,
  defendant_name TEXT, court TEXT, judge TEXT, tort_type TEXT, incident_date TEXT,
  incident_location TEXT, facts TEXT, duty_of_care TEXT, breach TEXT, causation TEXT,
  damage TEXT, relief_sought TEXT, applicable_laws TEXT, evidence TEXT, outcome TEXT,
  notes TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Company Law Cases ──────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS company_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT, company_name TEXT,
  company_rc TEXT, client_name TEXT, client_email TEXT, client_phone TEXT,
  client_address TEXT, opposing_party TEXT, court TEXT, judge TEXT, matter_title TEXT,
  date_filed TEXT, background TEXT, legal_issues TEXT, relief_sought TEXT,
  applicable_laws TEXT, evidence TEXT, outcome TEXT, notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Oil & Gas Cases ────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS oil_gas_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT, client_name TEXT,
  client_email TEXT, client_phone TEXT, client_address TEXT, opposing_party TEXT,
  regulatory_body TEXT, court TEXT, licence_number TEXT, field_location TEXT,
  matter_title TEXT, date_filed TEXT, background TEXT, legal_issues TEXT,
  relief_sought TEXT, applicable_laws TEXT, evidence TEXT, outcome TEXT, notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Human Rights Cases ─────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS human_rights_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_reference TEXT,
  case_title TEXT, status TEXT DEFAULT 'draft', lawyer_name TEXT, law_firm TEXT,
  applicant_name TEXT, applicant_email TEXT, applicant_phone TEXT, applicant_address TEXT,
  respondent TEXT, court TEXT, judge TEXT, rights_violated TEXT, incident_date TEXT,
  background TEXT, legal_issues TEXT, article_relied_on TEXT, relief_sought TEXT,
  applicable_laws TEXT, evidence TEXT, outcome TEXT, notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Employment Cases ───────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS employment_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT, case_type TEXT, case_title TEXT,
  case_reference TEXT, claimant_name TEXT, claimant_email TEXT, claimant_phone TEXT,
  respondent_name TEXT, tribunal TEXT, case_number TEXT, employment_start TEXT,
  employment_end TEXT, job_title TEXT, salary TEXT, claim_details TEXT,
  remedy_sought TEXT, lawyer_name TEXT, law_firm TEXT, status TEXT DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Property Cases ─────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS property_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_type TEXT, case_title TEXT, case_reference TEXT, status TEXT DEFAULT 'draft',
  client_name TEXT, client_email TEXT, client_phone TEXT, client_address TEXT,
  property_address TEXT, property_type TEXT, tenure TEXT,
  party_a_name TEXT, party_a_role TEXT, party_a_address TEXT, party_a_contact TEXT,
  party_b_name TEXT, party_b_role TEXT, party_b_address TEXT, party_b_contact TEXT,
  rent_amount TEXT, deposit_amount TEXT, tenancy_start TEXT, tenancy_end TEXT,
  notice_type TEXT, notice_date TEXT, notice_period TEXT,
  possession_ground TEXT, claim_details TEXT, defence TEXT,
  repairs_issues TEXT, covenant_breach TEXT,
  court_name TEXT, court_date TEXT, court_case_number TEXT,
  applicable_laws TEXT, evidence TEXT, outcome TEXT, notes TEXT,
  lawyer_name TEXT, law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
// Migrate existing property_cases tables to the expanded schema
$_existingCols = array_column($pdo->query("PRAGMA table_info(property_cases)")->fetchAll(PDO::FETCH_ASSOC), 'name');
foreach (['client_email','client_phone','client_address','property_type',
          'party_a_name','party_a_role','party_a_address','party_a_contact',
          'party_b_name','party_b_role','party_b_address','party_b_contact',
          'rent_amount','deposit_amount','tenancy_start','tenancy_end',
          'notice_type','notice_date','notice_period','possession_ground',
          'claim_details','defence','repairs_issues','covenant_breach',
          'court_name','court_date','court_case_number',
          'applicable_laws','evidence','outcome','notes','law_firm'] as $_col) {
    if (!in_array($_col, $_existingCols)) {
        try { $pdo->exec("ALTER TABLE property_cases ADD COLUMN {$_col} TEXT"); } catch (Exception $e) {}
    }
}
unset($_existingCols, $_col);

// ── Latin Maxims ───────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS latin_maxims (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  maxim TEXT NOT NULL, meaning TEXT NOT NULL, category TEXT DEFAULT 'General',
  details TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Seed admin user ────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
$stmt->execute([':u' => 'admin']);
if ($stmt->fetchColumn() == 0) {
    $pw   = 'change-me'; // change immediately after first login
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, password_hash, is_admin, created_at) VALUES (:u,:p,1,:c)"
    );
    $stmt->execute([':u' => 'admin', ':p' => $hash, ':c' => date('c')]);
    echo "Seeded admin user: username=admin / ******";
} else {
    echo "Admin user already present.\n";
}

echo "Database initialised at: {$dbFile}\n";
echo "All 19 tables created/verified.\n";
