<?php
require_once __DIR__ . '/includes/koneksi.php';
wajib_login();

// Perbarui otomatis makanan yang sudah lewat batas ambil
mysqli_query($koneksi,
    "UPDATE makanan SET status = 'kedaluwarsa'
     WHERE status = 'tersedia' AND batas_ambil <= NOW()");

$cari       = trim($_GET['q'] ?? '');
$idKategori = (int) ($_GET['kategori'] ?? 0);
$role       = $_SESSION['role'] ?? '';

$where  = ["m.status = 'tersedia'", "m.porsi_tersisa > 0", "m.batas_ambil > NOW()"];
$types  = '';
$params = [];

if ($cari !== '') {
    $where[]  = "(m.nama LIKE ? OR m.deskripsi LIKE ? OR m.lokasi LIKE ? OR u.nama LIKE ?)";
    $like     = '%' . $cari . '%';
    $types   .= 'ssss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($idKategori > 0) {
    $where[]  = "m.id_kategori = ?";
    $types   .= 'i';
    $params[] = $idKategori;
}

$sql = "SELECT m.id, m.nama, m.deskripsi, m.foto, m.porsi_tersisa, m.porsi_total,
               m.lokasi, m.batas_ambil, k.nama AS nama_kategori, u.nama AS nama_penyedia
        FROM makanan m
        JOIN kategori k ON m.id_kategori = k.id
        JOIN users u ON m.id_penyedia = u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY m.batas_ambil ASC";

$stmt = mysqli_prepare($koneksi, $sql);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$daftarKategori = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama");

$judul_halaman = 'Katalog Makanan';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
  <h2 style="margin:0;">Katalog Makanan Tersedia</h2>
  <?php if ($role === 'penerima'): ?>
    <a href="<?= BASE_URL ?>penerima/riwayat_klaim.php" class="btn btn-warning">Riwayat Klaim Saya</a>
  <?php endif; ?>
</div>

<?= tampil_flash() ?>

<div class="card" style="margin-bottom:20px;">
  <form method="get" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
    <input type="text" name="q" placeholder="Cari makanan, lokasi, atau penyedia..."
           value="<?= e($cari) ?>" style="flex:1; min-width:200px;">
    <select name="kategori" style="width:auto; min-width:180px;">
      <option value="0">Semua Kategori</option>
      <?php while ($k = mysqli_fetch_assoc($daftarKategori)): ?>
        <option value="<?= (int) $k['id'] ?>" <?= $idKategori === (int) $k['id'] ? 'selected' : '' ?>>
          <?= e($k['nama']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="btn">Filter</button>
    <?php if ($cari !== '' || $idKategori > 0): ?>
      <a href="<?= BASE_URL ?>katalog.php" class="btn btn-warning">Reset</a>
    <?php endif; ?>
  </form>
</div>

<?php if (mysqli_num_rows($hasil) === 0): ?>
  <div class="card">
    <p style="margin:0;">Belum ada makanan tersedia yang sesuai dengan pencarian.</p>
  </div>
<?php else: ?>
  <div class="grid">
    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
      <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div>
          <?php if (!empty($row['foto'])): ?>
            <img src="<?= BASE_URL ?>assets/img/makanan/<?= e($row['foto']) ?>"
                 alt="<?= e($row['nama']) ?>"
                 style="width:100%; height:160px; object-fit:cover; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>

          <span class="badge badge-ok"><?= e($row['nama_kategori']) ?></span>
          <h3 style="margin:8px 0 6px;"><?= e($row['nama']) ?></h3>

          <?php if (!empty($row['deskripsi'])): ?>
            <p style="font-size:14px; color:var(--color-ink-600); margin:0 0 10px;">
              <?= e($row['deskripsi']) ?>
            </p>
          <?php endif; ?>

          <div style="font-size:13px; line-height:1.6; margin-bottom:12px;">
            <div><strong>Penyedia:</strong> <?= e($row['nama_penyedia']) ?></div>
            <div><strong>Lokasi:</strong> <?= e($row['lokasi']) ?></div>
            <div><strong>Tersisa:</strong> <?= (int) $row['porsi_tersisa'] ?> dari <?= (int) $row['porsi_total'] ?> porsi</div>
            <div><strong>Batas ambil:</strong> <?= e(date('d/m/Y H:i', strtotime($row['batas_ambil']))) ?></div>
          </div>
        </div>

        <?php if ($role === 'penerima'): ?>
          <a href="<?= BASE_URL ?>penerima/klaim.php?id=<?= (int) $row['id'] ?>" class="btn" style="text-align:center;">Klaim</a>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

<?php
mysqli_stmt_close($stmt);
include __DIR__ . '/includes/footer.php';
?>