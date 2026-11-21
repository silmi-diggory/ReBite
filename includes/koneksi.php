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
    $akar     = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
    $docroot  = isset($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']))
        : '';
    $jalur    = ($docroot !== '' && stripos($akar, $docroot) === 0)
        ? substr($akar, strlen($docroot))
        : '';
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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sudah_login()
{
    return isset($_SESSION['id_user']);
}

function user_login()
{
    return [
        'id'   => $_SESSION['id_user'] ?? null,
        'nama' => $_SESSION['nama'] ?? null,
        'role' => $_SESSION['role'] ?? null,
    ];
}

function set_flash($tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampil_flash()
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['tipe']) . '">' . e($f['pesan']) . '</div>';
}

// Halaman wajib login (semua role)
function wajib_login()
{
    if (!sudah_login()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('auth/login.php');
    }
}

// Halaman khusus role tertentu. Contoh: wajib_role(['admin']);
//                                      wajib_role(['penyedia', 'admin']);
function wajib_role(array $role_diizinkan)
{
    wajib_login();
    if (!in_array($_SESSION['role'], $role_diizinkan, true)) {
        set_flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect(halaman_beranda($_SESSION['role']));
    }
}

// Halaman khusus tamu (login/register). Kalau sudah login, arahkan ke beranda.
function wajib_tamu()
{
    if (sudah_login()) {
        redirect(halaman_beranda($_SESSION['role']));
    }
}