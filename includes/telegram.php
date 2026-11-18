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

/**
 * Posts an announcement for one blog article into the public Telegram
 * channel (needs the bot added there as an administrator, since posting
 * to a channel — unlike a group — requires admin rights).
 *
 * $post needs: slug, cover_image, title, excerpt.
 */
function cms_send_telegram_channel_post(array $post): bool {
    $config = cms_telegram_config();
    if ($config === null || empty($config['channel_id'])) {
        return false;
    }

    $url = 'https://inter-pack.uz/blog/post.php?slug=' . rawurlencode($post['slug']);
    $title = htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8');
    $excerpt = htmlspecialchars((string) ($post['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8');

    $text = '<b>' . $title . '</b>';
    if ($excerpt !== '') {
        $text .= "\n\n" . $excerpt;
    }
    $text .= "\n\n<a href=\"" . $url . '">Читать статью на сайте →</a>';

    $coverImage = (string) ($post['cover_image'] ?? '');
    $coverUrl = $coverImage !== '' ? ('https://inter-pack.uz/' . $coverImage) : '';

    if ($coverUrl !== '') {
        $endpoint = 'https://api.telegram.org/bot' . $config['bot_token'] . '/sendPhoto';
        $fields = [
            'chat_id'    => $config['channel_id'],
            'photo'      => $coverUrl,
            'caption'    => $text,
            'parse_mode' => 'HTML',
        ];
    } else {
        $endpoint = 'https://api.telegram.org/bot' . $config['bot_token'] . '/sendMessage';
        $fields = [
            'chat_id'    => $config['channel_id'],
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];
    }

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $fields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $ok = $response !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);

    return $ok;
}

/**
 * Posts every published article that hasn't been sent to the channel yet
 * (telegram_announced = 0) — used once to backfill articles that existed
 * before this feature, and as a safety net to retry ones that failed to
 * send before (e.g. bot wasn't an admin yet).
 */
function cms_backfill_telegram_announcements(PDO $pdo): int {
    $stmt = $pdo->query("
        SELECT p.id, p.slug, p.cover_image, i.title, i.excerpt
        FROM blog_posts p
        JOIN blog_post_i18n i ON i.post_id = p.id AND i.lang = 'ru'
        WHERE p.published = 1 AND p.telegram_announced = 0
        ORDER BY p.published_at ASC
    ");
    $sent = 0;
    $markDone = $pdo->prepare('UPDATE blog_posts SET telegram_announced = 1 WHERE id = ?');
    foreach ($stmt->fetchAll() as $row) {
        if (cms_send_telegram_channel_post($row)) {
            $markDone->execute([$row['id']]);
            $sent++;
        }
    }
    return $sent;
}
