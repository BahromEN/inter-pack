<?php
require_once __DIR__ . '/../includes/auth.php';
cms_logout();
header('Location: login.php');
exit;
