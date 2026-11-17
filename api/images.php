<?php
require_once __DIR__ . '/../includes/content.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

echo json_encode(cms_image_overrides(), JSON_UNESCAPED_SLASHES);
