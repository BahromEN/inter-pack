<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
cms_start_session();
cms_require_login();

$active = 'blog';
$added = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $pdo = cms_db();
    $added = cms_seed_initial_blog_posts($pdo);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Стартовые статьи — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Добавить стартовые статьи</h1>
    <p class="sub">Добавляет 3 готовые статьи о производстве гофрокартона (на RU/EN/UZ), если их ещё нет в блоге. Безопасно нажимать несколько раз — уже существующие статьи (по URL-адресу) не дублируются.</p>

    <?php if ($added !== null): ?>
      <div class="msg ok">
        <?php if ($added > 0): ?>
          Добавлено статей: <?= (int) $added ?>. <a href="blog.php">Перейти к списку статей →</a>
        <?php else: ?>
          Все 3 статьи уже есть в блоге — ничего не добавлено.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:480px;">
      <form method="post">
        <?= cms_csrf_field() ?>
        <button type="submit" class="btn">Добавить 3 стартовые статьи</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
