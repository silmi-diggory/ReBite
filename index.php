<?php
require_once __DIR__ . '/includes/koneksi.php';

if (sudah_login()) {
    redirect(halaman_beranda($_SESSION['role']));
}
redirect('auth/login.php');