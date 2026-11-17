<?php
/** Shared formatting helpers for the public (Russian, server-rendered) blog pages. */

function blog_fmt_date_ru(?string $iso): string {
    if (!$iso) return '';
    $ts = strtotime($iso);
    if (!$ts) return '';
    $months = [1=>'января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts) . ' г.';
}

/**
 * Minimal, safe "mini-markdown" for article bodies: **bold**, *italic*,
 * blank-line-separated paragraphs, "> " for an expert-opinion style
 * blockquote, "## " for a subheading. Raw text is HTML-escaped before any
 * markup is applied, so the only real tags in the output are the ones
 * this function inserts itself.
 */
function blog_inline_format(string $text): string {
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
    $escaped = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $escaped);
    return nl2br($escaped);
}

function blog_render_body(string $raw): string {
    $raw = trim(str_replace("\r\n", "\n", $raw));
    if ($raw === '') {
        return '';
    }
    $blocks = preg_split('/\n{2,}/', $raw);
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $lines = explode("\n", $block);
        $isQuote = true;
        foreach ($lines as $line) {
            $t = ltrim($line);
            if (strpos($t, '> ') !== 0 && $t !== '>') {
                $isQuote = false;
                break;
            }
        }
        if ($isQuote) {
            $inner = implode("\n", array_map(function ($l) {
                return preg_replace('/^>\s?/', '', ltrim($l));
            }, $lines));
            $html .= '<blockquote class="blog-quote">' . blog_inline_format($inner) . '</blockquote>';
            continue;
        }
        if (preg_match('/^##\s+(.*)$/s', $block, $m)) {
            $html .= '<h3>' . blog_inline_format($m[1]) . '</h3>';
            continue;
        }
        $html .= '<p>' . blog_inline_format($block) . '</p>';
    }
    return $html;
}

function blog_views_label_ru(int $n): string {
    $mod10 = $n % 10;
    $mod100 = $n % 100;
    if ($mod10 === 1 && $mod100 !== 11) {
        $word = 'просмотр';
    } elseif (in_array($mod10, [2, 3, 4], true) && !in_array($mod100, [12, 13, 14], true)) {
        $word = 'просмотра';
    } else {
        $word = 'просмотров';
    }
    return $n . ' ' . $word;
}
