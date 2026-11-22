<?php
include_once "../includes/koneksi.php";
wajib_tamu();

$error  = array();
$nama   = "";
$email  = "";
$no_hp  = "";
$alamat = "";
$role   = "penerima";

if (isset($_POST['submit'])) {
    $nama       = trim($_POST['nama']);
    $email      = strtolower(trim($_POST['email']));
    $password   = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $role       = $_POST['role'];
    $no_hp      = trim($_POST['no_hp']);
    $alamat     = trim($_POST['alamat']);

    // Validasi
    if ($nama == "") {
        $error[] = "Nama wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Format email tidak valid.";
    }
    if (strlen($password) < 6) {
        $error[] = "Password minimal 6 karakter.";
    }
    if ($password != $konfirmasi) {
        $error[] = "Konfirmasi password tidak sama.";
    }
    if ($role != "penyedia" && $role != "penerima") {
        $error[] = "Pilih peran sebagai penyedia atau penerima.";
    }
    if ($alamat == "") {
        $error[] = "Alamat wajib diisi.";
    }

    $nama_db   = mysqli_real_escape_string($koneksi, $nama);
    $email_db  = mysqli_real_escape_string($koneksi, $email);
    $no_hp_db  = mysqli_real_escape_string($koneksi, $no_hp);
    $alamat_db = mysqli_real_escape_string($koneksi, $alamat);

    if (count($error) == 0) {
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email_db'");
        if (mysqli_num_rows($cek) > 0) {
            $error[] = "Email sudah terdaftar. Gunakan email lain atau login.";
        }
    }

    if (count($error) == 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($role == "penyedia") {
            $status = "pending";
        } else {
            $status = "terverifikasi";
        }

        if ($no_hp == "") {
            $no_hp_sql = "NULL";
        } else {
            $no_hp_sql = "'$no_hp_db'";
        }

        $simpan = mysqli_query($koneksi,
            "INSERT INTO users (nama, email, password, role, no_hp, alamat, status_verifikasi)
             VALUES ('$nama_db', '$email_db', '$hash', '$role', $no_hp_sql, '$alamat_db', '$status')");

        if ($simpan) {
            if ($role == "penyedia") {
                set_flash('success', 'Pendaftaran berhasil. Akun penyedia perlu diverifikasi admin sebelum bisa login.');
            } else {
                set_flash('success', 'Pendaftaran berhasil. Silakan login.');
            }
            redirect('auth/login.php');
        } else {
            $error[] = "Pendaftaran gagal, silakan coba lagi.";
        }
    }
}

$judul_halaman = "Daftar";
include "../includes/header.php";
?>
<section class="form-wrap">
    <h2>Daftar Akun ReBite</h2>

    <?php if (count($error) > 0) { ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($error as $pesan) { ?>
                    <li><?= e($pesan) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="post" class="form" onSubmit="return cek()">
        <label for="nama">Nama (atau nama usaha)</label>
        <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" maxlength="100">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="100">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <label for="konfirmasi">Konfirmasi password</label>
        <input type="password" id="konfirmasi" name="konfirmasi">

        <label for="role">Daftar sebagai</label>
        <select id="role" name="role">
            <option value="penerima" <?php if ($role == "penerima") { echo "selected"; } ?>>Penerima (mencari makanan)</option>
            <option value="penyedia" <?php if ($role == "penyedia") { echo "selected"; } ?>>Penyedia (membagikan makanan)</option>
        </select>

        <label for="no_hp">Nomor HP</label>
        <input type="text" id="no_hp" name="no_hp" value="<?= e($no_hp) ?>" maxlength="20">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3"><?= e($alamat) ?></textarea>

        <br><br>
        <input type="submit" name="submit" value="Daftar" class="btn">
        <p>Sudah punya akun? <a href="<?= BASE_URL ?>auth/login.php">Login</a></p>
    </form>
</section>

<script>
    function cek() {
        const nama = document.getElementById("nama");
        const email = document.getElementById("email");
        const password = document.getElementById("password");
        const konfirmasi = document.getElementById("konfirmasi");
        const alamat = document.getElementById("alamat");

        if (nama.value == "") {
            alert("Nama harus diisi!");
            nama.focus();
            return false;
        }
        if (email.value == "") {
            alert("Email harus diisi!");
            email.focus();
            return false;
        }
        if (password.value.length < 6) {
            alert("Password minimal 6 karakter!");
            password.focus();
            return false;
        }
        if (password.value != konfirmasi.value) {
            alert("Konfirmasi password tidak sama!");
            konfirmasi.focus();
            return false;
        }
        if (alamat.value == "") {
            alert("Alamat harus diisi!");
            alamat.focus();
            return false;
        }
        return true;
    }
</script>
<?php include "../includes/footer.php"; ?>