<?php
// FILE: csrf.php — CSRF token helpers
// Usage: require_once __DIR__ . '/csrf.php';
// Generate a token:  csrf_token()
// Verify on POST:    csrf_verify()
// Render hidden input: csrf_input()

function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    return $expected !== '' && hash_equals($expected, $token);
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '"/>';
}
