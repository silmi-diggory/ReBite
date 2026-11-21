<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['penyedia']);

$idPenyedia = (int)$_SESSION['id_user'];

// Otomatis ubah status menjadi kedaluwarsa jika batas ambil sudah lewat
$stmtExp = mysqli_prepare($koneksi,
    "UPDATE makanan SET status = 'kedaluwarsa'
     WHERE id_penyedia = ? AND status = 'tersedia' AND batas_ambil <= NOW()");
mysqli_stmt_bind_param($stmtExp, 'i',$idPenyedia);
mysqli_stmt_execute($stmtExp);
mysqli_stmt_close($stmtExp);

$stmt = mysqli_prepare($koneksi,
    "SELECT m.id, m.nama, m.foto, m.porsi_total, m.porsi_tersisa, m.lokasi, m.batas_ambil, m.status, k.nama AS nama_kategori
     FROM makanan m
     JOIN kategori k ON m.id_kategori = k.id
     WHERE m.id_penyedia = ?
     ORDER BY m.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i',$idPenyedia);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$judul_halaman = 'Makanan Saya';
include __DIR__ . '/../includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
  <h2 style="margin:0;">Makanan Saya</h2>
  <div>
    <a href="<?= BASE_URL ?>penyedia/makanan_tambah.php" class="btn">+ Posting Makanan</a>
    <?php if (file_exists(__DIR__ . '/../riwayat_berbagi.php')): ?>
      <a href="<?= BASE_URL ?>riwayat_berbagi.php" class="btn btn-warning">Riwayat Berbagi</a>
    <?php endif; ?>
  </div>
</div>

<?= tampil_flash() ?>

<?php if (mysqli_num_rows($hasil) === 0): ?>
  <div class="card">
    <p style="margin:0;">Anda belum memposting makanan. Klik tombol <strong>+ Posting Makanan</strong> untuk mulai berbagi.</p>
  </div>
<?php else: ?>
  <table>
    <tr>
      <th>Foto</th>
      <th>Makanan</th>
      <th>Porsi (Sisa / Total)</th>
      <th>Batas Ambil</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
      <tr>
        <td style="width:72px;">
          <?php if (!empty($row['foto'])): ?>
            <img src="<?= BASE_URL ?>assets/img/makanan/<?= e($row['foto']) ?>"
                 alt="<?= e($row['nama']) ?>"
                 style="width:56px; height:56px; object-fit:cover; border-radius:6px;">
          <?php else: ?>
            <span class="badge">Tanpa foto</span>
          <?php endif; ?>
        </td>
        <td>
          <strong><?= e($row['nama']) ?></strong><br>
          <small style="color:var(--color-ink-600);"><?= e($row['nama_kategori']) ?> &bull; <?= e($row['lokasi']) ?></small>
        </td>
        <td><?= (int) $row['porsi_tersisa'] ?> / <?= (int)$row['porsi_total'] ?></td>
        <td><?= e(date('d/m/Y H:i', strtotime($row['batas_ambil']))) ?></td>
        <td>
          <?php if ($row['status'] === 'tersedia'): ?>
            <span class="badge badge-ok">Tersedia</span>
          <?php elseif ($row['status'] === 'habis'): ?>
            <span class="badge badge-wait">Habis</span>
          <?php else: ?>
            <span class="badge">Kedaluwarsa</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="<?= BASE_URL ?>penyedia/makanan_edit.php?id=<?= (int) $row['id'] ?>" class="btn btn-warning">Edit</a>
          <form method="post" action="<?= BASE_URL ?>penyedia/makanan_hapus.php" style="display:inline;"
                onsubmit="return confirm('Yakin ingin menghapus postingan makanan ini?');">
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <button type="submit" class="btn btn-danger">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php endif; ?>

<?php
mysqli_stmt_close($stmt);
include __DIR__ . '/../includes/footer.php';
?>