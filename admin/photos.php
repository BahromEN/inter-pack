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
    } else {
        $mode = $slots[$slotKey]['mode'] ?? 'proxy';
        $targetPath = $mode === 'proxy' ? cms_image_override_path($slotKey) : $slots[$slotKey]['path'];

        if (isset($_POST['reset']) && $mode === 'proxy') {
            $full = realpath(__DIR__ . '/..') . '/' . $targetPath;
            if (file_exists($full)) {
                @unlink($full);
            }
            $message = 'Фото «' . $slots[$slotKey]['label'] . '» возвращено к стандартному.';
        } elseif (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'Выберите файл для загрузки.';
        } else {
            [$ok, $err] = cms_handle_image_upload($_FILES['photo'], $targetPath);
            if ($ok) {
                $message = 'Фото «' . $slots[$slotKey]['label'] . '» обновлено.';
            } else {
                $error = $err;
            }
        }
    }
}

$overrides = cms_image_overrides();
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
    <p class="sub">Загрузите новое изображение — оно сразу заменит текущее на сайте. JPG, PNG или WEBP, до 8 МБ. Большинство фото теперь хранятся отдельно от кода сайта и не пропадут при следующем обновлении/деплое.</p>

    <?php if ($message): ?><div class="msg ok"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
      <?php foreach ($slots as $key => $slot):
        $mode = $slot['mode'] ?? 'proxy';
        $hasOverride = $mode === 'proxy' && isset($overrides[$key]);
        $previewSrc = $hasOverride ? ('../' . $overrides[$key]) : ('../' . $slot['path'] . '?t=' . time());
      ?>
        <div class="image-slot">
          <img src="<?= htmlspecialchars($previewSrc) ?>" alt="">
          <div class="slot-info">
            <b><?= htmlspecialchars($slot['label']) ?></b>
            <code><?= htmlspecialchars($slot['path']) ?></code>
            <?php if ($hasOverride): ?><br><span class="pill on" style="margin-top:4px;">Загружено своё фото</span><?php endif; ?>
          </div>
          <form method="post" enctype="multipart/form-data" style="flex-wrap:wrap;">
            <?= cms_csrf_field() ?>
            <input type="hidden" name="slot" value="<?= htmlspecialchars($key) ?>">
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
            <button type="submit" class="btn secondary">Заменить</button>
          </form>
          <?php if ($hasOverride): ?>
            <form method="post" onsubmit="return confirm('Вернуть стандартное фото для этого места?');">
              <?= cms_csrf_field() ?>
              <input type="hidden" name="slot" value="<?= htmlspecialchars($key) ?>">
              <input type="hidden" name="reset" value="1">
              <button type="submit" class="btn secondary" style="color:#A33A2A;">Восстановить оригинал</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>
</body>
</html>
