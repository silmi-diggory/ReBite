<?php
include_once "../includes/koneksi.php";
wajib_role(array('penerima'));

$id_penerima = (int) $_SESSION['id_user'];

$hasil = mysqli_query($koneksi,
    "SELECT k.id, k.jumlah_porsi, k.status, k.tanggal_klaim,
            m.nama AS nama_makanan, m.lokasi, m.batas_ambil,
            u.nama AS nama_penyedia, u.no_hp
     FROM klaim k
     JOIN makanan m ON m.id = k.id_makanan
     JOIN users u ON u.id = m.id_penyedia
     WHERE k.id_penerima=$id_penerima
     ORDER BY k.tanggal_klaim DESC");

$hitung = mysqli_query($koneksi,
    "SELECT COALESCE(SUM(jumlah_porsi), 0) AS total FROM klaim
     WHERE id_penerima=$id_penerima AND status='diambil'");
$data_total = mysqli_fetch_assoc($hitung);

$label = array(
    'dipesan'    => 'Dipesan',
    'diambil'    => 'Sudah diambil',
    'dibatalkan' => 'Dibatalkan'
);

$judul_halaman = "Riwayat Klaim";
include "../includes/header.php";
?>
<section class="halaman-riwayat">
    <h2>Riwayat Klaim Saya</h2>
    <?= tampil_flash() ?>
    <p>Total makanan yang sudah kamu ambil: <b><?= (int) $data_total['total'] ?> porsi</b></p>

    <?php if (mysqli_num_rows($hasil) == 0) { ?>
        <p>Kamu belum pernah mengklaim makanan.</p>
        <a href="<?= BASE_URL ?>katalog.php" class="btn">Lihat Katalog</a>
    <?php } else { ?>
        <div class="tabel-scroll">
            <table border="1">
                <tr>
                    <th>Tanggal</th>
                    <th>Makanan</th>
                    <th>Porsi</th>
                    <th>Penyedia</th>
                    <th>Lokasi</th>
                    <th>Batas ambil</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($k = mysqli_fetch_assoc($hasil)) { ?>
                    <tr>
                        <td><?= e(date('d M Y H:i', strtotime($k['tanggal_klaim']))) ?></td>
                        <td><?= e($k['nama_makanan']) ?></td>
                        <td><?= (int) $k['jumlah_porsi'] ?></td>
                        <td><?= e($k['nama_penyedia']) ?><br><?= e($k['no_hp']) ?></td>
                        <td><?= e($k['lokasi']) ?></td>
                        <td><?= e(date('d M Y H:i', strtotime($k['batas_ambil']))) ?></td>
                        <td><span class="badge badge-<?= e($k['status']) ?>"><?= e($label[$k['status']]) ?></span></td>
                        <td>
                            <?php if ($k['status'] == 'dipesan') { ?>
                                <form method="post" action="<?= BASE_URL ?>penerima/klaim_batal.php"
                                      onSubmit="return confirm('Batalkan klaim ini?');">
                                    <input type="hidden" name="id_klaim" value="<?= (int) $k['id'] ?>">
                                    <input type="submit" name="batal" value="Batalkan" class="btn btn-danger">
                                </form>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</section>
<?php include "../includes/footer.php"; ?>