<?php
require_once 'koneksi.php';

// Total porsi yang diklaim atau sudah diambil (tidak dibatalkan)
$stmtPorsi = $pdo->query("
    SELECT COALESCE(SUM(jumlah_klaim), 0) AS total_porsi_terselamatkan
    FROM klaim_makanan
    WHERE status IN ('diklaim', 'diambil')
");
$totalPorsi = (int) $stmtPorsi->fetchColumn();

// Total transaksi klaim aktif/berhasil
$stmtTotalKlaim = $pdo->query("
    SELECT COUNT(*) AS total_klaim
    FROM klaim_makanan
    WHERE status IN ('diklaim', 'diambil')
");
$totalKlaim = (int) $stmtTotalKlaim->fetchColumn();

// Sisa porsi makanan yang masih tersedia saat ini
$stmtSisa = $pdo->query("
    SELECT COALESCE(SUM(jumlah_porsi), 0) AS sisa_porsi_tersedia
    FROM makanan
    WHERE status = 'tersedia'
");
$sisaPorsi = (int) $stmtSisa->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dampak Makanan</title>
</head>
<body>
    <h1>Statistik Dampak Makanan Diselamatkan</h1>
    <ul>
        <li>Total Porsi Terselamatkan: <strong><?= htmlspecialchars((string) $totalPorsi) ?></strong> porsi</li>
        <li>Total Klaim Berhasil: <strong><?= htmlspecialchars((string) $totalKlaim) ?></strong> transaksi</li>
        <li>Porsi Masih Tersedia: <strong><?= htmlspecialchars((string) $sisaPorsi) ?></strong> porsi</li>
    </ul>
</body>
</html>