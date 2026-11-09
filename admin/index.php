<?php
require_once __DIR__ . '/../includes/auth.php';
cms_start_session();
if (!cms_admin_exists()) { header('Location: setup.php'); exit; }
cms_require_login();

require_once __DIR__ . '/../includes/db.php';
$pdo = cms_db();
$postCount = (int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
$publishedCount = (int) $pdo->query('SELECT COUNT(*) FROM blog_posts WHERE published = 1')->fetchColumn();
$overrideCount = (int) $pdo->query('SELECT COUNT(*) FROM content_overrides')->fetchColumn();

$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>CRM — INTER PACK</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['admin_username']) ?></h1>
    <p class="sub">Отсюда можно менять тексты сайта, фотографии и вести блог/новости.</p>

    <div class="card">
      <h2>Коротко</h2>
      <p><?= $overrideCount ?> изменённых текстовых полей · <?= $postCount ?> статей в блоге (<?= $publishedCount ?> опубликовано)</p>
    </div>

    <div class="card">
      <h2>Куда дальше</h2>
      <p><a href="content.php">Тексты сайта</a> — заголовки, описания, контакты, характеристики товаров на RU/EN/UZ.</p>
      <p><a href="photos.php">Фотографии</a> — замена фоновых изображений, фото товаров, логотипа.</p>
      <p><a href="blog.php">Блог / новости</a> — создание, редактирование и публикация статей.</p>
    </div>
  </main>
</div>
</body>
</html>
