<?php
include_once "includes/koneksi.php";
wajib_login();

function ambil_angka($koneksi, $sql)
{
    $hasil = mysqli_query($koneksi, $sql);
    $baris = mysqli_fetch_row($hasil);
    return (int) $baris[0];
}

function angka($n)
{
    return number_format((int) $n, 0, ',', '.');
}

$role = $_SESSION['role'];
$id   = (int) $_SESSION['id_user'];

$porsi_diselamatkan = ambil_angka($koneksi, "SELECT COALESCE(SUM(jumlah_porsi), 0) FROM klaim WHERE status='diambil'");
$porsi_dipesan      = ambil_angka($koneksi, "SELECT COALESCE(SUM(jumlah_porsi), 0) FROM klaim WHERE status='dipesan'");
$porsi_diposting    = ambil_angka($koneksi, "SELECT COALESCE(SUM(porsi_total), 0) FROM makanan");
$jumlah_postingan   = ambil_angka($koneksi, "SELECT COUNT(*) FROM makanan");
$klaim_dibatalkan   = ambil_angka($koneksi, "SELECT COUNT(*) FROM klaim WHERE status='dibatalkan'");
$jumlah_penyedia    = ambil_angka($koneksi, "SELECT COUNT(*) FROM users WHERE role='penyedia' AND status_verifikasi='terverifikasi'");
$jumlah_penerima    = ambil_angka($koneksi, "SELECT COUNT(*) FROM users WHERE role='penerima'");

if ($porsi_diposting > 0) {
    $persen = round($porsi_diselamatkan / $porsi_diposting * 100);
} else {
    $persen = 0;
}

$per_kategori = array();
$hasil = mysqli_query($koneksi,
    "SELECT c.nama, COALESCE(SUM(k.jumlah_porsi), 0) AS porsi
     FROM kategori c
     LEFT JOIN makanan m ON m.id_kategori = c.id
     LEFT JOIN klaim k ON k.id_makanan = m.id AND k.status='diambil'
     GROUP BY c.id, c.nama
     ORDER BY porsi DESC, c.nama ASC");
$maks_kategori = 0;
while ($baris = mysqli_fetch_assoc($hasil)) {
    $per_kategori[] = $baris;
    if ($baris['porsi'] > $maks_kategori) {
        $maks_kategori = $baris['porsi'];
    }
}

$top_penyedia = array();
$hasil = mysqli_query($koneksi,
    "SELECT u.nama, SUM(k.jumlah_porsi) AS porsi
     FROM klaim k
     JOIN makanan m ON m.id = k.id_makanan
     JOIN users u ON u.id = m.id_penyedia
     WHERE k.status='diambil'
     GROUP BY u.id, u.nama
     ORDER BY porsi DESC
     LIMIT 5");
while ($baris = mysqli_fetch_assoc($hasil)) {
    $top_penyedia[] = $baris;
}

$porsi_saya = -1;
if ($role == 'penyedia') {
    $porsi_saya = ambil_angka($koneksi,
        "SELECT COALESCE(SUM(k.jumlah_porsi), 0)
         FROM klaim k JOIN makanan m ON m.id = k.id_makanan
         WHERE k.status='diambil' AND m.id_penyedia=$id");
} elseif ($role == 'penerima') {
    $porsi_saya = ambil_angka($koneksi,
        "SELECT COALESCE(SUM(jumlah_porsi), 0) FROM klaim
         WHERE status='diambil' AND id_penerima=$id");
}

$judul_halaman = "Dashboard Dampak";
include "includes/header.php";
?>
<section class="dashboard">
    <h2>Dashboard Dampak</h2>
    <?= tampil_flash() ?>
    <p>Berapa banyak makanan layak yang berhasil diselamatkan lewat ReBite.</p>

    <div class="kartu-statistik">
        <div class="kartu kartu-utama">
            <span class="angka"><?= angka($porsi_diselamatkan) ?></span>
            <span class="keterangan">porsi makanan diselamatkan (sudah diambil)</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= angka($porsi_dipesan) ?></span>
            <span class="keterangan">porsi sedang dipesan</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= angka($porsi_diposting) ?></span>
            <span class="keterangan">porsi diposting dari <?= angka($jumlah_postingan) ?> postingan</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= $persen ?>%</span>
            <span class="keterangan">porsi diposting yang sudah diselamatkan</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= angka($jumlah_penyedia) ?></span>
            <span class="keterangan">penyedia terverifikasi</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= angka($jumlah_penerima) ?></span>
            <span class="keterangan">penerima terdaftar</span>
        </div>
        <div class="kartu">
            <span class="angka"><?= angka($klaim_dibatalkan) ?></span>
            <span class="keterangan">klaim dibatalkan</span>
        </div>
    </div>

    <?php if ($porsi_saya >= 0) { ?>
        <div class="alert alert-success">
            <?php if ($role == 'penyedia') { ?>
                Makanan yang kamu bagikan dan sudah diambil: <b><?= angka($porsi_saya) ?> porsi</b>.
            <?php } else { ?>
                Makanan yang sudah kamu selamatkan: <b><?= angka($porsi_saya) ?> porsi</b>.
            <?php } ?>
        </div>
    <?php } ?>

    <h3>Porsi diselamatkan per kategori</h3>
    <?php foreach ($per_kategori as $kat) { ?>
        <?php
        if ($maks_kategori > 0) {
            $lebar = round($kat['porsi'] / $maks_kategori * 100);
        } else {
            $lebar = 0;
        }
        ?>
        <div class="baris-bar">
            <span class="label-bar"><?= e($kat['nama']) ?></span>
            <div class="bar"><div class="bar-isi" style="width: <?= $lebar ?>%"></div></div>
            <span class="nilai-bar"><?= angka($kat['porsi']) ?></span>
        </div>
    <?php } ?>

    <h3>Penyedia dengan dampak terbesar</h3>
    <?php if (count($top_penyedia) == 0) { ?>
        <p>Belum ada makanan yang diambil.</p>
    <?php } else { ?>
        <ol>
            <?php foreach ($top_penyedia as $p) { ?>
                <li><?= e($p['nama']) ?>: <?= angka($p['porsi']) ?> porsi</li>
            <?php } ?>
        </ol>
    <?php } ?>
</section>
<?php include "includes/footer.php"; ?>