<?php
$base = '../';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

$slug = (string) ($_GET['slug'] ?? '');
$post = null;

if ($slug !== '') {
    $pdo = cms_db();
    $stmt = $pdo->prepare("
        SELECT p.id, p.slug, p.cover_image, p.published_at, p.views, i.title, i.excerpt, i.body
        FROM blog_posts p
        JOIN blog_post_i18n i ON i.post_id = p.id AND i.lang = 'ru'
        WHERE p.slug = ? AND p.published = 1
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if ($post) {
        $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
        $post['views']++;
    }
}

if (!$post) {
    http_response_code(404);
}

$pageTitle = $post ? $post['title'] . ' — INTER PACK' : 'Статья не найдена — INTER PACK';
$pageDescription = $post ? $post['excerpt'] : 'Запрошенная статья не найдена.';
$canonicalUrl = $post ? ('https://inter-pack.uz/blog/post.php?slug=' . rawurlencode($post['slug'])) : '';
$coverUrl = ($post && $post['cover_image']) ? ('https://inter-pack.uz/' . $post['cover_image']) : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<?php if ($post): ?>
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($post['excerpt']) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
<?php if ($coverUrl): ?><meta property="og:image" content="<?= htmlspecialchars($coverUrl) ?>"><?php endif; ?>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $post['excerpt'],
    'datePublished' => $post['published_at'],
    'image' => $coverUrl ?: null,
    'publisher' => ['@type' => 'Organization', 'name' => 'INTER PACK'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<?php else: ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="icon" href="../assets/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css?v=28">
<link rel="stylesheet" href="../css/blog.css?v=2">
</head>
<body>

<div data-blog-base="../" style="display:none;"></div>

<?php include __DIR__ . '/../includes/site_header.php'; ?>

<main>
  <section class="section">
    <div class="container">
      <article class="blog-article" id="blogPost" data-ssr-lang="<?= $post ? 'ru' : '' ?>">
        <?php if ($post): ?>
          <a class="blog-back" href="./"><span aria-hidden="true">&larr;</span> <span data-i18n="blog.back">Ко всем статьям</span></a>
          <div class="blog-meta">
            <time><?= htmlspecialchars(blog_fmt_date_ru($post['published_at'])) ?></time>
            <span class="blog-views"><?= htmlspecialchars(blog_views_label_ru((int) $post['views'])) ?></span>
          </div>
          <h1><?= htmlspecialchars($post['title']) ?></h1>
          <?php if ($post['cover_image']): ?>
            <div class="blog-cover"><img src="../<?= htmlspecialchars($post['cover_image']) ?>" alt=""></div>
          <?php endif; ?>
          <div class="blog-body"><?= htmlspecialchars($post['body']) ?></div>
        <?php else: ?>
          <a class="blog-back" href="./"><span aria-hidden="true">&larr;</span> <span data-i18n="blog.back">Ко всем статьям</span></a>
          <p class="blog-empty">Статья не найдена.</p>
        <?php endif; ?>
      </article>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>

<script src="../js/i18n.js?v=14"></script>
<script src="../js/cms-override.js?v=1"></script>
<script src="../js/main.js?v=9"></script>
<script src="../js/blog.js?v=3"></script>
</body>
</html>
