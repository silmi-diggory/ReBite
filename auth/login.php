<?php
include_once "../includes/koneksi.php";
wajib_tamu();

$error = "";
$email = "";

if (isset($_POST['submit'])) {
    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    if ($email == "" || $password == "") {
        $error = "Email dan password wajib diisi.";
    } else {
        $email_db = mysqli_real_escape_string($koneksi, $email);
        $hasil = mysqli_query($koneksi,
            "SELECT id, nama, password, role, status_verifikasi FROM users WHERE email='$email_db'");
        $user = mysqli_fetch_assoc($hasil);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Email atau password salah.";
        } elseif ($user['status_verifikasi'] != "terverifikasi") {
            $error = "Akun Anda masih menunggu verifikasi admin.";
        } else {
            session_regenerate_id(true);
            $_SESSION['id_user'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            redirect(halaman_beranda($user['role']));
        }
    }
}

$judul_halaman = "Login";
include "../includes/header.php";
?>
<section class="form-wrap">
    <h2>Login</h2>
    <?= tampil_flash() ?>

    <?php if ($error != "") { ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php } ?>

    <form method="post" class="form" onSubmit="return cek()">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <br><br>
        <input type="submit" name="submit" value="Masuk" class="btn">
        <p>Belum punya akun? <a href="<?= BASE_URL ?>auth/register.php">Daftar</a></p>
    </form>
</section>

<script>
    function cek() {
        const email = document.getElementById("email");
        const password = document.getElementById("password");

        if (email.value == "") {
            alert("Email harus diisi!");
            email.focus();
            return false;
        }
        if (password.value == "") {
            alert("Password harus diisi!");
            password.focus();
            return false;
        }
        return true;
    }
</script>
<?php include "../includes/footer.php"; ?>