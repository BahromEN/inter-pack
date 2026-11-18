<?php
/**
 * Sitemap generated on the fly from the blog database, so new/edited
 * articles show up automatically — no manual sitemap.xml edits needed.
 */
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

function sitemap_date(string $timestamp): string {
    $ts = strtotime($timestamp);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

$pdo = cms_db();
$posts = $pdo->query("
    SELECT slug, published_at, updated_at
    FROM blog_posts
    WHERE published = 1
    ORDER BY published_at DESC
")->fetchAll();

$homeLastmod = date('Y-m-d', filemtime(__DIR__ . '/index.html'));
$blogIndexLastmod = $posts ? sitemap_date($posts[0]['updated_at'] ?: $posts[0]['published_at']) : $homeLastmod;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://inter-pack.uz/</loc>
    <lastmod><?= $homeLastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://inter-pack.uz/blog/</loc>
    <lastmod><?= $blogIndexLastmod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
<?php foreach ($posts as $post): ?>
  <url>
    <loc>https://inter-pack.uz/blog/post.php?slug=<?= htmlspecialchars(rawurlencode($post['slug']), ENT_QUOTES, 'UTF-8') ?></loc>
    <lastmod><?= sitemap_date($post['updated_at'] ?: $post['published_at']) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>
</urlset>
