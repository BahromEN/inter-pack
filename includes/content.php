<?php
/**
 * Content schema (default texts, mirrors js/i18n.js) + override merging.
 */

require_once __DIR__ . '/db.php';

function cms_schema(): array {
    static $schema = null;
    if ($schema === null) {
        $json = file_get_contents(__DIR__ . '/../data/content-schema.json');
        $schema = json_decode($json, true) ?: [];
    }
    return $schema;
}

/** Flatten a nested array into ["a.b.0.c" => "value", ...] for string leaves only. */
function cms_flatten(array $arr, string $prefix = ''): array {
    $out = [];
    foreach ($arr as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
        if (is_array($value)) {
            $out += cms_flatten($value, $path);
        } else {
            $out[$path] = (string) $value;
        }
    }
    return $out;
}

/** Set a value at a dot-path inside a nested array (creating arrays as needed). */
function cms_set_path(array &$arr, string $path, $value): void {
    $parts = explode('.', $path);
    $ref = &$arr;
    foreach ($parts as $i => $part) {
        $key = ctype_digit($part) ? (int) $part : $part;
        if ($i === count($parts) - 1) {
            $ref[$key] = $value;
        } else {
            if (!isset($ref[$key]) || !is_array($ref[$key])) {
                $ref[$key] = [];
            }
            $ref = &$ref[$key];
        }
    }
}

/** All override rows for a language, as path => value. */
function cms_overrides_for_lang(string $lang): array {
    $pdo = cms_db();
    $stmt = $pdo->prepare('SELECT path, value FROM content_overrides WHERE lang = ?');
    $stmt->execute([$lang]);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[$row['path']] = $row['value'];
    }
    return $out;
}

/** Final merged content for a language: schema defaults + overrides applied on top. */
function cms_content_for_lang(string $lang): array {
    $schema = cms_schema();
    $base = $schema[$lang] ?? [];
    $overrides = cms_overrides_for_lang($lang);
    foreach ($overrides as $path => $value) {
        cms_set_path($base, $path, $value);
    }
    return $base;
}

function cms_save_override(string $lang, string $path, string $value): void {
    $pdo = cms_db();
    $schema = cms_schema();
    $defaultFlat = cms_flatten($schema[$lang] ?? []);
    $default = $defaultFlat[$path] ?? null;

    if ($default !== null && $value === $default) {
        // Matches the default again — drop the override to keep the table tidy.
        $stmt = $pdo->prepare('DELETE FROM content_overrides WHERE lang = ? AND path = ?');
        $stmt->execute([$lang, $path]);
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO content_overrides (lang, path, value, updated_at)
        VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(lang, path) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$lang, $path, $value]);
}

/**
 * Editable image slots: label => file path (relative to site root).
 * Uploading overwrites the file in place, so no HTML/CSS changes are ever needed.
 */
function cms_image_slots(): array {
    return [
        'home_hero'   => ['label' => 'Фон главной страницы (hero)', 'path' => 'assets/images/backgrounds/home-hero.webp'],
        'about_photo' => ['label' => 'Фото в разделе «О компании»', 'path' => 'assets/images/about/factory.webp'],
        'achievements_bg' => ['label' => 'Фон раздела «Достижения»', 'path' => 'assets/images/backgrounds/achievements-bg.webp'],
        'contacts_bg' => ['label' => 'Фон раздела «Контакты»', 'path' => 'assets/images/backgrounds/contacts-bg.webp'],
        'og_image'    => ['label' => 'Превью для соцсетей/мессенджеров (OG image)', 'path' => 'assets/og-image.jpg'],
        'logo'        => ['label' => 'Логотип (значок)', 'path' => 'assets/logo-icon.png'],
        'product_0' => ['label' => 'Фото товара №1 (Пятислойные гофроящики)', 'path' => 'assets/images/products/five-layer-box.webp'],
        'product_1' => ['label' => 'Фото товара №2 (Четырёхклапанные гофроящики)', 'path' => 'assets/images/hero/hero-01.webp'],
        'product_2' => ['label' => 'Фото товара №3 (Архивные короба)', 'path' => 'assets/images/hero/hero-03.webp'],
        'product_3' => ['label' => 'Фото товара №4 (Коробки с флексопечатью)', 'path' => 'assets/images/hero/hero-04.webp'],
        'product_4' => ['label' => 'Фото товара №5 (Упаковка для бытовой техники)', 'path' => 'assets/images/hero/hero-05.webp'],
        'product_5' => ['label' => 'Фото товара №6 (Гофролист в листах)', 'path' => 'assets/images/hero/hero-06.webp'],
        'product_6' => ['label' => 'Фото товара №7 (Ящики с нахлёстом клапанов)', 'path' => 'assets/images/products/overlap-flap-box.webp'],
        'product_7' => ['label' => 'Фото товара №8 (Телескопические ящики)', 'path' => 'assets/images/products/telescope-box.webp'],
        'product_8' => ['label' => 'Фото товара №9 (Лотки для фруктов и овощей)', 'path' => 'assets/images/products/fruit-veg-crate.webp'],
        'product_9' => ['label' => 'Фото товара №10 (Упаковка для напитков)', 'path' => 'assets/images/products/beverage-dividers.webp'],
    ];
}
