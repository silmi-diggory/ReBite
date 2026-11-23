<?php
include_once "includes/koneksi.php";
wajib_role(['penerima']);

$judul_halaman = "Riwayat Klaim";
$id_penerima   = (int) $_SESSION['id_user'];

$stmt = mysqli_prepare($koneksi, "
    SELECT k.id, k.jumlah_porsi, k.status,
           m.nama_makanan, m.batas_ambil
    FROM klaim k
    JOIN makanan m ON m.id = k.id_makanan
    WHERE k.id_penerima = ?
    ORDER BY k.id DESC
");
mysqli_stmt_bind_param($stmt, "i", $id_penerima);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$daftar_klaim = [];
while ($row = mysqli_fetch_assoc($res)) {
    $daftar_klaim[] = $row;
}
mysqli_stmt_close($stmt);

include "includes/header.php";
?>
<section class="card">
    <h2>Riwayat Klaim Saya</h2>
    <?= tampil_flash() ?>

    <?php if (empty($daftar_klaim)): ?>
        <p>Belum ada riwayat klaim makanan.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Makanan</th>
                    <th>Jumlah Porsi</th>
                    <th>Batas Ambil</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_klaim as $item): ?>
                    <?php $badge = ($item['status'] === 'diambil') ? 'badge-ok' : 'badge-wait'; ?>
                    <tr>
                        <td><?= e($item['nama_makanan']) ?></td>
                        <td><?= e((string) $item['jumlah_porsi']) ?> porsi</td>
                        <td><?= e((string) $item['batas_ambil']) ?></td>
                        <td><span class="badge <?= e($badge) ?>"><?= e($item['status']) ?></span></td>
                        <td>
                            <?php if ($item['status'] === 'dipesan'): ?>
                                <form action="klaim_batal.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_klaim" value="<?= e((string) $item['id']) ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Batalkan klaim ini?');">Batalkan</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php include "includes/footer.php"; ?>