<?php
// FILE: claude_api.php
// Shared Claude API helper for AEP Legal Platform
// Never commit config.php — it contains your API key.

/**
 * Load Claude API key from config.php (server-side only, gitignored).
 * Returns the key string or null if not configured.
 */
function claude_api_key(): ?string {
    $cfg = __DIR__ . '/config.php';
    if (!file_exists($cfg)) return null;
    $conf = include $cfg;
    return $conf['claude_api_key'] ?? null;
}

/**
 * Send a prompt to Claude and return the response text.
 *
 * @param string $system  System prompt (legal context, role)
 * @param string $user    User prompt (case facts + question)
 * @param string $model   Anthropic model ID
 * @param int    $tokens  Max tokens to return
 * @return array ['ok' => bool, 'text' => string, 'error' => string]
 */
function claude_ask(string $system, string $user, string $model = 'claude-3-5-sonnet-20241022', int $tokens = 1500): array {
    $key = claude_api_key();
    if (!$key) {
        return ['ok' => false, 'text' => '', 'error' => 'Claude API key not configured. Create config.php with your API key.'];
    }

    $payload = json_encode([
        'model'      => $model,
        'max_tokens' => $tokens,
        'system'     => $system,
        'messages'   => [
            ['role' => 'user', 'content' => $user]
        ]
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01',
        ],
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return ['ok' => false, 'text' => '', 'error' => 'Network error: ' . $curl_error];
    }

    $data = json_decode($response, true);

    if ($http_code !== 200) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $http_code);
        return ['ok' => false, 'text' => '', 'error' => 'API error: ' . $msg];
    }

    $text = $data['content'][0]['text'] ?? '';
    return ['ok' => true, 'text' => $text, 'error' => ''];
}

/**
 * Build a legal analysis prompt for a given domain + case data.
 */
function claude_analysis_prompt(string $domain, array $case): array {
    $system = <<<SYS
You are a senior legal counsel with expertise in Nigerian and international law. 
You provide precise, professional legal analysis for practitioners.
Format your response with clear sections using these exact headings:
**LEGAL ISSUES IDENTIFIED**
**APPLICABLE LAW & STATUTES**
**RELEVANT CASE LAW**
**STRATEGIC ASSESSMENT**
**RECOMMENDED NEXT STEPS**
Be concise, authoritative and practical. Use bullet points within each section.
SYS;

    $facts = implode("\n", array_map(
        fn($k,$v) => ucfirst(str_replace('_',' ',$k)) . ': ' . ($v ?: 'Not provided'),
        array_keys($case), array_values($case)
    ));

    $user = "Please provide a deep legal analysis for the following {$domain} case:\n\n{$facts}";
    return ['system' => $system, 'user' => $user];
}

/**
 * Build a legal document drafting prompt.
 */
function claude_draft_prompt(string $domain, string $doc_type, array $case): array {
    $system = <<<SYS
You are a senior legal draftsman specialising in Nigerian law and international legal practice.
Draft professional, court-ready legal documents. 
Use formal legal language. Include proper legal structure and formatting.
Do not include placeholders like [INSERT NAME] — use the actual case data provided or omit if unavailable.
SYS;

    $doc_labels = [
        'demand_letter' => 'Demand Letter',
        'legal_opinion' => 'Legal Opinion',
        'court_filing'  => 'Court Filing Notice',
        'settlement'    => 'Settlement Proposal',
        'cease_desist'  => 'Cease & Desist Notice',
    ];
    $doc_label = $doc_labels[$doc_type] ?? $doc_type;

    $facts = implode("\n", array_map(
        fn($k,$v) => ucfirst(str_replace('_',' ',$k)) . ': ' . ($v ?: 'Not provided'),
        array_keys($case), array_values($case)
    ));

    $user = "Draft a professional {$doc_label} for the following {$domain} case:\n\n{$facts}";
    return ['system' => $system, 'user' => $user];
}
