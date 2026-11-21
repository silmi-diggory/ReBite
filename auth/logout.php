<?php
require_once __DIR__ . '/../includes/koneksi.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Mulai session baru hanya untuk membawa pesan
session_start();
set_flash('success', 'Anda berhasil keluar.');
redirect('auth/login.php');