<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['admin']);

// Proses tombol Verifikasi (harus sebelum ada output HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare($koneksi, "UPDATE users SET status_verifikasi = 'terverifikasi' WHERE id = ? AND role = 'penyedia'");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            set_flash('success', 'Penyedia berhasil diverifikasi.');
        } else {
            set_flash('error', 'Penyedia tidak ditemukan atau sudah terverifikasi.');
        }
        mysqli_stmt_close($stmt);
    }
    redirect('admin/verifikasi_penyedia.php');
}

// Ambil penyedia yang masih menunggu verifikasi
$hasil = mysqli_query($koneksi, "SELECT id, nama, email, no_hp, alamat, created_at FROM users WHERE role = 'penyedia' AND status_verifikasi = 'pending' ORDER BY created_at ASC");

$judul_halaman = 'Verifikasi Penyedia';
include __DIR__ . '/../includes/header.php';
?>

<h2>Verifikasi Penyedia</h2>
<?= tampil_flash() ?>

<?php if (mysqli_num_rows($hasil) === 0): ?>
  <p>Tidak ada penyedia yang menunggu verifikasi.</p>
<?php else: ?>
  <table>
    <tr>
      <th>Nama</th>
      <th>Email</th>
      <th>No. HP</th>
      <th>Alamat</th>
      <th>Tanggal Daftar</th>
      <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
      <tr>
        <td><?= e($row['nama']) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><?= e($row['no_hp']) ?></td>
        <td><?= e($row['alamat']) ?></td>
        <td><?= e(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button class="btn" type="submit">Verifikasi</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>