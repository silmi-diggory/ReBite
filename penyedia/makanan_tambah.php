<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_role(['penyedia']);

$idPenyedia = (int) $_SESSION['id_user'];

// Pengaturan upload foto
$folderFoto = __DIR__ . '/../assets/img/makanan/';
$maxFoto    = 2 * 1024 * 1024; // 2 MB
$tipeOk     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Alamat penyedia dipakai sebagai lokasi bawaan
$alamat = '';
$stmt = mysqli_prepare($koneksi, "SELECT alamat FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $idPenyedia);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $alamatDb);
if (mysqli_stmt_fetch($stmt)) {
    $alamat = (string) $alamatDb;
}
mysqli_stmt_close($stmt);

$nilai = [
    'nama'        => '',
    'id_kategori' => 0,
    'deskripsi'   => '',
    'porsi_total' => '',
    'lokasi'      => $alamat,
    'batas_ambil' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai['nama']        = trim($_POST['nama'] ?? '');
    $nilai['id_kategori'] = (int) ($_POST['id_kategori'] ?? 0);
    $nilai['deskripsi']   = trim($_POST['deskripsi'] ?? '');
    $nilai['porsi_total'] = trim($_POST['porsi_total'] ?? '');
    $nilai['lokasi']      = trim($_POST['lokasi'] ?? '');
    $nilai['batas_ambil'] = trim($_POST['batas_ambil'] ?? '');

    // Validasi
    if ($nilai['nama'] === '') {
        $errors[] = 'Nama makanan wajib diisi.';
    } elseif (mb_strlen($nilai['nama']) > 100) {
        $errors[] = 'Nama makanan maksimal 100 karakter.';
    }

    $cek = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE id = ?");
    mysqli_stmt_bind_param($cek, 'i', $nilai['id_kategori']);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);
    if (mysqli_stmt_num_rows($cek) === 0) {
        $errors[] = 'Pilih kategori yang valid.';
    }
    mysqli_stmt_close($cek);

    if (!ctype_digit($nilai['porsi_total']) || (int) $nilai['porsi_total'] < 1 || (int) $nilai['porsi_total'] > 500) {
        $errors[] = 'Jumlah porsi harus berupa angka 1 sampai 500.';
    }

    if ($nilai['lokasi'] === '') {
        $errors[] = 'Lokasi pengambilan wajib diisi.';
    } elseif (mb_strlen($nilai['lokasi']) > 255) {
        $errors[] = 'Lokasi maksimal 255 karakter.';
    }

    $batas = DateTime::createFromFormat('Y-m-d\TH:i', $nilai['batas_ambil']);
    if (!$batas || $batas->format('Y-m-d\TH:i') !== $nilai['batas_ambil']) {
        $errors[] = 'Batas waktu ambil tidak valid.';
    } elseif ($batas->getTimestamp() <= time()) {
        $errors[] = 'Batas waktu ambil harus di masa depan.';
    }

    // Validasi foto (opsional)
    $fileFoto = $_FILES['foto'] ?? null;
    $adaFoto  = $fileFoto && $fileFoto['error'] !== UPLOAD_ERR_NO_FILE;
    $ekstFoto = null;
    if ($adaFoto) {
        if ($fileFoto['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Foto gagal diunggah. Coba file lain.';
        } elseif ($fileFoto['size'] > $maxFoto) {
            $errors[] = 'Ukuran foto maksimal 2 MB.';
        } else {
            $info = @getimagesize($fileFoto['tmp_name']);
            $mime = $info['mime'] ?? '';
            if (!isset($tipeOk[$mime])) {
                $errors[] = 'Foto harus berformat JPG, PNG, atau WEBP.';
            } else {
                $ekstFoto = $tipeOk[$mime];
            }
        }
    }

    // Simpan file foto kalau semua valid
    $namaFoto = null;
    if (empty($errors) && $ekstFoto) {
        if (!is_dir($folderFoto)) {
            mkdir($folderFoto, 0755, true);
        }
        $namaFoto = bin2hex(random_bytes(8)) . '.' . $ekstFoto;
        if (!move_uploaded_file($fileFoto['tmp_name'], $folderFoto . $namaFoto)) {
            $errors[] = 'Foto gagal disimpan di server.';
            $namaFoto = null;
        }
    }

    // Simpan data ke database
    if (empty($errors)) {
        $porsi     = (int) $nilai['porsi_total'];
        $deskripsi = $nilai['deskripsi'] === '' ? null : $nilai['deskripsi'];
        $batasSql  = $batas->format('Y-m-d H:i:s');

        $stmt = mysqli_prepare($koneksi, "INSERT INTO makanan (id_penyedia, id_kategori, nama, deskripsi, foto, porsi_total, porsi_tersisa, lokasi, batas_ambil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iisssiiss',
            $idPenyedia, $nilai['id_kategori'], $nilai['nama'], $deskripsi, $namaFoto,
            $porsi, $porsi, $nilai['lokasi'], $batasSql
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Makanan berhasil diposting.');
            redirect(file_exists(__DIR__ . '/makanan_saya.php')
                ? 'penyedia/makanan_saya.php'
                : 'penyedia/makanan_tambah.php');
        }
        mysqli_stmt_close($stmt);
        if ($namaFoto) {
            @unlink($folderFoto . $namaFoto); // hapus foto kalau simpan data gagal
        }
        $errors[] = 'Gagal menyimpan makanan. Coba lagi.';
    }
}

$kategori = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama");

$judul_halaman = 'Posting Makanan';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-wrap" style="max-width:560px">
  <h2>Posting Makanan</h2>
  <?= tampil_flash() ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $err): ?>
        <div><?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form class="form" method="post" enctype="multipart/form-data">
    <label for="nama">Nama makanan</label>
    <input type="text" id="nama" name="nama" maxlength="100" required value="<?= e($nilai['nama']) ?>">

    <label for="id_kategori">Kategori</label>
    <select id="id_kategori" name="id_kategori" required>
      <option value="">-- Pilih kategori --</option>
      <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
        <option value="<?= (int) $k['id'] ?>" <?= (int) $k['id'] === (int) $nilai['id_kategori'] ? 'selected' : '' ?>>
          <?= e($k['nama']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <label for="deskripsi">Deskripsi (opsional)</label>
    <textarea id="deskripsi" name="deskripsi" rows="3"><?= e($nilai['deskripsi']) ?></textarea>

    <label for="foto">Foto makanan (opsional, JPG/PNG/WEBP, maks. 2 MB)</label>
    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">

    <label for="porsi_total">Jumlah porsi</label>
    <input type="number" id="porsi_total" name="porsi_total" min="1" max="500" required value="<?= e($nilai['porsi_total']) ?>">

    <label for="lokasi">Lokasi pengambilan</label>
    <input type="text" id="lokasi" name="lokasi" maxlength="255" required value="<?= e($nilai['lokasi']) ?>">

    <label for="batas_ambil">Batas waktu ambil</label>
    <input type="datetime-local" id="batas_ambil" name="batas_ambil" min="<?= date('Y-m-d\TH:i') ?>" required value="<?= e($nilai['batas_ambil']) ?>">

    <br><br>
    <button class="btn" type="submit">Posting</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>