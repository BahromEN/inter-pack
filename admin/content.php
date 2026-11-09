<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';
cms_start_session();
cms_require_login();

$active = 'content';
$lang = $_GET['lang'] ?? 'ru';
if (!in_array($lang, ['ru', 'en', 'uz'], true)) {
    $lang = 'ru';
}

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $postLang = $_POST['lang'] ?? 'ru';
    if (in_array($postLang, ['ru', 'en', 'uz'], true)) {
        $lang = $postLang;
    }
    $fields = $_POST['fields'] ?? [];
    foreach ($fields as $path => $value) {
        cms_save_override($lang, $path, (string) $value);
    }
    $saved = true;
}

$content = cms_content_for_lang($lang);

$sectionLabels = [
    'nav' => 'Меню навигации',
    'common' => 'Общие надписи',
    'home' => 'Главный экран (hero)',
    'about' => 'О компании',
    'achievements' => 'Достижения',
    'products' => 'Продукция',
    'pd' => 'Карточка товара (модальное окно)',
    'productsToggle' => 'Кнопка "Показать всё"',
    'video' => 'Видео о компании',
    'contact' => 'Форма «Связаться»',
    'contacts' => 'Контакты',
    'footer' => 'Подвал сайта',
];

/** Recursively render form fields for a nested content node. */
function cms_render_node($node, string $path, int $depth = 0): void {
    if (is_array($node)) {
        $isList = array_is_list($node);
        foreach ($node as $key => $value) {
            $childPath = $path === '' ? (string) $key : $path . '.' . $key;
            $label = is_string($key) ? $key : ('#' . ((int) $key + 1));
            if (is_array($value)) {
                echo '<details class="section-group"' . ($depth < 1 ? '' : '') . '>';
                echo '<summary>' . htmlspecialchars($label) . '</summary>';
                echo '<div style="padding-left:' . (16) . 'px;">';
                cms_render_node($value, $childPath, $depth + 1);
                echo '</div></details>';
            } else {
                $safePath = htmlspecialchars($childPath, ENT_QUOTES, 'UTF-8');
                $safeVal = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                $long = mb_strlen((string) $value) > 70;
                echo '<div class="field">';
                echo '<label>' . htmlspecialchars($label) . ' <code style="font-weight:400;color:#9aa39c;">(' . $safePath . ')</code></label>';
                if ($long) {
                    echo '<textarea name="fields[' . $safePath . ']">' . $safeVal . '</textarea>';
                } else {
                    echo '<input type="text" name="fields[' . $safePath . ']" value="' . $safeVal . '">';
                }
                echo '</div>';
            }
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
<title>Тексты сайта — INTER PACK CRM</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/_nav.php'; ?>
  <main class="admin-main">
    <h1>Тексты сайта</h1>
    <p class="sub">Любое изменённое поле сразу заменит текст на сайте. Если вернуть исходный текст — поле перестанет считаться изменённым.</p>

    <div class="lang-tabs">
      <a href="?lang=ru" class="<?= $lang === 'ru' ? 'active' : '' ?>">Русский</a>
      <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">English</a>
      <a href="?lang=uz" class="<?= $lang === 'uz' ? 'active' : '' ?>">O'zbekcha</a>
    </div>

    <?php if ($saved): ?><div class="msg ok">Сохранено.</div><?php endif; ?>

    <form method="post">
      <?= cms_csrf_field() ?>
      <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">

      <?php foreach ($content as $sectionKey => $sectionValue): ?>
        <div class="card">
          <h2><?= htmlspecialchars($sectionLabels[$sectionKey] ?? $sectionKey) ?></h2>
          <?php cms_render_node($sectionValue, (string) $sectionKey); ?>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn">Сохранить изменения</button>
    </form>
  </main>
</div>
</body>
</html>
