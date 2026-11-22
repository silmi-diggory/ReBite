<?php
include_once "../includes/koneksi.php";
wajib_role(array('penerima'));

$id_penerima = (int) $_SESSION['id_user'];

if (isset($_POST['batal'])) {
    $id_klaim = (int) $_POST['id_klaim'];

    $hasil = mysqli_query($koneksi,
        "SELECT id_makanan, jumlah_porsi, status FROM klaim
         WHERE id=$id_klaim AND id_penerima=$id_penerima");
    $klaim = mysqli_fetch_assoc($hasil);

    if (!$klaim) {
        set_flash('error', 'Klaim tidak ditemukan.');
    } elseif ($klaim['status'] != 'dipesan') {
        set_flash('error', 'Hanya klaim berstatus dipesan yang bisa dibatalkan.');
    } else {
        mysqli_query($koneksi,
            "UPDATE klaim SET status='dibatalkan' WHERE id=$id_klaim AND status='dipesan'");

        if (mysqli_affected_rows($koneksi) == 1) {
            $id_makanan = (int) $klaim['id_makanan'];
            $jumlah     = (int) $klaim['jumlah_porsi'];

            mysqli_query($koneksi,
                "UPDATE makanan SET porsi_tersisa = LEAST(porsi_total, porsi_tersisa + $jumlah)
                 WHERE id=$id_makanan");

            mysqli_query($koneksi,
                "UPDATE makanan SET status='tersedia'
                 WHERE id=$id_makanan AND status='habis'
                   AND porsi_tersisa > 0 AND batas_ambil > NOW()");

            set_flash('success', 'Klaim berhasil dibatalkan dan porsi dikembalikan.');
        } else {
            set_flash('error', 'Pembatalan gagal, silakan coba lagi.');
        }
    }
}

redirect('penerima/riwayat_klaim.php');
?>