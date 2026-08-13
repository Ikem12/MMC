<?php
// FILE: auth.php
// Require a logged-in admin user (session-based).
// Usage: require_once __DIR__ . '/auth.php';

// Start or resume session with safer cookie flags for dev.
// Note: set 'secure' => true only when running over HTTPS.
session_set_cookie_params([
    'httponly' => true,
    'secure' => false,
    'samesite' => 'Lax'
]);
session_start();

// If not logged in, redirect to login page with a return URL (safe, only local relative paths)
if (empty($_SESSION['user_id'])) {
    $return = $_SERVER['REQUEST_URI'] ?? '/';
    // Only allow internal relative return paths to avoid open-redirects:
    if (strpos($return, 'http://') === 0 || strpos($return, 'https://') === 0) {
        $return = '/';
    }
    $returnEnc = urlencode($return);
    header("Location: login.php?return={$returnEnc}");
    exit;
}

// If logged in but not admin, show 403 and stop
if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo "403 Forbidden — admin access required.";
    exit;
}