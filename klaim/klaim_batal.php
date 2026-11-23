<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: riwayat_klaim.php');
    exit;
}

$penerimaId = (int) $_SESSION['user_id'];
$klaimId    = (int) ($_POST['klaim_id'] ?? 0);

if ($klaimId <= 0) {
    die('ID klaim tidak valid.');
}

try {
    $pdo->beginTransaction();

    // Ambil data klaim dan kunci barisnya
    $stmtKlaim = $pdo->prepare("
        SELECT id, makanan_id, jumlah_klaim, status
        FROM klaim_makanan
        WHERE id = :klaim_id AND penerima_id = :penerima_id
        FOR UPDATE
    ");
    $stmtKlaim->execute([
        'klaim_id'    => $klaimId,
        'penerima_id' => $penerimaId,
    ]);
    $klaim = $stmtKlaim->fetch();

    if (!$klaim) {
        $pdo->rollBack();
        die('Data klaim tidak ditemukan.');
    }

    if ($klaim['status'] !== 'diklaim') {
        $pdo->rollBack();
        die('Klaim ini sudah dibatalkan atau sudah selesai diproses.');
    }

    // Ubah status klaim menjadi dibatalkan
    $stmtBatal = $pdo->prepare("
        UPDATE klaim_makanan
        SET status = 'dibatalkan', updated_at = NOW()
        WHERE id = :klaim_id
    ");
    $stmtBatal->execute(['klaim_id' => $klaimId]);

    // Kembalikan porsi ke makanan dan aktifkan kembali status 'tersedia'
    $stmtKembali = $pdo->prepare("
        UPDATE makanan
        SET jumlah_porsi = jumlah_porsi + :jumlah_klaim,
            status = 'tersedia'
        WHERE id = :makanan_id
    ");
    $stmtKembali->execute([
        'jumlah_klaim' => $klaim['jumlah_klaim'],
        'makanan_id'   => $klaim['makanan_id'],
    ]);

    $pdo->commit();
    header('Location: riwayat_klaim.php?status=batal_berhasil');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Gagal membatalkan klaim: ' . $e->getMessage());
}