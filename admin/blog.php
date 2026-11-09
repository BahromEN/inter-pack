<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
cms_start_session();
cms_require_login();

$active = 'blog';
$pdo = cms_db();

// Quick publish/unpublish toggle from the list.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    cms_require_csrf();
    $id = (int) $_POST['toggle_id'];
    $stmt = $pdo->prepare('SELECT published FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
    $cur = $stmt->fetchColumn();
    if ($cur !== false) {
        $new = $cur ? 0 : 1;
        $publishedAt = $new ? "datetime('now')" : 'NULL';
        $pdo->prepare("UPDATE blog_posts SET published = ?, published_at = COALESCE(published_at, $publishedAt), updated_at = CURRENT_TIMESTAMP WHERE id = ?")
            ->execute([$new, $id]);
    }
    header('Location: blog.php');
    exit;
}

$posts = $pdo->query("
    SELECT p.id, p.slug, p.published, p.published_at, p.updated_at,
           (SELECT title FROM blog_post_i18n WHERE post_id = p.id AND lang = 'ru' LIMIT 1) AS title_ru
    FROM blog_posts p
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Блог — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Блог / новости</h1>
    <p class="sub"><a href="blog_edit.php" class="btn">+ Новая статья</a></p>

    <div class="card">
      <?php if (!$posts): ?>
        <p>Пока нет ни одной статьи.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Заголовок (RU)</th><th>Статус</th><th>Обновлено</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['title_ru'] ?: '(без заголовка)') ?><br><code style="color:#9aa39c;font-size:12px;"><?= htmlspecialchars($p['slug']) ?></code></td>
            <td><span class="pill <?= $p['published'] ? 'on' : 'off' ?>"><?= $p['published'] ? 'Опубликовано' : 'Черновик' ?></span></td>
            <td><?= htmlspecialchars($p['updated_at']) ?></td>
            <td class="row-actions">
              <a href="blog_edit.php?id=<?= (int) $p['id'] ?>">Редактировать</a>
              <form method="post" style="display:inline;">
                <?= cms_csrf_field() ?>
                <input type="hidden" name="toggle_id" value="<?= (int) $p['id'] ?>">
                <button type="submit" class="btn secondary" style="padding:4px 10px;font-size:12.5px;"><?= $p['published'] ? 'Снять с публикации' : 'Опубликовать' ?></button>
              </form>
              <form method="post" action="blog_delete.php" style="display:inline;" onsubmit="return confirm('Удалить статью безвозвратно?');">
                <?= cms_csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit" class="danger" style="background:none;border:none;padding:0;font-size:13px;cursor:pointer;text-decoration:underline;">Удалить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>
