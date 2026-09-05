<?php
require_once __DIR__ . '/phase2.php';
p2_start();
p2_check_csrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
$type = $_POST['type'] ?? '';
$allowed = ['document.printed', 'analysis.generated'];
if (!in_array($type, $allowed, true)) {
    http_response_code(422);
    exit;
}
$matterId = (int)($_POST['matter_id'] ?? 0);
p2_log($type, $type === 'document.printed' ? 'Printed a generated document' : 'Generated a case analysis', $matterId ?: null);
http_response_code(204);
