<?php
set_time_limit(120);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/legal_library.php';

session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = aep_db();
$apiConfig = aep_api_config();
$profiles = aep_domain_profiles();
$domain = $_GET['domain'] ?? 'human_rights';
$caseId = (int)($_GET['id'] ?? 0);
$docType = $_GET['doc_type'] ?? 'analysis_brief';
$reasoningDepth = $_GET['reasoning_depth'] ?? 'standard';
$templateKey = $_GET['template'] ?? '';
$jurisdiction = $_GET['jurisdiction'] ?? 'england_wales';
$track = $_GET['track'] ?? 'general';
$phraseCategory = $_GET['phrase_category'] ?? '';
$focus = trim((string)($_GET['focus'] ?? ''));
$instructions = trim($_GET['instructions'] ?? '');
if ($instructions !== '') {
    $instructions = preg_replace('/\x{1F4CB}\s*Copy this case.*$/u', '', $instructions) ?? $instructions;
    $instructions = preg_replace('/Copy this case.*$/i', '', $instructions) ?? $instructions;
    $instructions = trim($instructions);
}
if (strlen($instructions) > 2000) {
    $instructions = substr($instructions, 0, 2000);
}
$case = null;
$analysisMode = 'none';

$extractInstructionSlice = static function (string $text, array $patterns, int $max = 2): string {
    $normalized = trim((string)(preg_replace('/\s+/', ' ', $text) ?? $text));
    if ($normalized === '') {
        return '';
    }
    $sentences = preg_split('/(?<=[\.\!\?])\s+/', $normalized) ?: [];
    $picked = [];
    foreach ($sentences as $sentence) {
        $s = trim($sentence);
        if ($s === '') {
            continue;
        }
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $s) === 1) {
                $picked[] = $s;
                break;
            }
        }
        if (count($picked) >= $max) {
            break;
        }
    }
    if (empty($picked)) {
        $picked = array_slice(array_values(array_filter(array_map('trim', $sentences))), 0, $max);
    }
    return implode(' ', array_unique($picked));
};

$containsFieldFragment = static function (string $field, array $fragments): bool {
    foreach ($fragments as $fragment) {
        if (str_contains($field, $fragment)) {
            return true;
        }
    }
    return false;
};

if (!isset($profiles[$domain])) {
    $domain = 'human_rights';
}

if ($caseId > 0) {
    $case = aep_load_case($domain, $caseId);
    if ($case) {
        $analysisMode = 'saved_case';
    }
}

if (!$case && $instructions !== '') {
    $profile = $profiles[$domain] ?? null;
    if (is_array($profile)) {
        $factsText = $extractInstructionSlice($instructions, [
            '/\barrest|charge|interview|detain|released|attended|incident|event|happened|on\s+\d{1,2}\s+[a-z]{3,9}\s+\d{4}/i',
            '/\bclaim|dispute|facts|chronology|police say|defendant says|client says/i',
        ], 3);
        $legalText = $extractInstructionSlice($instructions, [
            '/\bsection\b|\bact\b|\barticle\b|\bpace\b|\bstatute\b|\blegal\b|\bright\b/i',
        ], 2);
        $defenceText = $extractInstructionSlice($instructions, [
            '/\bdenies\b|\bnot guilty\b|\bdefence\b|\bdisputes\b|\bposition\b/i',
        ], 2);
        $evidenceText = $extractInstructionSlice($instructions, [
            '/\bevidence\b|\bbodycam\b|\bcctv\b|\bwitness\b|\brecord\b|\bdisclosure\b|\bvideo\b/i',
        ], 2);
        $reliefText = $extractInstructionSlice($instructions, [
            '/\bacquittal\b|\bdismissal\b|\bexclude\b|\brelief\b|\bremedy\b|\bwithdraw\b|\bmitigat/i',
        ], 2);
        $prosecutionText = $extractInstructionSlice($instructions, [
            '/\bpolice say\b|\bprosecution\b|\balleg/i',
        ], 2);

        $dateYmd = '';
        if (preg_match('/\b(\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4})\b/', $instructions, $match) === 1) {
            $timestamp = strtotime($match[1]);
            if ($timestamp !== false) {
                $dateYmd = date('Y-m-d', $timestamp);
            }
        }

        $case = [
            'title' => 'Instruction-led matter',
            'summary' => $factsText !== '' ? $factsText : $instructions,
            'grounds' => $defenceText !== '' ? $defenceText : ($factsText !== '' ? $factsText : $instructions),
            'dispute_summary' => $factsText !== '' ? $factsText : $instructions,
            'dispute_description' => $factsText !== '' ? $factsText : $instructions,
            'legal_basis' => $legalText !== '' ? $legalText : 'Legal basis to be verified from client instructions.',
            'representations' => $defenceText !== '' ? $defenceText : 'Client position to be refined after instructions conference.',
            'evidence_available' => $evidenceText !== '' ? $evidenceText : 'Evidence inventory to be confirmed.',
            'remedy' => $reliefText !== '' ? $reliefText : 'Relief to be confirmed after merits review.',
            'relief_sought' => $reliefText !== '' ? $reliefText : 'Relief to be confirmed after merits review.',
            'damages_claimed' => $reliefText !== '' ? $reliefText : '',
            'charges' => $legalText !== '' ? $legalText : ($prosecutionText !== '' ? $prosecutionText : ''),
            'offence_description' => $prosecutionText !== '' ? $prosecutionText : ($factsText !== '' ? $factsText : ''),
            'plea' => (stripos($instructions, 'not guilty') !== false) ? 'Not guilty' : 'To be confirmed',
            'offence_date' => $dateYmd,
            'charge_date' => $dateYmd,
            'decision_date' => $dateYmd,
            'hearing_date' => $dateYmd,
            'status' => 'draft',
        ];
        foreach (array_merge($profile['required_fields'] ?? [], $profile['issue_fields'] ?? []) as $field) {
            if (!array_key_exists($field, $case)) {
                if ($containsFieldFragment($field, ['date']) && $dateYmd !== '') {
                    $case[$field] = $dateYmd;
                } elseif ($containsFieldFragment($field, ['legal', 'article', 'ground', 'treaty', 'charge'])) {
                    $case[$field] = $legalText !== '' ? $legalText : 'Legal basis to confirm.';
                } elseif ($containsFieldFragment($field, ['summary', 'description', 'facts', 'grounds', 'dispute'])) {
                    $case[$field] = $factsText !== '' ? $factsText : 'Core facts to confirm from instructions.';
                } elseif ($containsFieldFragment($field, ['evidence', 'document', 'disclosure'])) {
                    $case[$field] = $evidenceText !== '' ? $evidenceText : 'Evidence position to confirm.';
                } elseif ($containsFieldFragment($field, ['relief', 'remedy', 'damages'])) {
                    $case[$field] = $reliefText !== '' ? $reliefText : 'Relief to confirm.';
                } elseif ($containsFieldFragment($field, ['plea'])) {
                    $case[$field] = (stripos($instructions, 'not guilty') !== false) ? 'Not guilty' : 'To be confirmed';
                } elseif ($containsFieldFragment($field, ['status'])) {
                    $case[$field] = 'draft';
                } else {
                    $case[$field] = '';
                }
            }
        }
        $analysisMode = 'instructions_only';
    }
}

