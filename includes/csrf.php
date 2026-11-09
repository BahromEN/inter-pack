<?php
/**
 * CSRF token helpers. Call cms_start_session() before using these.
 */

function cms_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function cms_csrf_field(): string {
    $token = htmlspecialchars(cms_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function cms_csrf_verify(): bool {
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    return $sent !== '' && $known !== '' && hash_equals($known, $sent);
}

function cms_require_csrf(): void {
    if (!cms_csrf_verify()) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
}
