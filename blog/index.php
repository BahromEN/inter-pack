<?php
$base = '../';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

$pdo = cms_db();
$posts = $pdo->query("
    SELECT p.slug, p.cover_image, p.published_at, p.views, i.title, i.excerpt
    FROM blog_posts p
    JOIN blog_post_i18n i ON i.post_id = p.id AND i.lang = 'ru'
    WHERE p.published = 1
    ORDER BY p.published_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Блог — INTER PACK</title>
<meta name="description" content="Новости и статьи компании INTER PACK: производство гофротары и упаковки в Узбекистане.">
<link rel="canonical" href="https://inter-pack.uz/blog/">

<link rel="icon" href="../assets/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png" media="(prefers-color-scheme: light)">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png" media="(prefers-color-scheme: light)">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16-dark.png" media="(prefers-color-scheme: dark)">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32-dark.png" media="(prefers-color-scheme: dark)">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css?v=28">
<link rel="stylesheet" href="../css/blog.css?v=3">
</head>
<body>

<div data-blog-base="../" style="display:none;"></div>

<?php include __DIR__ . '/../includes/site_header.php'; ?>

<main>
  <section class="section">
    <div class="container">
      <div class="page-hero reveal">
        <span class="eyebrow" data-i18n="blog.eyebrow">Блог</span>
        <h1 data-i18n="blog.title">Новости и статьи</h1>
        <p data-i18n="blog.subtitle">Новости компании, материалы о производстве и упаковке</p>
      </div>

      <div class="blog-grid" id="blogList" data-ssr-lang="ru">
        <?php if (!$posts): ?>
          <p class="blog-empty" data-i18n="blog.empty">Пока нет опубликованных статей — загляните позже.</p>
        <?php else: foreach ($posts as $p): ?>
          <a class="blog-card" href="post.php?slug=<?= urlencode($p['slug']) ?>">
            <?php if ($p['cover_image']): ?>
              <div class="blog-card-img"><img src="../<?= htmlspecialchars($p['cover_image']) ?>" alt="" loading="lazy"></div>
            <?php endif; ?>
            <div class="blog-meta">
              <time><?= htmlspecialchars(blog_fmt_date_ru($p['published_at'])) ?></time>
              <span class="blog-views"><?= htmlspecialchars(blog_views_label_ru((int) $p['views'])) ?></span>
            </div>
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p><?= htmlspecialchars($p['excerpt']) ?></p>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>

<script src="../js/i18n.js?v=14"></script>
<script src="../js/cms-override.js?v=1"></script>
<script src="../js/main.js?v=9"></script>
<script src="../js/blog.js?v=4"></script>
</body>
</html>
