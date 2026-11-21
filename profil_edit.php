<?php
require_once __DIR__ . '/includes/koneksi.php';
wajib_login();

$id = $_SESSION['id_user'];
$galat = [];

$stmt = mysqli_prepare($koneksi, 'SELECT nama, email, no_hp, alamat, password FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    redirect('auth/logout.php');
}

$nama   = $user['nama'];
$no_hp  = $user['no_hp'] ?? '';
$alamat = $user['alamat'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $pw_lama    = $_POST['password_lama'] ?? '';
    $pw_baru    = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || mb_strlen($nama) > 100) {
        $galat[] = 'Nama wajib diisi (maksimal 100 karakter).';
    }
    if ($no_hp !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $no_hp)) {
        $galat[] = 'Nomor HP tidak valid.';
    }
    if ($alamat === '') {
        $galat[] = 'Alamat wajib diisi.';
    }

    // Ganti password bersifat opsional
    $ganti_password = ($pw_lama !== '' || $pw_baru !== '' || $konfirmasi !== '');
    if ($ganti_password) {
        if (!password_verify($pw_lama, $user['password'])) {
            $galat[] = 'Password lama salah.';
        }
        if (strlen($pw_baru) < 6) {
            $galat[] = 'Password baru minimal 6 karakter.';
        }
        if ($pw_baru !== $konfirmasi) {
            $galat[] = 'Konfirmasi password baru tidak sama.';
        }
    }

    if (!$galat) {
        $hp = ($no_hp === '') ? null : $no_hp;

        if ($ganti_password) {
            $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi,
                'UPDATE users SET nama = ?, no_hp = ?, alamat = ?, password = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $hp, $alamat, $hash, $id);
        } else {
            $stmt = mysqli_prepare($koneksi,
                'UPDATE users SET nama = ?, no_hp = ?, alamat = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'sssi', $nama, $hp, $alamat, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['nama'] = $nama;
            set_flash('success', 'Profil berhasil diperbarui.');
            redirect('profil.php');
        }
        mysqli_stmt_close($stmt);
        $galat[] = 'Gagal menyimpan perubahan, coba lagi.';
    }
}

$judul_halaman = 'Ubah Profil';
include __DIR__ . '/includes/header.php';
?>
<section class="form-wrap">
    <h1>Ubah Profil</h1>
    <?php if ($galat): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <label for="email">Email (tidak dapat diubah)</label>
        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" required maxlength="100">

        <label for="no_hp">Nomor HP</label>
        <input type="text" id="no_hp" name="no_hp" value="<?= e($no_hp) ?>" maxlength="20">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3" required><?= e($alamat) ?></textarea>

        <fieldset>
            <legend>Ganti password (kosongkan jika tidak ingin mengganti)</legend>
            <label for="password_lama">Password lama</label>
            <input type="password" id="password_lama" name="password_lama" autocomplete="current-password">

            <label for="password_baru">Password baru</label>
            <input type="password" id="password_baru" name="password_baru" autocomplete="new-password">

            <label for="konfirmasi">Konfirmasi password baru</label>
            <input type="password" id="konfirmasi" name="konfirmasi" autocomplete="new-password">
        </fieldset>

        <button type="submit" class="btn">Simpan</button>
        <a href="<?= BASE_URL ?>profil.php">Batal</a>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>