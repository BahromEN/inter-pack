<?php
/** Shared formatting helpers for the public (Russian, server-rendered) blog pages. */

function blog_fmt_date_ru(?string $iso): string {
    if (!$iso) return '';
    $ts = strtotime($iso);
    if (!$ts) return '';
    $months = [1=>'января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts) . ' г.';
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
