-- ============================================================
-- ReBite: Rescue food, share happiness
-- Struktur database dan data contoh
--
-- Cara pakai: phpMyAdmin -> tab Import -> pilih file ini -> Go.
-- Script ini membuat database `rebite` sendiri, jadi tidak perlu
-- membuatnya manual terlebih dahulu. Aman dijalankan ulang
-- (tabel lama dihapus lalu dibuat ulang).
--
-- Akun contoh (password semua akun: rebite123)
--   admin    : admin@rebite.test
--   penyedia : ichiraku@rebite.test, totoro@rebite.test, kantin.ft@rebite.test
--              (kiki@rebite.test masih menunggu verifikasi)
--   penerima : naruto@rebite.test, mikasa@rebite.test, tanjiro@rebite.test,
--              hinata@rebite.test, zoro@rebite.test, nezuko@rebite.test
-- ============================================================

CREATE DATABASE IF NOT EXISTS `rebite`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `rebite`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `klaim`;
DROP TABLE IF EXISTS `makanan`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabel users
-- role               : admin, penyedia, penerima
-- status_verifikasi  : pending, terverifikasi
--   Penyedia baru berstatus pending sampai diverifikasi admin.
--   Admin dan penerima langsung terverifikasi.
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','penyedia','penerima') NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_verifikasi` enum('pending','terverifikasi') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabel kategori
-- ------------------------------------------------------------
CREATE TABLE `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kategori_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabel makanan (makanan sisa yang dibagikan penyedia)
-- status : tersedia, habis, kedaluwarsa
-- ------------------------------------------------------------
CREATE TABLE `makanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_penyedia` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `porsi_total` int(11) NOT NULL,
  `porsi_tersisa` int(11) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `batas_ambil` datetime NOT NULL,
  `status` enum('tersedia','habis','kedaluwarsa') NOT NULL DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_makanan_penyedia` (`id_penyedia`),
  KEY `idx_makanan_kategori` (`id_kategori`),
  CONSTRAINT `fk_makanan_penyedia` FOREIGN KEY (`id_penyedia`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_makanan_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabel klaim (penerima mengklaim makanan)
-- status : dipesan, diambil, dibatalkan
-- ------------------------------------------------------------
CREATE TABLE `klaim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_makanan` int(11) NOT NULL,
  `id_penerima` int(11) NOT NULL,
  `jumlah_porsi` int(11) NOT NULL,
  `status` enum('dipesan','diambil','dibatalkan') NOT NULL DEFAULT 'dipesan',
  `tanggal_klaim` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_klaim_makanan` (`id_makanan`),
  KEY `idx_klaim_penerima` (`id_penerima`),
  CONSTRAINT `fk_klaim_makanan` FOREIGN KEY (`id_makanan`) REFERENCES `makanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_klaim_penerima` FOREIGN KEY (`id_penerima`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- DATA CONTOH
-- ============================================================

-- Pengguna: 1 admin, 4 penyedia, 6 penerima (password: rebite123)
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `no_hp`, `alamat`, `status_verifikasi`) VALUES
(1,  'Kakashi Hatake',            'admin@rebite.test',      '$2y$10$xALqRyECIIj8YAVZr2W5J.082jlJs52HAsByQM/bV3.EXBo4Tf/W.', 'admin',    '081200000001', 'Purwokerto',                                   'terverifikasi'),
(2,  'Warung Ichiraku',          'ichiraku@rebite.test',       '$2y$10$RQj1m8p1VqChgtXowvX.yuafuSDJpfHdMGPApDvzhPLKYYv06fbIO', 'penyedia', '081200000002', 'Jl. Gerilya, Purwokerto Selatan',             'terverifikasi'),
(3,  'Katering Totoro', 'totoro@rebite.test',     '$2y$10$MtrbIS0coa2TfPLAjDmXYOtljrk50Lr4ZI/vd/weYRePh5HtBqUV2', 'penyedia', '081200000003', 'Jl. Dr. Suparno, Purwokerto Utara',          'terverifikasi'),
(4,  'Kantin Fakultas Teknik',  'kantin.ft@rebite.test',  '$2y$10$FnRJDCWtFy2bdSZHciwmV.JbesedlAX3/NAfdUwPFjBTCsBd1GcGa', 'penyedia', '081200000004', 'Kampus Universitas Jenderal Soedirman',        'terverifikasi'),
(5,  'Bakery Kiki',  'kiki@rebite.test','$2y$10$x5QNquPx5dd8LRF1ULKcEOxxghuaDs9KZpHcaVca5BNbPN8O1.olW', 'penyedia', '081200000005', 'Jl. Overste Isdiman, Purwokerto',            'pending'),
(6,  'Naruto Uzumaki',           'naruto@rebite.test',      '$2y$10$pOi1F3wzwdk93h/5HgDsqe3ZpYRYa5J9vYeVss45GsRGW8GwuUEVu', 'penerima', '081200000006', 'Kos Karangwangkal, Purwokerto',               'terverifikasi'),
(7,  'Mikasa Ackerman',             'mikasa@rebite.test',      '$2y$10$Hc4xeVniZ.wQkbxMhqZmUOKBCFmDlStcDInybqfgeMHETNtjiu5sm', 'penerima', '081200000007', 'Kos Grendeng, Purwokerto Utara',               'terverifikasi'),
(8,  'Tanjiro Kamado',           'tanjiro@rebite.test',      '$2y$10$5fDetNwdaeUB0MO0mZw8ReDFZeKQ.s6WWhUp5TEFe6GBKN8jaNQKW', 'penerima', '081200000008', 'Kos Bancarkembar, Purwokerto',                'terverifikasi'),
(9,  'Hinata Hyuga',            'hinata@rebite.test',     '$2y$10$vBwiTQfv793Jl6YQEhlUUOcU6G64iAtNYPuXaLHFRD2VlRrB2tqlG', 'penerima', '081200000009', 'Perumahan Berkoh, Purwokerto Selatan',         'terverifikasi'),
(10, 'Zoro Roronoa',           'zoro@rebite.test',      '$2y$10$J6Z2xkee6iW/1hJfZ9vJnOO99Vo.GXFTR7hhVjoZVPDxcfma5wX7C', 'penerima', '081200000010', 'Kos Pabuaran, Purwokerto Utara',               'terverifikasi'),
(11, 'Nezuko Kamado',           'nezuko@rebite.test',      '$2y$10$HxWhFAkiDyal.SaxtKI5vOanJQHhEVoaNDLm3TQU2VQJie9P4G5mG', 'penerima', '081200000011', 'Kos Dukuhwaluh, Purwokerto',                  'terverifikasi');

-- Kategori
INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'Makanan matang'),
(2, 'Bahan mentah'),
(3, 'Roti dan kue'),
(4, 'Makanan kemasan'),
(5, 'Minuman');

-- Makanan sisa (batas_ambil dibuat relatif terhadap waktu import
-- supaya data contoh selalu tampil sebagai "tersedia" saat demo)
INSERT INTO `makanan` (`id`, `id_penyedia`, `id_kategori`, `nama`, `deskripsi`, `porsi_total`, `porsi_tersisa`, `lokasi`, `batas_ambil`, `status`) VALUES
(1, 3, 1, 'Nasi Kotak Ayam Goreng',        'Kelebihan pesanan acara. Dimasak pagi ini, lauk ayam goreng, sambal, dan lalapan.',          20, 16, 'Jl. Dr. Suparno, Purwokerto Utara',      DATE_ADD(NOW(), INTERVAL 5 HOUR),  'tersedia'),
(2, 2, 1, 'Soto Ayam Porsi Siang',         'Soto ayam kuah hangat, sisa jualan siang. Sebaiknya segera dikonsumsi.',                      10,  9, 'Jl. Gerilya, Purwokerto Selatan',        DATE_ADD(NOW(), INTERVAL 3 HOUR),  'tersedia'),
(3, 2, 1, 'Gado-gado',                     'Sayur segar dengan bumbu kacang terpisah, dimasak hari ini.',                                  8,  8, 'Jl. Gerilya, Purwokerto Selatan',        DATE_ADD(NOW(), INTERVAL 4 HOUR),  'tersedia'),
(4, 4, 1, 'Nasi Rames Kantin',             'Nasi dengan sayur dan lauk, sisa jam makan siang.',                                            3,  0, 'Kantin Fakultas Teknik, Unsoed',          DATE_ADD(NOW(), INTERVAL 2 HOUR),  'habis'),
(5, 3, 3, 'Roti dan Pastry Sisa Hari Ini', 'Aneka roti manis dan pastry yang tidak terjual hari ini, masih baik dan lembut.',               15, 13, 'Jl. Dr. Suparno, Purwokerto Utara',      DATE_ADD(NOW(), INTERVAL 8 HOUR),  'tersedia'),
(6, 2, 2, 'Sayur Segar Mendekati Layu',    'Bayam, kangkung, dan sawi yang masih layak masak. Cocok dimasak hari ini juga.',               6,  6, 'Jl. Gerilya, Purwokerto Selatan',        DATE_ADD(NOW(), INTERVAL 24 HOUR), 'tersedia'),
(7, 4, 4, 'Mie Instan Kemasan Utuh',       'Stok mendekati batas jual, kemasan masih utuh dan tersegel.',                                 10, 10, 'Kantin Fakultas Teknik, Unsoed',          DATE_ADD(NOW(), INTERVAL 48 HOUR), 'tersedia'),
(8, 2, 1, 'Bubur Ayam Pagi',               'Postingan kemarin yang batas ambilnya sudah lewat. Contoh makanan berstatus kedaluwarsa.',      5,  5, 'Jl. Gerilya, Purwokerto Selatan',        DATE_SUB(NOW(), INTERVAL 1 DAY),   'kedaluwarsa');

-- Klaim (porsi_tersisa di tabel makanan sudah disesuaikan dengan klaim di bawah)
INSERT INTO `klaim` (`id`, `id_makanan`, `id_penerima`, `jumlah_porsi`, `status`, `tanggal_klaim`) VALUES
(1, 1, 6,  2, 'diambil',    DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 1, 7,  2, 'dipesan',    DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 2, 8,  1, 'diambil',    DATE_SUB(NOW(), INTERVAL 90 MINUTE)),
(4, 3, 9,  2, 'dibatalkan', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 4, 10, 3, 'diambil',    DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(6, 5, 11, 2, 'dipesan',    DATE_SUB(NOW(), INTERVAL 15 MINUTE));