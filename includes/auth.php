<?php
/**
 * Session-based admin authentication.
 */

require_once __DIR__ . '/db.php';

function cms_start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
        ]);
        session_name('ip_admin_sess');
        session_start();
    }
}

function cms_is_logged_in(): bool {
    cms_start_session();
    return !empty($_SESSION['admin_user_id']);
}

function cms_require_login(): void {
    if (!cms_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function cms_admin_exists(): bool {
    $pdo = cms_db();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    return $count > 0;
}

/** Basic IP-based throttling: max 8 attempts per 5 minutes. */
function cms_login_rate_limited(string $ip): bool {
    $pdo = cms_db();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > datetime('now', '-5 minutes')");
    $stmt->execute([$ip]);
    return ((int) $stmt->fetchColumn()) >= 8;
}

function cms_record_login_attempt(string $ip): void {
    $pdo = cms_db();
    $stmt = $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)');
    $stmt->execute([$ip]);
}

function cms_attempt_login(string $username, string $password): bool {
    $pdo = cms_db();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }

    cms_start_session();
    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = $row['id'];
    $_SESSION['admin_username'] = $username;
    return true;
}

function cms_logout(): void {
    cms_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
