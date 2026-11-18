<?php
require_once __DIR__ . '/../includes/telegram.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

// Honeypot: бот-заполнители обычно трогают скрытое поле.
if (trim((string)($_POST['_honey'] ?? '')) !== '') {
    echo json_encode(['ok' => true]);
    exit;
}

$ok = cms_send_telegram_lead([
    'name'    => $_POST['name'] ?? '',
    'company' => $_POST['company'] ?? '',
    'phone'   => $_POST['phone'] ?? '',
    'email'   => $_POST['email'] ?? '',
    'message' => $_POST['message'] ?? '',
]);

echo json_encode(['ok' => $ok]);
