<nav class="admin-nav">
  <div class="brand">INTER <span>PACK</span> · CRM</div>
  <a href="index.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Главная</a>
  <a href="content.php" class="<?= $active === 'content' ? 'active' : '' ?>">Тексты сайта</a>
  <a href="photos.php" class="<?= $active === 'photos' ? 'active' : '' ?>">Фотографии</a>
  <a href="blog.php" class="<?= $active === 'blog' ? 'active' : '' ?>">Блог / новости</a>
  <a href="account.php" class="<?= $active === 'account' ? 'active' : '' ?>">Мой аккаунт</a>
  <a href="logout.php" class="logout">Выйти</a>
</nav>