$jurisdictionOptions = aep_jurisdiction_options();
if (!isset($jurisdictionOptions[$jurisdiction])) {
    $jurisdiction = 'england_wales';
}
$trackOptions = aep_track_options();
if (!isset($trackOptions[$track])) {
    $track = 'general';
}
$reasoningOptions = aep_reasoning_depth_options();
if (!isset($reasoningOptions[$reasoningDepth])) {
    $reasoningDepth = 'standard';
}
$templates = aep_domain_templates($domain);
$phraseCategories = aep_phrase_bank_categories();

$package = $case ? aep_counsel_package($domain, $case, $docType, $instructions, $reasoningDepth, $focus) : null;
$remoteAiOutput = null;
$remoteAiError = null;
if (isset($_GET['ai']) && $_GET['ai'] == '1' && $case && $apiConfig['enabled']) {
    $aiSummary = $case['summary'] ?? ($package['summary'] ?? '');
    $domainLabel = $profiles[$domain]['label'] ?? ucfirst(str_replace('_', ' ', $domain));
    $docTypeLabel = match ($docType) {
        'analysis_brief' => 'Analysis brief',
        'draft_outline' => 'Draft outline',
        'instructions_note' => 'Instructions note',
        'counsel_note' => 'Counsel note',
        default => aep_labelize($docType),
    };
    $documentDirective = match ($docType) {
        'draft_outline' => "Treat this as a litigation drafting task. Use short, forceful lines. Build a skeleton, not a narrative.",
        'instructions_note' => "Treat this as a client instructions memorandum. State what must be proved, what is missing, and the next move.",
        default => "Treat this as a litigation-grade counsel note. Prioritise legal test, evidence, risk, and relief. Keep it tight.",
    };
    $domainDirective = $domain === 'human_rights'
        ? "For Human Rights matters, anchor the response in: right engaged, interference, legal test/proportionality, proof, remedies, and procedure."
        : "Anchor the response in the legal test, cause of action, proof, defence, and remedy.";
    $aiPrompt = "Prepare a litigation-grade legal opinion.\n\n"
        . "Domain: {$domainLabel}\n"
        . "Document type: {$docTypeLabel}\n"
        . "Reasoning depth: " . ($reasoningOptions[$reasoningDepth] ?? 'Standard') . "\n"
        . "Jurisdiction: " . ($jurisdictionOptions[$jurisdiction] ?? $jurisdiction) . "\n"
        . "Procedure track: " . ($trackOptions[$track] ?? $track) . "\n"
        . "Case summary: " . $aiSummary . "\n"
        . ($instructions !== '' ? "User instructions: {$instructions}\n" : '')
        . "\nMandatory style rules:\n"
        . "- Be blunt, firm, and concise.\n"
        . "- Short sentences only.\n"
        . "- No pedestal language, no generic preambles, no motivational tone.\n"
        . "- If the case is weak, say so plainly.\n"
        . "- Do not invent law, authorities, or facts.\n"
        . "- Use legal terms where appropriate, but keep sentences direct.\n"
        . "- End with a firm conclusion.\n\n"
        . $documentDirective . "\n"
        . $domainDirective . "\n\n"
        . "Use exactly these headings:\n"
        . "1. Bottom line\n"
        . "2. Cause of action / right engaged\n"
        . "3. Key evidence\n"
        . "4. Main arguments\n"
        . "5. Weaknesses / defences\n"
        . "6. Relief sought\n"
        . "7. Immediate next steps\n\n"
        . "Under each heading, use very short bullet points only. No filler. No hedging. Lead with the conclusion.";
    $depthDirective = match ($reasoningDepth) {
        'advanced' => "Reasoning depth ADVANCED: apply law to facts explicitly, test at least two defence/prosecution counter-positions, and state procedural route implications.",
        'aggressive' => "Reasoning depth AGGRESSIVE: reach firmer conclusions, prioritize fatal weaknesses, and state decisive litigation moves with strict proof demands.",
        default => "Reasoning depth STANDARD: concise merits assessment with practical next actions.",
    };
    $aiPrompt .= "\n\n" . $depthDirective;
    if ($instructions !== '') {
        $aiPrompt .= "\n\nAdditional instructions:\n" . $instructions;
    }
    if (!empty($package['violations'])) {
        $aiPrompt .= "\n\nStructured legal findings:\nViolations: " . implode(' | ', array_map(static fn($row) => ($row['label'] ?? '') . (!empty($row['remedy_hint']) ? ' -> ' . $row['remedy_hint'] : ''), array_slice($package['violations'], 0, 4)));
    }
    if (!empty($package['chronology'])) {
        $aiPrompt .= "\nChronology: " . implode(' | ', array_map(static fn($row) => ($row['date'] ?? 'n/a') . ' ' . ($row['label'] ?? '') . ' ' . ($row['event'] ?? ''), array_slice($package['chronology'], 0, 5)));
    }
    if (!empty($package['authority_citation'])) {
        $aiPrompt .= "\nAuthorities: " . implode(' | ', array_map(static fn($row) => ($row['target'] ?? '') . ' [' . ($row['status'] ?? '') . '] ' . (($row['principle'] ?? '') !== '' ? ($row['principle'] . ' ') : '') . ($row['citation_hook'] ?? ''), array_slice($package['authority_citation'], 0, 4)));
    }
    if (!empty($package['clauses'])) {
        $aiPrompt .= "\nClauses/obligations: " . implode(' | ', array_map(static fn($row) => ($row['clause'] ?? '') . ': ' . ($row['text'] ?? ''), array_slice($package['clauses'], 0, 3)));
    }
    if (!empty($package['issue_engine'])) {
        $aiPrompt .= "\nIssue map: " . implode(' | ', array_map(static fn($row) => ($row['issue'] ?? '') . ' (' . ($row['why_it_matters'] ?? '') . ')', array_slice($package['issue_engine'], 0, 4)));
    }
    if (!empty($package['fact_to_issues'])) {
        $aiPrompt .= "\nFact-to-issues: " . implode(' | ', array_map(static fn($row) => ($row['fact'] ?? '') . ' => ' . ($row['issue'] ?? ''), array_slice($package['fact_to_issues'], 0, 4)));
    }
    if (!empty($package['procedure_limitation'])) {
        $aiPrompt .= "\nProcedure/limitation: track=" . ($package['procedure_limitation']['track'] ?? '') . "; risk=" . ($package['procedure_limitation']['risk'] ?? '') . "; next=" . ($package['procedure_limitation']['next_step'] ?? '');
    }
    if (!empty($package['pleading_generator'])) {
        $aiPrompt .= "\nPleading scaffold: " . ($package['pleading_generator']['opening'] ?? '') . ' ' . ($package['pleading_generator']['cause'] ?? '') . ' ' . ($package['pleading_generator']['conclusion'] ?? '');
    }
    if (!empty($package['relief_drafting'])) {
        $aiPrompt .= "\nRelief draft: " . implode(' | ', $package['relief_drafting']['draft_prayers'] ?? []);
    }
    if (!empty($package['domain_strategy'])) {
        $aiPrompt .= "\nDomain strategy: theory=" . ($package['domain_strategy']['case_theory'] ?? '') . "; elements=" . implode(', ', array_slice($package['domain_strategy']['elements'] ?? [], 0, 4)) . "; pressure=" . implode(', ', array_slice($package['domain_strategy']['pressure_points'] ?? [], 0, 3));
    }
    if (!empty($package['skeleton_argument'])) {
        $aiPrompt .= "\nSkeleton: propositions=" . implode(' | ', array_slice($package['skeleton_argument']['propositions'] ?? [], 0, 3)) . "; relief=" . ($package['skeleton_argument']['relief_line'] ?? '');
    }
    if (!empty($package['letter_before_action'])) {
        $aiPrompt .= "\nPre-action demand: " . ($package['letter_before_action']['opening'] ?? '') . ' ' . ($package['letter_before_action']['deadline'] ?? '');
    }
    if (!empty($package['bundle_disclosure'])) {
        $aiPrompt .= "\nBundle/disclosure: sections=" . implode(', ', array_slice($package['bundle_disclosure']['sections'] ?? [], 0, 4)) . "; missing=" . implode(', ', array_slice($package['bundle_disclosure']['missing_documents'] ?? [], 0, 3));
    }
    if (!empty($package['counterarguments'])) {
        $aiPrompt .= "\nCounterarguments: " . implode(' | ', array_slice($package['counterarguments'], 0, 3));
    }
    if (!empty($package['remedies'])) {
        $aiPrompt .= "\nRemedy fit: requested=" . ($package['remedies']['requested'] ?? '') . "; recommended=" . implode(', ', $package['remedies']['recommended'] ?? []);
    }
    $remoteResult = aep_call_remote_model($aiPrompt, 'You are a senior legal counsel assistant. Write in a firm, practical style. Be specific, decisive, and grounded in the supplied facts. Avoid ornate phrasing, praise, and generic filler. If the case is weak, say so plainly and explain the gap. Do not invent law or facts.');
    if ($remoteResult['ok']) {
        $remoteAiOutput = $remoteResult['content'];
    } else {
        $remoteAiError = $remoteResult['error'];
    }
}
$phraseCategory = aep_normalize_phrase_category((string)$phraseCategory, $domain);
if (($phraseCategory === '' || !isset($_GET['phrase_category'])) && $docType !== '') {
    $phraseCategory = match ($docType) {
        'counsel_note', 'analysis_brief' => 'relief_remedies',
        'instructions_note' => 'evidence',
        'draft_outline' => 'pleadings',
        default => $phraseCategory,
    };
}
$templateText = $templateKey !== '' ? aep_render_template_text($domain, $templateKey, $case ?: [], [
    'jurisdiction' => $jurisdiction,
    'track' => $track,
    'reasoning_depth' => $reasoningDepth,
    'phrase_category' => $phraseCategory,
    'doc_type' => $docType,
    'instructions' => $instructions,
    'focus' => $focus,
]) : '';
$templateDownloadQuery = '';
if ($templateKey !== '') {
    $parts = [
        'domain=' . urlencode($domain),
        'template=' . urlencode($templateKey),
        'jurisdiction=' . urlencode($jurisdiction),
        'track=' . urlencode($track),
        'reasoning_depth=' . urlencode($reasoningDepth),
        'phrase_category=' . urlencode($phraseCategory),
        'focus=' . urlencode($focus),
    ];
    if ($caseId > 0) {
        $parts[] = 'id=' . $caseId;
    }
    $templateDownloadQuery = implode('&', $parts);
}
$cases = [];
if (aep_table_exists($pdo, $profiles[$domain]['table'])) {
    $labelField = aep_first_existing_field($pdo, $profiles[$domain]['table'], [
        'title',
        'case_reference',
        'case_title',
        'client_name',
        'defendant_name',
        'claimant_name',
        'recipient_name',
    ]) ?? 'id';
    $caseSql = "SELECT id, {$labelField} AS label FROM " . $profiles[$domain]['table'] . " ORDER BY id DESC LIMIT 100";
    $cases = $pdo->query($caseSql)->fetchAll(PDO::FETCH_ASSOC);
    if ($caseId > 0 && $case && !empty($case[$labelField])) {
        $selectedExists = false;
        foreach ($cases as $row) {
            if ((int)($row['id'] ?? 0) === $caseId) {
                $selectedExists = true;
                break;
            }
        }
        if (!$selectedExists) {
            array_unshift($cases, [
                'id' => $caseId,
                'label' => (string)$case[$labelField],
            ]);
        }
    }
}

