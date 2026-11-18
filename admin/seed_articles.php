<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/telegram.php';
cms_start_session();
cms_require_login();

$active = 'blog';
$added = null;
$upgraded = null;
$telegramSent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $pdo = cms_db();
    if (isset($_POST['action']) && $_POST['action'] === 'upgrade') {
        $upgraded = cms_upgrade_starter_blog_content($pdo);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'telegram_backfill') {
        $telegramSent = cms_backfill_telegram_announcements($pdo);
    } else {
        $added = cms_seed_initial_blog_posts($pdo);
    }
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
    <h1>Стартовые статьи</h1>

    <?php if ($added !== null): ?>
      <div class="msg ok">
        <?php if ($added > 0): ?>
          Добавлено статей: <?= (int) $added ?>. <a href="blog.php">Перейти к списку статей →</a>
        <?php else: ?>
          Все 3 статьи уже есть в блоге — ничего не добавлено.
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if ($upgraded !== null): ?>
      <div class="msg ok">
        <?php if ($upgraded > 0): ?>
          Текст обновлён у <?= (int) $upgraded ?> из 3 статей. <a href="blog.php">Перейти к списку статей →</a>
        <?php else: ?>
          Ни одна из 3 стартовых статей не найдена (возможно, удалены) — обновлять нечего.
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if ($telegramSent !== null): ?>
      <div class="msg ok">
        <?php if ($telegramSent > 0): ?>
          Отправлено в Telegram-канал: <?= (int) $telegramSent ?> статей.
        <?php else: ?>
          Нечего отправлять — либо все опубликованные статьи уже были отправлены ранее, либо не настроен <code>includes/telegram_config.php</code> (поле <code>channel_id</code>), либо бот не администратор канала.
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:560px;">
      <h2>Добавить</h2>
      <p class="sub" style="margin-bottom:16px;">Добавляет 3 готовые статьи о производстве гофрокартона (на RU/EN/UZ), если их ещё нет в блоге. Безопасно нажимать несколько раз — уже существующие статьи (по URL-адресу) не дублируются.</p>
      <form method="post">
        <?= cms_csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <button type="submit" class="btn">Добавить 3 стартовые статьи</button>
      </form>
    </div>

    <div class="card" style="max-width:560px;">
      <h2>Обновить текст и оформление</h2>
      <p class="sub" style="margin-bottom:16px;">Если эти 3 статьи уже добавлены, но текст выглядит «сухо» (без форматирования) — эта кнопка заменит их текст на новую версию с выделением жирным/курсивом и мнением эксперта. <b>Внимание:</b> если вы сами редактировали текст одной из этих 3 статей, ваши правки будут заменены. На остальные (ваши собственные) статьи это не влияет.</p>
      <form method="post" onsubmit="return confirm('Заменить текст 3 стартовых статей на новую версию? Ваши собственные правки в них (если были) потеряются.');">
        <?= cms_csrf_field() ?>
        <input type="hidden" name="action" value="upgrade">
        <button type="submit" class="btn secondary">Обновить текст стартовых статей</button>
      </form>
    </div>

    <div class="card" style="max-width:560px;">
      <h2>Отправить в Telegram-канал</h2>
      <p class="sub" style="margin-bottom:16px;">Публикует в канал анонс с заголовком, фото и ссылкой на статью — для всех опубликованных статей, которые туда ещё не отправлялись (новые статьи после сохранения отправляются автоматически, эта кнопка — для уже существующих). Безопасно нажимать несколько раз: уже отправленные статьи не дублируются.</p>
      <form method="post">
        <?= cms_csrf_field() ?>
        <input type="hidden" name="action" value="telegram_backfill">
        <button type="submit" class="btn secondary">Отправить существующие статьи в канал</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
