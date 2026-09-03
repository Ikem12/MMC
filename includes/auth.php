<?php
/**
 * FILE: includes/auth.php
 * Authentication guard for the AEP Legal Intelligence Platform.
 * Starts a session with secure cookie flags and exposes helpers to
 * read the current user and guard pages that require login/admin.
 *
 * Usage: require_once __DIR__ . '/includes/auth.php';
 * Including this file automatically enforces the login guard (redirecting
 * to login.php if not authenticated). Call aep_require_admin() afterwards
 * on pages that must also be restricted to administrators.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!function_exists('aep_current_user')) {
    /** Returns the logged-in user's session data, or null if not authenticated. */
    function aep_current_user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => (int) $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'is_admin' => !empty($_SESSION['is_admin']),
        ];
    }
}

require_once __DIR__ . '/functions.php';

if (!function_exists('aep_require_auth')) {
    /** Guard a page: redirect to login if the visitor is not signed in. */
    function aep_require_auth(): void
    {
        if (aep_current_user() === null) {
            $return = aep_safe_return_url($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: login.php?return=' . urlencode($return));
            exit;
        }
    }
}

if (!function_exists('aep_require_admin')) {
    /** Guard a page: require login, then respond 403 if the user is not an admin. */
    function aep_require_admin(): void
    {
        aep_require_auth();
        $user = aep_current_user();
        if (!$user || empty($user['is_admin'])) {
            http_response_code(403);
            exit('403 Forbidden — admin access required.');
        }
    }
}

// Including this file enforces the login guard for the current page.
aep_require_auth();
