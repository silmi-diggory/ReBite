<?php
require_once __DIR__ . '/../includes/koneksi.php';
wajib_tamu();

$galat = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $galat = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi,
            'SELECT id, nama, password, role, status_verifikasi FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        if (!$user || !password_verify($password, $user['password'])) {
            $galat = 'Email atau password salah.';
        } elseif ($user['status_verifikasi'] !== 'terverifikasi') {
            $galat = 'Akun Anda masih menunggu verifikasi admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['id_user'] = (int) $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            redirect(halaman_beranda($user['role']));
        }
    }
}

$judul_halaman = 'Login';
include __DIR__ . '/../includes/header.php';
?>
<section class="form-wrap">
    <h1>Login</h1>
    <?= tampil_flash() ?>
    <?php if ($galat): ?>
        <div class="alert alert-error"><?= e($galat) ?></div>
    <?php endif; ?>

    <form method="post" class="form">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn">Masuk</button>
        <p>Belum punya akun? <a href="<?= BASE_URL ?>auth/register.php">Daftar</a></p>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>