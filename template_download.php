<?php
require_once __DIR__ . '/legal_library.php';
require_once __DIR__ . '/includes/functions.php';

session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function aep_pdf_escape(string $value): string
{
    $value = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $value);
    return preg_replace('/[^\x20-\x7E]/', '?', $value) ?? '';
}

function aep_pdf_wrapped_lines(string $text, int $width = 95): array
{
    $result = [];
    $lines = preg_split("/\r\n|\n|\r/", $text) ?: [];
    foreach ($lines as $line) {
        $clean = trim((string)$line);
        if ($clean === '') {
            $result[] = '';
            continue;
        }
        $wrapped = wordwrap($clean, $width, "\n", true);
        foreach (explode("\n", $wrapped) as $part) {
            $result[] = $part;
        }
    }
    return $result;
}

function aep_build_simple_pdf(string $title, string $text): string
{
    $lines = aep_pdf_wrapped_lines($text, 95);
    $linesPerPage = 48;
    $pages = array_chunk($lines, $linesPerPage);
    if (empty($pages)) {
        $pages = [['']];
    }

    $objects = [];
    $pageCount = count($pages);
    $pageStart = 3;
    $contentStart = $pageStart + $pageCount;
    $fontObj = $contentStart + $pageCount;
    $infoObj = $fontObj + 1;

    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

    $kids = [];
    for ($i = 0; $i < $pageCount; $i++) {
        $kids[] = ($pageStart + $i) . " 0 R";
    }
    $objects[2] = "<< /Type /Pages /Count {$pageCount} /Kids [" . implode(' ', $kids) . "] >>";

    for ($i = 0; $i < $pageCount; $i++) {
        $pageObj = $pageStart + $i;
        $contentObj = $contentStart + $i;
        $objects[$pageObj] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$fontObj} 0 R >> >> /Contents {$contentObj} 0 R >>";

        $commands = [];
        $commands[] = "BT";
        $commands[] = "/F1 11 Tf";
        $commands[] = "50 790 Td";
        $commands[] = "14 TL";
        foreach ($pages[$i] as $line) {
            $commands[] = "(" . aep_pdf_escape($line) . ") Tj";
            $commands[] = "T*";
        }
        $commands[] = "ET";
        $stream = implode("\n", $commands) . "\n";
        $len = strlen($stream);
        $objects[$contentObj] = "<< /Length {$len} >>\nstream\n{$stream}endstream";
    }

    $objects[$fontObj] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $safeTitle = aep_pdf_escape($title);
    $objects[$infoObj] = "<< /Title ({$safeTitle}) /Producer (AEP Legal Platform) >>";

    ksort($objects);
    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    $maxObj = max(array_keys($objects));
    for ($i = 1; $i <= $maxObj; $i++) {
        $obj = $objects[$i] ?? "<<>>";
        $offsets[$i] = strlen($pdf);
        $pdf .= "{$i} 0 obj\n{$obj}\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $maxObj; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R /Info {$infoObj} 0 R >>\n";
    $pdf .= "startxref\n{$xrefPos}\n%%EOF";
    return $pdf;
}

$domain = (string)($_GET['domain'] ?? '');
$templateKey = (string)($_GET['template'] ?? '');
$caseId = (int)($_GET['id'] ?? 0);
$format = (string)($_GET['format'] ?? 'txt');
$jurisdiction = (string)($_GET['jurisdiction'] ?? 'england_wales');
$track = (string)($_GET['track'] ?? 'general');
$phraseCategory = (string)($_GET['phrase_category'] ?? '');
$focus = trim((string)($_GET['focus'] ?? ''));

$jurisdictionOptions = aep_jurisdiction_options();
if (!isset($jurisdictionOptions[$jurisdiction])) {
    $jurisdiction = 'england_wales';
}
$trackOptions = aep_track_options();
if (!isset($trackOptions[$track])) {
    $track = 'general';
}
$phraseCategories = aep_phrase_bank_categories();
$phraseCategory = aep_normalize_phrase_category((string)$phraseCategory, (string)$domain);

$profile = aep_supported_domain($domain);
if (!$profile) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid domain.';
    exit;
}

$templates = aep_domain_templates($domain);
if (!isset($templates[$templateKey])) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid template.';
    exit;
}

aep_log_activity(aep_database(), 'Templates', 'Generated ' . (string)($templates[$templateKey]['label'] ?? $templateKey));

$case = null;
if ($caseId > 0) {
    $case = aep_load_case($domain, $caseId);
}

$text = aep_render_template_text($domain, $templateKey, $case ?: [], [
    'jurisdiction' => $jurisdiction,
    'track' => $track,
    'phrase_category' => $phraseCategory,
    'focus' => $focus,
]);
if ($text === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Unable to render template output.';
    exit;
}

$templateLabel = (string)($templates[$templateKey]['label'] ?? $templateKey);
$baseName = strtolower($profile['label'] . '_' . $templateLabel . '_' . date('Ymd_His'));
$fileName = preg_replace('/[^a-z0-9._-]+/', '_', $baseName);
$fileName = trim((string)$fileName, '_');
if ($fileName === '') {
    $fileName = 'template_output_' . date('Ymd_His');
}

if ($format === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8"/>
      <title>Template Print View</title>
      <meta name="viewport" content="width=device-width,initial-scale=1"/>
      <style>
        body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;color:#222;margin:0;padding:24px}
        .card{max-width:900px;margin:0 auto;background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
        pre{white-space:pre-wrap;line-height:1.6;background:#f8fbff;border:1px solid #dfe6f0;border-radius:8px;padding:14px}
        .actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
        .btn{display:inline-block;padding:8px 12px;text-decoration:none;border-radius:4px;background:#1a3c5e;color:#fff}
        .btn-outline{background:#fff;color:#1a3c5e;border:1px solid #1a3c5e}
        @media print {.actions{display:none} body{padding:0} .card{box-shadow:none;border-radius:0;max-width:none}}
      </style>
    </head>
    <body>
      <div class="card">
        <div class="actions">
          <a class="btn btn-outline" href="javascript:window.print()">Print</a>
          <a class="btn" href="template_download.php?domain=<?php echo urlencode($domain); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>&focus=<?php echo urlencode($focus); ?><?php echo $caseId > 0 ? '&id=' . $caseId : ''; ?>&format=txt">Download TXT</a>
          <a class="btn" href="template_download.php?domain=<?php echo urlencode($domain); ?>&template=<?php echo urlencode($templateKey); ?>&jurisdiction=<?php echo urlencode($jurisdiction); ?>&track=<?php echo urlencode($track); ?>&phrase_category=<?php echo urlencode($phraseCategory); ?>&focus=<?php echo urlencode($focus); ?><?php echo $caseId > 0 ? '&id=' . $caseId : ''; ?>&format=pdf">Download PDF</a>
        </div>
        <pre><?php echo htmlspecialchars($text); ?></pre>
      </div>
    </body>
    </html>
    <?php
    exit;
}

if ($format === 'pdf') {
    $pdf = aep_build_simple_pdf($profile['label'] . ' - ' . $templateLabel, $text);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fileName . '.txt"');
echo $text;
