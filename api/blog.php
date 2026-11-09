<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$lang = $_GET['lang'] ?? 'ru';
if (!in_array($lang, ['ru', 'en', 'uz'], true)) {
    $lang = 'ru';
}

$pdo = cms_db();

if (isset($_GET['slug'])) {
    $slug = (string) $_GET['slug'];
    $stmt = $pdo->prepare('
        SELECT p.id, p.slug, p.cover_image, p.published, p.published_at,
               i.title, i.excerpt, i.body
        FROM blog_posts p
        JOIN blog_post_i18n i ON i.post_id = p.id AND i.lang = ?
        WHERE p.slug = ? AND p.published = 1
        LIMIT 1
    ');
    $stmt->execute([$lang, $slug]);
    $post = $stmt->fetch();
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'not_found']);
        exit;
    }
    echo json_encode($post, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$stmt = $pdo->prepare('
    SELECT p.id, p.slug, p.cover_image, p.published_at,
           i.title, i.excerpt
    FROM blog_posts p
    JOIN blog_post_i18n i ON i.post_id = p.id AND i.lang = ?
    WHERE p.published = 1
    ORDER BY p.published_at DESC
');
$stmt->execute([$lang]);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
