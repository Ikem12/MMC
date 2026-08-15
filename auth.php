<?php
// FILE: auth.php
// Require a logged-in user (session-based).
// Usage: require_once __DIR__ . '/auth.php';
// For admin-only pages, additionally check: if (empty($_SESSION['is_admin'])) { ... }

// Start or resume session with safer cookie flags for dev.
// Note: set 'secure' => true only when running over HTTPS.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => false,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// If not logged in, redirect to login page with a return URL (safe, only local relative paths)
if (empty($_SESSION['user_id'])) {
    $return = $_SERVER['REQUEST_URI'] ?? '/';
    // Only allow internal relative return paths to avoid open-redirects:
    if (preg_match('#^https?://#i', $return) || strpos($return, '//') === 0) {
        $return = '/';
    }
    $returnEnc = urlencode($return);
    header("Location: login.php?return={$returnEnc}");
    exit;
}