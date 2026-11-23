<?php
include_once "includes/koneksi.php";
wajib_role(['penyedia']);

$judul_halaman = "Riwayat Berbagi";
$id_penyedia   = (int) $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'konfirmasi_ambil') {
    $id_klaim = (int) ($_POST['id_klaim'] ?? 0);
    if ($id_klaim > 0) {
        $status_diambil = 'diambil';
        $status_dipesan = 'dipesan';
        $stmt_upd = mysqli_prepare($koneksi, "
            UPDATE klaim k
            JOIN makanan m ON m.id = k.id_makanan
            SET k.status = ?
            WHERE k.id = ? AND m.id_penyedia = ? AND k.status = ?
        ");
        mysqli_stmt_bind_param($stmt_upd, "siis", $status_diambil, $id_klaim, $id_penyedia, $status_dipesan);
        mysqli_stmt_execute($stmt_upd);
        $terubah = mysqli_stmt_affected_rows($stmt_upd);
        mysqli_stmt_close($stmt_upd);

        if ($terubah > 0) {
            set_flash('success', 'Status klaim berhasil diubah menjadi diambil.');
        } else {
            set_flash('error', 'Klaim tidak ditemukan atau sudah diproses.');
        }
    }
    redirect('riwayat_berbagi.php');
}

$stmt = mysqli_prepare($koneksi, "
    SELECT k.id, k.jumlah_porsi, k.status,
           m.nama_makanan
    FROM klaim k
    JOIN makanan m ON m.id = k.id_makanan
    WHERE m.id_penyedia = ?
    ORDER BY k.id DESC
");
mysqli_stmt_bind_param($stmt, "i", $id_penyedia);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$daftar_berbagi = [];
while ($row = mysqli_fetch_assoc($res)) {
    $daftar_berbagi[] = $row;
}
mysqli_stmt_close($stmt);

include "includes/header.php";
?>
<section class="card">
    <h2>Riwayat Klaim Makanan Dibagikan</h2>
    <?= tampil_flash() ?>

    <?php if (empty($daftar_berbagi)): ?>
        <p>Belum ada klaim pada makanan yang kamu bagikan.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Makanan</th>
                    <th>Jumlah Porsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_berbagi as $row): ?>
                    <?php $badge = ($row['status'] === 'diambil') ? 'badge-ok' : 'badge-wait'; ?>
                    <tr>
                        <td><?= e($row['nama_makanan']) ?></td>
                        <td><?= e((string) $row['jumlah_porsi']) ?> porsi</td>
                        <td><span class="badge <?= e($badge) ?>"><?= e($row['status']) ?></span></td>
                        <td>
                            <?php if ($row['status'] === 'dipesan'): ?>
                                <form action="riwayat_berbagi.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="aksi" value="konfirmasi_ambil">
                                    <input type="hidden" name="id_klaim" value="<?= e((string) $row['id']) ?>">
                                    <button type="submit" class="btn btn-warning">Tandai Diambil</button>
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