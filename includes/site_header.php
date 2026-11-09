<?php
/**
 * Shared site header, used by index.html's static markup (copy) and by
 * PHP-rendered pages (blog). Expects $base to be '' on root pages or '../'
 * on pages one directory deep (e.g. blog/index.php).
 */
$base = $base ?? '';
$home = $base === '' ? '#home' : $base . 'index.html#home';
$about = $base === '' ? '#about' : $base . 'index.html#about';
$achievements = $base === '' ? '#achievements' : $base . 'index.html#achievements';
$products = $base === '' ? '#products' : $base . 'index.html#products';
$video = $base === '' ? '#video' : $base . 'index.html#video';
$blogHref = $base === '' ? 'blog/' : $base . 'blog/';
$contact = $base === '' ? '#contact' : $base . 'index.html#contact';
$contacts = $base === '' ? '#contacts' : $base . 'index.html#contacts';
?>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= $home ?>" class="logo" aria-label="INTER PACK">
      <img src="<?= $base ?>assets/logo-icon.png" alt="INTER PACK" class="logo-mark">
      <span class="logo-word">INTER <span>PACK</span></span>
    </a>
    <nav class="main-nav" id="mainNav">
      <ul>
        <li><a href="<?= $home ?>"><span class="nav-num">01</span><span data-i18n="nav.home">Главная</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $about ?>"><span class="nav-num">02</span><span data-i18n="nav.about">О компании</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $achievements ?>"><span class="nav-num">03</span><span data-i18n="nav.achievements">Достижения</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $products ?>"><span class="nav-num">04</span><span data-i18n="nav.products">Продукция</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $video ?>"><span class="nav-num">05</span><span data-i18n="nav.video">Видео</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $blogHref ?>"><span class="nav-num">06</span><span data-i18n="nav.blog">Блог</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $contact ?>"><span class="nav-num">07</span><span data-i18n="nav.contact">Связаться</span><span class="nav-arrow">↗</span></a></li>
        <li><a href="<?= $contacts ?>"><span class="nav-num">08</span><span data-i18n="nav.contacts">Контакты</span><span class="nav-arrow">↗</span></a></li>
      </ul>
    </nav>
    <div class="header-actions">
      <button class="search-btn" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </button>
      <div class="lang-switch" id="langSwitch">
        <button data-lang="uz">UZ</button><span class="sep">/</span>
        <button data-lang="ru">RU</button><span class="sep">/</span>
        <button data-lang="en">EN</button>
      </div>
      <a href="<?= $contact ?>" class="btn header-contact-btn" data-i18n="nav.contact">Связаться</a>
      <button class="burger" id="burgerBtn" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
