<?php
include_once "includes/koneksi.php";
wajib_login();

$id = (int) $_SESSION['id_user'];
$error = array();

$hasil = mysqli_query($koneksi, "SELECT nama, email, no_hp, alamat, password FROM users WHERE id=$id");
$user = mysqli_fetch_assoc($hasil);

if (!$user) {
    redirect('auth/logout.php');
}

$nama   = $user['nama'];
$no_hp  = $user['no_hp'];
$alamat = $user['alamat'];

if (isset($_POST['submit'])) {
    $nama    = trim($_POST['nama']);
    $no_hp   = trim($_POST['no_hp']);
    $alamat  = trim($_POST['alamat']);
    $pw_lama = $_POST['password_lama'];
    $pw_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($nama == "") {
        $error[] = "Nama wajib diisi.";
    }
    if ($alamat == "") {
        $error[] = "Alamat wajib diisi.";
    }

    $ganti_password = false;
    if ($pw_lama != "" || $pw_baru != "" || $konfirmasi != "") {
        $ganti_password = true;
        if (!password_verify($pw_lama, $user['password'])) {
            $error[] = "Password lama salah.";
        }
        if (strlen($pw_baru) < 6) {
            $error[] = "Password baru minimal 6 karakter.";
        }
        if ($pw_baru != $konfirmasi) {
            $error[] = "Konfirmasi password baru tidak sama.";
        }
    }

    if (count($error) == 0) {
        $nama_db   = mysqli_real_escape_string($koneksi, $nama);
        $no_hp_db  = mysqli_real_escape_string($koneksi, $no_hp);
        $alamat_db = mysqli_real_escape_string($koneksi, $alamat);

        if ($no_hp == "") {
            $no_hp_sql = "NULL";
        } else {
            $no_hp_sql = "'$no_hp_db'";
        }

        $sql = "UPDATE users SET nama='$nama_db', no_hp=$no_hp_sql, alamat='$alamat_db'";
        if ($ganti_password) {
            $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
            $sql = $sql . ", password='$hash'";
        }
        $sql = $sql . " WHERE id=$id";

        $simpan = mysqli_query($koneksi, $sql);
        if ($simpan) {
            $_SESSION['nama'] = $nama;
            set_flash('success', 'Profil berhasil diperbarui.');
            redirect('profil.php');
        } else {
            $error[] = "Gagal menyimpan perubahan, silakan coba lagi.";
        }
    }
}

$judul_halaman = "Ubah Profil";
include "includes/header.php";
?>
<section class="form-wrap">
    <h2>Ubah Profil</h2>

    <?php if (count($error) > 0) { ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($error as $pesan) { ?>
                    <li><?= e($pesan) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="post" class="form">
        <label for="email">Email (tidak dapat diubah)</label>
        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" maxlength="100">

        <label for="no_hp">Nomor HP</label>
        <input type="text" id="no_hp" name="no_hp" value="<?= e($no_hp) ?>" maxlength="20">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3"><?= e($alamat) ?></textarea>

        <p><b>Ganti password</b> (kosongkan jika tidak ingin mengganti)</p>

        <label for="password_lama">Password lama</label>
        <input type="password" id="password_lama" name="password_lama">

        <label for="password_baru">Password baru</label>
        <input type="password" id="password_baru" name="password_baru">

        <label for="konfirmasi">Konfirmasi password baru</label>
        <input type="password" id="konfirmasi" name="konfirmasi">

        <br><br>
        <input type="submit" name="submit" value="Simpan" class="btn">
        <a href="<?= BASE_URL ?>profil.php">Batal</a>
    </form>
</section>
<?php include "includes/footer.php"; ?>