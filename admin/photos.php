<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/upload.php';
cms_start_session();
cms_require_login();

$active = 'photos';
$slots = cms_image_slots();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $slotKey = $_POST['slot'] ?? '';
    if (!isset($slots[$slotKey])) {
        $error = 'Неизвестный слот изображения.';
    } elseif (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Выберите файл для загрузки.';
    } else {
        [$ok, $err] = cms_handle_image_upload($_FILES['photo'], $slots[$slotKey]['path']);
        if ($ok) {
            $message = 'Фото «' . $slots[$slotKey]['label'] . '» обновлено.';
        } else {
            $error = $err;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Фотографии — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Фотографии сайта</h1>
    <p class="sub">Загрузите новое изображение — оно сразу заменит текущее на сайте (имя файла и место на странице не меняются). JPG, PNG или WEBP, до 8 МБ.</p>

    <?php if ($message): ?><div class="msg ok"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
      <?php foreach ($slots as $key => $slot): ?>
        <div class="image-slot">
          <img src="../<?= htmlspecialchars($slot['path']) ?>?t=<?= time() ?>" alt="">
          <div class="slot-info">
            <b><?= htmlspecialchars($slot['label']) ?></b>
            <code><?= htmlspecialchars($slot['path']) ?></code>
          </div>
          <form method="post" enctype="multipart/form-data">
            <?= cms_csrf_field() ?>
            <input type="hidden" name="slot" value="<?= htmlspecialchars($key) ?>">
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
            <button type="submit" class="btn secondary">Заменить</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>
</body>
</html>
