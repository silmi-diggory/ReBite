<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['penyedia']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('penyedia/makanan_saya.php');
}

$idPenyedia = (int) $_SESSION['id_user'];
$idMakanan  = (int) ($_POST['id'] ?? 0);

if ($idMakanan <= 0) {
    set_flash('error', 'ID makanan tidak valid.');
    redirect('penyedia/makanan_saya.php');
}

// Pastikan makanan benar-benar milik penyedia yang login
$stmt = mysqli_prepare($koneksi, "SELECT foto FROM makanan WHERE id = ? AND id_penyedia = ?");
mysqli_stmt_bind_param($stmt, 'ii', $idMakanan, $idPenyedia);
mysqli_stmt_execute($stmt);
$makanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$makanan) {
    set_flash('error', 'Makanan tidak ditemukan atau bukan milik Anda.');
    redirect('penyedia/makanan_saya.php');
}

// Cegah hapus jika masih ada klaim aktif berstatus 'dipesan'
$cek = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM klaim WHERE id_makanan = ? AND status = 'dipesan'");
mysqli_stmt_bind_param($cek, 'i', $idMakanan);
mysqli_stmt_execute($cek);
mysqli_stmt_bind_result($cek, $klaimAktif);
mysqli_stmt_fetch($cek);
mysqli_stmt_close($cek);

if ($klaimAktif > 0) {
    set_flash('error', "Makanan tidak bisa dihapus karena masih ada $klaimAktif klaim yang sedang dipesan.");
    redirect('penyedia/makanan_saya.php');
}

$del = mysqli_prepare($koneksi, "DELETE FROM makanan WHERE id = ? AND id_penyedia = ?");
mysqli_stmt_bind_param($del, 'ii', $idMakanan, $idPenyedia);

if (mysqli_stmt_execute($del)) {
    mysqli_stmt_close($del);
    if (!empty($makanan['foto'])) {
        @unlink(__DIR__ . '/../assets/img/makanan/' . $makanan['foto']);
    }
    set_flash('success', 'Postingan makanan berhasil dihapus.');
} else {
    mysqli_stmt_close($del);
    set_flash('error', 'Gagal menghapus makanan.');
}

redirect('penyedia/makanan_saya.php');