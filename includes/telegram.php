<?php
/**
 * Отправка заявки с сайта в Telegram-чат через Bot API.
 * Конфиг (токен бота + chat_id) хранится в telegram_config.php,
 * который не отслеживается git — см. telegram_config.example.php.
 */

function cms_telegram_config(): ?array {
    $path = __DIR__ . '/telegram_config.php';
    if (!is_file($path)) {
        return null;
    }
    $config = require $path;
    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        return null;
    }
    return $config;
}

function cms_send_telegram_lead(array $fields): bool {
    $config = cms_telegram_config();
    if ($config === null) {
        return false;
    }

    $lines = ['<b>Новая заявка с сайта INTER PACK</b>', ''];
    $labels = [
        'name'    => 'Имя',
        'company' => 'Компания',
        'phone'   => 'Телефон',
        'email'   => 'Email',
        'message' => 'Сообщение',
    ];
    foreach ($labels as $key => $label) {
        $value = trim((string)($fields[$key] ?? ''));
        if ($value === '') {
            continue;
        }
        $lines[] = '<b>' . $label . ':</b> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    $text = implode("\n", $lines);

    $url = 'https://api.telegram.org/bot' . $config['bot_token'] . '/sendMessage';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'chat_id'    => $config['chat_id'],
            'text'       => $text,
            'parse_mode' => 'HTML',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $response = curl_exec($ch);
    $ok = $response !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);

    return $ok;
}
