<?php
$role  = $_SESSION['role'] ?? null;
$login = isset($_SESSION['id_user']);
?>
<nav>
  <a href="<?= BASE_URL ?>index.php" class="nav-brand">
    <img src="<?= BASE_URL ?>assets/img/logo.png" alt="ReBite" class="logo">
  </a>

  <a href="<?= BASE_URL ?>penerima/katalog.php">Katalog</a>

  <?php if ($role === 'penyedia'): ?>
    <a href="<?= BASE_URL ?>penyedia/makanan_saya.php">Makanan Saya</a>
  <?php endif; ?>

  <?php if ($role === 'admin'): ?>
    <a href="<?= BASE_URL ?>admin/verifikasi_penyedia.php">Verifikasi</a>
    <a href="<?= BASE_URL ?>admin/kategori.php">Kategori</a>
    <a href="<?= BASE_URL ?>admin/kelola_pengguna.php">Pengguna</a>
  <?php endif; ?>

  <?php if ($login): ?>
    <a href="<?= BASE_URL ?>profil.php" class="nav-kanan">Profil</a>
    <a href="<?= BASE_URL ?>auth/logout.php">Logout</a>
  <?php else: ?>
    <a href="<?= BASE_URL ?>auth/login.php" class="nav-kanan">Login</a>
    <a href="<?= BASE_URL ?>auth/register.php">Daftar</a>
  <?php endif; ?>
</nav>