$domainListRoutes = [
    'human_rights' => 'human-rights.php',
    'admin_law' => 'admin_law.php',
    'tort' => 'tort.php',
    'oil_gas' => 'oil_gas.php',
    'commercial_law' => 'commercial_law.php',
    'contract' => 'contract_list.php',
    'property' => 'property.php',
    'criminal' => 'criminal_list.php',
    'immigration' => 'immigration_list.php',
    'employment' => 'employment_list.php',
    'family' => 'family_law.php',
    'latin_maxims' => 'latin_maxims.php',
    'international_arbitration' => 'international_arbitration.php',
];
$domainCreateRoutes = [
    'human_rights' => 'hr_create.php',
    'admin_law' => 'admin_law_create.php',
    'tort' => 'tort_create.php',
    'oil_gas' => 'oil_gas_create.php',
    'commercial_law' => 'commercial_law_create.php',
    'contract' => 'contract_create.php',
    'property' => 'property_create.php',
    'criminal' => 'criminal_create.php',
    'immigration' => 'immigration_create.php',
    'employment' => 'employment_create.php',
    'family' => 'family_create.php',
    'latin_maxims' => 'latin_create.php',
    'international_arbitration' => 'international_arbitration_create.php',
];
$domainListHref = $domainListRoutes[$domain] ?? 'library.php';
$domainCreateHref = $domainCreateRoutes[$domain] ?? $domainListHref;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Counsel Engine - AEP Legal Platform</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222}
    .topbar{background:#1a3c5e;color:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap}
    .topbar a{color:#fff;text-decoration:none;margin-left:14px}
    .hero{background:linear-gradient(135deg,#1a3c5e,#2e6da4);color:#fff;padding:28px 40px;margin-bottom:24px}
    .container{max-width:1200px;margin:0 auto;padding:0 24px 40px}
    .panel{background:#fff;border-radius:10px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:18px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    label{display:block;font-size:.8rem;font-weight:bold;color:#555;margin-bottom:5px}
    select,input,textarea{width:100%;padding:9px 12px;border:1px solid #ccd;border-radius:4px;font-size:.9rem}
    textarea{min-height:90px}
    .btn{display:inline-block;padding:9px 18px;border:none;border-radius:4px;text-decoration:none;cursor:pointer}
    .primary{background:#1a3c5e;color:#fff}
    .secondary{background:#6c757d;color:#fff}
    .outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
    .card-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .stat{background:#fff;border:1px solid #e6eaf0;border-radius:8px;padding:14px}
    .stat .n{font-size:1.5rem;font-weight:bold;color:#1a3c5e}
    .stat .l{font-size:.75rem;color:#666;text-transform:uppercase;letter-spacing:.5px}
    .section{font-size:1rem;font-weight:bold;color:#1a3c5e;margin-bottom:10px;padding-bottom:6px;border-bottom:2px solid #e0e6ef}
    ul{margin:10px 0 0 20px}
    li{margin-bottom:8px;line-height:1.5}
    pre{white-space:pre-wrap;background:#f8fafc;border:1px solid #e6eaf0;border-radius:6px;padding:14px;line-height:1.7}
    .muted{color:#666}
  </style>
</head>
<body>
<div class="topbar">
  <strong>Counsel Engine</strong>
  <div>
    <a href="index.php">Home</a>
    <a href="library.php">Library</a>
    <a href="domain_templates.php">Domain Templates</a>
    <a href="domain_map.php">Domain Map</a>
    <a href="phrase_bank.php">Phrase Bank</a>
    <a href="dashboard.php">Dashboard</a>
  </div>
</div>

<div class="hero">
  <h1>Advanced Counsel Engine</h1>
  <p>Shared analysis, summary, instructions and drafting layer for supported modules.</p>
</div>

<div class="container">
  <div class="panel">
    <div style="margin-bottom:16px;padding:10px 12px;border-radius:6px;background:#f8fafc;border:1px solid #dfe7f1;color:#1f2937;">
      API status: <strong><?php echo htmlspecialchars(aep_api_status_label()); ?></strong>
      <?php if (!$apiConfig['enabled']): ?>
        <span class="muted">Set AEP_API_KEY in a local .env file to enable remote model calls.</span>
      <?php endif; ?>
    </div>
    <form method="GET">
      <div class="grid">
        <div>
          <label>Domain</label>
          <select name="domain" onchange="this.form.submit()">
            <?php foreach ($profiles as $key => $profile): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $domain === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($profile['label']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Case</label>
          <select name="id">
            <option value="">Select case</option>
            <?php foreach ($cases as $row): ?>
              <option value="<?php echo (int)$row['id']; ?>" <?php echo $caseId === (int)$row['id'] ? 'selected' : ''; ?>>
                #<?php echo (int)$row['id']; ?> - <?php echo htmlspecialchars($row['label']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Mode</label>
          <select name="doc_type">
            <option value="analysis_brief" <?php echo $docType === 'analysis_brief' ? 'selected' : ''; ?>>Analysis brief</option>
            <option value="draft_outline" <?php echo $docType === 'draft_outline' ? 'selected' : ''; ?>>Draft outline</option>
            <option value="instructions_note" <?php echo $docType === 'instructions_note' ? 'selected' : ''; ?>>Instructions note</option>
            <option value="counsel_note" <?php echo $docType === 'counsel_note' ? 'selected' : ''; ?>>Counsel note</option>
          </select>
        </div>
        <div>
          <label>Reasoning depth</label>
          <select name="reasoning_depth">
            <?php foreach ($reasoningOptions as $key => $label): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $reasoningDepth === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Client instructions</label>
          <input type="text" name="instructions" value="<?php echo htmlspecialchars($instructions); ?>" placeholder="Short brief from the client">
        </div>
        <div>
          <label>Template</label>
          <select name="template">
            <option value="">None</option>
            <?php foreach ($templates as $key => $template): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $templateKey === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($template['label'] ?? $key); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Jurisdiction</label>
          <select name="jurisdiction">
            <?php foreach ($jurisdictionOptions as $key => $label): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $jurisdiction === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Procedure Track</label>
          <select name="track">
            <?php foreach ($trackOptions as $key => $label): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $track === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Phrase Pack</label>
          <select name="phrase_category">
            <?php foreach ($phraseCategories as $key => $label): ?>
              <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $phraseCategory === $key ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn primary" type="submit">Generate</button>
        <button class="btn outline" type="submit" name="ai" value="1" <?php echo $apiConfig['enabled'] ? '' : 'disabled'; ?>>AI Assist</button>
        <a class="btn secondary" href="counsel_engine.php">Reset</a>
      </div>
    </form>
    <?php if ($caseId <= 0): ?>
      <div style="margin-top:14px;padding:10px 12px;border-radius:6px;background:#fff3cd;border:1px solid #ffe69c;color:#664d03;">
        Select a saved case for record-based analysis, or provide detailed Client instructions to run an instructions-led analysis.
        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn outline" href="<?php echo htmlspecialchars($domainListHref); ?>">Open domain list</a>
          <a class="btn primary" href="<?php echo htmlspecialchars($domainCreateHref); ?>">Create new case</a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($package): ?>
    <?php if ($analysisMode === 'instructions_only'): ?>
      <div class="panel">
        <p class="muted"><strong>Mode:</strong> Instructions-led analysis (no saved case selected).</p>
      </div>
    <?php endif; ?>
    <div class="card-grid">
      <div class="stat"><div class="n"><?php echo $package['pct']; ?>%</div><div class="l">Strength</div></div>
      <div class="stat"><div class="n"><?php echo $package['rating']; ?></div><div class="l">Rating</div></div>
      <div class="stat"><div class="n"><?php echo $package['score']; ?>/<?php echo $package['total']; ?></div><div class="l">Core Items</div></div>
      <div class="stat"><div class="n"><?php echo count($package['gaps']); ?></div><div class="l">Gaps</div></div>
    </div>

    <?php if ($remoteAiOutput !== null): ?>
      <div class="panel">
        <div class="section">AI Assist</div>
        <pre><?php echo htmlspecialchars($remoteAiOutput); ?></pre>
      </div>
    <?php elseif ($remoteAiError !== null): ?>
      <div class="panel">
        <div class="section">AI Assist</div>
        <p class="muted"><?php echo htmlspecialchars($remoteAiError); ?></p>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="section">Case Summary</div>
      <p><?php echo htmlspecialchars($package['summary'] ?: 'No summary available.'); ?></p>
    </div>

    <?php $outcomeV2 = $package['outcome_v2'] ?? []; ?>
    <?php if (!empty($outcomeV2)): ?>
      <div class="panel">
        <div class="section">Outcome Engine V2</div>
        <div class="card-grid" style="margin-bottom:12px">
          <div class="stat"><div class="n"><?php echo (int)$outcomeV2['outcome_index']; ?>%</div><div class="l">Outcome Index</div></div>
          <div class="stat"><div class="n"><?php echo htmlspecialchars($outcomeV2['confidence_band']); ?></div><div class="l">Confidence Band</div></div>
          <div class="stat"><div class="n"><?php echo (int)$outcomeV2['risk_score']; ?>%</div><div class="l">Risk Score</div></div>
          <div class="stat"><div class="n"><?php echo (int)$outcomeV2['procedural_readiness']; ?>%</div><div class="l">Procedural Ready</div></div>
        </div>
        <ul>
          <li><?php echo htmlspecialchars($outcomeV2['scenario_best'] ?? ''); ?></li>
          <li><?php echo htmlspecialchars($outcomeV2['scenario_likely'] ?? ''); ?></li>
          <li><?php echo htmlspecialchars($outcomeV2['scenario_worst'] ?? ''); ?></li>
        </ul>
      </div>

      <div class="panel">
        <div class="section">Outcome V2 Recommendations</div>
        <ul>
          <?php foreach (($outcomeV2['recommendations'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="section">Counsel Arguments</div>
      <ul>
        <?php foreach ($package['arguments'] as $item): ?>
          <li><?php echo htmlspecialchars($item); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Analysis</div>
      <ul>
        <?php foreach (($package['analysis'] ?? []) as $item): ?>
          <li><?php echo htmlspecialchars($item); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Findings of Fact</div>
      <ul>
        <?php foreach (array_slice(($package['fact_to_issues'] ?? []), 0, 10) as $row): ?>
          <li><?php echo htmlspecialchars(($row['fact'] ?? 'Fact pending') . ' -> ' . ($row['issue'] ?? 'Issue pending')); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Application of Law to Relevant Facts</div>
      <ul>
        <?php foreach (array_slice(($package['violations'] ?? []), 0, 8) as $row): ?>
          <li>
            <?php echo htmlspecialchars(($row['rule'] ?? 'Legal rule to confirm') . ': ' . ($row['conduct'] ?? 'Fact application pending')); ?>
            <?php if (!empty($row['remedy_hint'])): ?><br><?php echo htmlspecialchars('Relief link: ' . $row['remedy_hint']); ?><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Domain Essentials and Engine Health</div>
      <p><strong>Essentials to complete</strong></p>
      <ul>
        <?php foreach (($package['essentials'] ?? []) as $item): ?>
          <li><?php echo htmlspecialchars($item); ?></li>
        <?php endforeach; ?>
      </ul>
      <p style="margin-top:10px"><strong>Engine status</strong></p>
      <ul>
        <?php foreach (($package['engine_status'] ?? []) as $row): ?>
          <li><?php echo htmlspecialchars(($row['label'] ?? 'Engine') . ': ' . strtoupper((string)($row['status'] ?? 'unknown'))); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Chronology Engine</div>
      <?php if (empty($package['chronology'])): ?>
        <p class="muted">No chronology data available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['chronology'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars(trim(($row['date'] ?? '') . ' ' . ($row['label'] ?? ''))); ?></strong>
              <?php if (!empty($row['event'])): ?><br><?php echo htmlspecialchars($row['event']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Authority / Citation Engine</div>
      <?php if (empty($package['authority_citation'])): ?>
        <p class="muted">No authority mapping available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['authority_citation'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['target'] ?? ''); ?></strong>
              <?php if (!empty($row['status'])): ?> — <?php echo htmlspecialchars($row['status']); ?><?php endif; ?>
              <?php if (!empty($row['principle'])): ?><br><em><?php echo htmlspecialchars($row['principle']); ?></em><?php endif; ?>
              <?php if (!empty($row['source_hint'])): ?><br>Source hint: <?php echo htmlspecialchars($row['source_hint']); ?><?php endif; ?>
              <?php if (!empty($row['citation_hook'])): ?><br><?php echo htmlspecialchars($row['citation_hook']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Legal Violation Engine</div>
      <?php if (empty($package['violations'])): ?>
        <p class="muted">No violation mapping available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['violations'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['label'] ?? ''); ?></strong>
              <?php if (!empty($row['rule'])): ?> — Rule: <?php echo htmlspecialchars($row['rule']); ?><?php endif; ?>
              <?php if (!empty($row['conduct'])): ?><br>Conduct: <?php echo htmlspecialchars($row['conduct']); ?><?php endif; ?>
              <?php if (!empty($row['proof'])): ?><br>Proof: <?php echo htmlspecialchars($row['proof']); ?><?php endif; ?>
              <?php if (!empty($row['remedy_hint'])): ?><br>Remedy: <?php echo htmlspecialchars($row['remedy_hint']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Clause and Obligation Engine</div>
      <?php if (empty($package['clauses'])): ?>
        <p class="muted">No clause extraction available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['clauses'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['clause'] ?? ''); ?></strong>
              <?php if (!empty($row['impact'])): ?> — <?php echo htmlspecialchars($row['impact']); ?><?php endif; ?>
              <?php if (!empty($row['text'])): ?><br><?php echo htmlspecialchars($row['text']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Evidence Matrix Engine</div>
      <?php if (empty($package['evidence_matrix'])): ?>
        <p class="muted">No evidence matrix available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['evidence_matrix'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['source'] ?? ''); ?></strong>
              — <?php echo htmlspecialchars($row['status'] ?? ''); ?>
              <?php if (!empty($row['importance'])): ?><br><?php echo htmlspecialchars($row['importance']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Counterargument Engine</div>
      <ul>
        <?php foreach (($package['counterarguments'] ?? []) as $item): ?>
          <li><?php echo htmlspecialchars($item); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Remedy Engine</div>
      <?php if (!empty($package['remedies'])): ?>
        <p><strong>Requested:</strong> <?php echo htmlspecialchars($package['remedies']['requested'] ?? ''); ?></p>
        <p><strong>Fit:</strong> <?php echo htmlspecialchars($package['remedies']['fit'] ?? ''); ?></p>
        <ul>
          <?php foreach (($package['remedies']['recommended'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Issue Map</div>
      <?php if (empty($package['issues'])): ?>
        <p class="muted">No issue fields recorded yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['issues'] as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Issue Spotting Engine</div>
      <?php if (empty($package['issue_engine'])): ?>
        <p class="muted">No issue spotting data available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['issue_engine'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['issue'] ?? ''); ?></strong>
              <?php if (!empty($row['why_it_matters'])): ?><br><?php echo htmlspecialchars($row['why_it_matters']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Fact-to-Issues Mapper</div>
      <?php if (empty($package['fact_to_issues'])): ?>
        <p class="muted">No fact-to-issues mapping available yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['fact_to_issues'] as $row): ?>
            <li>
              <strong><?php echo htmlspecialchars($row['issue'] ?? ''); ?></strong>
              <?php if (!empty($row['fact'])): ?><br><?php echo htmlspecialchars($row['fact']); ?><?php endif; ?>
              <?php if (!empty($row['use'])): ?><br><?php echo htmlspecialchars($row['use']); ?><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Procedure / Limitation Engine</div>
      <?php if (empty($package['procedure_limitation'])): ?>
        <p class="muted">No procedure data available yet.</p>
      <?php else: ?>
        <p><strong>Track:</strong> <?php echo htmlspecialchars($package['procedure_limitation']['track'] ?? ''); ?></p>
        <p><strong>Limitation:</strong> <?php echo htmlspecialchars($package['procedure_limitation']['limitation'] ?? ''); ?></p>
        <p><strong>Risk:</strong> <?php echo htmlspecialchars($package['procedure_limitation']['risk'] ?? ''); ?></p>
        <p><strong>Next step:</strong> <?php echo htmlspecialchars($package['procedure_limitation']['next_step'] ?? ''); ?></p>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Pleading Generator</div>
      <?php if (empty($package['pleading_generator'])): ?>
        <p class="muted">No pleading scaffold available yet.</p>
      <?php else: ?>
        <p><strong>Heading:</strong> <?php echo htmlspecialchars($package['pleading_generator']['heading'] ?? ''); ?></p>
        <p><strong>Opening:</strong> <?php echo htmlspecialchars($package['pleading_generator']['opening'] ?? ''); ?></p>
        <p><strong>Facts:</strong> <?php echo htmlspecialchars($package['pleading_generator']['facts'] ?? ''); ?></p>
        <p><strong>Cause:</strong> <?php echo htmlspecialchars($package['pleading_generator']['cause'] ?? ''); ?></p>
        <p><strong>Conclusion:</strong> <?php echo htmlspecialchars($package['pleading_generator']['conclusion'] ?? ''); ?></p>
        <ul>
          <?php foreach (($package['pleading_generator']['issues'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Relief Drafting Engine</div>
      <?php if (empty($package['relief_drafting'])): ?>
        <p class="muted">No relief draft available yet.</p>
      <?php else: ?>
        <p><strong>Requested:</strong> <?php echo htmlspecialchars($package['relief_drafting']['requested'] ?? ''); ?></p>
        <p><strong>Fit:</strong> <?php echo htmlspecialchars($package['relief_drafting']['fit'] ?? ''); ?></p>
        <p><strong>Short form:</strong> <?php echo htmlspecialchars($package['relief_drafting']['short_form'] ?? ''); ?></p>
        <ul>
          <?php foreach (($package['relief_drafting']['draft_prayers'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Domain Strategy Engine</div>
      <?php if (empty($package['domain_strategy'])): ?>
        <p class="muted">No domain strategy available yet.</p>
      <?php else: ?>
        <p><strong>Case theory:</strong> <?php echo htmlspecialchars($package['domain_strategy']['case_theory'] ?? ''); ?></p>
        <p><strong>Case anchor:</strong> <?php echo htmlspecialchars($package['domain_strategy']['case_anchor'] ?? ''); ?></p>
        <p><strong>File focus:</strong> <?php echo htmlspecialchars($package['domain_strategy']['file_focus'] ?? ''); ?></p>
        <div class="grid">
          <div>
            <label>Elements</label>
            <ul>
              <?php foreach (($package['domain_strategy']['elements'] ?? []) as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div>
            <label>Pressure points</label>
            <ul>
              <?php foreach (($package['domain_strategy']['pressure_points'] ?? []) as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Skeleton Argument Engine</div>
      <?php if (empty($package['skeleton_argument'])): ?>
        <p class="muted">No skeleton argument available yet.</p>
      <?php else: ?>
        <p><strong>Heading:</strong> <?php echo htmlspecialchars($package['skeleton_argument']['heading'] ?? ''); ?></p>
        <p><strong>Standard:</strong> <?php echo htmlspecialchars($package['skeleton_argument']['standard'] ?? ''); ?></p>
        <p><strong>Relief line:</strong> <?php echo htmlspecialchars($package['skeleton_argument']['relief_line'] ?? ''); ?></p>
        <label>Core propositions</label>
        <ul>
          <?php foreach (($package['skeleton_argument']['propositions'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <label>Authority lines</label>
        <ul>
          <?php foreach (($package['skeleton_argument']['authorities'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Letter Before Action Engine</div>
      <?php if (empty($package['letter_before_action'])): ?>
        <p class="muted">No pre-action letter draft available yet.</p>
      <?php else: ?>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($package['letter_before_action']['subject'] ?? ''); ?></p>
        <p><strong>Opening:</strong> <?php echo htmlspecialchars($package['letter_before_action']['opening'] ?? ''); ?></p>
        <p><strong>Recipient:</strong> <?php echo htmlspecialchars($package['letter_before_action']['recipient'] ?? ''); ?></p>
        <p><strong>Deadline:</strong> <?php echo htmlspecialchars($package['letter_before_action']['deadline'] ?? ''); ?></p>
        <p><strong>Escalation:</strong> <?php echo htmlspecialchars($package['letter_before_action']['escalation'] ?? ''); ?></p>
        <label>Breach points</label>
        <ul>
          <?php foreach (($package['letter_before_action']['breaches'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <label>Demands</label>
        <ul>
          <?php foreach (($package['letter_before_action']['demands'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Bundle / Disclosure Engine</div>
      <?php if (empty($package['bundle_disclosure'])): ?>
        <p class="muted">No bundle/disclosure plan available yet.</p>
      <?php else: ?>
        <label>Bundle sections</label>
        <ul>
          <?php foreach (($package['bundle_disclosure']['sections'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <label>Priority documents</label>
        <ul>
          <?php foreach (($package['bundle_disclosure']['priority_documents'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <label>Missing documents</label>
        <ul>
          <?php foreach (($package['bundle_disclosure']['missing_documents'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
        <label>Disclosure requests</label>
        <ul>
          <?php foreach (($package['bundle_disclosure']['disclosure_requests'] ?? []) as $item): ?>
            <li><?php echo htmlspecialchars($item); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="section">Draft Outline</div>
      <ul>
        <?php foreach ($package['draft_outline'] as $item): ?>
          <li><?php echo htmlspecialchars($item); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="panel">
      <div class="section">Effective Draft Reasoned Response</div>
      <pre><?php echo htmlspecialchars($package['reasoned_response'] ?? 'No response available.'); ?></pre>
    </div>

    <div class="panel">
      <div class="section">Counsel Note</div>
      <pre><?php echo htmlspecialchars("Domain: {$package['label']}\nReasoning depth: " . ($reasoningOptions[$reasoningDepth] ?? 'Standard') . "\nJurisdiction: " . ($jurisdictionOptions[$jurisdiction] ?? 'Unknown') . "\nTrack: " . ($trackOptions[$track] ?? 'General') . "\nRating: {$package['rating']} ({$package['pct']}%)\nOutcome Index: " . (int)($outcomeV2['outcome_index'] ?? 0) . "%\nConfidence: " . ($outcomeV2['confidence_band'] ?? 'Unknown') . "\nLikely Case: " . ($outcomeV2['scenario_likely'] ?? 'Not available') . "\nSummary: {$package['summary']}\n\nNext steps:\n- " . implode("\n- ", $package['next_steps'])); ?></pre>
    </div>

    <?php if ($templateText !== ''): ?>
      <div class="panel">
        <div class="section">Template Draft Starter</div>
        <?php if ($templateDownloadQuery !== ''): ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=txt">Download Template TXT</a>
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=pdf">Download Template PDF</a>
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=html">Print Template View</a>
          </div>
        <?php endif; ?>
        <pre><?php echo htmlspecialchars($templateText); ?></pre>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="section">Gaps To Fix</div>
      <?php if (empty($package['gaps'])): ?>
        <p class="muted">No core gaps detected.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($package['gaps'] as $gap): ?>
            <li><?php echo htmlspecialchars(aep_labelize($gap)); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="panel">
      <p class="muted">Select a case or enter detailed Client instructions to generate the counsel pack.</p>
    </div>
    <?php if ($templateText !== ''): ?>
      <div class="panel">
        <div class="section">Template Draft Starter</div>
        <?php if ($templateDownloadQuery !== ''): ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=txt">Download Template TXT</a>
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=pdf">Download Template PDF</a>
            <a class="btn outline" href="template_download.php?<?php echo $templateDownloadQuery; ?>&format=html">Print Template View</a>
          </div>
        <?php endif; ?>
        <pre><?php echo htmlspecialchars($templateText); ?></pre>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
