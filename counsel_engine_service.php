<?php
/**
 * Deterministic, local service used by every Counsel Engine entry point.
 * It never retrieves law or facts outside the supplied matter record.
 */

function counselEngineStructure(array $reasoning, ?array $draft = null): array {
    return [
        'Facts' => array_values($reasoning['facts'] ?? []),
        'Issues' => array_values($reasoning['issues'] ?? []),
        'Law' => array_values($reasoning['law'] ?? []),
        'Evidence' => array_values($reasoning['evidence'] ?? []),
        'Arguments' => array_values($reasoning['arguments'] ?? []),
        'Risks' => array_values($reasoning['risks'] ?? []),
        'Strategy' => array_values($reasoning['strategy'] ?? []),
        'Remedies' => array_values($reasoning['remedies'] ?? []),
        'Generated Draft' => $draft,
        'preliminary' => true,
        'source' => 'local deterministic matter record',
    ];
}

function counselEngineReasoning(array $analysis): array {
    $keys = ['Facts' => 'facts', 'Issues' => 'issues', 'Law' => 'law', 'Evidence' => 'evidence', 'Risks' => 'risks', 'Strategy' => 'strategy', 'Remedies' => 'remedies'];
    $reasoning = [];
    foreach ($keys as $structured => $key) $reasoning[$key] = array_values((array)($analysis[$structured] ?? []));
    $reasoning['arguments'] = array_values((array)($analysis['Arguments'] ?? ['No argument map recorded; verify the issue, facts, evidence and law before relying on a draft.']));
    return $reasoning;
}

function counselEngineRecordReasoning(array $record): array {
    $title = trim((string)($record['title'] ?? 'matter'));
    $description = trim((string)($record['description'] ?? $record['summary'] ?? ''));
    $facts = [];
    foreach (['claimant' => 'Client / claimant', 'respondent' => 'Respondent', 'status' => 'Status'] as $field => $label) {
        if (trim((string)($record[$field] ?? '')) !== '') $facts[] = $label . ': ' . trim((string)$record[$field]);
    }
    if ($description !== '') $facts[] = 'Recorded summary: ' . p6_excerpt($description, 1200);
    if (!$facts) $facts[] = 'No factual summary is recorded; obtain and verify the chronology and source materials.';

    $issues = [];
    if ($description === '') $issues[] = 'The material factual chronology and client objective have not been recorded.';
    foreach (['right_violated' => 'right engaged', 'tort_type' => 'cause of action', 'ground_of_review' => 'ground of review', 'contract_type' => 'contractual basis'] as $field => $label) {
        if (trim((string)($record[$field] ?? '')) !== '') $issues[] = 'Recorded ' . $label . ': ' . trim((string)$record[$field]) . '.';
    }
    if (!$issues) $issues[] = 'Identify the legal questions, governing forum and client objective before relying on a draft.';

    $law = [];
    foreach (['article_section', 'legal_provision', 'applicable_law'] as $field) {
        if (trim((string)($record[$field] ?? '')) !== '') $law[] = 'Recorded legal reference: ' . trim((string)$record[$field]);
    }
    if (!$law) $law[] = 'No authority is inferred. Confirm the applicable law, current authorities and procedure independently.';

    $evidence = [];
    foreach (['grounds', 'duty_of_care', 'damages', 'decision_maker', 'licence_number', 'field_location'] as $field) {
        if (trim((string)($record[$field] ?? '')) !== '') $evidence[] = 'Recorded ' . str_replace('_', ' ', $field) . ': ' . p6_excerpt((string)$record[$field], 300);
    }
    if (!$evidence) $evidence[] = 'No supporting evidence is recorded; identify documents, witnesses and other source materials.';

    $remedies = [];
    foreach (['remedy', 'relief_sought', 'damages'] as $field) {
        if (trim((string)($record[$field] ?? '')) !== '') $remedies[] = 'Recorded requested outcome: ' . trim((string)$record[$field]);
    }
    if (!$remedies) $remedies[] = 'No remedy is inferred; confirm the requested outcome with the responsible lawyer.';

    return [
        'facts' => $facts,
        'issues' => $issues,
        'law' => $law,
        'evidence' => $evidence,
        'risks' => ['Preliminary local analysis only: verify facts, law, evidence, limitation dates, forum and remedy before action.'],
        'strategy' => ['Preserve source materials, confirm the client objective and obtain a lawyer-reviewed position before issue.'],
        'arguments' => ['No argument map is generated from a standalone record; test each proposition against verified evidence and authority.'],
        'remedies' => $remedies,
    ];
}

function analyseMatter($pdoOrMatter, ?array $matter = null): array {
    if ($pdoOrMatter instanceof PDO && $matter !== null) return counselEngineStructure(p6_reasoning($pdoOrMatter, $matter));
    return counselEngineStructure(counselEngineRecordReasoning(is_array($pdoOrMatter) ? $pdoOrMatter : ($matter ?? [])));
}

function identifyIssues(array $analysis): array { return (array)($analysis['Issues'] ?? []); }
function identifyApplicableLaw(array $analysis): array { return (array)($analysis['Law'] ?? []); }
function identifyEvidenceGaps(array $analysis): array {
    $evidence = (array)($analysis['Evidence'] ?? []);
    $gaps = array_values(array_filter($evidence, static fn(string $item): bool => stripos($item, 'no ') === 0 || stripos($item, 'to obtain') !== false));
    return $gaps ?: ['Confirm that the available evidence is complete, authentic and linked to each issue.'];
}
function assessRisks(array $analysis): array { return (array)($analysis['Risks'] ?? []); }

function counselEngineGenerate(string $workflow, array $analysis, array $matter = [], string $instructions = ''): array {
    $matter += ['reference' => 'UNLINKED-MATTER', 'title' => 'Unlinked matter'];
    $reasoning = counselEngineReasoning($analysis);
    [$title, $content] = p6_generate_output($matter, $workflow, $reasoning, trim($instructions));
    return counselEngineStructure($reasoning, ['title' => $title, 'content' => $content]);
}

function generateStrategy(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('strategy', $analysis, $matter, $instructions); }
function generateAdvice(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('advice', $analysis, $matter, $instructions); }
function generateLetter(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('letter', $analysis, $matter, $instructions); }
function generateAppeal(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('appeal', $analysis, $matter, $instructions); }
function generateWitnessStatement(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('witness', $analysis, $matter, $instructions); }
function generateSkeletonArgument(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('skeleton', $analysis, $matter, $instructions); }
function generateResearchNote(array $analysis, array $matter = [], string $instructions = ''): array { return counselEngineGenerate('research', $analysis, $matter, $instructions); }
