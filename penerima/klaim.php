<?php
include_once "../includes/koneksi.php";
wajib_role(array('penerima'));

$id_penerima = (int) $_SESSION['id_user'];

/* ---------- PROSES KLAIM (form dikirim) ---------- */
if (isset($_POST['klaim'])) {
    $id_makanan = (int) $_POST['id_makanan'];
    $jumlah     = (int) $_POST['jumlah_porsi'];

    if ($jumlah < 1) {
        set_flash('error', 'Jumlah porsi minimal 1.');
        redirect('penerima/klaim.php?id=' . $id_makanan);
    }

    mysqli_query($koneksi,
        "UPDATE makanan SET porsi_tersisa = porsi_tersisa - $jumlah
         WHERE id=$id_makanan AND status='tersedia'
           AND batas_ambil > NOW() AND porsi_tersisa >= $jumlah");

    if (mysqli_affected_rows($koneksi) == 1) {
        $simpan = mysqli_query($koneksi,
            "INSERT INTO klaim (id_makanan, id_penerima, jumlah_porsi, status)
             VALUES ($id_makanan, $id_penerima, $jumlah, 'dipesan')");

        if ($simpan) {
            mysqli_query($koneksi,
                "UPDATE makanan SET status='habis' WHERE id=$id_makanan AND porsi_tersisa=0");
            set_flash('success', 'Klaim berhasil. Segera ambil makanan sebelum batas waktu.');
            redirect('penerima/riwayat_klaim.php');
        } else {
            mysqli_query($koneksi,
                "UPDATE makanan SET porsi_tersisa = porsi_tersisa + $jumlah WHERE id=$id_makanan");
            set_flash('error', 'Klaim gagal diproses, silakan coba lagi.');
            redirect('penerima/klaim.php?id=' . $id_makanan);
        }
    } else {
        set_flash('error', 'Makanan sudah tidak tersedia atau porsi tidak cukup.');
        redirect('katalog.php');
    }
}

/* ---------- FORM KLAIM (halaman dibuka dengan ?id=...) ---------- */
$id_makanan = 0;
if (isset($_GET['id'])) {
    $id_makanan = (int) $_GET['id'];
}
if ($id_makanan < 1) {
    redirect('katalog.php');
}

$hasil = mysqli_query($koneksi,
    "SELECT m.id, m.nama, m.deskripsi, m.porsi_tersisa, m.lokasi, m.batas_ambil, m.status,
            (m.batas_ambil > NOW()) AS masih_berlaku,
            k.nama AS nama_kategori, u.nama AS nama_penyedia, u.no_hp
     FROM makanan m
     JOIN kategori k ON k.id = m.id_kategori
     JOIN users u ON u.id = m.id_penyedia
     WHERE m.id=$id_makanan");
$m = mysqli_fetch_assoc($hasil);

if (!$m) {
    set_flash('error', 'Makanan tidak ditemukan.');
    redirect('katalog.php');
}

$bisa_klaim = false;
if ($m['status'] == 'tersedia' && $m['masih_berlaku'] == 1 && $m['porsi_tersisa'] > 0) {
    $bisa_klaim = true;
}

$judul_halaman = "Klaim Makanan";
include "../includes/header.php";
?>
<section class="form-wrap">
    <h2>Klaim Makanan</h2>
    <?= tampil_flash() ?>

    <table class="tabel-profil">
        <tr><th>Makanan</th><td><?= e($m['nama']) ?></td></tr>
        <tr><th>Kategori</th><td><?= e($m['nama_kategori']) ?></td></tr>
        <tr><th>Deskripsi</th><td><?= e($m['deskripsi']) ?></td></tr>
        <tr><th>Penyedia</th><td><?= e($m['nama_penyedia']) ?> (<?= e($m['no_hp']) ?>)</td></tr>
        <tr><th>Lokasi ambil</th><td><?= e($m['lokasi']) ?></td></tr>
        <tr><th>Batas ambil</th><td><?= e(date('d M Y H:i', strtotime($m['batas_ambil']))) ?></td></tr>
        <tr><th>Porsi tersisa</th><td><?= (int) $m['porsi_tersisa'] ?></td></tr>
    </table>

    <?php if ($bisa_klaim) { ?>
        <form method="post" class="form" onSubmit="return cek()">
            <input type="hidden" name="id_makanan" value="<?= (int) $m['id'] ?>">

            <label for="jumlah_porsi">Jumlah porsi yang diklaim</label>
            <input type="number" id="jumlah_porsi" name="jumlah_porsi" min="1"
                   max="<?= (int) $m['porsi_tersisa'] ?>" value="1">

            <br><br>
            <input type="submit" name="klaim" value="Klaim Sekarang" class="btn">
            <a href="<?= BASE_URL ?>katalog.php">Batal</a>
        </form>

        <script>
            function cek() {
                const jumlah = document.getElementById("jumlah_porsi");
                const maks = <?= (int) $m['porsi_tersisa'] ?>;

                if (jumlah.value == "" || jumlah.value < 1) {
                    alert("Jumlah porsi minimal 1!");
                    jumlah.focus();
                    return false;
                }
                if (jumlah.value > maks) {
                    alert("Porsi tersisa hanya " + maks + "!");
                    jumlah.focus();
                    return false;
                }
                return true;
            }
        </script>
    <?php } else { ?>
        <div class="alert alert-error">Makanan ini sudah tidak tersedia untuk diklaim.</div>
        <a href="<?= BASE_URL ?>katalog.php" class="btn">Kembali ke Katalog</a>
    <?php } ?>
</section>
<?php include "../includes/footer.php"; ?>