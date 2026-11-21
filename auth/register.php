<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_tamu();

$galat = [];
$nama = $email = $no_hp = $alamat = '';
$role = 'penerima';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    $role       = $_POST['role'] ?? '';
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');

    if ($nama === '' || mb_strlen($nama) > 100) {
        $galat[] = 'Nama wajib diisi (maksimal 100 karakter).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $galat[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $galat[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $galat[] = 'Konfirmasi password tidak sama.';
    }
    // Admin tidak boleh mendaftar lewat form ini
    if (!in_array($role, ['penyedia', 'penerima'], true)) {
        $galat[] = 'Pilih peran sebagai penyedia atau penerima.';
    }
    if ($no_hp !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $no_hp)) {
        $galat[] = 'Nomor HP tidak valid.';
    }
    if ($alamat === '') {
        $galat[] = 'Alamat wajib diisi.';
    }

    if (!$galat) {
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $ada = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($ada) {
            $galat[] = 'Email sudah terdaftar. Gunakan email lain atau login.';
        }
    }

    if (!$galat) {
        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $status = ($role === 'penyedia') ? 'pending' : 'terverifikasi';
        $hp     = ($no_hp === '') ? null : $no_hp;

        $stmt = mysqli_prepare($koneksi,
            'INSERT INTO users (nama, email, password, role, no_hp, alamat, status_verifikasi)
             VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssssss', $nama, $email, $hash, $role, $hp, $alamat, $status);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            if ($role === 'penyedia') {
                set_flash('success', 'Pendaftaran berhasil. Akun penyedia perlu diverifikasi admin sebelum bisa login.');
            } else {
                set_flash('success', 'Pendaftaran berhasil. Silakan login.');
            }
            redirect('auth/login.php');
        }
        mysqli_stmt_close($stmt);
        $galat[] = 'Pendaftaran gagal, coba lagi.';
    }
}

$judul_halaman = 'Daftar';
include __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap">
    <h1>Daftar Akun ReBite</h1>
    <?= tampil_flash() ?>
    <?php if ($galat): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <label for="nama">Nama (atau nama usaha)</label>
        <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" required maxlength="100">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required maxlength="100">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="konfirmasi">Konfirmasi password</label>
        <input type="password" id="konfirmasi" name="konfirmasi" required minlength="6">

        <label for="role">Daftar sebagai</label>
        <select id="role" name="role" required>
            <option value="penerima" <?= $role === 'penerima' ? 'selected' : '' ?>>Penerima (mencari makanan)</option>
            <option value="penyedia" <?= $role === 'penyedia' ? 'selected' : '' ?>>Penyedia (membagikan makanan)</option>
        </select>

        <label for="no_hp">Nomor HP</label>
        <input type="text" id="no_hp" name="no_hp" value="<?= e($no_hp) ?>" maxlength="20">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3" required><?= e($alamat) ?></textarea>

        <button type="submit" class="btn">Daftar</button>
        <p>Sudah punya akun? <a href="<?= BASE_URL ?>auth/login.php">Login</a></p>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
