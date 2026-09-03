<?php
/**
 * FILE: includes/functions.php
 * Shared helpers used across the AEP Legal Intelligence Platform:
 * output escaping, CSRF protection, validation, activity logging and
 * small request-handling utilities used by the operational modules
 * (clients, matters, tasks, deadlines, etc).
 */

require_once __DIR__ . '/database.php';

if (!function_exists('aep_h')) {
    /** HTML-escape a value for safe output. */
    function aep_h(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('aep_safe_return_url')) {
    /** Restrict a return URL to a local relative path to avoid open redirects. */
    function aep_safe_return_url(?string $url): string
    {
        $url = $url ?? '/';
        if ($url === '' || $url[0] !== '/' || (isset($url[1]) && $url[1] === '/')) {
            return '/';
        }
        return $url;
    }
}

if (!function_exists('aep_csrf_token')) {
    /** Return the current CSRF token, generating one if necessary. */
    function aep_csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('aep_csrf_field')) {
    /** Render a hidden form field containing the CSRF token. */
    function aep_csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . aep_h(aep_csrf_token()) . '">';
    }
}

if (!function_exists('aep_csrf_verify')) {
    /** Validate a submitted CSRF token against the session token. */
    function aep_csrf_verify(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('aep_validate_csrf')) {
    /** Guard a POST request; stops execution with a 400 if the token is invalid. */
    function aep_validate_csrf(): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (!aep_csrf_verify($token)) {
            http_response_code(400);
            exit('Invalid or expired form submission. Please go back and try again.');
        }
    }
}

if (!function_exists('aep_validate_email')) {
    function aep_validate_email(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('aep_validate_date')) {
    function aep_validate_date(string $date, string $format = 'Y-m-d'): bool
    {
        $parsed = DateTime::createFromFormat($format, $date);
        return $parsed !== false && $parsed->format($format) === $date;
    }
}

if (!function_exists('aep_validate_status')) {
    function aep_validate_status(string $status, array $allowed): bool
    {
        return in_array($status, $allowed, true);
    }
}

if (!function_exists('aep_valid_status')) {
    /** Return the submitted status if allowed, otherwise a default value. */
    function aep_valid_status(string $status, array $allowed, string $default): string
    {
        return aep_validate_status($status, $allowed) ? $status : $default;
    }
}

if (!function_exists('aep_post_string')) {
    /** Fetch a trimmed, length-limited string from $_POST. */
    function aep_post_string(string $key, int $maxLength = 255): string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        if ($maxLength > 0 && strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        return $value;
    }
}

if (!function_exists('aep_reference')) {
    /** Generate a short, unique, human-friendly reference such as CLI-7F3A9C1B. */
    function aep_reference(string $prefix): string
    {
        return strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}

if (!function_exists('aep_log_activity')) {
    /**
     * Record an activity log entry. $entityId/$note are optional; when the
     * acting user id is not supplied it is taken from the current session.
     */
    function aep_log_activity(PDO $pdo, string $entity, string $action, ?int $entityId = null, ?string $note = null, ?int $userId = null): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO activity_log (user_id, action, entity, entity_id, note) VALUES (:user_id, :action, :entity, :entity_id, :note)'
            );
            return $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':entity' => $entity,
                ':entity_id' => $entityId,
                ':note' => $note,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('aep_parse_json_safe')) {
    /** Decode JSON without throwing; returns $default on any failure. */
    function aep_parse_json_safe(string $json, array $default = []): array
    {
        if (trim($json) === '') {
            return $default;
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : $default;
    }
}
