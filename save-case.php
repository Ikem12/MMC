<?php
// FILE: save-case.php
session_set_cookie_params(['httponly' => true, 'secure' => false, 'samesite' => 'Lax']);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorised']);
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/data/aep.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

header('Content-Type: application/json');

$domain  = trim($_POST['domain'] ?? '');
$case_id = (int)($_POST['id'] ?? 0);
$field   = trim($_POST['field'] ?? '');
$value   = trim($_POST['value'] ?? '');

$tables = [
    'human_rights' => 'human_rights_cases',
    'tort'         => 'tort_cases',
    'admin_law'    => 'admin_law_cases',
    'oil_gas'      => 'oil_gas_cases',
];

$allowed = [
    'human_rights' => [
        'title','right_violated','claimant','respondent',
        'article_section','violation_date','remedy',
        'summary','grounds','status'
    ],
    'tort' => [
        'title','tort_type','claimant','defendant',
        'duty_of_care','breach','damages',
        'summary','status'
    ],
    'admin_law' => [
        'title','decision_maker','claimant','respondent',
        'ground_of_review','decision_date','relief_sought',
        'summary','status'
    ],
    'oil_gas' => [
        'title','licence_number','field_location','contract_type',
        'operator','contractor','dispute_type',
        'summary','status'
    ],
];

if (!$domain || !isset($tables[$domain])) {
    echo json_encode(['success' => false, 'message' => 'Invalid domain.']);
    exit;
}
if (!$case_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid case ID.']);
    exit;
}
if (!$field || !in_array($field, $allowed[$domain] ?? [])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or disallowed field.']);
    exit;
}

$table = $tables[$domain];

try {
    $check = $pdo->prepare("SELECT id FROM {$table} WHERE id = :id");
    $check->execute([':id' => $case_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Case not found.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE {$table} SET {$field} = :value WHERE id = :id");
    $stmt->execute([':value' => $value, ':id' => $case_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Field updated successfully.',
        'domain'  => $domain,
        'id'      => $case_id,
        'field'   => $field,
        'value'   => $value,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}