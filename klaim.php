<?php
include_once "includes/koneksi.php";
wajib_role(['penerima']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('katalog.php');
}

$id_penerima  = (int) $_SESSION['id_user'];
$id_makanan   = (int) ($_POST['id_makanan'] ?? 0);
$jumlah_porsi = max(1, (int) ($_POST['jumlah_porsi'] ?? 1));

if ($id_makanan <= 0) {
    set_flash('error', 'Data makanan tidak valid.');
    redirect('katalog.php');
}

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, porsi_tersisa, status, batas_ambil
        FROM makanan
        WHERE id = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "i", $id_makanan);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $makanan = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$makanan) {
        throw new Exception('Makanan tidak ditemukan.');
    }

    if ($makanan['status'] !== 'tersedia' || strtotime($makanan['batas_ambil']) <= time()) {
        throw new Exception('Makanan sudah tidak tersedia atau batas ambil telah lewat.');
    }

    if ((int) $makanan['porsi_tersisa'] < $jumlah_porsi) {
        throw new Exception('Porsi tersisa tidak mencukupi.');
    }

    $sisa_baru   = (int) $makanan['porsi_tersisa'] - $jumlah_porsi;
    $status_baru = ($sisa_baru <= 0) ? 'habis' : 'tersedia';

    $stmt_upd = mysqli_prepare($koneksi, "
        UPDATE makanan
        SET porsi_tersisa = ?, status = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt_upd, "isi", $sisa_baru, $status_baru, $id_makanan);
    mysqli_stmt_execute($stmt_upd);
    mysqli_stmt_close($stmt_upd);

    $status_klaim = 'dipesan';
    $stmt_ins = mysqli_prepare($koneksi, "
        INSERT INTO klaim (id_makanan, id_penerima, jumlah_porsi, status)
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt_ins, "iiis", $id_makanan, $id_penerima, $jumlah_porsi, $status_klaim);
    mysqli_stmt_execute($stmt_ins);
    mysqli_stmt_close($stmt_ins);

    mysqli_commit($koneksi);
    set_flash('success', 'Makanan berhasil diklaim.');
    redirect('riwayat_klaim.php');
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    set_flash('error', $e->getMessage());
    redirect('katalog.php');
}