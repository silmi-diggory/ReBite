<?php
if (!defined('BASE_URL')) define('BASE_URL', '/ReBite/');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars(isset($judul_halaman) ? $judul_halaman . ' | ReBite' : 'ReBite') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/menu.php'; ?>
<main class="container">