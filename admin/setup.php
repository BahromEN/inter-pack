<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
cms_start_session();

// This page only works until the first admin account is created.
if (cms_admin_exists()) {
    header('Location: login.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    if ($username === '' || mb_strlen($username) < 3) {
        $error = 'Логин должен быть не короче 3 символов.';
    } elseif (mb_strlen($password) < 8) {
        $error = 'Пароль должен быть не короче 8 символов.';
    } elseif ($password !== $password2) {
        $error = 'Пароли не совпадают.';
    } else {
        $pdo = cms_db();
        $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT)]);
        cms_attempt_login($username, $password);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Первичная настройка — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <h1>Создайте учётную запись администратора</h1>
    <p class="sub" style="margin-top:-12px;">Эта страница доступна только один раз — до создания первого аккаунта. Запомните логин и пароль, после создания аккаунта страница перестанет работать.</p>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <?= cms_csrf_field() ?>
      <div class="field">
        <label>Логин</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="field">
        <label>Пароль (минимум 8 символов)</label>
        <input type="password" name="password" required minlength="8">
      </div>
      <div class="field">
        <label>Повторите пароль</label>
        <input type="password" name="password2" required minlength="8">
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;">Создать аккаунт</button>
    </form>
  </div>
</div>
</body>
</html>
