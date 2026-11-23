<?php
include_once "includes/koneksi.php";
wajib_role(['penerima']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('riwayat_klaim.php');
}

$id_penerima = (int) $_SESSION['id_user'];
$id_klaim    = (int) ($_POST['id_klaim'] ?? 0);

if ($id_klaim <= 0) {
    set_flash('error', 'ID klaim tidak valid.');
    redirect('riwayat_klaim.php');
}

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, id_makanan, jumlah_porsi, status
        FROM klaim
        WHERE id = ? AND id_penerima = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "ii", $id_klaim, $id_penerima);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $klaim = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$klaim || $klaim['status'] !== 'dipesan') {
        throw new Exception('Klaim tidak dapat dibatalkan.');
    }

    $status_batal = 'dibatalkan';
    $stmt_batal = mysqli_prepare($koneksi, "
        UPDATE klaim
        SET status = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt_batal, "si", $status_batal, $id_klaim);
    mysqli_stmt_execute($stmt_batal);
    mysqli_stmt_close($stmt_batal);

    $id_makanan = (int) $klaim['id_makanan'];
    $stmt_mak = mysqli_prepare($koneksi, "
        SELECT status, batas_ambil
        FROM makanan
        WHERE id = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt_mak, "i", $id_makanan);
    mysqli_stmt_execute($stmt_mak);
    $res_mak = mysqli_stmt_get_result($stmt_mak);
    $makanan = mysqli_fetch_assoc($res_mak);
    mysqli_stmt_close($stmt_mak);

    $status_makanan = $makanan['status'] ?? 'tersedia';
    if ($status_makanan === 'habis' && !empty($makanan['batas_ambil']) && strtotime($makanan['batas_ambil']) > time()) {
        $status_makanan = 'tersedia';
    }

    $jumlah_porsi = (int) $klaim['jumlah_porsi'];
    $stmt_kembali = mysqli_prepare($koneksi, "
        UPDATE makanan
        SET porsi_tersisa = porsi_tersisa + ?, status = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt_kembali, "isi", $jumlah_porsi, $status_makanan, $id_makanan);
    mysqli_stmt_execute($stmt_kembali);
    mysqli_stmt_close($stmt_kembali);

    mysqli_commit($koneksi);
    set_flash('success', 'Klaim berhasil dibatalkan dan porsi dikembalikan.');
    redirect('riwayat_klaim.php');
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    set_flash('error', $e->getMessage());
    redirect('riwayat_klaim.php');
}