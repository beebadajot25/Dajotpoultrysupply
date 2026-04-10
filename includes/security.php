<?php
if (!isset($_SESSION)) session_start();

/**
 * XSS Protection Helper
 * Escapes HTML entities to prevent Cross-Site Scripting.
 */
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate a CSRF token and store it in the session.
 */
if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verify the provided CSRF token against the session token.
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
}

/**
 * Output a hidden input field with the CSRF token.
 */
if (!function_exists('csrf_input')) {
    function csrf_input() {
        $token = generate_csrf_token();
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
?>
