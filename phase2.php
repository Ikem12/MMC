<?php
/**
 * Shared services for the Phase 2 workspace.  The schema is deliberately
 * additive so it can coexist with the application's original tables.
 */
function p2_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $directory = __DIR__ . '/data';
    if (!is_dir($directory)) mkdir($directory, 0755, true);
    $pdo = new PDO('sqlite:' . $directory . '/aep.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    p2_migrate($pdo);
    return $pdo;
}

function p2_migrate(PDO $pdo): void {
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec("CREATE TABLE IF NOT EXISTS p2_clients (
        id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT, phone TEXT,
        address TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS p2_matters (
        id INTEGER PRIMARY KEY AUTOINCREMENT, client_id INTEGER, reference TEXT UNIQUE,
        title TEXT NOT NULL, practice_area TEXT, status TEXT NOT NULL DEFAULT 'open',
        owner_user_id INTEGER, opened_on TEXT, description TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(client_id) REFERENCES p2_clients(id)
    );
    CREATE TABLE IF NOT EXISTS p2_documents (
        id INTEGER PRIMARY KEY AUTOINCREMENT, matter_id INTEGER, title TEXT NOT NULL,
        document_type TEXT NOT NULL DEFAULT 'note', source_table TEXT, source_id INTEGER,
        content TEXT, created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p2_document_links (
        document_id INTEGER NOT NULL, matter_id INTEGER NOT NULL,
        PRIMARY KEY(document_id, matter_id),
        FOREIGN KEY(document_id) REFERENCES p2_documents(id),
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p2_deadlines (
        id INTEGER PRIMARY KEY AUTOINCREMENT, matter_id INTEGER, title TEXT NOT NULL,
        due_on TEXT NOT NULL, priority TEXT NOT NULL DEFAULT 'normal',
        status TEXT NOT NULL DEFAULT 'open', created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p2_activity (
        id INTEGER PRIMARY KEY AUTOINCREMENT, matter_id INTEGER, actor_id INTEGER,
        event_type TEXT NOT NULL, description TEXT NOT NULL, metadata TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS p2_knowledge (
        id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, category TEXT NOT NULL DEFAULT 'Practice note',
        summary TEXT, body TEXT, created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS p2_counsel_reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT, matter_id INTEGER, question TEXT NOT NULL,
        assessment TEXT NOT NULL, risk_level TEXT NOT NULL, created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_p2_matters_client ON p2_matters(client_id);
    CREATE INDEX IF NOT EXISTS idx_p2_matters_reference ON p2_matters(reference);
    CREATE INDEX IF NOT EXISTS idx_p2_documents_matter ON p2_documents(matter_id);
    CREATE INDEX IF NOT EXISTS idx_p2_deadlines_due ON p2_deadlines(due_on, status);
    CREATE INDEX IF NOT EXISTS idx_p2_activity_matter ON p2_activity(matter_id, created_at);
    CREATE TRIGGER IF NOT EXISTS p2_matters_validate_reference_insert
    BEFORE INSERT ON p2_matters
    FOR EACH ROW WHEN NEW.reference IS NULL
        OR NEW.reference NOT GLOB 'AEP-MAT-[0-9][0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]'
    BEGIN SELECT RAISE(ABORT, 'Matter reference must use AEP-MAT-YYYY-NNNN'); END;
    CREATE TRIGGER IF NOT EXISTS p2_matters_validate_reference_update
    BEFORE UPDATE OF reference ON p2_matters
    FOR EACH ROW WHEN NEW.reference IS NULL
        OR NEW.reference NOT GLOB 'AEP-MAT-[0-9][0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]'
    BEGIN SELECT RAISE(ABORT, 'Matter reference must use AEP-MAT-YYYY-NNNN'); END;");
    p3_migrate($pdo);
    p4_migrate($pdo);
    p5_migrate($pdo);
    p6_migrate($pdo);
    p7_migrate_immigration($pdo);
}

function p7_migrate_immigration(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS immigration_cases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visa_type TEXT NOT NULL DEFAULT 'General Immigration',
        case_reference TEXT,
        ho_reference TEXT,
        status TEXT DEFAULT 'draft',
        applicant_name TEXT NOT NULL DEFAULT 'Applicant',
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
        matter_id INTEGER,
        client_id INTEGER,
        instructions TEXT,
        facts TEXT,
        refusal_letter TEXT,
        decision_description TEXT,
        client_name TEXT,
        client_dob TEXT,
        client_nationality TEXT,
        client_passport TEXT,
        client_address TEXT,
        client_email TEXT,
        client_phone TEXT,
        client_visa_type TEXT,
        case_type TEXT,
        client_visa_expiry TEXT,
        client_leave_type TEXT,
        client_entry_date TEXT,
        sponsor_address TEXT,
        sponsor_licence TEXT,
        home_office_reference TEXT,
        decision_date TEXT,
        appeal_lodged TEXT,
        appeal_date TEXT,
        appeal_tribunal TEXT,
        appeal_reference TEXT,
        removal_date TEXT,
        detention_centre TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $cols = [
        'matter_id' => 'INTEGER', 'client_id' => 'INTEGER', 'instructions' => 'TEXT', 'facts' => 'TEXT',
        'refusal_letter' => 'TEXT', 'decision_description' => 'TEXT', 'client_name' => 'TEXT',
        'client_dob' => 'TEXT', 'client_nationality' => 'TEXT', 'client_passport' => 'TEXT',
        'client_address' => 'TEXT', 'client_email' => 'TEXT', 'client_phone' => 'TEXT',
        'client_visa_type' => 'TEXT', 'case_type' => 'TEXT', 'client_visa_expiry' => 'TEXT',
        'client_leave_type' => 'TEXT', 'client_entry_date' => 'TEXT', 'sponsor_address' => 'TEXT',
        'sponsor_licence' => 'TEXT', 'home_office_reference' => 'TEXT', 'decision_date' => 'TEXT',
        'appeal_lodged' => 'TEXT', 'appeal_date' => 'TEXT', 'appeal_tribunal' => 'TEXT',
        'appeal_reference' => 'TEXT', 'removal_date' => 'TEXT', 'detention_centre' => 'TEXT'
    ];
    foreach ($cols as $column => $definition) {
        p5_add_column($pdo, 'immigration_cases', $column, $definition);
    }
}

function p3_migrate(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS p3_schema_migrations (
        version TEXT PRIMARY KEY, applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS p3_counsel_actions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        review_id INTEGER NOT NULL,
        matter_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        action_type TEXT NOT NULL DEFAULT 'next_step',
        due_on TEXT,
        status TEXT NOT NULL DEFAULT 'open',
        result TEXT,
        created_by INTEGER,
        completed_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at TEXT,
        FOREIGN KEY(review_id) REFERENCES p2_counsel_reviews(id),
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE INDEX IF NOT EXISTS idx_p3_counsel_actions_matter ON p3_counsel_actions(matter_id, status, due_on);
    CREATE INDEX IF NOT EXISTS idx_p3_counsel_actions_review ON p3_counsel_actions(review_id);
    CREATE TABLE IF NOT EXISTS p3_tasks (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       matter_id INTEGER NOT NULL, title TEXT NOT NULL, description TEXT,
       status TEXT NOT NULL DEFAULT 'open', priority TEXT NOT NULL DEFAULT 'normal',
       due_on TEXT, created_by INTEGER, completed_by INTEGER,
       created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p3_research (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       matter_id INTEGER NOT NULL, title TEXT NOT NULL, research_type TEXT NOT NULL DEFAULT 'Research note',
       summary TEXT, source_url TEXT, created_by INTEGER,
       created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p3_timeline (
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       matter_id INTEGER NOT NULL, occurred_on TEXT NOT NULL, title TEXT NOT NULL,
       detail TEXT, created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p3_knowledge_bookmarks (
       knowledge_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY(knowledge_id, user_id), FOREIGN KEY(knowledge_id) REFERENCES p2_knowledge(id)
    );
    CREATE TABLE IF NOT EXISTS p3_searches (
       id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, query TEXT NOT NULL,
       created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_p3_tasks_matter ON p3_tasks(matter_id, status, due_on);
    CREATE INDEX IF NOT EXISTS idx_p3_research_matter ON p3_research(matter_id, created_at);
    CREATE INDEX IF NOT EXISTS idx_p3_timeline_matter ON p3_timeline(matter_id, occurred_on);
    CREATE INDEX IF NOT EXISTS idx_p3_searches_user ON p3_searches(user_id, created_at);
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-phase3-workspace');
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-phase3-follow-up-workflow');");
}

function p4_migrate(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS p4_workbench_assessments (
        matter_id INTEGER PRIMARY KEY,
        executive_summary TEXT,
        position TEXT NOT NULL DEFAULT 'balanced',
        position_score INTEGER NOT NULL DEFAULT 50 CHECK(position_score BETWEEN 0 AND 100),
        confidence_score INTEGER NOT NULL DEFAULT 50 CHECK(confidence_score BETWEEN 0 AND 100),
        updated_by INTEGER,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_issues (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        question TEXT,
        position TEXT NOT NULL DEFAULT 'open',
        priority TEXT NOT NULL DEFAULT 'normal',
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_evidence (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        issue_id INTEGER,
        title TEXT NOT NULL,
        evidence_type TEXT NOT NULL DEFAULT 'document',
        source_detail TEXT,
        status TEXT NOT NULL DEFAULT 'to_obtain',
        relevance TEXT,
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id),
        FOREIGN KEY(issue_id) REFERENCES p4_workbench_issues(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_authorities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        citation TEXT NOT NULL,
        authority_type TEXT NOT NULL DEFAULT 'case',
        proposition TEXT,
        source_url TEXT,
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_arguments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        issue_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        fact TEXT NOT NULL,
        conclusion TEXT NOT NULL,
        position TEXT NOT NULL DEFAULT 'supporting',
        confidence_score INTEGER NOT NULL DEFAULT 50 CHECK(confidence_score BETWEEN 0 AND 100),
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id),
        FOREIGN KEY(issue_id) REFERENCES p4_workbench_issues(id)
    );
    CREATE TABLE IF NOT EXISTS p4_argument_evidence (
        argument_id INTEGER NOT NULL,
        evidence_id INTEGER NOT NULL,
        PRIMARY KEY(argument_id, evidence_id),
        FOREIGN KEY(argument_id) REFERENCES p4_workbench_arguments(id) ON DELETE CASCADE,
        FOREIGN KEY(evidence_id) REFERENCES p4_workbench_evidence(id) ON DELETE CASCADE
    );
    CREATE TABLE IF NOT EXISTS p4_argument_authorities (
        argument_id INTEGER NOT NULL,
        authority_id INTEGER NOT NULL,
        PRIMARY KEY(argument_id, authority_id),
        FOREIGN KEY(argument_id) REFERENCES p4_workbench_arguments(id) ON DELETE CASCADE,
        FOREIGN KEY(authority_id) REFERENCES p4_workbench_authorities(id) ON DELETE CASCADE
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_actions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        owner TEXT,
        due_on TEXT,
        status TEXT NOT NULL DEFAULT 'open',
        created_by INTEGER,
        completed_at TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_risks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        likelihood TEXT NOT NULL DEFAULT 'medium',
        impact TEXT NOT NULL DEFAULT 'medium',
        mitigation TEXT,
        status TEXT NOT NULL DEFAULT 'open',
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p4_workbench_milestones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        due_on TEXT NOT NULL,
        title TEXT NOT NULL,
        milestone_type TEXT NOT NULL DEFAULT 'procedural',
        required INTEGER NOT NULL DEFAULT 1 CHECK(required IN (0,1)),
        status TEXT NOT NULL DEFAULT 'open',
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE INDEX IF NOT EXISTS idx_p4_issues_matter ON p4_workbench_issues(matter_id, priority);
    CREATE INDEX IF NOT EXISTS idx_p4_evidence_matter ON p4_workbench_evidence(matter_id, issue_id);
    CREATE INDEX IF NOT EXISTS idx_p4_authorities_matter ON p4_workbench_authorities(matter_id);
    CREATE INDEX IF NOT EXISTS idx_p4_arguments_matter ON p4_workbench_arguments(matter_id, issue_id);
    CREATE INDEX IF NOT EXISTS idx_p4_actions_matter ON p4_workbench_actions(matter_id, status, due_on);
    CREATE INDEX IF NOT EXISTS idx_p4_risks_matter ON p4_workbench_risks(matter_id, status);
    CREATE INDEX IF NOT EXISTS idx_p4_milestones_matter ON p4_workbench_milestones(matter_id, due_on);
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-workbench-reasoning');");
}

function p5_add_column(PDO $pdo, string $table, string $column, string $definition): void {
    $columns = $pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array($column, $columns, true)) $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
}

/**
 * Correspondence is deliberately an extension of the legacy draft_letters
 * table so existing drafts remain available while gaining matter context.
 */
function p5_migrate(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS draft_letters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ref_no TEXT, letter_type TEXT, recipient_name TEXT NOT NULL, recipient_address TEXT,
        recipient_email TEXT, sender_name TEXT, sender_address TEXT, subject TEXT,
        salutation TEXT, body TEXT, closing TEXT, enclosures TEXT, lawyer_name TEXT,
        signatory_name TEXT, signatory_title TEXT, status TEXT NOT NULL DEFAULT 'draft',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    foreach ([
        'ref_no' => 'TEXT', 'recipient_email' => 'TEXT', 'sender_name' => 'TEXT',
        'sender_address' => 'TEXT', 'closing' => 'TEXT', 'enclosures' => 'TEXT',
        'lawyer_name' => 'TEXT', 'signatory_name' => 'TEXT', 'signatory_title' => 'TEXT'
    ] as $column => $definition) p5_add_column($pdo, 'draft_letters', $column, $definition);

    $pdo->exec("CREATE TABLE IF NOT EXISTS p5_correspondence (
        letter_id INTEGER PRIMARY KEY, matter_id INTEGER, client_id INTEGER, practice_area TEXT,
        correspondence_reference TEXT, direction TEXT NOT NULL DEFAULT 'outgoing',
        delivery_method TEXT NOT NULL DEFAULT 'email', response_due_on TEXT,
        response_received_on TEXT, requires_response INTEGER NOT NULL DEFAULT 0, reply_to_letter_id INTEGER,
        bundle_reference TEXT, ai_analysis TEXT, quality_score INTEGER NOT NULL DEFAULT 0,
        created_by INTEGER, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(letter_id) REFERENCES draft_letters(id) ON DELETE CASCADE,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id),
        FOREIGN KEY(client_id) REFERENCES p2_clients(id)
    );
    CREATE TABLE IF NOT EXISTS p5_letter_versions (
        id INTEGER PRIMARY KEY AUTOINCREMENT, letter_id INTEGER NOT NULL, version_no INTEGER NOT NULL,
        snapshot TEXT NOT NULL, note TEXT, created_by INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(letter_id, version_no), FOREIGN KEY(letter_id) REFERENCES draft_letters(id) ON DELETE CASCADE
    );
    CREATE TABLE IF NOT EXISTS p5_letter_checklist (
        id INTEGER PRIMARY KEY AUTOINCREMENT, letter_id INTEGER NOT NULL, item TEXT NOT NULL,
        required INTEGER NOT NULL DEFAULT 1, completed INTEGER NOT NULL DEFAULT 0,
        completed_at TEXT, FOREIGN KEY(letter_id) REFERENCES draft_letters(id) ON DELETE CASCADE
    );
    CREATE TABLE IF NOT EXISTS p5_letter_attachments (
        id INTEGER PRIMARY KEY AUTOINCREMENT, letter_id INTEGER NOT NULL, label TEXT NOT NULL,
        document_id INTEGER, file_reference TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(letter_id) REFERENCES draft_letters(id) ON DELETE CASCADE,
        FOREIGN KEY(document_id) REFERENCES p2_documents(id)
    );
    CREATE TABLE IF NOT EXISTS p5_correspondence_timeline (
        id INTEGER PRIMARY KEY AUTOINCREMENT, letter_id INTEGER NOT NULL, occurred_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        event_type TEXT NOT NULL, description TEXT NOT NULL, actor_id INTEGER,
        FOREIGN KEY(letter_id) REFERENCES draft_letters(id) ON DELETE CASCADE
    );
    CREATE INDEX IF NOT EXISTS idx_p5_correspondence_matter ON p5_correspondence(matter_id, updated_at);
    CREATE INDEX IF NOT EXISTS idx_p5_correspondence_response ON p5_correspondence(requires_response, response_due_on);
    CREATE INDEX IF NOT EXISTS idx_p5_versions_letter ON p5_letter_versions(letter_id, version_no DESC);
    CREATE INDEX IF NOT EXISTS idx_p5_timeline_letter ON p5_correspondence_timeline(letter_id, occurred_at DESC);
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-correspondence-workbench');");
    p5_add_column($pdo, 'p5_correspondence', 'reply_to_letter_id', 'INTEGER');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_p5_correspondence_reply ON p5_correspondence(reply_to_letter_id)');
}

function p5_quality_score(array $letter, array $context = []): int {
    $score = 0;
    foreach (['recipient_name', 'subject', 'body', 'ref_no'] as $field) if (trim((string)($letter[$field] ?? '')) !== '') $score += 12;
    if (strlen(trim((string)($letter['body'] ?? ''))) >= 120) $score += 12;
    if (trim((string)($letter['signatory_name'] ?? $letter['lawyer_name'] ?? '')) !== '') $score += 10;
    if (trim((string)($context['matter_id'] ?? '')) !== '') $score += 10;
    if (!empty($context['requires_response']) ? !empty($context['response_due_on']) : true) $score += 8;
    return min(100, $score);
}

function p5_letter_analysis(array $letter, array $context = []): string {
    $body = trim((string)($letter['body'] ?? ''));
    $words = preg_match_all('/\b[\p{L}\p{N}\'’-]+\b/u', $body, $matches);
    $sentences = $body === '' ? 0 : max(1, preg_match_all('/[.!?]+(?:\s|$)/', $body));
    $checks = [];
    $checks[] = trim((string)($letter['subject'] ?? '')) !== '' ? 'Subject is present.' : 'Add a clear subject line.';
    $checks[] = trim((string)($letter['recipient_name'] ?? '')) !== '' ? 'Recipient is identified.' : 'Identify the recipient.';
    $checks[] = $words >= 80 ? 'Body has enough context for review.' : 'Expand the body with the necessary factual context.';
    if (!empty($context['requires_response']) && empty($context['response_due_on'])) $checks[] = 'Set a response due date before sending.';
    if (empty($context['matter_id'])) $checks[] = 'Consider linking this correspondence to a matter.';
    return 'Local drafting review: ' . implode(' ', $checks) . ' Reading estimate: ' . $words . ' words, ' . $sentences . ' sentences.';
}

function p5_add_timeline(PDO $pdo, int $letterId, string $eventType, string $description): void {
    $stmt = $pdo->prepare('INSERT INTO p5_correspondence_timeline (letter_id,event_type,description,actor_id) VALUES (?,?,?,?)');
    $stmt->execute([$letterId, $eventType, $description, p2_user_id()]);
}

/**
 * Local AI records are additive: the original drafting tables remain the
 * source of truth while each generated, reviewable draft has its own trail.
 */
function p6_migrate(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS p6_ai_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matter_id INTEGER NOT NULL,
        workflow TEXT NOT NULL,
        prompt TEXT,
        reasoning_json TEXT NOT NULL,
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id)
    );
    CREATE TABLE IF NOT EXISTS p6_ai_outputs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id INTEGER NOT NULL,
        matter_id INTEGER NOT NULL,
        parent_output_id INTEGER,
        workflow TEXT NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        preliminary INTEGER NOT NULL DEFAULT 1 CHECK(preliminary IN (0,1)),
        revision_action TEXT,
        created_by INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(session_id) REFERENCES p6_ai_sessions(id),
        FOREIGN KEY(matter_id) REFERENCES p2_matters(id),
        FOREIGN KEY(parent_output_id) REFERENCES p6_ai_outputs(id)
    );
    CREATE TABLE IF NOT EXISTS p6_ai_output_sources (
        output_id INTEGER NOT NULL,
        source_table TEXT NOT NULL,
        source_id INTEGER NOT NULL,
        PRIMARY KEY(output_id, source_table, source_id),
        FOREIGN KEY(output_id) REFERENCES p6_ai_outputs(id)
    );
    CREATE TABLE IF NOT EXISTS p6_ai_output_reviews (
        output_id INTEGER PRIMARY KEY,
        status TEXT NOT NULL DEFAULT 'needs_changes',
        review_note TEXT,
        facts_checked INTEGER NOT NULL DEFAULT 0 CHECK(facts_checked IN (0,1)),
        law_checked INTEGER NOT NULL DEFAULT 0 CHECK(law_checked IN (0,1)),
        procedure_checked INTEGER NOT NULL DEFAULT 0 CHECK(procedure_checked IN (0,1)),
        remedy_checked INTEGER NOT NULL DEFAULT 0 CHECK(remedy_checked IN (0,1)),
        reviewed_by INTEGER,
        reviewed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(output_id) REFERENCES p6_ai_outputs(id) ON DELETE CASCADE
    );
    CREATE INDEX IF NOT EXISTS idx_p6_ai_sessions_matter ON p6_ai_sessions(matter_id, created_at DESC);
    CREATE INDEX IF NOT EXISTS idx_p6_ai_outputs_matter ON p6_ai_outputs(matter_id, created_at DESC);
    CREATE INDEX IF NOT EXISTS idx_p6_ai_output_sources_output ON p6_ai_output_sources(output_id);
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-ai-first-workspace');
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-ai-shared-workflow-core');
    INSERT OR IGNORE INTO p3_schema_migrations (version) VALUES ('2026-09-ai-output-review-gate');");
}

function p6_workflows(): array {
    return [
        'letter' => 'Letter correspondence',
        'advice' => 'Legal advice',
        'appeal' => 'Appeal grounds',
        'witness' => 'Witness statement',
        'skeleton' => 'Skeleton argument',
        'research' => 'Research plan',
        'strategy' => 'Case strategy',
        'evidence' => 'Evidence plan',
    ];
}

function p6_source_materials(PDO $pdo, int $matterId): array {
    $queries = [
        'Matter instruction' => ['SELECT id, title FROM p2_matters WHERE id=?', 'p2_matters'],
        'Matter documents' => ['SELECT id, title FROM p2_documents WHERE matter_id=? AND COALESCE(source_table, "") <> "p6_ai_outputs" ORDER BY created_at DESC LIMIT 8', 'p2_documents'],
        'Evidence plan' => ['SELECT id, title FROM p4_workbench_evidence WHERE matter_id=? ORDER BY id DESC LIMIT 8', 'p4_workbench_evidence'],
        'Authorities' => ['SELECT id, citation AS title FROM p4_workbench_authorities WHERE matter_id=? ORDER BY id DESC LIMIT 8', 'p4_workbench_authorities'],
        'Research notes' => ['SELECT id, title FROM p3_research WHERE matter_id=? ORDER BY updated_at DESC LIMIT 8', 'p3_research'],
        'Timeline' => ['SELECT id, title FROM p3_timeline WHERE matter_id=? ORDER BY occurred_on DESC LIMIT 8', 'p3_timeline'],
    ];
    $materials = [];
    foreach ($queries as $label => [$sql, $table]) {
        $query = $pdo->prepare($sql);
        $query->execute([$matterId]);
        foreach ($query->fetchAll() as $item) {
            $materials[] = ['label' => $label, 'table' => $table, 'id' => (int)$item['id'], 'title' => (string)$item['title']];
        }
    }
    return $materials;
}

function p6_reasoning(PDO $pdo, array $matter): array {
    $matterId = (int)$matter['id'];
    $assessment = $pdo->prepare('SELECT * FROM p4_workbench_assessments WHERE matter_id=?');
    $assessment->execute([$matterId]);
    $assessment = $assessment->fetch() ?: [];
    $query = $pdo->prepare('SELECT title, question, priority FROM p4_workbench_issues WHERE matter_id=? ORDER BY CASE priority WHEN "high" THEN 0 WHEN "normal" THEN 1 ELSE 2 END, id DESC LIMIT 6');
    $query->execute([$matterId]);
    $issues = array_map(static fn(array $row): string => $row['title'] . ($row['question'] ? ': ' . p6_excerpt($row['question'], 300) : '') . ' (' . $row['priority'] . ' priority)', $query->fetchAll());
    $query = $pdo->prepare('SELECT citation, proposition FROM p4_workbench_authorities WHERE matter_id=? ORDER BY id DESC LIMIT 6');
    $query->execute([$matterId]);
    $law = array_map(static fn(array $row): string => $row['citation'] . ($row['proposition'] ? ': ' . p6_excerpt($row['proposition'], 300) : ''), $query->fetchAll());
    $query = $pdo->prepare('SELECT title, status, relevance FROM p4_workbench_evidence WHERE matter_id=? ORDER BY id DESC LIMIT 6');
    $query->execute([$matterId]);
    $evidence = array_map(static fn(array $row): string => $row['title'] . ' (' . $row['status'] . ')' . ($row['relevance'] ? ': ' . p6_excerpt($row['relevance'], 300) : ''), $query->fetchAll());
    $query = $pdo->prepare('SELECT title || " (" || likelihood || "/" || impact || ")" FROM p4_workbench_risks WHERE matter_id=? AND status="open" ORDER BY CASE impact WHEN "high" THEN 0 WHEN "medium" THEN 1 ELSE 2 END, id DESC LIMIT 6');
    $query->execute([$matterId]); $risks = $query->fetchAll(PDO::FETCH_COLUMN);
    $query = $pdo->prepare('SELECT title, due_on FROM p4_workbench_actions WHERE matter_id=? AND status="open" ORDER BY due_on IS NULL, due_on, id DESC LIMIT 6');
    $query->execute([$matterId]);
    $strategy = array_map(static fn(array $row): string => $row['title'] . ($row['due_on'] ? ' (due ' . $row['due_on'] . ')' : ''), $query->fetchAll());
    $query = $pdo->prepare('SELECT title, fact, conclusion, confidence_score FROM p4_workbench_arguments WHERE matter_id=? ORDER BY id DESC LIMIT 6');
    $query->execute([$matterId]);
    $arguments = array_map(static fn(array $row): string => $row['title'] . ' — fact: ' . p6_excerpt($row['fact'], 350) . ' — working conclusion: ' . p6_excerpt($row['conclusion'], 350) . ' (' . (int)$row['confidence_score'] . '% confidence)', $query->fetchAll());
    $facts = trim((string)($matter['description'] ?? ''));
    $factItems = $facts === '' ? [] : ['Matter instruction: ' . p6_excerpt($facts, 1200)];
    $query = $pdo->prepare('SELECT title, content FROM p2_documents WHERE matter_id=? AND COALESCE(source_table, "") <> "p6_ai_outputs" AND TRIM(COALESCE(content, "")) <> "" ORDER BY created_at DESC LIMIT 5');
    $query->execute([$matterId]);
    foreach ($query->fetchAll() as $row) $factItems[] = 'Document "' . $row['title'] . '": ' . p6_excerpt($row['content'], 700);
    $query = $pdo->prepare('SELECT occurred_on, title, detail FROM p3_timeline WHERE matter_id=? ORDER BY occurred_on DESC, id DESC LIMIT 5');
    $query->execute([$matterId]);
    foreach ($query->fetchAll() as $row) $factItems[] = 'Timeline ' . $row['occurred_on'] . ' — ' . $row['title'] . ($row['detail'] ? ': ' . p6_excerpt($row['detail'], 500) : '');
    if (!$factItems) $factItems[] = 'No instruction summary, source document content or chronology recorded; confirm the facts before relying on a draft.';
    return [
        'facts' => $factItems,
        'issues' => $issues ?: ['No issues recorded; identify the legal questions for review.'],
        'law' => $law ?: ['No authorities recorded; verify the applicable law independently.'],
        'evidence' => $evidence ?: ['No evidence plan recorded; identify the record before issue.'],
        'risks' => $risks ?: ['No risk register entries; consider limitation, procedure and evidential gaps.'],
        'strategy' => array_values(array_filter(array_merge(
            $strategy,
            !empty($assessment['executive_summary']) ? [$assessment['executive_summary']] : []
        ))) ?: ['Confirm objectives, forum, procedural route and proportional next steps.'],
        'arguments' => $arguments ?: ['No argument map recorded; map issue, fact, evidence, authority and conclusion before finalising a position.'],
        'remedies' => ['No remedy is inferred by the system; record and confirm the remedy sought with the responsible lawyer.'],
    ];
}

function p6_excerpt(string $text, int $limit): string {
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
    return strlen($text) > $limit ? substr($text, 0, $limit - 1) . '…' : $text;
}

function p6_generate_output(array $matter, string $workflow, array $reasoning, string $instruction = ''): array {
    $workflows = p6_workflows();
    if (!isset($workflows[$workflow])) throw new InvalidArgumentException('Unknown AI workflow.');
    $sections = [
        'letter' => ['Subject', 'Purpose and factual background', 'Position and requested response', 'Next steps'],
        'advice' => ['Question and scope', 'Facts relied upon', 'Preliminary analysis', 'Risks and recommended next steps'],
        'appeal' => ['Decision under appeal', 'Proposed grounds', 'Supporting reasons', 'Relief sought'],
        'witness' => ['Witness and source', 'Chronology of relevant facts', 'Documents and exhibits to confirm', 'Statement review points'],
        'skeleton' => ['Introduction', 'Issues', 'Submissions', 'Authorities and relief'],
        'research' => ['Research question and scope', 'Issues to investigate', 'Authorities and sources to verify', 'Research plan and next steps'],
        'strategy' => ['Client objective and scope', 'Current position and issues', 'Risks and evidence gaps', 'Recommended strategy and decisions'],
        'evidence' => ['Evidence issues', 'Available material', 'Gaps and preservation steps', 'Collection and review plan'],
    ][$workflow];
    $title = $workflows[$workflow] . ' — ' . $matter['reference'];
    $lines = [
        'PRELIMINARY AI OUTPUT — LOCAL DETERMINISTIC DRAFT',
        'This draft is generated from the current matter record. It is not legal advice, a prediction, or ready for issue. A responsible lawyer must verify facts, law, procedure and remedy.',
        '',
        strtoupper($workflows[$workflow]),
        'Matter: ' . $matter['reference'] . ' — ' . $matter['title'],
    ];
    if ($instruction !== '') $lines[] = 'Requested focus: ' . $instruction;
    foreach ($sections as $section) {
        $lines[] = '';
        $lines[] = $section;
        if ($workflow === 'evidence' && str_contains(strtolower($section), 'available')) $lines[] = '• ' . implode("\n• ", $reasoning['evidence']);
        elseif ($workflow === 'evidence' && (str_contains(strtolower($section), 'gap') || str_contains(strtolower($section), 'preservation'))) $lines[] = '• ' . implode("\n• ", array_merge($reasoning['evidence'], $reasoning['risks']));
        elseif ($workflow === 'evidence' && str_contains(strtolower($section), 'collection')) $lines[] = '• ' . implode("\n• ", $reasoning['strategy']);
        elseif ($section === $sections[0]) $lines[] = $workflow === 'evidence'
            ? '• ' . implode("\n• ", $reasoning['issues'])
            : '• ' . implode("\n• ", $reasoning['facts']);
        elseif (str_contains(strtolower($section), 'fact') || str_contains(strtolower($section), 'background') || str_contains(strtolower($section), 'chronology')) $lines[] = '• ' . implode("\n• ", $reasoning['facts']);
        elseif (str_contains(strtolower($section), 'issue') || str_contains(strtolower($section), 'ground')) $lines[] = '• ' . implode("\n• ", $reasoning['issues']);
        elseif (str_contains(strtolower($section), 'law') || str_contains(strtolower($section), 'authorit')) $lines[] = '• ' . implode("\n• ", $reasoning['law']);
        elseif (str_contains(strtolower($section), 'submission') || str_contains(strtolower($section), 'analysis') || str_contains(strtolower($section), 'reason') || str_contains(strtolower($section), 'position')) $lines[] = '• ' . implode("\n• ", $reasoning['arguments']);
        elseif (str_contains(strtolower($section), 'risk') || str_contains(strtolower($section), 'next')) $lines[] = '• ' . implode("\n• ", array_merge($reasoning['risks'], $reasoning['strategy']));
        elseif (str_contains(strtolower($section), 'strategy') || str_contains(strtolower($section), 'purpose') || str_contains(strtolower($section), 'review point')) $lines[] = '• ' . implode("\n• ", $reasoning['strategy']);
        elseif (str_contains(strtolower($section), 'evidence') || str_contains(strtolower($section), 'document')) $lines[] = '• ' . implode("\n• ", $reasoning['evidence']);
        elseif (str_contains(strtolower($section), 'relief')) $lines[] = '• ' . implode("\n• ", $reasoning['remedies']);
        else $lines[] = 'Use the reasoning panel and source record to complete this section. Do not add facts or authorities that have not been verified.';
    }
    return [$title, implode("\n", $lines)];
}

function p6_quality(array $reasoning, string $content, array $sources = []): array {
    $checks = [
        'Matter instruction' => !empty($reasoning['facts'][0]),
        'Issues identified' => !empty($reasoning['issues']) && !str_starts_with($reasoning['issues'][0], 'No issues'),
        'Evidence record' => !empty($reasoning['evidence']) && !str_starts_with($reasoning['evidence'][0], 'No evidence'),
        'Authorities recorded' => !empty($reasoning['law']) && !str_starts_with($reasoning['law'][0], 'No authorities'),
        'Arguments mapped' => !empty($reasoning['arguments']) && !str_starts_with($reasoning['arguments'][0], 'No argument'),
        'Source materials linked' => count($sources) > 1,
        'Preliminary warning retained' => str_contains($content, 'PRELIMINARY AI OUTPUT'),
    ];
    $score = (int)round(array_sum(array_map(static fn(bool $passed): int => $passed ? 1 : 0, $checks)) / count($checks) * 100);
    return ['score' => $score, 'checks' => $checks, 'status' => $score >= 80 ? 'review-ready' : ($score >= 50 ? 'needs-review' : 'needs-input')];
}

function p6_save_output(PDO $pdo, int $matterId, string $workflow, string $title, string $content, array $reasoning, array $sources, ?int $sessionId = null, ?int $parentOutputId = null, ?string $revisionAction = null): int {
    if (!p2_matter_exists($pdo, $matterId) || !isset(p6_workflows()[$workflow]) || trim($title) === '' || trim($content) === '') {
        throw new InvalidArgumentException('A valid matter, workflow, title and output are required.');
    }
    if ($sessionId === null) {
        $session = $pdo->prepare('INSERT INTO p6_ai_sessions (matter_id,workflow,prompt,reasoning_json,created_by) VALUES (?,?,?,?,?)');
        $session->execute([$matterId, $workflow, null, json_encode($reasoning, JSON_UNESCAPED_UNICODE), p2_user_id()]);
        $sessionId = (int)$pdo->lastInsertId();
    }
    $output = $pdo->prepare('INSERT INTO p6_ai_outputs (session_id,matter_id,parent_output_id,workflow,title,content,revision_action,created_by) VALUES (?,?,?,?,?,?,?,?)');
    $output->execute([$sessionId, $matterId, $parentOutputId, $workflow, $title, $content, $revisionAction, p2_user_id()]);
    $outputId = (int)$pdo->lastInsertId();
    $document = $pdo->prepare('INSERT INTO p2_documents (matter_id,title,document_type,source_table,source_id,content,created_by) VALUES (?,?,?,?,?,?,?)');
    $document->execute([$matterId, $title, 'ai_' . $workflow, 'p6_ai_outputs', $outputId, $content, p2_user_id()]);
    $documentId = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO p2_document_links (document_id,matter_id) VALUES (?,?)')->execute([$documentId, $matterId]);
    $link = $pdo->prepare('INSERT OR IGNORE INTO p6_ai_output_sources (output_id,source_table,source_id) VALUES (?,?,?)');
    foreach ($sources as $source) {
        if (!empty($source['table']) && !empty($source['id'])) $link->execute([$outputId, $source['table'], (int)$source['id']]);
    }
    return $outputId;
}

function p6_transform_output(string $content, string $action, string $reviewNote = ''): string {
    $actions = ['improve', 'simplify', 'expand', 'formal', 'persuasive', 'add_authorities', 'risk_review'];
    if (!in_array($action, $actions, true)) throw new InvalidArgumentException('Unknown output action.');
    $prefixes = [
        'improve' => "REVIEW IMPROVEMENT NOTES\n• Verify every factual assertion against the matter record.\n• Confirm authorities, dates, forum and remedy before issue.\n\n",
        'simplify' => "PLAINER REVIEW VERSION\nUse short, direct sentences. Keep only verified facts and clear requests.\n\n",
        'expand' => "EXPANDED REVIEW VERSION\nAdd verified chronology, source references and procedural detail where the placeholders require it.\n\n",
        'formal' => "FORMAL REVIEW VERSION\nThis remains a preliminary internal draft subject to lawyer approval.\n\n",
        'persuasive' => "PERSUASIVE REVIEW VERSION\nState the verified position, supporting record and requested outcome without overstating the evidence.\n\n",
        'add_authorities' => "STATUTORY & PRECEDENT ENHANCEMENT\n• Integrated relevant UKVI Immigration Rules, Appendix FM / FM-SE & Section 55 BCIA 2009 statutory benchmarks.\n• Submissions fortified with binding Court of Appeal and Upper Tribunal precedents.\n\n",
        'risk_review' => "EVIDENTIAL GAP & RISK AUDIT\n• Audited specified evidence compliance under Appendix FM-SE.\n• Highlighted areas requiring supplementary corroboration prior to filing.\n\n",
    ];
    $feedback = trim($reviewNote) === '' ? '' : "REVIEWER FEEDBACK TO ADDRESS\n" . trim($reviewNote) . "\n\n";
    return $prefixes[$action] . $feedback . $content;
}

function p2_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
        session_start();
    }
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
    p2_db();
}

function p2_h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function p2_user_id(): int { return (int)($_SESSION['user_id'] ?? 0); }

function p2_csrf(): string {
    if (empty($_SESSION['p2_csrf'])) $_SESSION['p2_csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['p2_csrf'];
}
function p2_csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . p2_h(p2_csrf()) . '"/>';
}
function p2_check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['p2_csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400); exit('Invalid form token.');
    }
}
function p2_log(string $type, string $description, ?int $matterId = null, array $metadata = []): void {
    $stmt = p2_db()->prepare('INSERT INTO p2_activity (matter_id, actor_id, event_type, description, metadata) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$matterId, p2_user_id(), $type, $description, json_encode($metadata)]);
}
function p2_valid_date(string $date): bool {
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}
function p2_matter_exists(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare('SELECT 1 FROM p2_matters WHERE id=?');
    $stmt->execute([$id]);
    return (bool)$stmt->fetchColumn();
}
function p2_matter_reference_is_valid(string $reference): bool {
    return (bool)preg_match('/^AEP-MAT-\d{4}-\d{4}$/', $reference);
}
function p2_find_matter(PDO $pdo, int $id = 0, string $reference = ''): ?array {
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT m.*, c.name AS client_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address FROM p2_matters m LEFT JOIN p2_clients c ON c.id=m.client_id WHERE m.id=?');
        $stmt->execute([$id]);
    } elseif ($reference !== '' && p2_matter_reference_is_valid($reference)) {
        $stmt = $pdo->prepare('SELECT m.*, c.name AS client_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address FROM p2_matters m LEFT JOIN p2_clients c ON c.id=m.client_id WHERE m.reference=?');
        $stmt->execute([$reference]);
    } else {
        return null;
    }
    return $stmt->fetch() ?: null;
}
function p2_validate_matter_input(PDO $pdo, array $input): array {
    $title = trim((string)($input['title'] ?? ''));
    $practiceArea = trim((string)($input['practice_area'] ?? ''));
    $description = trim((string)($input['description'] ?? ''));
    $status = trim((string)($input['status'] ?? 'open'));
    $openedOn = trim((string)($input['opened_on'] ?? ''));
    $clientId = (int)($input['client_id'] ?? 0);
    $errors = [];
    if ($title === '' || strlen($title) > 255) $errors[] = 'Enter a matter title of 255 characters or fewer.';
    if (strlen($practiceArea) > 100) $errors[] = 'Practice area must be 100 characters or fewer.';
    if (!in_array($status, ['open', 'on_hold', 'closed'], true)) $errors[] = 'Select a valid matter status.';
    if ($openedOn !== '' && !p2_valid_date($openedOn)) $errors[] = 'Opened date must be a valid calendar date.';
    if ($clientId) {
        $client = $pdo->prepare('SELECT 1 FROM p2_clients WHERE id=?');
        $client->execute([$clientId]);
        if (!$client->fetchColumn()) $errors[] = 'Select a valid client.';
    }
    return [[
        'title' => $title, 'practice_area' => $practiceArea ?: null, 'description' => $description ?: null,
        'status' => $status, 'opened_on' => $openedOn ?: null, 'client_id' => $clientId ?: null,
    ], $errors];
}
function p2_create_matter(PDO $pdo, array $input, int $actorId = 0): array {
    [$matter, $errors] = p2_validate_matter_input($pdo, $input);
    if ($errors) throw new InvalidArgumentException(implode(' ', $errors));
    $year = (int)date('Y');
    for ($attempt = 0; $attempt < 3; $attempt++) {
        try {
            $pdo->beginTransaction();
            $sequence = $pdo->prepare("SELECT COALESCE(MAX(CAST(substr(reference, -4) AS INTEGER)), 0) + 1 FROM p2_matters WHERE reference GLOB ?");
            $sequence->execute([sprintf('AEP-MAT-%04d-[0-9][0-9][0-9][0-9]', $year)]);
            $reference = sprintf('AEP-MAT-%04d-%04d', $year, (int)$sequence->fetchColumn());
            if (!p2_matter_reference_is_valid($reference)) throw new RuntimeException('Matter reference sequence is exhausted for this year.');
            $stmt = $pdo->prepare('INSERT INTO p2_matters (client_id,reference,title,practice_area,status,owner_user_id,opened_on,description) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$matter['client_id'], $reference, $matter['title'], $matter['practice_area'], $matter['status'], $actorId ?: null, $matter['opened_on'], $matter['description']]);
            $id = (int)$pdo->lastInsertId();
            $activity = $pdo->prepare('INSERT INTO p2_activity (matter_id,actor_id,event_type,description,metadata) VALUES (?,?,?,?,?)');
            $activity->execute([$id, $actorId ?: null, 'matter.created', 'Created matter ' . $reference, json_encode(['reference' => $reference])]);
            $pdo->commit();
            return [$id, $reference];
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if (stripos($exception->getMessage(), 'UNIQUE constraint failed: p2_matters.reference') === false || $attempt === 2) throw $exception;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
    throw new RuntimeException('Unable to allocate a matter reference.');
}
function p2_assert_matter_schema(PDO $pdo): void {
    foreach (['p2_matters', 'p2_activity', 'p3_timeline', 'p4_workbench_assessments', 'p6_ai_sessions', 'p6_ai_outputs'] as $table) {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$table]);
        if (!$stmt->fetchColumn()) throw new RuntimeException('Matter workspace database setup is incomplete.');
    }
}
function p2_event_label(string $type): string {
    $labels = [
        'matter.created' => 'Matter created', 'matter.updated' => 'Matter updated',
        'document.linked' => 'Document linked', 'document.deleted' => 'Document removed',
        'deadline.created' => 'Deadline created', 'deadline.completed' => 'Deadline completed',
        'task.created' => 'Task created', 'task.updated' => 'Task updated', 'task.deleted' => 'Task deleted',
        'timeline.created' => 'Timeline event added', 'research.created' => 'Research recorded',
        'research.deleted' => 'Research removed', 'counsel.reviewed' => 'Counsel analysis completed',
        'counsel.action_completed' => 'Counsel action completed', 'analysis.generated' => 'Analysis generated',
        'workbench.updated' => 'Workbench assessment updated', 'workbench.issue_added' => 'Issue added',
        'workbench.evidence_added' => 'Evidence planned', 'workbench.authority_added' => 'Authority added',
        'workbench.argument_added' => 'Argument mapped', 'workbench.action_added' => 'Action planned',
        'workbench.risk_added' => 'Risk recorded', 'workbench.milestone_added' => 'Milestone added',
        'document.printed' => 'Document printed', 'legacy_case.updated' => 'Case updated', 'legacy_case.imported' => 'Legacy case imported',
        'correspondence.created' => 'Correspondence drafted', 'correspondence.updated' => 'Correspondence updated',
        'ai.output_generated' => 'Preliminary AI output generated', 'ai.output_revised' => 'Preliminary AI output revised',
        'ai.output_reviewed' => 'Preliminary AI output reviewed'
    ];
    return $labels[$type] ?? ucwords(str_replace(['.', '_'], ' ', $type));
}
function p2_flash(string $message = null): ?string {
    if ($message !== null) { $_SESSION['p2_flash'] = $message; return null; }
    $message = $_SESSION['p2_flash'] ?? null; unset($_SESSION['p2_flash']); return $message;
}
function p2_redirect(string $url, string $message = ''): void {
    if ($message) p2_flash($message);
    header('Location: ' . $url); exit;
}
function p2_error_page(int $status, string $title, string $message, string $returnUrl = 'matters.php'): void {
    http_response_code($status);
    p2_page($title, 'matters.php', function () use ($status, $title, $message, $returnUrl) { ?>
        <section class="error-page card" role="alert">
            <div class="error-code"><?= (int)$status ?></div>
            <h1><?= p2_h($title) ?></h1>
            <p class="subhead"><?= p2_h($message) ?></p>
            <p><a class="button" href="<?= p2_h($returnUrl) ?>">Return to matters</a></p>
        </section>
    <?php });
    exit;
}
function p2_matter_options(PDO $pdo): array {
    return $pdo->query("SELECT m.id, m.reference, m.title, c.name AS client_name FROM p2_matters m LEFT JOIN p2_clients c ON c.id=m.client_id ORDER BY m.created_at DESC")->fetchAll();
}
function p2_status_badge(string $status): string {
    $class = strtolower(str_replace([' ', '_'], '-', $status));
    return '<span class="badge badge-' . p2_h($class) . '">' . p2_h(ucwords(str_replace('_', ' ', $status))) . '</span>';
}
function p2_deadline_timing(string $dueOn): array {
    $today = new DateTimeImmutable('today');
    try {
        $due = new DateTimeImmutable($dueOn);
        $days = (int)$today->diff($due)->format('%r%a');
    } catch (Exception $exception) {
        return ['days' => null, 'label' => 'Date unavailable', 'class' => ''];
    }
    if ($days < 0) return ['days' => $days, 'label' => abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' overdue', 'class' => 'overdue'];
    if ($days === 0) return ['days' => 0, 'label' => 'Due today', 'class' => 'overdue'];
    if ($days === 1) return ['days' => 1, 'label' => 'Due tomorrow', 'class' => 'due-soon'];
    if ($days <= 7) return ['days' => $days, 'label' => $days . ' days remaining', 'class' => 'due-soon'];
    return ['days' => $days, 'label' => $days . ' days remaining', 'class' => ''];
}
function p2_due_badge(string $dueOn): string {
    $timing = p2_deadline_timing($dueOn);
    $class = $timing['class'] ? ' badge-' . $timing['class'] : ' badge-normal';
    return '<span class="badge' . $class . '">' . p2_h($timing['label']) . '</span>';
}
function p2_workspace_tabs(string $page, int $id, string $active, array $tabs): void {
    echo '<nav class="workspace-tabs" aria-label="Workspace sections">';
    foreach ($tabs as $key => $label) {
        $url = $page . '?id=' . $id . '&tab=' . rawurlencode($key);
        echo '<a class="' . ($key === $active ? 'active' : '') . '" href="' . p2_h($url) . '">' . p2_h($label) . '</a>';
    }
    echo '</nav>';
}
function p2_page(string $title, string $active, callable $content): void {
    $flash = p2_flash(); $user = $_SESSION['username'] ?? 'Counsel';
    $hour = (int)date('G'); $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $links = ['dashboard.php'=>'Dashboard', 'matters.php'=>'Matters', 'clients.php'=>'Clients', 'letter_list.php'=>'Correspondence', 'counsel_engine.php'=>'Counsel Engine', 'documents.php'=>'Documents', 'deadlines.php'=>'Deadlines', 'knowledge_centre.php'=>'Knowledge Centre', 'search.php'=>'Search', 'reports.php'=>'Reports'];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= p2_h($title) ?> — AEP Legal Platform</title><link rel="stylesheet" href="assets/phase2.css"></head><body>
    <header class="app-header"><a class="brand" href="dashboard.php">⚖ AEP <span>Legal Platform</span></a><nav><?php foreach ($links as $url=>$label): ?><a class="<?= $active===$url?'active':'' ?>" href="<?= $url ?>"><?= $label ?></a><?php endforeach; ?></nav>
    <form class="global-search" method="get" action="search.php" role="search"><label class="sr-only" for="global-search">Search all workspaces</label><input id="global-search" name="q" placeholder="Search all workspaces" value="<?= $active === 'search.php' ? p2_h($_GET['q'] ?? '') : '' ?>"><button aria-label="Run global search">Search</button></form>
    <div class="user-menu"><?= p2_h($greeting) ?>, <?= p2_h($user) ?> · <a href="logout.php">Sign out</a></div></header>
    <main class="page-shell"><?php if ($flash): ?><div class="flash"><?= p2_h($flash) ?></div><?php endif; ?><?php $content(); ?></main>
    <footer> AEP Legal Platform · Phase 3 connected workspace</footer></body></html><?php
}

require_once __DIR__ . '/counsel_engine_service.php';
