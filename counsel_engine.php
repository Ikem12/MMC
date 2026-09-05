<?php
require_once __DIR__ . '/phase2.php';
p2_start();
$pdo = p2_db();
p2_check_csrf();

function wb_text(string $value, int $limit): bool {
    return $value !== '' && strlen($value) <= $limit;
}
function wb_matter_item_exists(PDO $pdo, string $table, int $id, int $matterId): bool {
    $allowed = ['p4_workbench_issues', 'p4_workbench_evidence', 'p4_workbench_authorities'];
    if (!in_array($table, $allowed, true) || $id < 1) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE id=? AND matter_id=?");
    $stmt->execute([$id, $matterId]);
    return (bool)$stmt->fetchColumn();
}
function wb_redirect(int $matterId, string $message, string $fragment = ''): void {
    p2_redirect('counsel_engine.php?matter_id=' . $matterId . $fragment, $message);
}
function wb_document_content(array $matter, array $assessment, string $documentType): string {
    $summary = trim((string)($assessment['executive_summary'] ?? ''));
    $instruction = trim((string)($matter['description'] ?? ''));
    $background = $summary ?: ($instruction ?: 'Review the matter record before issue.');
    return strtoupper($documentType) . "\n\nMatter: {$matter['reference']} — {$matter['title']}\n\n"
        . "Purpose: Draft for review using the workbench assessment and connected records.\n\n"
        . "Background\n{$background}\n\n"
        . "Next step: Confirm the factual record, authorities and procedural dates before issue.";
}

function wb_import_legacy_case(PDO $pdo, int $matterId, string $domain, int $caseId): bool {
    $tables = [
        'human_rights' => 'human_rights_cases',
        'tort' => 'tort_cases',
        'admin_law' => 'admin_law_cases',
        'oil_gas' => 'oil_gas_cases',
    ];
    if (!isset($tables[$domain]) || $caseId < 1) {
        throw new InvalidArgumentException('Select a valid legacy case to import.');
    }
    $case = $pdo->prepare('SELECT * FROM ' . $tables[$domain] . ' WHERE id=?');
    $case->execute([$caseId]);
    $case = $case->fetch();
    if (!$case) throw new InvalidArgumentException('The selected legacy case is unavailable.');
    $title = trim((string)($case['title'] ?? 'Legacy case #' . $caseId));
    $existing = $pdo->prepare('SELECT id FROM p2_documents WHERE matter_id=? AND source_table=? AND source_id=?');
    $existing->execute([$matterId, $tables[$domain], $caseId]);
    if ($existing->fetchColumn()) return false;
    $labels = [
        'title' => 'Title', 'claimant' => 'Claimant / applicant', 'respondent' => 'Respondent / defendant',
        'summary' => 'Summary', 'status' => 'Legacy status', 'grounds' => 'Grounds',
        'right_violated' => 'Right identified', 'article_section' => 'Provision recorded',
        'remedy' => 'Remedy recorded', 'tort_type' => 'Tort type', 'duty_of_care' => 'Duty of care',
        'damages' => 'Damages', 'decision_maker' => 'Decision maker', 'ground_of_review' => 'Ground of review',
        'relief_sought' => 'Relief sought', 'licence_number' => 'Licence number',
        'field_location' => 'Field location', 'contract_type' => 'Contract type',
    ];
    $lines = ['IMPORTED LEGACY CASE RECORD — VERIFY BEFORE RELYING ON IT'];
    foreach ($labels as $field => $label) {
        if (isset($case[$field]) && trim((string)$case[$field]) !== '') $lines[] = $label . ': ' . trim((string)$case[$field]);
    }
    $document = $pdo->prepare('INSERT INTO p2_documents (matter_id,title,document_type,source_table,source_id,content,created_by) VALUES (?,?,?,?,?,?,?)');
    $document->execute([$matterId, 'Imported legacy case — ' . $title, 'legacy_case_record', $tables[$domain], $caseId, implode("\n\n", $lines), p2_user_id()]);
    $documentId = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT OR IGNORE INTO p2_document_links (document_id,matter_id) VALUES (?,?)')->execute([$documentId, $matterId]);
    return true;
}

