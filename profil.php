<?php
require_once __DIR__ . '/includes/koneksi.php';
wajib_login();

$id = $_SESSION['id_user'];
$stmt = mysqli_prepare($koneksi,
    'SELECT nama, email, role, no_hp, alamat, status_verifikasi, created_at FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    // Akun sudah dihapus admin saat session masih aktif
    redirect('auth/logout.php');
}

$judul_halaman = 'Profil Saya';
include __DIR__ . '/includes/header.php';
?>
<section class="profil">
    <h1>Profil Saya</h1>
    <?= tampil_flash() ?>
    <table class="tabel-profil">
        <tr><th>Nama</th><td><?= e($user['nama']) ?></td></tr>
        <tr><th>Email</th><td><?= e($user['email']) ?></td></tr>
        <tr><th>Peran</th><td><?= e(ucfirst($user['role'])) ?></td></tr>
        <tr><th>No. HP</th><td><?= e($user['no_hp'] ?: '-') ?></td></tr>
        <tr><th>Alamat</th><td><?= nl2br(e($user['alamat'] ?: '-')) ?></td></tr>
        <tr><th>Status</th><td><?= e(ucfirst($user['status_verifikasi'])) ?></td></tr>
        <tr><th>Bergabung</th><td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td></tr>
    </table>
    <a href="<?= BASE_URL ?>profil_edit.php" class="btn">Ubah Profil</a>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>