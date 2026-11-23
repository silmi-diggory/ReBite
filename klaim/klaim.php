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

$penerimaId  = (int) $_SESSION['user_id'];
$makananId   = (int) ($_POST['makanan_id'] ?? 0);
$jumlahKlaim = max(1, (int) ($_POST['jumlah_klaim'] ?? 1));

if ($makananId <= 0) {
    die('Data makanan tidak valid.');
}

try {
    $pdo->beginTransaction();

    // Kurangi porsi hanya jika porsi cukup dan makanan masih tersedia
    $stmtUpdate = $pdo->prepare("
        UPDATE makanan
        SET jumlah_porsi = jumlah_porsi - :jumlah_klaim
        WHERE id = :makanan_id
          AND jumlah_porsi >= :jumlah_klaim_cek
    ");
    $stmtUpdate->execute([
        'jumlah_klaim'     => $jumlahKlaim,
        'makanan_id'       => $makananId,
        'jumlah_klaim_cek' => $jumlahKlaim,
    ]);

    if ($stmtUpdate->rowCount() === 0) {
        $pdo->rollBack();
        die('Maaf, porsi makanan tidak mencukupi atau sudah habis.');
    }

    // Jika sisa porsi menjadi 0, ubah status menjadi 'habis'
    $stmtStatus = $pdo->prepare("
        UPDATE makanan
        SET status = CASE WHEN jumlah_porsi <= 0 THEN 'habis' ELSE 'tersedia' END
        WHERE id = :makanan_id
    ");
    $stmtStatus->execute(['makanan_id' => $makananId]);

    // Catat riwayat klaim
    $stmtInsert = $pdo->prepare("
        INSERT INTO klaim_makanan (makanan_id, penerima_id, jumlah_klaim, status)
        VALUES (:makanan_id, :penerima_id, :jumlah_klaim, 'diklaim')
    ");
    $stmtInsert->execute([
        'makanan_id'  => $makananId,
        'penerima_id' => $penerimaId,
        'jumlah_klaim'=> $jumlahKlaim,
    ]);

    $pdo->commit();
    header('Location: riwayat_klaim.php?status=klaim_berhasil');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Terjadi kesalahan saat memproses klaim: ' . $e->getMessage());
}