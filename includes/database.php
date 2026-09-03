<?php
/**
 * FILE: includes/database.php
 * SQLite connection singleton with auto-migration for the AEP Legal
 * Intelligence Platform. Provides aep_db()/aep_database() as the shared
 * PDO connection, plus schema helper utilities used across the platform.
 */

if (!function_exists('aep_db')) {
    /**
     * Returns a shared PDO connection to the SQLite database, creating the
     * database file and running schema migrations on first access.
     */
    function aep_db(): PDO
    {
        static $pdo = null;
        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $dbFile = defined('AEP_DB_PATH') ? AEP_DB_PATH : (__DIR__ . '/../data/aep.sqlite');
        $dir = dirname($dbFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');

        aep_ensure_platform_tables($pdo);

        return $pdo;
    }
}

if (!function_exists('aep_database')) {
    /** Alias of aep_db() used by several platform pages. */
    function aep_database(): PDO
    {
        return aep_db();
    }
}

if (!function_exists('aep_table_exists')) {
    function aep_table_exists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :table");
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    }
}

if (!function_exists('aep_first_existing_field')) {
    function aep_first_existing_field(PDO $pdo, string $table, array $candidates): ?string
    {
        if (!aep_table_exists($pdo, $table)) {
            return null;
        }

        $stmt = $pdo->query("PRAGMA table_info({$table})");
        $columns = array_map(static fn(array $row) => $row['name'], $stmt->fetchAll(PDO::FETCH_ASSOC));
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (!function_exists('aep_ensure_platform_tables')) {
    /**
     * Creates every table used by the platform (case management + the
     * 15 domain-specific legal practice tables) if it does not already
     * exist. Safe to call repeatedly; used both by aep_db() on first
     * connection and by init_db.php.
     */
    function aep_ensure_platform_tables(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  client_name TEXT,
  address TEXT,
  date TEXT,
  case_type TEXT,
  facts TEXT,
  instructions TEXT,
  created_at TEXT
);
CREATE TABLE IF NOT EXISTS hr_requests (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_id INTEGER,
  title TEXT,
  note TEXT,
  requested_by TEXT,
  requested_at TEXT,
  status TEXT DEFAULT 'pending'
);
CREATE TABLE IF NOT EXISTS property_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  case_type TEXT,
  property_address TEXT,
  landlord_name TEXT,
  tenant_name TEXT,
  tenancy_type TEXT,
  tenancy_start TEXT,
  tenancy_end TEXT,
  rent_amount TEXT,
  arrears_amount TEXT,
  notice_type TEXT,
  notice_date TEXT,
  possession_status TEXT,
  dispute_summary TEXT,
  legal_basis TEXT,
  evidence_available TEXT,
  representations TEXT,
  outcome TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at TEXT
);
CREATE TABLE IF NOT EXISTS contract_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  case_type TEXT,
  client_name TEXT,
  client_address TEXT,
  client_email TEXT,
  client_phone TEXT,
  opponent_name TEXT,
  opponent_address TEXT,
  opponent_contact TEXT,
  contract_date TEXT,
  contract_value TEXT,
  contract_description TEXT,
  breach_date TEXT,
  breach_description TEXT,
  damages_claimed TEXT,
  court_issued TEXT,
  court_date TEXT,
  court_venue TEXT,
  legal_basis TEXT,
  evidence_available TEXT,
  representations TEXT,
  settlement_offers TEXT,
  without_prejudice TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS criminal_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  case_type TEXT,
  defendant_name TEXT,
  defendant_dob TEXT,
  defendant_address TEXT,
  defendant_email TEXT,
  defendant_phone TEXT,
  defendant_nationality TEXT,
  defendant_custody TEXT,
  offence_date TEXT,
  offence_description TEXT,
  offence_location TEXT,
  charge_date TEXT,
  charges TEXT,
  plea TEXT,
  court_name TEXT,
  court_reference TEXT,
  hearing_date TEXT,
  trial_date TEXT,
  judge_name TEXT,
  prosecution_name TEXT,
  bail_status TEXT,
  bail_conditions TEXT,
  legal_aid TEXT,
  legal_aid_reference TEXT,
  legal_basis TEXT,
  evidence_available TEXT,
  representations TEXT,
  sentence TEXT,
  outcome TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS immigration_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  case_type TEXT,
  client_name TEXT,
  client_dob TEXT,
  client_address TEXT,
  client_email TEXT,
  client_phone TEXT,
  client_nationality TEXT,
  client_passport TEXT,
  client_entry_date TEXT,
  client_visa_type TEXT,
  client_visa_expiry TEXT,
  client_leave_type TEXT,
  sponsor_name TEXT,
  sponsor_address TEXT,
  sponsor_licence TEXT,
  home_office_reference TEXT,
  decision_date TEXT,
  decision_description TEXT,
  appeal_lodged TEXT,
  appeal_date TEXT,
  appeal_tribunal TEXT,
  appeal_reference TEXT,
  removal_date TEXT,
  detention_centre TEXT,
  legal_basis TEXT,
  evidence_available TEXT,
  representations TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS employment_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_type TEXT,
  case_reference TEXT,
  tribunal_reference TEXT,
  status TEXT DEFAULT 'draft',
  claimant_name TEXT,
  claimant_dob TEXT,
  claimant_address TEXT,
  claimant_email TEXT,
  claimant_phone TEXT,
  claimant_job_title TEXT,
  claimant_start_date TEXT,
  claimant_end_date TEXT,
  claimant_salary TEXT,
  claimant_notice_period TEXT,
  respondent_name TEXT,
  respondent_address TEXT,
  respondent_contact TEXT,
  respondent_sector TEXT,
  dismissal_date TEXT,
  dismissal_reason TEXT,
  disciplinary_process TEXT,
  appeal_lodged TEXT,
  appeal_outcome TEXT,
  claim_unfair_dismissal TEXT,
  claim_wrongful_dismissal TEXT,
  claim_discrimination TEXT,
  discrimination_type TEXT,
  claim_harassment TEXT,
  claim_whistleblowing TEXT,
  claim_redundancy TEXT,
  claim_unpaid_wages TEXT,
  claim_other TEXT,
  et1_filed TEXT,
  et1_date TEXT,
  et3_received TEXT,
  et3_date TEXT,
  hearing_date TEXT,
  hearing_venue TEXT,
  preliminary_hearing TEXT,
  legal_basis TEXT,
  schedule_loss TEXT,
  without_prejudice TEXT,
  acas_certificate TEXT,
  acas_number TEXT,
  settlement_offers TEXT,
  representations TEXT,
  evidence_available TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS family_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  case_type TEXT,
  petitioner TEXT,
  respondent TEXT,
  children TEXT,
  relief TEXT,
  court TEXT,
  summary TEXT,
  status TEXT DEFAULT 'open',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS witness_statements (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
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
);
CREATE TABLE IF NOT EXISTS latin_maxims (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  maxim TEXT,
  meaning TEXT,
  category TEXT,
  details TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS admin_law_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  case_type TEXT,
  client_name TEXT,
  client_address TEXT,
  client_email TEXT,
  client_phone TEXT,
  public_body_name TEXT,
  public_body_address TEXT,
  public_body_contact TEXT,
  decision_date TEXT,
  decision_description TEXT,
  grounds_of_challenge TEXT,
  judicial_review TEXT,
  jr_permission_date TEXT,
  jr_hearing_date TEXT,
  jr_venue TEXT,
  tribunal_name TEXT,
  tribunal_reference TEXT,
  tribunal_hearing_date TEXT,
  human_rights_article TEXT,
  regulatory_body TEXT,
  licence_reference TEXT,
  legal_basis TEXT,
  evidence_available TEXT,
  representations TEXT,
  settlement_offers TEXT,
  outcome TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS human_rights_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  right_violated TEXT,
  claimant TEXT,
  respondent TEXT,
  article_section TEXT,
  remedy TEXT,
  status TEXT DEFAULT 'draft',
  grounds TEXT,
  violation_date TEXT,
  summary TEXT,
  created_by TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS tort_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  tort_type TEXT,
  claimant_name TEXT,
  defendant_name TEXT,
  incident_date TEXT,
  damages_claimed TEXT,
  status TEXT DEFAULT 'draft',
  injury_description TEXT,
  duty_of_care TEXT,
  breach TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS oil_gas_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  licence_number TEXT,
  operator TEXT,
  regulatory_body TEXT,
  dispute_type TEXT,
  status TEXT DEFAULT 'draft',
  contract_type TEXT,
  dispute_description TEXT,
  legal_basis TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS commercial_law_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  transaction_type TEXT,
  client_name TEXT,
  client_email TEXT,
  client_contact TEXT,
  client_address TEXT,
  counterparty_name TEXT,
  counterparty_contact TEXT,
  counterparty_address TEXT,
  transaction_date TEXT,
  value_at_risk TEXT,
  transaction_description TEXT,
  dispute_summary TEXT,
  legal_basis TEXT,
  commercial_context TEXT,
  regulatory_compliance TEXT,
  governing_law TEXT,
  evidence_available TEXT,
  representations TEXT,
  settlement_offers TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS international_arbitration_cases (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  case_reference TEXT,
  status TEXT DEFAULT 'draft',
  investor_name TEXT,
  investor_country TEXT,
  investor_entity_type TEXT,
  respondent_state TEXT,
  treaty_basis TEXT,
  treaty_text TEXT,
  investment_description TEXT,
  investment_date TEXT,
  investment_value TEXT,
  dispute_amount TEXT,
  damages_claimed TEXT,
  regulatory_action TEXT,
  regulatory_action_date TEXT,
  dispute_summary TEXT,
  seat_of_arbitration TEXT,
  arbitration_rules TEXT,
  procedural_language TEXT,
  tribunal_composition TEXT,
  preliminary_objections_filed TEXT,
  merits_hearing_date TEXT,
  award_date TEXT,
  award_amount TEXT,
  expropriation_claim TEXT,
  fair_and_equitable_treatment_claim TEXT,
  full_protection_security_claim TEXT,
  most_favored_nation_claim TEXT,
  expert_reports_commissioned TEXT,
  enforcement_strategy TEXT,
  lawyer_name TEXT,
  law_firm TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  client_reference TEXT UNIQUE,
  name TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  address TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS matters (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  matter_reference TEXT UNIQUE,
  client_id INTEGER NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
  subject TEXT,
  practice_area TEXT,
  status TEXT NOT NULL DEFAULT 'open',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS tasks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  matter_id INTEGER REFERENCES matters(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  description TEXT,
  status TEXT NOT NULL DEFAULT 'open',
  due_date TEXT,
  assigned_to INTEGER REFERENCES users(id) ON DELETE SET NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS deadlines (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  matter_id INTEGER REFERENCES matters(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  due_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'open',
  priority TEXT NOT NULL DEFAULT 'normal',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS activity_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
  action TEXT NOT NULL,
  entity TEXT NOT NULL,
  entity_id INTEGER,
  note TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
        );
    }
}