$selected = (int)($_GET['matter_id'] ?? $_POST['matter_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export'])) {
    $outputId = $_GET['export'] === 'latest' ? 0 : (int)$_GET['export'];
    $format = $_GET['format'] ?? 'text';
    $output = $pdo->prepare($outputId
        ? 'SELECT o.title,o.content FROM p6_ai_outputs o WHERE o.id=? AND o.matter_id=?'
        : 'SELECT o.title,o.content FROM p6_ai_outputs o WHERE o.matter_id=? ORDER BY o.created_at DESC,o.id DESC LIMIT 1');
    $output->execute($outputId ? [$outputId, $selected] : [$selected]);
    $output = $output->fetch();
    if (!$output) p2_error_page(404, 'AI output not found', 'This output is unavailable on the selected matter.', 'counsel_engine.php?matter_id=' . $selected);
    if ($format === 'word') {
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="aep-preliminary-ai-output-' . $outputId . '.doc"');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . p2_h($output['title']) . '</title></head><body><pre style="white-space:pre-wrap;font-family:Calibri,Arial,sans-serif">' . p2_h($output['content']) . '</pre></body></html>';
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="aep-preliminary-ai-output-' . $outputId . '.txt"');
        echo $output['content'];
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $matterId = (int)($_POST['matter_id'] ?? 0);
    if (!p2_matter_exists($pdo, $matterId)) p2_error_page(404, 'Matter not found', 'Select an available matter before updating the workbench.');

    if ($action === 'import_legacy_case') {
        try {
            $pdo->beginTransaction();
            $imported = wb_import_legacy_case($pdo, $matterId, (string)($_POST['legacy_domain'] ?? ''), (int)($_POST['legacy_case_id'] ?? 0));
            $pdo->commit();
            if ($imported) p2_log('legacy_case.imported', 'Imported a legacy case record into the shared Counsel Workbench', $matterId, ['domain' => (string)($_POST['legacy_domain'] ?? ''), 'case_id' => (int)($_POST['legacy_case_id'] ?? 0)]);
            wb_redirect($matterId, $imported ? 'Legacy case record linked to this matter. Review it in the reasoning panel before generating output.' : 'This legacy case is already linked to the selected matter.', '#ai-workflows');
        } catch (InvalidArgumentException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            wb_redirect($matterId, $exception->getMessage());
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            wb_redirect($matterId, 'The legacy case could not be imported. Please verify the selected record and try again.');
        }
    }

    if ($action === 'review') {
        $question = trim($_POST['question'] ?? '');
        $facts = trim($_POST['facts'] ?? '');
        if (!wb_text($question, 1000)) wb_redirect($matterId, 'Enter the question for preliminary review.', '#preliminary-review');
        $missing = [];
        if (strlen($facts) < 80) $missing[] = 'a fuller factual chronology';
        if (!preg_match('/\b(date|on \d|20\d\d)\b/i', $facts)) $missing[] = 'key dates and limitation analysis';
        if (!preg_match('/\b(evidence|document|email|letter|witness)\b/i', $facts)) $missing[] = 'identified supporting evidence';
        $score = max(20, 100 - count($missing) * 20 - (stripos($question, 'urgent') !== false ? 10 : 0));
        $risk = $score >= 80 ? 'low' : ($score >= 55 ? 'medium' : 'high');
        $review = "Preliminary review only. The current instruction supports a {$risk}-risk working position (readiness score {$score}/100). ";
        $review .= $missing ? 'Before advice is finalised, obtain ' . implode(', ', $missing) . '.' : 'The instruction is sufficiently developed for counsel to confirm the governing law, remedies and procedural route.';
        $review .= ' Consider the applicable limitation period, forum, opponent response and settlement options before committing the client to a course of action.';
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO p2_counsel_reviews (matter_id,question,assessment,risk_level,created_by) VALUES (?,?,?,?,?)');
            $stmt->execute([$matterId, $question, $review, $risk, p2_user_id()]);
            $reviewId = (int)$pdo->lastInsertId();
            $step = $pdo->prepare('INSERT INTO p3_counsel_actions (review_id,matter_id,title,action_type,due_on,created_by) VALUES (?,?,?,?,?,?)');
            foreach ([['Confirm the client objective, jurisdiction and governing law', 'legal_analysis'], ['Preserve evidence and identify missing documents or witnesses', 'evidence'], ['Calculate limitation and procedural dates', 'deadline']] as [$title, $type]) {
                $step->execute([$reviewId, $matterId, $title, $type, null, p2_user_id()]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('counsel.reviewed', 'Completed Counsel Engine preliminary review', $matterId, ['risk_level' => $risk, 'review_id' => $reviewId]);
        wb_redirect($matterId, 'Preliminary review and follow-up actions created.', '#preliminary-review');
    }

    if ($action === 'complete_action') {
        $actionId = (int)($_POST['action_id'] ?? 0);
        $result = trim($_POST['result'] ?? '');
        if (strlen($result) > 10000) wb_redirect($matterId, 'Keep the action result to 10,000 characters or fewer.', '#preliminary-review');
        $stmt = $pdo->prepare("UPDATE p3_counsel_actions SET status='complete', result=?, completed_by=?, completed_at=CURRENT_TIMESTAMP WHERE id=? AND matter_id=? AND status='open'");
        $stmt->execute([$result ?: null, p2_user_id(), $actionId, $matterId]);
        if ($stmt->rowCount()) p2_log('counsel.action_completed', 'Completed a Counsel Engine action', $matterId, ['action_id' => $actionId]);
        wb_redirect($matterId, $stmt->rowCount() ? 'Counsel action completed and result recorded.' : 'That action is no longer available.', '#preliminary-review');
    }

    if ($action === 'save_assessment') {
        $summary = trim($_POST['executive_summary'] ?? '');
        $position = $_POST['position'] ?? 'balanced';
        $positionScore = filter_var($_POST['position_score'] ?? null, FILTER_VALIDATE_INT);
        $confidenceScore = filter_var($_POST['confidence_score'] ?? null, FILTER_VALIDATE_INT);
        if (strlen($summary) > 10000 || !in_array($position, ['favourable', 'balanced', 'adverse'], true)
            || $positionScore === false || $positionScore < 0 || $positionScore > 100
            || $confidenceScore === false || $confidenceScore < 0 || $confidenceScore > 100) {
            wb_redirect($matterId, 'Enter an executive summary and valid assessment scores.');
        }
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_assessments (matter_id,executive_summary,position,position_score,confidence_score,updated_by)
            VALUES (?,?,?,?,?,?)
            ON CONFLICT(matter_id) DO UPDATE SET executive_summary=excluded.executive_summary, position=excluded.position,
            position_score=excluded.position_score, confidence_score=excluded.confidence_score, updated_by=excluded.updated_by, updated_at=CURRENT_TIMESTAMP');
        $stmt->execute([$matterId, $summary ?: null, $position, $positionScore, $confidenceScore, p2_user_id()]);
        p2_log('workbench.updated', 'Updated executive summary and case assessment', $matterId);
        wb_redirect($matterId, 'Executive summary and assessment saved.');
    }

    if ($action === 'add_issue') {
        $title = trim($_POST['title'] ?? '');
        $question = trim($_POST['question'] ?? '');
        $position = $_POST['position'] ?? 'open';
        $priority = $_POST['priority'] ?? 'normal';
        if (!wb_text($title, 255) || strlen($question) > 5000 || !in_array($position, ['open', 'client', 'opponent'], true)
            || !in_array($priority, ['low', 'normal', 'high'], true)) wb_redirect($matterId, 'Enter a valid issue, position and priority.');
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_issues (matter_id,title,question,position,priority,created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$matterId, $title, $question ?: null, $position, $priority, p2_user_id()]);
        p2_log('workbench.issue_added', 'Added issue: ' . $title, $matterId);
        wb_redirect($matterId, 'Issue added to the matrix.');
    }

    if ($action === 'add_evidence') {
        $title = trim($_POST['title'] ?? '');
        $issueId = (int)($_POST['issue_id'] ?? 0);
        $type = $_POST['evidence_type'] ?? 'document';
        $status = $_POST['status'] ?? 'to_obtain';
        $source = trim($_POST['source_detail'] ?? '');
        $relevance = trim($_POST['relevance'] ?? '');
        if (!wb_text($title, 255) || ($issueId && !wb_matter_item_exists($pdo, 'p4_workbench_issues', $issueId, $matterId))
            || !in_array($type, ['document', 'witness', 'expert', 'digital', 'other'], true)
            || !in_array($status, ['to_obtain', 'available', 'reviewed'], true) || strlen($source) > 1000 || strlen($relevance) > 5000) {
            wb_redirect($matterId, 'Enter valid evidence details.');
        }
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_evidence (matter_id,issue_id,title,evidence_type,source_detail,status,relevance,created_by) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$matterId, $issueId ?: null, $title, $type, $source ?: null, $status, $relevance ?: null, p2_user_id()]);
        p2_log('workbench.evidence_added', 'Added evidence item: ' . $title, $matterId);
        wb_redirect($matterId, 'Evidence item added to the planner.');
    }

    if ($action === 'add_authority') {
        $citation = trim($_POST['citation'] ?? '');
        $type = $_POST['authority_type'] ?? 'case';
        $proposition = trim($_POST['proposition'] ?? '');
        $url = trim($_POST['source_url'] ?? '');
        if (!wb_text($citation, 500) || !in_array($type, ['case', 'statute', 'regulation', 'guidance', 'other'], true)
            || strlen($proposition) > 5000 || strlen($url) > 2000 || ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false)) {
            wb_redirect($matterId, 'Enter a valid authority citation and optional source URL.');
        }
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_authorities (matter_id,citation,authority_type,proposition,source_url,created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$matterId, $citation, $type, $proposition ?: null, $url ?: null, p2_user_id()]);
        p2_log('workbench.authority_added', 'Added authority: ' . $citation, $matterId);
        wb_redirect($matterId, 'Authority added and available to link to arguments.');
    }

    if ($action === 'add_argument') {
        $issueId = (int)($_POST['issue_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $fact = trim($_POST['fact'] ?? '');
        $conclusion = trim($_POST['conclusion'] ?? '');
        $position = $_POST['position'] ?? 'supporting';
        $confidence = filter_var($_POST['confidence_score'] ?? null, FILTER_VALIDATE_INT);
        $evidenceIds = array_unique(array_map('intval', (array)($_POST['evidence_ids'] ?? [])));
        $authorityIds = array_unique(array_map('intval', (array)($_POST['authority_ids'] ?? [])));
        if (!wb_matter_item_exists($pdo, 'p4_workbench_issues', $issueId, $matterId) || !wb_text($title, 255)
            || !wb_text($fact, 5000) || !wb_text($conclusion, 5000) || !in_array($position, ['supporting', 'answering', 'alternative'], true)
            || $confidence === false || $confidence < 0 || $confidence > 100) wb_redirect($matterId, 'Enter a complete argument map with a valid confidence score.');
        foreach ($evidenceIds as $evidenceId) if (!wb_matter_item_exists($pdo, 'p4_workbench_evidence', $evidenceId, $matterId)) wb_redirect($matterId, 'An evidence link is not available on this matter.');
        foreach ($authorityIds as $authorityId) if (!wb_matter_item_exists($pdo, 'p4_workbench_authorities', $authorityId, $matterId)) wb_redirect($matterId, 'An authority link is not available on this matter.');
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO p4_workbench_arguments (matter_id,issue_id,title,fact,conclusion,position,confidence_score,created_by) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$matterId, $issueId, $title, $fact, $conclusion, $position, $confidence, p2_user_id()]);
            $argumentId = (int)$pdo->lastInsertId();
            $linkEvidence = $pdo->prepare('INSERT INTO p4_argument_evidence (argument_id,evidence_id) VALUES (?,?)');
            foreach ($evidenceIds as $evidenceId) if ($evidenceId > 0) $linkEvidence->execute([$argumentId, $evidenceId]);
            $linkAuthority = $pdo->prepare('INSERT INTO p4_argument_authorities (argument_id,authority_id) VALUES (?,?)');
            foreach ($authorityIds as $authorityId) if ($authorityId > 0) $linkAuthority->execute([$argumentId, $authorityId]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('workbench.argument_added', 'Mapped argument: ' . $title, $matterId);
        wb_redirect($matterId, 'Argument map added.');
    }

    if ($action === 'add_action' || $action === 'complete_workbench_action') {
        if ($action === 'add_action') {
            $title = trim($_POST['title'] ?? '');
            $owner = trim($_POST['owner'] ?? '');
            $dueOn = trim($_POST['due_on'] ?? '');
            if (!wb_text($title, 255) || strlen($owner) > 255 || ($dueOn !== '' && !p2_valid_date($dueOn))) wb_redirect($matterId, 'Enter a valid next step and optional due date.');
            $stmt = $pdo->prepare('INSERT INTO p4_workbench_actions (matter_id,title,owner,due_on,created_by) VALUES (?,?,?,?,?)');
            $stmt->execute([$matterId, $title, $owner ?: null, $dueOn ?: null, p2_user_id()]);
            p2_log('workbench.action_added', 'Planned next step: ' . $title, $matterId);
            wb_redirect($matterId, 'Recommended next step added.');
        }
        $actionId = (int)($_POST['action_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE p4_workbench_actions SET status='complete', completed_at=CURRENT_TIMESTAMP WHERE id=? AND matter_id=? AND status='open'");
        $stmt->execute([$actionId, $matterId]);
        wb_redirect($matterId, $stmt->rowCount() ? 'Next step marked complete.' : 'That next step is no longer available.');
    }

    if ($action === 'add_risk') {
        $title = trim($_POST['title'] ?? '');
        $likelihood = $_POST['likelihood'] ?? 'medium';
        $impact = $_POST['impact'] ?? 'medium';
        $mitigation = trim($_POST['mitigation'] ?? '');
        if (!wb_text($title, 255) || !in_array($likelihood, ['low', 'medium', 'high'], true) || !in_array($impact, ['low', 'medium', 'high'], true) || strlen($mitigation) > 5000) wb_redirect($matterId, 'Enter a valid risk and mitigation.');
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_risks (matter_id,title,likelihood,impact,mitigation,created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$matterId, $title, $likelihood, $impact, $mitigation ?: null, p2_user_id()]);
        p2_log('workbench.risk_added', 'Recorded risk: ' . $title, $matterId);
        wb_redirect($matterId, 'Risk added to the register.');
    }

    if ($action === 'add_milestone') {
        $dueOn = trim($_POST['due_on'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $type = $_POST['milestone_type'] ?? 'procedural';
        $required = isset($_POST['required']) ? 1 : 0;
        if (!p2_valid_date($dueOn) || !wb_text($title, 255) || !in_array($type, ['procedural', 'evidence', 'client', 'court'], true)) wb_redirect($matterId, 'Enter a valid milestone date and title.');
        $stmt = $pdo->prepare('INSERT INTO p4_workbench_milestones (matter_id,due_on,title,milestone_type,required,created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$matterId, $dueOn, $title, $type, $required, p2_user_id()]);
        p2_log('workbench.milestone_added', 'Added milestone: ' . $title, $matterId);
        wb_redirect($matterId, 'Milestone added to the case timeline.');
    }

    if ($action === 'generate_ai_output') {
        $workflow = trim((string)($_POST['workflow'] ?? ''));
        $instruction = trim((string)($_POST['instruction'] ?? ''));
        if (!isset(p6_workflows()[$workflow]) || strlen($instruction) > 4000) {
            wb_redirect($matterId, 'Select a valid workflow and keep the requested focus to 4,000 characters or fewer.', '#ai-workflows');
        }
        $matter = p2_find_matter($pdo, $matterId);
        $analysis = analyseMatter($pdo, $matter);
        $generators = [
            'letter' => 'generateLetter',
            'advice' => 'generateAdvice',
            'appeal' => 'generateAppeal',
            'witness' => 'generateWitnessStatement',
            'skeleton' => 'generateSkeletonArgument',
            'research' => 'generateResearchNote',
            'strategy' => 'generateStrategy',
        ];
        $generated = isset($generators[$workflow])
            ? $generators[$workflow]($analysis, $matter, $instruction)
            : counselEngineGenerate($workflow, $analysis, $matter, $instruction);
        $reasoning = counselEngineReasoning($generated);
        [$title, $content] = [$generated['Generated Draft']['title'], $generated['Generated Draft']['content']];
        $sources = p6_source_materials($pdo, $matterId);
        $pdo->beginTransaction();
        try {
            $session = $pdo->prepare('INSERT INTO p6_ai_sessions (matter_id,workflow,prompt,reasoning_json,created_by) VALUES (?,?,?,?,?)');
            $session->execute([$matterId, $workflow, $instruction ?: null, json_encode($reasoning, JSON_UNESCAPED_UNICODE), p2_user_id()]);
            $sessionId = (int)$pdo->lastInsertId();
            $outputId = p6_save_output($pdo, $matterId, $workflow, $title, $content, $reasoning, $sources, $sessionId);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('ai.output_generated', 'Generated preliminary AI ' . p6_workflows()[$workflow], $matterId, ['output_id' => $outputId, 'workflow' => $workflow]);
        wb_redirect($matterId, 'Preliminary AI output saved to this matter’s document library and activity history.', '#ai-workflows');
    }

    if ($action === 'transform_ai_output') {
        $outputId = (int)($_POST['output_id'] ?? 0);
        $transform = trim((string)($_POST['transform'] ?? ''));
        $output = $pdo->prepare('SELECT * FROM p6_ai_outputs WHERE id=? AND matter_id=?');
        $output->execute([$outputId, $matterId]);
        $original = $output->fetch();
        if (!$original || !in_array($transform, ['improve', 'simplify', 'expand', 'formal', 'persuasive'], true)) {
            wb_redirect($matterId, 'That AI output or revision action is not available on this matter.', '#ai-workflows');
        }
        $review = $pdo->prepare('SELECT review_note FROM p6_ai_output_reviews WHERE output_id=?');
        $review->execute([$outputId]);
        $content = p6_transform_output($original['content'], $transform, (string)($review->fetchColumn() ?: ''));
        $title = $original['title'] . ' · ' . ucfirst($transform) . ' review';
        $reasoning = p6_reasoning($pdo, p2_find_matter($pdo, $matterId));
        $sources = p6_source_materials($pdo, $matterId);
        $pdo->beginTransaction();
        try {
            $revisionId = p6_save_output($pdo, $matterId, $original['workflow'], $title, $content, $reasoning, $sources, (int)$original['session_id'], $outputId, $transform);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('ai.output_revised', 'Created ' . $transform . ' review of preliminary AI output', $matterId, ['output_id' => $revisionId, 'parent_output_id' => $outputId]);
        wb_redirect($matterId, 'Revised preliminary output saved to the matter document library.', '#ai-workflows');
    }

    if ($action === 'save_ai_revision') {
        $outputId = (int)($_POST['output_id'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));
        $output = $pdo->prepare('SELECT * FROM p6_ai_outputs WHERE id=? AND matter_id=?');
        $output->execute([$outputId, $matterId]);
        $original = $output->fetch();
        if (!$original || $content === '' || strlen($content) > 50000) {
            wb_redirect($matterId, 'Enter an edited output of 50,000 characters or fewer.', '#ai-workflows');
        }
        $reasoning = p6_reasoning($pdo, p2_find_matter($pdo, $matterId));
        $pdo->beginTransaction();
        try {
            $revisionId = p6_save_output($pdo, $matterId, $original['workflow'], $original['title'] . ' · Edited review', $content, $reasoning, p6_source_materials($pdo, $matterId), (int)$original['session_id'], $outputId, 'edited');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('ai.output_revised', 'Saved edited preliminary AI output', $matterId, ['output_id' => $revisionId, 'parent_output_id' => $outputId]);
        wb_redirect($matterId, 'Edited preliminary output saved to the matter document library.', '#ai-workflows');
    }

    if ($action === 'record_ai_review') {
        $outputId = (int)($_POST['output_id'] ?? 0);
        $status = (string)($_POST['review_status'] ?? 'needs_changes');
        $note = trim((string)($_POST['review_note'] ?? ''));
        $checks = [
            'facts_checked' => isset($_POST['facts_checked']) ? 1 : 0,
            'law_checked' => isset($_POST['law_checked']) ? 1 : 0,
            'procedure_checked' => isset($_POST['procedure_checked']) ? 1 : 0,
            'remedy_checked' => isset($_POST['remedy_checked']) ? 1 : 0,
        ];
        $output = $pdo->prepare('SELECT id FROM p6_ai_outputs WHERE id=? AND matter_id=?');
        $output->execute([$outputId, $matterId]);
        if (!$output->fetchColumn() || !in_array($status, ['reviewed', 'needs_changes'], true) || strlen($note) > 5000
            || ($status === 'reviewed' && in_array(0, $checks, true))
            || ($status === 'needs_changes' && $note === '')) {
            wb_redirect($matterId, 'For reviewed output confirm facts, law, procedure and remedy; for changes required, record reviewer feedback.', '#ai-workflows');
        }
        $stmt = $pdo->prepare('INSERT INTO p6_ai_output_reviews (output_id,status,review_note,facts_checked,law_checked,procedure_checked,remedy_checked,reviewed_by,reviewed_at)
            VALUES (?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)
            ON CONFLICT(output_id) DO UPDATE SET status=excluded.status, review_note=excluded.review_note,
            facts_checked=excluded.facts_checked, law_checked=excluded.law_checked, procedure_checked=excluded.procedure_checked,
            remedy_checked=excluded.remedy_checked, reviewed_by=excluded.reviewed_by, reviewed_at=CURRENT_TIMESTAMP');
        $stmt->execute([$outputId, $status, $note ?: null, $checks['facts_checked'], $checks['law_checked'], $checks['procedure_checked'], $checks['remedy_checked'], p2_user_id()]);
        p2_log('ai.output_reviewed', $status === 'reviewed' ? 'Recorded lawyer review of preliminary AI output' : 'Recorded changes required for preliminary AI output', $matterId, ['output_id' => $outputId, 'status' => $status]);
        wb_redirect($matterId, $status === 'reviewed' ? 'Review confirmation recorded. The output remains preliminary until issued through the appropriate process.' : 'Reviewer feedback saved. Create an improved revision from this output.', '#ai-workflows');
    }

    if ($action === 'generate_document') {
        $documentTypes = ['demand_letter' => 'Demand Letter', 'legal_opinion' => 'Legal Opinion', 'court_filing' => 'Court Filing Notice', 'settlement' => 'Settlement Proposal', 'cease_desist' => 'Cease & Desist'];
        $documentType = $_POST['document_type'] ?? '';
        if (!isset($documentTypes[$documentType])) wb_redirect($matterId, 'Select a document type to generate.');
        $matter = p2_find_matter($pdo, $matterId);
        $assessment = $pdo->prepare('SELECT * FROM p4_workbench_assessments WHERE matter_id=?');
        $assessment->execute([$matterId]);
        $content = wb_document_content($matter, $assessment->fetch() ?: [], $documentTypes[$documentType]);
        $title = $documentTypes[$documentType] . ' — ' . $matter['reference'];
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO p2_documents (matter_id,title,document_type,source_table,source_id,content,created_by) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$matterId, $title, $documentType, 'p4_workbench_assessments', $matterId, $content, p2_user_id()]);
            $documentId = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO p2_document_links (document_id,matter_id) VALUES (?,?)')->execute([$documentId, $matterId]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        p2_log('document.linked', 'Generated workbench document: ' . $title, $matterId, ['document_id' => $documentId]);
        wb_redirect($matterId, 'Draft generated and linked to the matter.');
    }

    p2_error_page(400, 'Request not recognised', 'The requested workbench action could not be completed.');
}

$matters = p2_matter_options($pdo);
$matter = $selected ? p2_find_matter($pdo, $selected) : null;
if ($selected && !$matter) p2_error_page(404, 'Matter not found', 'Select an available matter from the workbench.');
$assessment = $issues = $evidence = $authorities = $arguments = $actions = $risks = $milestones = $documents = $reviews = $counselActions = $aiOutputs = $sourceMaterials = [];
$aiReasoning = [];
$selectedWorkflow = trim((string)($_GET['workflow'] ?? 'advice'));
if (!isset(p6_workflows()[$selectedWorkflow])) $selectedWorkflow = 'advice';
if ($matter) {
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_assessments WHERE matter_id=?'); $stmt->execute([$selected]); $assessment = $stmt->fetch() ?: [];
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_issues WHERE matter_id=? ORDER BY CASE priority WHEN "high" THEN 0 WHEN "normal" THEN 1 ELSE 2 END, id DESC'); $stmt->execute([$selected]); $issues = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT e.*, i.title AS issue_title FROM p4_workbench_evidence e LEFT JOIN p4_workbench_issues i ON i.id=e.issue_id WHERE e.matter_id=? ORDER BY e.id DESC'); $stmt->execute([$selected]); $evidence = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT a.*, (SELECT COUNT(*) FROM p4_argument_authorities aa WHERE aa.authority_id=a.id) AS argument_count FROM p4_workbench_authorities a WHERE a.matter_id=? ORDER BY a.id DESC'); $stmt->execute([$selected]); $authorities = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT a.*, i.title AS issue_title,
        (SELECT GROUP_CONCAT(e.title, " | ") FROM p4_argument_evidence ae JOIN p4_workbench_evidence e ON e.id=ae.evidence_id WHERE ae.argument_id=a.id) AS evidence_titles,
        (SELECT GROUP_CONCAT(au.citation, " | ") FROM p4_argument_authorities aa JOIN p4_workbench_authorities au ON au.id=aa.authority_id WHERE aa.argument_id=a.id) AS authority_citations
        FROM p4_workbench_arguments a JOIN p4_workbench_issues i ON i.id=a.issue_id WHERE a.matter_id=? ORDER BY a.id DESC'); $stmt->execute([$selected]); $arguments = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_actions WHERE matter_id=? ORDER BY CASE status WHEN "open" THEN 0 ELSE 1 END, due_on IS NULL, due_on, id DESC'); $stmt->execute([$selected]); $actions = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_risks WHERE matter_id=? ORDER BY CASE impact WHEN "high" THEN 0 WHEN "medium" THEN 1 ELSE 2 END, id DESC'); $stmt->execute([$selected]); $risks = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM p4_workbench_milestones WHERE matter_id=? ORDER BY due_on, id'); $stmt->execute([$selected]); $milestones = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM p2_documents WHERE matter_id=? AND source_table="p4_workbench_assessments" ORDER BY id DESC LIMIT 8'); $stmt->execute([$selected]); $documents = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT * FROM p2_counsel_reviews WHERE matter_id=? ORDER BY created_at DESC LIMIT 5'); $stmt->execute([$selected]); $reviews = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT a.*, r.question FROM p3_counsel_actions a JOIN p2_counsel_reviews r ON r.id=a.review_id WHERE a.matter_id=? ORDER BY CASE WHEN a.status="open" THEN 0 ELSE 1 END, a.created_at DESC LIMIT 12'); $stmt->execute([$selected]); $counselActions = $stmt->fetchAll();
    $aiReasoning = counselEngineReasoning(analyseMatter($pdo, $matter));
    $sourceMaterials = p6_source_materials($pdo, $selected);
    $stmt = $pdo->prepare('SELECT o.*, s.reasoning_json FROM p6_ai_outputs o JOIN p6_ai_sessions s ON s.id=o.session_id WHERE o.matter_id=? ORDER BY o.created_at DESC, o.id DESC LIMIT 8'); $stmt->execute([$selected]); $aiOutputs = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT r.* FROM p6_ai_output_reviews r JOIN p6_ai_outputs o ON o.id=r.output_id WHERE o.matter_id=?'); $stmt->execute([$selected]);
    $aiReviews = [];
    foreach ($stmt->fetchAll() as $review) $aiReviews[(int)$review['output_id']] = $review;
    foreach ($aiOutputs as &$output) {
        $savedReasoning = json_decode((string)$output['reasoning_json'], true);
        $output['reasoning'] = is_array($savedReasoning) ? $savedReasoning : $aiReasoning;
        $output['review'] = $aiReviews[(int)$output['id']] ?? null;
    }
    unset($output);
}

p2_page('Counsel Workbench', 'counsel_engine.php', function () use ($matters, $selected, $matter, $assessment, $issues, $evidence, $authorities, $arguments, $actions, $risks, $milestones, $documents, $reviews, $counselActions, $aiReasoning, $aiOutputs, $sourceMaterials, $selectedWorkflow) { ?>
<div class="toolbar"><div><h1>Counsel Workbench</h1><p class="subhead">A matter-centred legal reasoning surface. Record the working position, evidence, authorities, arguments, risks and procedural commitments in one accountable place.</p></div></div>
<section class="card"><form method="get" class="inline"><div style="flex:1;min-width:240px"><label for="matter_id">Matter</label><select id="matter_id" name="matter_id" required><option value="">Select a matter</option><?php foreach ($matters as $item): ?><option value="<?= (int)$item['id'] ?>" <?= $selected === (int)$item['id'] ? 'selected' : '' ?>><?= p2_h($item['reference'] . ' — ' . $item['title']) ?></option><?php endforeach; ?></select></div><button>Open workbench</button></form></section>
<?php if (!$matter): ?><section class="card empty" style="margin-top:18px">Select a matter to build its structured assessment and action plan.</section><?php return; endif; ?>

<section class="card workbench-summary" style="margin-top:18px"><div class="split"><div><span class="small"><?= p2_h($matter['reference']) ?></span><h2>Executive summary</h2></div><a class="button secondary" href="matters.php?id=<?= $selected ?>">Matter workspace</a></div>
<div class="grid grid-3"><div><div class="metric"><?= (int)($assessment['position_score'] ?? 50) ?>%</div><div class="metric-label">Position score · <?= p2_h($assessment['position'] ?? 'balanced') ?></div></div><div><div class="metric"><?= (int)($assessment['confidence_score'] ?? 50) ?>%</div><div class="metric-label">Confidence in current record</div></div><div><div class="metric"><?= count($issues) ?></div><div class="metric-label">Issues under review</div></div></div>
<p><?= nl2br(p2_h($assessment['executive_summary'] ?? 'No executive summary yet. Capture the working position, decisive gaps and recommended direction.')) ?></p>
<details><summary>Update executive summary and assessment</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="save_assessment"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Executive summary</label><textarea maxlength="10000" name="executive_summary" placeholder="State the current legal position, decisive facts, material uncertainty and recommended direction."><?= p2_h($assessment['executive_summary'] ?? '') ?></textarea></div><div><label>Position</label><select name="position"><?php foreach (['favourable' => 'Favourable', 'balanced' => 'Balanced', 'adverse' => 'Adverse'] as $value => $label): ?><option value="<?= $value ?>" <?= ($assessment['position'] ?? 'balanced') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div><div><label>Position score (0–100)</label><input required type="number" min="0" max="100" name="position_score" value="<?= (int)($assessment['position_score'] ?? 50) ?>"></div><div><label>Confidence score (0–100)</label><input required type="number" min="0" max="100" name="confidence_score" value="<?= (int)($assessment['confidence_score'] ?? 50) ?>"></div><div><button>Save assessment</button></div></form></details></section>

<div class="grid grid-2" style="margin-top:18px"><section class="card"><h2>Issues matrix</h2><table><thead><tr><th>Issue / question</th><th>Position</th><th>Priority</th></tr></thead><tbody><?php if (!$issues): ?><tr><td colspan="3" class="empty">Add the legal issues that require a reasoned view.</td></tr><?php else: foreach ($issues as $issue): ?><tr><td><strong><?= p2_h($issue['title']) ?></strong><br><span class="small"><?= p2_h($issue['question']) ?></span></td><td><?= p2_status_badge($issue['position']) ?></td><td><?= p2_status_badge($issue['priority']) ?></td></tr><?php endforeach; endif; ?></tbody></table><details><summary>Add issue</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_issue"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Issue *</label><input required maxlength="255" name="title" placeholder="e.g. Whether the notice was contractually effective"></div><div class="full"><label>Question / test</label><textarea maxlength="5000" name="question"></textarea></div><div><label>Current position</label><select name="position"><option value="open">Open</option><option value="client">Client</option><option value="opponent">Opponent</option></select></div><div><label>Priority</label><select name="priority"><option value="high">High</option><option value="normal">Normal</option><option value="low">Low</option></select></div><div><button>Add issue</button></div></form></details></section>
<section class="card"><h2>Evidence planner</h2><table><thead><tr><th>Evidence</th><th>Issue / status</th></tr></thead><tbody><?php if (!$evidence): ?><tr><td colspan="2" class="empty">Plan the evidence needed to prove or test each issue.</td></tr><?php else: foreach ($evidence as $item): ?><tr><td><strong><?= p2_h($item['title']) ?></strong><br><span class="small"><?= p2_h($item['evidence_type']) ?><?= $item['source_detail'] ? ' · ' . p2_h($item['source_detail']) : '' ?></span></td><td><?= p2_h($item['issue_title'] ?: 'General') ?><br><?= p2_status_badge($item['status']) ?></td></tr><?php endforeach; endif; ?></tbody></table><details><summary>Add evidence item</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_evidence"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Evidence item *</label><input required maxlength="255" name="title"></div><div><label>Issue</label><select name="issue_id"><option value="">General</option><?php foreach ($issues as $issue): ?><option value="<?= $issue['id'] ?>"><?= p2_h($issue['title']) ?></option><?php endforeach; ?></select></div><div><label>Type</label><select name="evidence_type"><option value="document">Document</option><option value="witness">Witness</option><option value="expert">Expert</option><option value="digital">Digital</option><option value="other">Other</option></select></div><div><label>Status</label><select name="status"><option value="to_obtain">To obtain</option><option value="available">Available</option><option value="reviewed">Reviewed</option></select></div><div><label>Source / custodian</label><input maxlength="1000" name="source_detail"></div><div class="full"><label>Relevance</label><textarea maxlength="5000" name="relevance"></textarea></div><div><button>Add evidence</button></div></form></details></section></div>

<div class="grid grid-2" style="margin-top:18px"><section id="authorities" class="card"><h2>Authorities relied upon</h2><p class="small">Authorities become part of the reasoning only when linked to an argument below.</p><ul class="compact-list"><?php if (!$authorities): ?><li class="empty">No authorities recorded.</li><?php else: foreach ($authorities as $authority): ?><li><span><strong><?= p2_h($authority['citation']) ?></strong><small><?= p2_h($authority['authority_type']) ?><?= $authority['proposition'] ? ' · ' . p2_h($authority['proposition']) : '' ?> · Linked to <?= (int)$authority['argument_count'] ?> argument<?= (int)$authority['argument_count'] === 1 ? '' : 's' ?></small></span><?php if ($authority['source_url']): ?><a href="<?= p2_h($authority['source_url']) ?>" target="_blank" rel="noopener">Source</a><?php endif; ?></li><?php endforeach; endif; ?></ul><details><summary>Add authority</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_authority"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Citation *</label><input required maxlength="500" name="citation"></div><div><label>Type</label><select name="authority_type"><option value="case">Case</option><option value="statute">Statute</option><option value="regulation">Regulation</option><option value="guidance">Guidance</option><option value="other">Other</option></select></div><div><label>Source URL</label><input maxlength="2000" type="url" name="source_url"></div><div class="full"><label>Proposition supported</label><textarea maxlength="5000" name="proposition"></textarea></div><div><button>Add authority</button></div></form></details></section>
<section class="card"><h2>Case assessment</h2><dl class="detail-list"><dt>Working position</dt><dd><?= p2_status_badge($assessment['position'] ?? 'balanced') ?></dd><dt>Position score</dt><dd><?= (int)($assessment['position_score'] ?? 50) ?> / 100</dd><dt>Confidence score</dt><dd><?= (int)($assessment['confidence_score'] ?? 50) ?> / 100</dd><dt>Record basis</dt><dd><?= count($evidence) ?> evidence items · <?= count($authorities) ?> authorities · <?= count($arguments) ?> mapped arguments</dd></dl><p class="small">Scores are a transparent internal working assessment, not a prediction or substitute for legal judgment.</p></section></div>

<section class="card" style="margin-top:18px"><h2>Argument map</h2><p class="small">Each map follows Issue → Fact → Evidence → Authority → Conclusion. Add more than one argument to test alternative positions.</p><?php if (!$arguments): ?><div class="empty">Create an issue, then map one or more arguments against it.</div><?php else: ?><div class="argument-map"><?php foreach ($arguments as $argument): ?><article class="argument-card"><div><span>Issue</span><strong><?= p2_h($argument['issue_title']) ?></strong></div><div><span>Fact</span><p><?= nl2br(p2_h($argument['fact'])) ?></p></div><div><span>Evidence</span><p><?= p2_h($argument['evidence_titles'] ?: 'No evidence linked') ?></p></div><div><span>Authority</span><p><?= p2_h($argument['authority_citations'] ?: 'No authority linked') ?></p></div><div><span>Conclusion</span><p><?= nl2br(p2_h($argument['conclusion'])) ?></p><small><?= p2_status_badge($argument['position']) ?> Confidence <?= (int)$argument['confidence_score'] ?>%</small></div></article><?php endforeach; ?></div><?php endif; ?><details><summary>Add argument map</summary><?php if (!$issues): ?><p class="small">Add an issue before mapping an argument.</p><?php else: ?><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_argument"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div><label>Issue *</label><select required name="issue_id"><?php foreach ($issues as $issue): ?><option value="<?= $issue['id'] ?>"><?= p2_h($issue['title']) ?></option><?php endforeach; ?></select></div><div><label>Argument position</label><select name="position"><option value="supporting">Supporting</option><option value="answering">Answering</option><option value="alternative">Alternative</option></select></div><div class="full"><label>Argument title *</label><input required maxlength="255" name="title"></div><div class="full"><label>Material fact *</label><textarea required maxlength="5000" name="fact"></textarea></div><div><label>Linked evidence</label><select multiple name="evidence_ids[]" size="4"><?php foreach ($evidence as $item): ?><option value="<?= $item['id'] ?>"><?= p2_h($item['title']) ?></option><?php endforeach; ?></select></div><div><label>Linked authorities</label><select multiple name="authority_ids[]" size="4"><?php foreach ($authorities as $authority): ?><option value="<?= $authority['id'] ?>"><?= p2_h($authority['citation']) ?></option><?php endforeach; ?></select></div><div class="full"><label>Conclusion *</label><textarea required maxlength="5000" name="conclusion"></textarea></div><div><label>Confidence score (0–100)</label><input required type="number" min="0" max="100" name="confidence_score" value="50"></div><div><button>Add argument</button></div></form><?php endif; ?></details></section>

<div class="grid grid-2" style="margin-top:18px"><section class="card"><h2>Recommended next steps</h2><ul class="compact-list"><?php if (!$actions): ?><li class="empty">No action plan yet.</li><?php else: foreach ($actions as $item): ?><li><span><strong><?= p2_h($item['title']) ?></strong><small><?= p2_h($item['owner'] ?: 'Unassigned') ?><?= $item['due_on'] ? ' · due ' . p2_h($item['due_on']) : '' ?></small></span><?php if ($item['status'] === 'open'): ?><form method="post"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="complete_workbench_action"><input type="hidden" name="matter_id" value="<?= $selected ?>"><input type="hidden" name="action_id" value="<?= $item['id'] ?>"><button class="button secondary">Complete</button></form><?php else: ?><?= p2_status_badge('complete') ?><?php endif; ?></li><?php endforeach; endif; ?></ul><details><summary>Add next step</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_action"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Action *</label><input required maxlength="255" name="title"></div><div><label>Owner</label><input maxlength="255" name="owner"></div><div><label>Due date</label><input type="date" name="due_on"></div><div><button>Add next step</button></div></form></details></section>
<section id="risk-review" class="card"><h2>Risk register</h2><table><thead><tr><th>Risk</th><th>Likelihood / impact</th></tr></thead><tbody><?php if (!$risks): ?><tr><td colspan="2" class="empty">No risks recorded.</td></tr><?php else: foreach ($risks as $risk): ?><tr><td><strong><?= p2_h($risk['title']) ?></strong><br><span class="small"><?= p2_h($risk['mitigation'] ?: 'No mitigation recorded.') ?></span></td><td><?= p2_status_badge($risk['likelihood']) ?> <?= p2_status_badge($risk['impact']) ?></td></tr><?php endforeach; endif; ?></tbody></table><details><summary>Add risk</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_risk"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Risk *</label><input required maxlength="255" name="title"></div><div><label>Likelihood</label><select name="likelihood"><option value="high">High</option><option value="medium" selected>Medium</option><option value="low">Low</option></select></div><div><label>Impact</label><select name="impact"><option value="high">High</option><option value="medium" selected>Medium</option><option value="low">Low</option></select></div><div class="full"><label>Mitigation</label><textarea maxlength="5000" name="mitigation"></textarea></div><div><button>Add risk</button></div></form></details></section></div>

<section class="card" style="margin-top:18px"><h2>Case timeline and required milestone dates</h2><div class="timeline-visual"><?php if (!$milestones): ?><div class="empty">Add procedural, court, evidence or client milestones to make required dates visible.</div><?php else: foreach ($milestones as $milestone): $timing = p2_deadline_timing($milestone['due_on']); ?><article class="timeline-point <?= p2_h($timing['class']) ?>"><time><?= p2_h(date('d M Y', strtotime($milestone['due_on']))) ?></time><div><strong><?= p2_h($milestone['title']) ?></strong> <?= $milestone['required'] ? '<span class="badge badge-high">Required</span>' : '' ?><br><span class="small"><?= p2_h($milestone['milestone_type']) ?> · <?= p2_h($timing['label']) ?></span></div></article><?php endforeach; endif; ?></div><details><summary>Add milestone</summary><form method="post" class="form-grid workbench-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="add_milestone"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div><label>Date *</label><input required type="date" name="due_on"></div><div><label>Milestone *</label><input required maxlength="255" name="title"></div><div><label>Type</label><select name="milestone_type"><option value="procedural">Procedural</option><option value="court">Court</option><option value="evidence">Evidence</option><option value="client">Client</option></select></div><div><label><input checked type="checkbox" name="required" value="1"> Required date</label></div><div><button>Add milestone</button></div></form></details></section>

<section id="ai-workflows" class="ai-workspace" style="margin-top:18px">
 <aside id="ai-analysis" class="card ai-reasoning-panel">
  <div class="split"><h2>AI reasoning panel</h2><span class="badge badge-draft">Preliminary</span></div>
  <p class="small">Always grounded in this matter’s recorded workspace. Verify every item before relying on it.</p>
  <?php foreach (['facts' => 'Facts and source excerpts', 'issues' => 'Issues', 'law' => 'Law', 'evidence' => 'Evidence', 'arguments' => 'Argument map', 'risks' => 'Risks', 'strategy' => 'Strategy', 'remedies' => 'Remedies'] as $key => $label): ?>
   <details class="reasoning-section" open><summary><?= $label ?></summary><ul><?php foreach ($aiReasoning[$key] as $item): ?><li><?= nl2br(p2_h($item)) ?></li><?php endforeach; ?></ul></details>
  <?php endforeach; ?>
 </aside>
 <section class="card ai-output-panel">
  <div class="split"><div><h2>AI drafting workflows</h2><p class="small">Local deterministic drafts for correspondence, advice, appeals, witness statements, skeleton arguments, research, strategy and evidence planning. They are preliminary AI output, not legal advice or final documents.</p></div><a class="button secondary" href="matters.php?id=<?= $selected ?>&tab=documents">Document library</a></div>
  <section class="ai-source-materials"><h3>Facts, evidence and source materials</h3><?php if (!$sourceMaterials): ?><p class="small">No linked source materials yet. Add documents, evidence, authorities, research or timeline records before relying on a draft.</p><?php else: ?><ul><?php foreach ($sourceMaterials as $source): ?><li><span><?= p2_h($source['label']) ?></span> <?= p2_h($source['title']) ?></li><?php endforeach; ?></ul><?php endif; ?></section>
  <form id="ai-generate-form" method="post" class="form-grid workbench-form">
   <input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="generate_ai_output"><input type="hidden" name="matter_id" value="<?= $selected ?>">
   <div><label>Workflow</label><select name="workflow"><?php foreach (p6_workflows() as $key => $label): ?><option value="<?= p2_h($key) ?>" <?= $selectedWorkflow === $key ? 'selected' : '' ?>><?= p2_h($label) ?></option><?php endforeach; ?></select></div>
   <div class="full"><label>Instructions (optional)</label><textarea name="instruction" maxlength="4000" placeholder="For example: focus on the missing evidence and a response deadline."></textarea></div>
   <div><button>Generate preliminary draft</button></div>
  </form>
  <div class="ai-action-toolbar no-print"><a class="button secondary" href="#ai-analysis">Analyse</a><button form="ai-generate-form" type="submit" class="button secondary">Generate Draft</button><a class="button secondary" href="#generated-output">Improve Draft</a><a class="button secondary" href="#authorities">Add Authorities</a><a class="button secondary" href="#risk-review">Risk Review</a><button type="button" class="button secondary" onclick="window.print()">Export PDF</button><a class="button secondary" href="counsel_engine.php?matter_id=<?= $selected ?>&export=latest&format=word">Export Word</a><a class="button secondary" href="matters.php?id=<?= $selected ?>&tab=documents">Save To Matter</a></div>
  <?php if (!$aiOutputs): ?><div class="empty">Choose a workflow to create a reviewable, matter-linked draft. Each draft is saved to the client matter’s activity and document library.</div><?php else: ?>
   <div class="ai-output-list"><?php foreach ($aiOutputs as $output): ?><article id="generated-output" class="ai-output">
    <?php $quality = p6_quality($output['reasoning'], $output['content'], $sourceMaterials); $outputReview = $output['review']; ?>
    <div class="split"><div><strong><?= p2_h($output['title']) ?></strong><small>Preliminary AI output · <?= p2_h(date('d M Y H:i', strtotime($output['created_at']))) ?><?= $output['revision_action'] ? ' · ' . p2_h($output['revision_action']) . ' review' : '' ?></small></div><div class="ai-export-actions"><a class="button secondary" href="counsel_engine.php?matter_id=<?= $selected ?>&export=<?= (int)$output['id'] ?>">Text</a><a class="button secondary" href="counsel_engine.php?matter_id=<?= $selected ?>&export=<?= (int)$output['id'] ?>&format=word">Word</a><a class="button secondary" href="mailto:?subject=<?= rawurlencode($output['title']) ?>&body=<?= rawurlencode('Preliminary AI output for ' . $matter['reference'] . '. Review the attached matter document before sending.') ?>">Email</a></div></div>
    <div class="ai-quality"><strong>Quality panel: <?= (int)$quality['score'] ?>/100</strong> <span class="badge badge-<?= p2_h($quality['status']) ?>"><?= p2_h(str_replace('-', ' ', $quality['status'])) ?></span><ul><?php foreach ($quality['checks'] as $check => $passed): ?><li class="<?= $passed ? 'passed' : 'needs-input' ?>"><?= $passed ? 'Checked' : 'Needs input' ?>: <?= p2_h($check) ?></li><?php endforeach; ?></ul></div>
    <form method="post"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="save_ai_revision"><input type="hidden" name="matter_id" value="<?= $selected ?>"><input type="hidden" name="output_id" value="<?= (int)$output['id'] ?>"><label for="output-<?= (int)$output['id'] ?>">Editable output</label><textarea id="output-<?= (int)$output['id'] ?>" class="ai-output-editor" name="content" maxlength="50000"><?= p2_h($output['content']) ?></textarea><div class="ai-output-print"><?= nl2br(p2_h($output['content'])) ?></div><div class="ai-output-actions"><button name="action" value="save_ai_revision">Save edited version to matter</button></div></form>
    <form method="post" class="ai-output-actions"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="transform_ai_output"><input type="hidden" name="matter_id" value="<?= $selected ?>"><input type="hidden" name="output_id" value="<?= (int)$output['id'] ?>"><?php foreach (['improve' => 'Improve', 'simplify' => 'Simplify', 'expand' => 'Expand', 'formal' => 'Formal', 'persuasive' => 'Persuasive'] as $key => $label): ?><button class="button secondary" name="transform" value="<?= $key ?>"><?= $label ?></button><?php endforeach; ?></form>
    <details class="ai-review-panel"><summary><strong>Lawyer review <?= $outputReview ? '· ' . p2_h(str_replace('_', ' ', $outputReview['status'])) : 'required' ?></strong></summary><p class="small">Review is an accountability record; it does not turn this preliminary output into advice or an issued document.</p><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="record_ai_review"><input type="hidden" name="matter_id" value="<?= $selected ?>"><input type="hidden" name="output_id" value="<?= (int)$output['id'] ?>"><div><label>Review outcome</label><select name="review_status"><option value="needs_changes" <?= ($outputReview['status'] ?? '') === 'needs_changes' ? 'selected' : '' ?>>Changes required</option><option value="reviewed" <?= ($outputReview['status'] ?? '') === 'reviewed' ? 'selected' : '' ?>>Reviewed</option></select></div><div class="full"><label>Reviewer feedback</label><textarea name="review_note" maxlength="5000" placeholder="Required when changes are needed."><?= p2_h($outputReview['review_note'] ?? '') ?></textarea></div><div class="full"><label><input type="checkbox" name="facts_checked" value="1" <?= !empty($outputReview['facts_checked']) ? 'checked' : '' ?>> Facts checked</label><label><input type="checkbox" name="law_checked" value="1" <?= !empty($outputReview['law_checked']) ? 'checked' : '' ?>> Law and authorities checked</label><label><input type="checkbox" name="procedure_checked" value="1" <?= !empty($outputReview['procedure_checked']) ? 'checked' : '' ?>> Procedure and dates checked</label><label><input type="checkbox" name="remedy_checked" value="1" <?= !empty($outputReview['remedy_checked']) ? 'checked' : '' ?>> Remedy and requested outcome checked</label></div><div><button>Save review record</button></div></form></details>
   </article><?php endforeach; ?></div>
  <?php endif; ?>
 </section>
</section>

<section class="card" style="margin-top:18px"><h2>Existing document generation</h2><p class="small">The established matter-linked drafting tools remain available alongside the AI workflows above.</p><form method="post" class="inline"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="generate_document"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div style="min-width:240px"><label>Document type</label><select name="document_type"><option value="demand_letter">Demand Letter</option><option value="legal_opinion">Legal Opinion</option><option value="court_filing">Court Filing Notice</option><option value="settlement">Settlement Proposal</option><option value="cease_desist">Cease &amp; Desist</option></select></div><button>Generate and link draft</button></form><?php if ($documents): ?><ul class="compact-list" style="margin-top:14px"><?php foreach ($documents as $document): ?><li><span><strong><?= p2_h($document['title']) ?></strong><small><?= p2_h($document['document_type']) ?> · <?= p2_h(date('d M Y', strtotime($document['created_at']))) ?></small></span><a href="matters.php?id=<?= $selected ?>&tab=documents">View</a></li><?php endforeach; ?></ul><?php endif; ?></section>
<section id="preliminary-review" class="card" style="margin-top:18px"><h2>Preliminary review</h2><p class="small">Retained review workflow: capture an instruction-quality assessment and follow its generated actions alongside the structured workbench.</p><div class="grid grid-2"><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="review"><input type="hidden" name="matter_id" value="<?= $selected ?>"><div class="full"><label>Question for counsel *</label><input required maxlength="1000" name="question" placeholder="What decision or legal issue needs a view?"></div><div class="full"><label>Facts and available evidence</label><textarea name="facts" placeholder="Set out chronology, dates, documents, witnesses and client objective."></textarea></div><div><button>Generate review and actions</button></div></form><div><?php if (!$reviews): ?><div class="empty">No preliminary reviews have been recorded.</div><?php else: ?><ul class="compact-list"><?php foreach ($reviews as $review): ?><li><span><strong><?= p2_h($review['question']) ?></strong><small><?= p2_h(date('d M Y H:i', strtotime($review['created_at']))) ?> · <?= p2_h($review['assessment']) ?></small></span><?= p2_status_badge($review['risk_level']) ?></li><?php endforeach; ?></ul><?php endif; ?></div></div>
<?php if ($counselActions): ?><table style="margin-top:14px"><thead><tr><th>Review action</th><th>Status / result</th></tr></thead><tbody><?php foreach ($counselActions as $action): ?><tr><td><strong><?= p2_h($action['title']) ?></strong><br><span class="small"><?= p2_h($action['question']) ?></span></td><td><?php if ($action['status'] === 'open'): ?><form method="post" class="action-form"><input type="hidden" name="csrf" value="<?= p2_csrf() ?>"><input type="hidden" name="action" value="complete_action"><input type="hidden" name="matter_id" value="<?= $selected ?>"><input type="hidden" name="action_id" value="<?= $action['id'] ?>"><input required maxlength="10000" name="result" placeholder="Record result"><button>Complete</button></form><?php else: ?><?= p2_status_badge('complete') ?><br><span class="small"><?= nl2br(p2_h($action['result'] ?: 'No result recorded.')) ?></span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
<?php }); ?>
