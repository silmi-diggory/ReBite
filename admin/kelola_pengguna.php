<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['admin']);

$idAdmin = (int) $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'hapus' && $id > 0) {
        if ($id === $idAdmin) {
            set_flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        } else {
            $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ? AND role <> 'admin'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                set_flash('success', 'Akun pengguna berhasil dihapus.');
            } else {
                set_flash('error', 'Pengguna tidak ditemukan atau akun admin tidak bisa dihapus.');
            }
            mysqli_stmt_close($stmt);
        }
    }
    redirect('admin/kelola_pengguna.php');
}

$filterRole = $_GET['role'] ?? '';
if (in_array($filterRole, ['admin', 'penyedia', 'penerima'], true)) {
    $stmt = mysqli_prepare($koneksi,
        "SELECT id, nama, email, role, no_hp, alamat, status_verifikasi, created_at
         FROM users WHERE role = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $filterRole);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
} else {
    $filterRole = '';
    $hasil = mysqli_query($koneksi,
        "SELECT id, nama, email, role, no_hp, alamat, status_verifikasi, created_at
         FROM users ORDER BY created_at DESC");
}

$judul_halaman = 'Kelola Pengguna';
include __DIR__ . '/../includes/header.php';
?>

<h2>Kelola Pengguna</h2>
<?= tampil_flash() ?>

<div style="margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap;">
  <a href="<?= BASE_URL ?>admin/kelola_pengguna.php" class="btn <?= $filterRole === '' ? '' : 'btn-warning' ?>">Semua</a>
  <a href="<?= BASE_URL ?>admin/kelola_pengguna.php?role=penyedia" class="btn <?= $filterRole === 'penyedia' ? '' : 'btn-warning' ?>">Penyedia</a>
  <a href="<?= BASE_URL ?>admin/kelola_pengguna.php?role=penerima" class="btn <?= $filterRole === 'penerima' ? '' : 'btn-warning' ?>">Penerima</a>
  <a href="<?= BASE_URL ?>admin/kelola_pengguna.php?role=admin" class="btn <?= $filterRole === 'admin' ? '' : 'btn-warning' ?>">Admin</a>
</div>

<table>
  <tr>
    <th>Nama</th>
    <th>Email</th>
    <th>Peran</th>
    <th>No. HP</th>
    <th>Status</th>
    <th>Terdaftar</th>
    <th>Aksi</th>
  </tr>
  <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
    <tr>
      <td>
        <strong><?= e($row['nama']) ?></strong><br>
        <small style="color:var(--color-ink-600);"><?= e($row['alamat'] ?: '-') ?></small>
      </td>
      <td><?= e($row['email']) ?></td>
      <td><?= e(ucfirst($row['role'])) ?></td>
      <td><?= e($row['no_hp'] ?: '-') ?></td>
      <td>
        <?php if ($row['status_verifikasi'] === 'terverifikasi'): ?>
          <span class="badge badge-ok">Terverifikasi</span>
        <?php else: ?>
          <span class="badge badge-wait">Pending</span>
        <?php endif; ?>
      </td>
      <td><?= e(date('d/m/Y', strtotime($row['created_at']))) ?></td>
      <td>
        <?php if ($row['role'] !== 'admin' && (int) $row['id'] !== $idAdmin): ?>
          <form method="post" style="display:inline;"
                onsubmit="return confirm('Hapus akun pengguna ini? Semua data terkait juga akan terhapus.');">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button type="submit" class="btn btn-danger">Hapus</button>
          </form>
        <?php else: ?>
          <span class="badge">Dilindungi</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
include __DIR__ . '/../includes/footer.php';
?>