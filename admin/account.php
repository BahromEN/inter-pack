<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
cms_start_session();
cms_require_login();

$active = 'account';
$error = null;
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $new2 = (string) ($_POST['new_password2'] ?? '');

    $pdo = cms_db();
    $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_user_id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        $error = 'Текущий пароль указан неверно.';
    } elseif (mb_strlen($new) < 8) {
        $error = 'Новый пароль должен быть не короче 8 символов.';
    } elseif ($new !== $new2) {
        $error = 'Новые пароли не совпадают.';
    } else {
        $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_BCRYPT), $_SESSION['admin_user_id']]);
        $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Мой аккаунт — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Мой аккаунт</h1>
    <p class="sub">Вход выполнен как <b><?= htmlspecialchars($_SESSION['admin_username']) ?></b></p>

    <div class="card" style="max-width:420px;">
      <h2>Сменить пароль</h2>
      <?php if ($ok): ?><div class="msg ok">Пароль обновлён.</div><?php endif; ?>
      <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post">
        <?= cms_csrf_field() ?>
        <div class="field">
          <label>Текущий пароль</label>
          <input type="password" name="current_password" required>
        </div>
        <div class="field">
          <label>Новый пароль (минимум 8 символов)</label>
          <input type="password" name="new_password" required minlength="8">
        </div>
        <div class="field">
          <label>Повторите новый пароль</label>
          <input type="password" name="new_password2" required minlength="8">
        </div>
        <button type="submit" class="btn">Сохранить</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
