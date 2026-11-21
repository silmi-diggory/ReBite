<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['admin']);

// Proses form (harus sebelum ada output HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        if ($nama === '') {
            set_flash('error', 'Nama kategori tidak boleh kosong.');
        } elseif (mb_strlen($nama) > 50) {
            set_flash('error', 'Nama kategori maksimal 50 karakter.');
        } else {
            // Cek nama kembar (saat ubah, kategori itu sendiri diabaikan)
            $idAbaikan = ($aksi === 'ubah') ? $id : 0;
            $cek = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE nama = ? AND id <> ?");
            mysqli_stmt_bind_param($cek, 'si', $nama, $idAbaikan);
            mysqli_stmt_execute($cek);
            mysqli_stmt_store_result($cek);
            $kembar = mysqli_stmt_num_rows($cek) > 0;
            mysqli_stmt_close($cek);

            if ($kembar) {
                set_flash('error', 'Kategori dengan nama itu sudah ada.');
            } elseif ($aksi === 'tambah') {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama) VALUES (?)");
                mysqli_stmt_bind_param($stmt, 's', $nama);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                set_flash('success', 'Kategori berhasil ditambahkan.');
            } else {
                $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $nama, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                set_flash('success', 'Kategori berhasil diubah.');
            }
        }
    } elseif ($aksi === 'hapus' && $id > 0) {
        // Kategori yang masih dipakai makanan tidak boleh dihapus
        $cek = mysqli_prepare($koneksi, "SELECT COUNT(*) FROM makanan WHERE id_kategori = ?");
        mysqli_stmt_bind_param($cek, 'i', $id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_bind_result($cek, $jumlah);
        mysqli_stmt_fetch($cek);
        mysqli_stmt_close($cek);

        if ($jumlah > 0) {
            set_flash('error', "Kategori masih dipakai $jumlah makanan, jadi tidak bisa dihapus.");
        } else {
            $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            set_flash('success', 'Kategori berhasil dihapus.');
        }
    }
    redirect('admin/kategori.php');
}

// Mode ubah: ambil kategori yang dipilih lewat ?edit=ID
$edit = null;
if (isset($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM kategori WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idEdit);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eid, $enama);
    if (mysqli_stmt_fetch($stmt)) {
        $edit = ['id' => $eid, 'nama' => $enama];
    }
    mysqli_stmt_close($stmt);
}

// Daftar kategori beserta jumlah makanan yang memakainya
$daftar = mysqli_query($koneksi, "SELECT k.id, k.nama, COUNT(m.id) AS jumlah
                                  FROM kategori k
                                  LEFT JOIN makanan m ON m.id_kategori = k.id
                                  GROUP BY k.id, k.nama
                                  ORDER BY k.nama");

$judul_halaman = 'Kelola Kategori';
include __DIR__ . '/../includes/header.php';
?>

<h2>Kelola Kategori</h2>
<?= tampil_flash() ?>

<div class="card" style="max-width:420px; margin-bottom:24px;">
  <form method="post">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?>
      <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
    <?php endif; ?>

    <label for="nama"><?= $edit ? 'Ubah nama kategori' : 'Tambah kategori baru' ?></label>
    <input type="text" id="nama" name="nama" maxlength="50" required value="<?= e($edit['nama'] ?? '') ?>">
    <br><br>
    <button class="btn" type="submit"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
    <?php if ($edit): ?>
      <a href="<?= BASE_URL ?>admin/kategori.php" class="btn btn-warning">Batal</a>
    <?php endif; ?>
  </form>
</div>

<table>
  <tr>
    <th>Nama Kategori</th>
    <th>Jumlah Makanan</th>
    <th>Aksi</th>
  </tr>
  <?php while ($row = mysqli_fetch_assoc($daftar)): ?>
    <tr>
      <td><?= e($row['nama']) ?></td>
      <td><?= (int) $row['jumlah'] ?></td>
      <td>
        <a href="<?= BASE_URL ?>admin/kategori.php?edit=<?= (int) $row['id'] ?>" class="btn btn-warning">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Hapus kategori ini?');">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
          <button class="btn btn-danger" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>