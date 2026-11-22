<?php
date_default_timezone_set('Asia/Jakarta');

$server   = "localhost";
$username = "root";
$password = "";
$database = "rebite";

mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_connect($server, $username, $password, $database)
    or die("Gagal koneksi ke database: " . mysqli_connect_error());

mysqli_set_charset($koneksi, "utf8mb4");

if (!defined('BASE_URL')) {
    $akar    = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
    $docroot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']));
    $jalur   = '';
    if ($docroot != '' && stripos($akar, $docroot) === 0) {
        $jalur = substr($akar, strlen($docroot));
    }
    define('BASE_URL', rtrim($jalur, '/') . '/');
}

function redirect($tujuan)
{
    header('Location: ' . BASE_URL . ltrim($tujuan, '/'));
    exit;
}

function halaman_beranda($role)
{
    switch ($role) {
        case 'admin':
            return 'dashboard.php';
        case 'penyedia':
            return 'penyedia/makanan_saya.php';
        case 'penerima':
            return 'katalog.php';
        default:
            return 'auth/login.php';
    }
}

function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/* ============================================================
   Session dan hak akses
   ============================================================ */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function sudah_login()
{
    return isset($_SESSION['id_user']);
}

function user_login()
{
    $data = array('id' => null, 'nama' => null, 'role' => null);
    if (isset($_SESSION['id_user'])) {
        $data['id']   = $_SESSION['id_user'];
        $data['nama'] = $_SESSION['nama'];
        $data['role'] = $_SESSION['role'];
    }
    return $data;
}

function set_flash($tipe, $pesan)
{
    $_SESSION['flash_tipe']  = $tipe;
    $_SESSION['flash_pesan'] = $pesan;
}

function tampil_flash()
{
    $hasil = '';
    if (isset($_SESSION['flash_pesan'])) {
        $hasil = '<div class="alert alert-' . e($_SESSION['flash_tipe']) . '">'
               . e($_SESSION['flash_pesan']) . '</div>';
        unset($_SESSION['flash_tipe']);
        unset($_SESSION['flash_pesan']);
    }
    return $hasil;
}

function wajib_login()
{
    if (!sudah_login()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('auth/login.php');
    }
}

function wajib_role($role_diizinkan)
{
    wajib_login();
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        set_flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect(halaman_beranda($_SESSION['role']));
    }
}

function wajib_tamu()
{
    if (sudah_login()) {
        redirect(halaman_beranda($_SESSION['role']));
    }
}
?>