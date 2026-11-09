<?php
require_once __DIR__ . '/../includes/content.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$lang = $_GET['lang'] ?? 'ru';
if (!in_array($lang, ['ru', 'en', 'uz'], true)) {
    $lang = 'ru';
}

echo json_encode(cms_content_for_lang($lang), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
