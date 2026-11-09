<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/upload.php';
cms_start_session();
cms_require_login();

$active = 'blog';
$pdo = cms_db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$isNew = $id === 0;

$post = ['slug' => '', 'cover_image' => '', 'published' => 0];
$i18n = [
    'ru' => ['title' => '', 'excerpt' => '', 'body' => ''],
    'en' => ['title' => '', 'excerpt' => '', 'body' => ''],
    'uz' => ['title' => '', 'excerpt' => '', 'body' => ''],
];

if (!$isNew) {
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        die('Статья не найдена.');
    }
    $post = $row;

    $stmt = $pdo->prepare('SELECT lang, title, excerpt, body FROM blog_post_i18n WHERE post_id = ?');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $r) {
        if (isset($i18n[$r['lang']])) {
            $i18n[$r['lang']] = ['title' => $r['title'], 'excerpt' => $r['excerpt'], 'body' => $r['body']];
        }
    }
}

$error = null;
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();

    $slug = trim(strtolower((string) ($_POST['slug'] ?? '')));
    $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
    $slug = trim($slug, '-');
    $published = isset($_POST['published']) ? 1 : 0;
    $postedI18n = $_POST['i18n'] ?? [];

    if ($slug === '') {
        $error = 'Укажите URL-адрес статьи (slug), например: novaya-liniya-2026.';
    } elseif (empty($postedI18n['ru']['title'])) {
        $error = 'Заголовок на русском обязателен.';
    } else {
        // Uniqueness check.
        $stmt = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $error = 'Статья с таким URL (' . htmlspecialchars($slug) . ') уже существует.';
        }
    }

    if (!$error) {
        $coverImage = $post['cover_image'] ?? '';
        if (!empty($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
            $targetPath = 'assets/images/blog/' . $slug . '.webp';
            [$ok, $err] = cms_handle_image_upload($_FILES['cover'], $targetPath);
            if (!$ok) {
                $error = $err;
            } else {
                $coverImage = $targetPath;
            }
        }
    }

    if (!$error) {
        $pdo->beginTransaction();
        if ($isNew) {
            $publishedAt = $published ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare('INSERT INTO blog_posts (slug, cover_image, published, published_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$slug, $coverImage, $published, $publishedAt]);
            $id = (int) $pdo->lastInsertId();
        } else {
            $publishedAt = $post['published_at'] ?? null;
            if ($published && !$publishedAt) {
                $publishedAt = date('Y-m-d H:i:s');
            }
            $stmt = $pdo->prepare('UPDATE blog_posts SET slug = ?, cover_image = ?, published = ?, published_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$slug, $coverImage, $published, $publishedAt, $id]);
        }

        foreach (['ru', 'en', 'uz'] as $lang) {
            $title = trim((string) ($postedI18n[$lang]['title'] ?? ''));
            $excerpt = trim((string) ($postedI18n[$lang]['excerpt'] ?? ''));
            $body = trim((string) ($postedI18n[$lang]['body'] ?? ''));
            $stmt = $pdo->prepare('
                INSERT INTO blog_post_i18n (post_id, lang, title, excerpt, body)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT(post_id, lang) DO UPDATE SET title = excluded.title, excerpt = excluded.excerpt, body = excluded.body
            ');
            $stmt->execute([$id, $lang, $title, $excerpt, $body]);
        }
        $pdo->commit();

        header('Location: blog_edit.php?id=' . $id . '&saved=1');
        exit;
    }

    // Re-render with posted values on error.
    $post['slug'] = $slug;
    $post['published'] = $published;
    foreach (['ru', 'en', 'uz'] as $lang) {
        $i18n[$lang] = [
            'title' => $postedI18n[$lang]['title'] ?? '',
            'excerpt' => $postedI18n[$lang]['excerpt'] ?? '',
            'body' => $postedI18n[$lang]['body'] ?? '',
        ];
    }
}

if (isset($_GET['saved'])) {
    $saved = true;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= $isNew ? 'Новая статья' : 'Редактирование статьи' ?> — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1><?= $isNew ? 'Новая статья' : 'Редактирование статьи' ?></h1>
    <p class="sub"><a href="blog.php">&larr; ко всем статьям</a></p>

    <?php if ($saved): ?><div class="msg ok">Сохранено.</div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <?= cms_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $id ?>">

      <div class="card">
        <h2>Основное</h2>
        <div class="field">
          <label>URL статьи (slug) — латиница, цифры, дефисы</label>
          <input type="text" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" placeholder="novaya-liniya-2026" required>
        </div>
        <div class="field">
          <label>Обложка статьи</label>
          <?php if (!empty($post['cover_image'])): ?>
            <img src="../<?= htmlspecialchars($post['cover_image']) ?>?t=<?= time() ?>" style="width:160px;height:110px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;border:1px solid var(--c-border);">
          <?php endif; ?>
          <input type="file" name="cover" accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="field">
          <label style="display:flex;align-items:center;gap:8px;font-weight:400;">
            <input type="checkbox" name="published" value="1" style="width:auto;" <?= $post['published'] ? 'checked' : '' ?>>
            Опубликовано (видно на сайте)
          </label>
        </div>
      </div>

      <?php foreach (['ru' => 'Русский', 'en' => 'English', 'uz' => "O'zbekcha"] as $lang => $label): ?>
        <div class="card">
          <h2><?= htmlspecialchars($label) ?></h2>
          <div class="field">
            <label>Заголовок</label>
            <input type="text" name="i18n[<?= $lang ?>][title]" value="<?= htmlspecialchars($i18n[$lang]['title']) ?>" <?= $lang === 'ru' ? 'required' : '' ?>>
          </div>
          <div class="field">
            <label>Краткое описание (для списка статей)</label>
            <textarea name="i18n[<?= $lang ?>][excerpt]"><?= htmlspecialchars($i18n[$lang]['excerpt']) ?></textarea>
          </div>
          <div class="field">
            <label>Текст статьи</label>
            <textarea name="i18n[<?= $lang ?>][body]" style="min-height:220px;"><?= htmlspecialchars($i18n[$lang]['body']) ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn">Сохранить статью</button>
    </form>
  </main>
</div>
</body>
</html>
