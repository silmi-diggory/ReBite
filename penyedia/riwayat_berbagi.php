<?php
include_once "../includes/koneksi.php";
wajib_role(array('penyedia'));

$id_penyedia = (int) $_SESSION['id_user'];

if (isset($_POST['ambil'])) {
    $id_klaim = (int) $_POST['id_klaim'];

    mysqli_query($koneksi,
        "UPDATE klaim SET status='diambil'
         WHERE id=$id_klaim AND status='dipesan'
           AND id_makanan IN (SELECT id FROM makanan WHERE id_penyedia=$id_penyedia)");

    if (mysqli_affected_rows($koneksi) == 1) {
        set_flash('success', 'Klaim ditandai sudah diambil.');
    } else {
        set_flash('error', 'Klaim tidak ditemukan atau sudah diproses.');
    }
    redirect('penyedia/riwayat_berbagi.php');
}

$hasil_makanan = mysqli_query($koneksi,
    "SELECT m.id, m.nama, m.porsi_total, m.porsi_tersisa, m.status, m.created_at,
            COALESCE(SUM(CASE WHEN k.status='diambil' THEN k.jumlah_porsi END), 0) AS porsi_diambil,
            COALESCE(SUM(CASE WHEN k.status='dipesan' THEN k.jumlah_porsi END), 0) AS porsi_dipesan
     FROM makanan m
     LEFT JOIN klaim k ON k.id_makanan = m.id
     WHERE m.id_penyedia=$id_penyedia
     GROUP BY m.id, m.nama, m.porsi_total, m.porsi_tersisa, m.status, m.created_at
     ORDER BY m.created_at DESC");

$hasil_klaim = mysqli_query($koneksi,
    "SELECT k.id, k.jumlah_porsi, k.status, k.tanggal_klaim,
            m.nama AS nama_makanan, u.nama AS nama_penerima, u.no_hp
     FROM klaim k
     JOIN makanan m ON m.id = k.id_makanan
     JOIN users u ON u.id = k.id_penerima
     WHERE m.id_penyedia=$id_penyedia
     ORDER BY FIELD(k.status, 'dipesan', 'diambil', 'dibatalkan'), k.tanggal_klaim DESC");

$hitung = mysqli_query($koneksi,
    "SELECT COALESCE(SUM(k.jumlah_porsi), 0) AS total
     FROM klaim k JOIN makanan m ON m.id = k.id_makanan
     WHERE m.id_penyedia=$id_penyedia AND k.status='diambil'");
$data_total = mysqli_fetch_assoc($hitung);

$label_klaim = array(
    'dipesan'    => 'Dipesan',
    'diambil'    => 'Sudah diambil',
    'dibatalkan' => 'Dibatalkan'
);
$label_makanan = array(
    'tersedia'    => 'Tersedia',
    'habis'       => 'Habis',
    'kedaluwarsa' => 'Kedaluwarsa'
);

$judul_halaman = "Riwayat Berbagi";
include "../includes/header.php";
?>
<section class="halaman-riwayat">
    <h2>Riwayat Makanan yang Dibagikan</h2>
    <?= tampil_flash() ?>
    <p>Total makanan yang sudah diambil penerima: <b><?= (int) $data_total['total'] ?> porsi</b></p>

    <h3>Makanan yang kamu posting</h3>
    <?php if (mysqli_num_rows($hasil_makanan) == 0) { ?>
        <p>Kamu belum memposting makanan.</p>
    <?php } else { ?>
        <div class="tabel-scroll">
            <table border="1">
                <tr>
                    <th>Diposting</th>
                    <th>Makanan</th>
                    <th>Porsi total</th>
                    <th>Dipesan</th>
                    <th>Diambil</th>
                    <th>Sisa</th>
                    <th>Status</th>
                </tr>
                <?php while ($m = mysqli_fetch_assoc($hasil_makanan)) { ?>
                    <tr>
                        <td><?= e(date('d M Y H:i', strtotime($m['created_at']))) ?></td>
                        <td><?= e($m['nama']) ?></td>
                        <td><?= (int) $m['porsi_total'] ?></td>
                        <td><?= (int) $m['porsi_dipesan'] ?></td>
                        <td><?= (int) $m['porsi_diambil'] ?></td>
                        <td><?= (int) $m['porsi_tersisa'] ?></td>
                        <td><span class="badge badge-<?= e($m['status']) ?>"><?= e($label_makanan[$m['status']]) ?></span></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>

    <h3>Klaim dari penerima</h3>
    <?php if (mysqli_num_rows($hasil_klaim) == 0) { ?>
        <p>Belum ada klaim.</p>
    <?php } else { ?>
        <div class="tabel-scroll">
            <table border="1">
                <tr>
                    <th>Tanggal</th>
                    <th>Makanan</th>
                    <th>Penerima</th>
                    <th>Porsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($k = mysqli_fetch_assoc($hasil_klaim)) { ?>
                    <tr>
                        <td><?= e(date('d M Y H:i', strtotime($k['tanggal_klaim']))) ?></td>
                        <td><?= e($k['nama_makanan']) ?></td>
                        <td><?= e($k['nama_penerima']) ?><br><?= e($k['no_hp']) ?></td>
                        <td><?= (int) $k['jumlah_porsi'] ?></td>
                        <td><span class="badge badge-<?= e($k['status']) ?>"><?= e($label_klaim[$k['status']]) ?></span></td>
                        <td>
                            <?php if ($k['status'] == 'dipesan') { ?>
                                <form method="post" onSubmit="return confirm('Tandai klaim ini sudah diambil?');">
                                    <input type="hidden" name="id_klaim" value="<?= (int) $k['id'] ?>">
                                    <input type="submit" name="ambil" value="Tandai diambil" class="btn">
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