<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
cms_start_session();

if (!cms_admin_exists()) {
    header('Location: setup.php');
    exit;
}

if (cms_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (cms_login_rate_limited($ip)) {
        $error = 'Слишком много попыток входа. Подождите несколько минут.';
    } else {
        cms_record_login_attempt($ip);
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if (cms_attempt_login($username, $password)) {
            header('Location: index.php');
            exit;
        }
        $error = 'Неверный логин или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Вход — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <h1>Вход в CRM</h1>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <?= cms_csrf_field() ?>
      <div class="field">
        <label>Логин</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="field">
        <label>Пароль</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;">Войти</button>
    </form>
  </div>
</div>
</body>
</html>
