<?php
include_once "includes/koneksi.php";
wajib_login();

$id = (int) $_SESSION['id_user'];
$hasil = mysqli_query($koneksi,
    "SELECT nama, email, role, no_hp, alamat, status_verifikasi, created_at FROM users WHERE id=$id");
$user = mysqli_fetch_assoc($hasil);

if (!$user) {
    redirect('auth/logout.php');
}

function isi($teks)
{
    if ($teks == "") {
        return "-";
    }
    return e($teks);
}

$judul_halaman = "Profil Saya";
include "includes/header.php";
?>
<section class="profil">
    <h2>Profil Saya</h2>
    <?= tampil_flash() ?>

    <table class="tabel-profil">
        <tr><th>Nama</th><td><?= isi($user['nama']) ?></td></tr>
        <tr><th>Email</th><td><?= isi($user['email']) ?></td></tr>
        <tr><th>Peran</th><td><?= isi(ucfirst($user['role'])) ?></td></tr>
        <tr><th>No. HP</th><td><?= isi($user['no_hp']) ?></td></tr>
        <tr><th>Alamat</th><td><?= isi($user['alamat']) ?></td></tr>
        <tr><th>Status</th><td><?= isi(ucfirst($user['status_verifikasi'])) ?></td></tr>
        <tr><th>Bergabung</th><td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td></tr>
    </table>
    <br>
    <a href="<?= BASE_URL ?>profil_edit.php" class="btn">Ubah Profil</a>
</section>
<?php include "includes/footer.php"; ?>