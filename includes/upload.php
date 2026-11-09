<?php
/**
 * Safe image upload: validates real image content (via GD), re-encodes it
 * (strips any non-image payload hidden in the file), and overwrites the
 * target asset path in place. Keeps one timestamped backup of the old file.
 */

function cms_handle_image_upload(array $file, string $targetRelativePath): array {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Загрузка не удалась (код ошибки: ' . ($file['error'] ?? '?') . ').'];
    }
    if ($file['size'] <= 0 || $file['size'] > 8 * 1024 * 1024) {
        return [false, 'Файл пустой или больше 8 МБ.'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [false, 'Файл не похож на изображение.'];
    }
    $mime = $info['mime'];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        return [false, 'Допустимые форматы: JPG, PNG, WEBP.'];
    }

    switch ($mime) {
        case 'image/jpeg':
            $img = @imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $img = @imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/webp':
            $img = @imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            $img = false;
    }
    if (!$img) {
        return [false, 'Не удалось прочитать изображение.'];
    }

    $siteRoot = realpath(__DIR__ . '/..');
    $targetPath = $siteRoot . '/' . ltrim($targetRelativePath, '/');
    $targetDir = dirname($targetPath);

    // git doesn't track empty directories, so a freshly deployed checkout
    // may be missing e.g. assets/images/blog/ — create it on first use.
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    $realTargetDir = realpath($targetDir);
    if ($realTargetDir === false || strpos($realTargetDir, $siteRoot) !== 0) {
        imagedestroy($img);
        return [false, 'Недопустимый путь назначения.'];
    }

    // Backup current file before overwriting.
    if (file_exists($targetPath)) {
        $backupDir = $siteRoot . '/assets/images/_backups';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }
        $backupName = date('Ymd-His') . '-' . basename($targetPath);
        @copy($targetPath, $backupDir . '/' . $backupName);
    }

    $ext = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $ok = false;
    if ($ext === 'webp') {
        $ok = imagewebp($img, $targetPath, 86);
    } elseif ($ext === 'png') {
        $ok = imagepng($img, $targetPath, 6);
    } else {
        $ok = imagejpeg($img, $targetPath, 88);
    }
    imagedestroy($img);

    if (!$ok) {
        return [false, 'Не удалось сохранить файл на сервере.'];
    }
    return [true, null];
}
