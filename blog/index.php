<?php $base = '../'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Блог — INTER PACK</title>
<meta name="description" content="Новости и статьи компании INTER PACK: производство гофротары и упаковки в Узбекистане.">
<link rel="canonical" href="https://inter-pack.uz/blog/">

<link rel="icon" href="../assets/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css?v=28">
<link rel="stylesheet" href="../css/blog.css?v=1">
</head>
<body>

<div data-blog-base="../" style="display:none;"></div>

<?php include __DIR__ . '/../includes/site_header.php'; ?>

<main>
  <section class="section">
    <div class="container">
      <div class="page-hero reveal">
        <span class="eyebrow" data-i18n="blog.eyebrow">Блог</span>
        <h1 data-i18n="blog.title">Новости и статьи</h1>
        <p data-i18n="blog.subtitle">Новости компании, материалы о производстве и упаковке</p>
      </div>

      <div class="blog-grid" id="blogList"></div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../includes/site_footer.php'; ?>

<script src="../js/i18n.js?v=14"></script>
<script src="../js/cms-override.js?v=1"></script>
<script src="../js/main.js?v=9"></script>
<script src="../js/blog.js?v=1"></script>
</body>
</html>
