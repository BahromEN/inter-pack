<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
cms_start_session();
cms_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: blog.php');
    exit;
}
cms_require_csrf();

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo = cms_db();
    $stmt = $pdo->prepare('DELETE FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: blog.php');
exit;
