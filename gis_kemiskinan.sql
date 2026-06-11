-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Waktu pembuatan: 11 Jun 2026 pada 12.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gis_kemiskinan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `rumah`
--

CREATE TABLE `rumah` (
  `id` int(11) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `status` enum('miskin','tidak_miskin') DEFAULT 'miskin',
  `alamat` text DEFAULT NULL,
  `nik` varchar(16) DEFAULT '',
  `nama` varchar(100) DEFAULT '',
  `ttl` varchar(100) DEFAULT '',
  `pendidikan` varchar(50) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rumah`
--

INSERT INTO `rumah` (`id`, `lat`, `lng`, `status`, `alamat`, `nik`, `nama`, `ttl`, `pendidikan`) VALUES
(2, -0.024156202282737894, 109.32903081178668, 'miskin', 'Gang Bayan, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '', '', '', ''),
(3, -0.024164252873410178, 109.32896912097931, 'miskin', 'Gang Bayan, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '', '', '', ''),
(4, -0.02418035405476747, 109.32888865470888, 'tidak_miskin', 'Gang Bayan, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '', '', '', ''),
(5, -0.024306479975143014, 109.32889670133592, 'miskin', 'Gang Belibis, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '', '', '', ''),
(6, -0.024523845922785795, 109.32900398969652, 'tidak_miskin', 'Gang Belibis, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '', '', '', ''),
(8, -0.03882407834072974, 109.33314800262453, 'miskin', 'Jalan Ahmad Marzuki, Akcaya, Pontianak Selatan, Pontianak, West Kalimantan, Kalimantan, 78117, Indonesia', '', '', '', ''),
(9, -0.0241514282241702, 109.32919442653657, 'miskin', 'Gang Bayan, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', '1111111111111111', 'Ahmad', 'Pontianak, 02 Januari 2026', 'SMA/Sederajat'),
(10, -0.04244148341739716, 109.33556199073793, 'tidak_miskin', 'Jalan Syuhada, Akcaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 78121, Indonesia', '1111111111111112', 'A', 'Pontianak, 1 Januari 2000', 'S1'),
(11, -0.042548771748473165, 109.33563709259035, 'miskin', 'Jalan Syuhada, Akcaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 78121, Indonesia', '1111111111111113', 'B', 'Pontianak, 1 Januari 2001', 'Tidak Sekolah'),
(12, -0.0388312309510227, 109.33321237564088, 'miskin', 'Jalan Ahmad Marzuki, Akcaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 78117, Indonesia', '1111111111111114', 'C', 'Pontianak, 1 Januari 2002', 'SMP/Sederajat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tempat_ibadah`
--

CREATE TABLE `tempat_ibadah` (
  `id` int(11) NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `radius` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `alamat` text DEFAULT NULL,
  `nama` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tempat_ibadah`
--

INSERT INTO `tempat_ibadah` (`id`, `jenis`, `lat`, `lng`, `radius`, `created_at`, `alamat`, `nama`) VALUES
(11, 'Masjid', -0.024344299151599625, 109.32909250259401, 200, '2026-05-06 01:54:48', 'Gang Belibis, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78244, Indonesia', ''),
(12, 'Masjid', -0.04147410027844799, 109.33633446693422, 500, '2026-05-06 03:36:43', 'Mujahiddin, Jalan Mujahidin, Akcaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 78121, Indonesia', ''),
(13, 'Masjid', -0.05705236443070954, 109.34619426727296, 300, '2026-05-06 03:41:43', 'Universitas Tanjungpura, Jalan Reformasi Untan, Bansir Laut, Pontianak Tenggara, Pontianak, Kalimantan Barat, Kalimantan, 79124, Indonesia', ''),
(14, 'Masjid', -0.05461334353835791, 109.34729933738708, 300, '2026-05-06 03:42:14', 'Masjid Polnep, Jalan Ahmad Yani, Bansir Laut, Pontianak Tenggara, Pontianak, Kalimantan Barat, Kalimantan, 71127, Indonesia', ''),
(15, 'Masjid', -0.05328654474069965, 109.3503624200821, 300, '2026-05-06 03:42:51', 'Universitas Tanjungpura, Vigor Sport Center, Parit Tokaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 71127, Indonesia', ''),
(16, 'Masjid', -0.058347870748418774, 109.35142725706102, 300, '2026-05-06 03:43:37', 'Al-Azhar, Jalan Sepakat 2, Bansir Darat, Pontianak Tenggara, Pontianak, Kalimantan Barat, Kalimantan, 79124, Indonesia', ''),
(17, 'Masjid', -0.05907653714139404, 109.35188591480257, 300, '2026-05-06 03:43:55', 'Toss, Jalan Ahmad Yani, Bansir Darat, Pontianak Tenggara, Pontianak, Kalimantan Barat, Kalimantan, 79124, Indonesia', ''),
(18, 'Masjid', -0.060203064317339586, 109.34991180896759, 300, '2026-05-06 03:46:06', 'Bansir Laut, Pontianak Tenggara, Pontianak, Kalimantan Barat, Kalimantan, 79124, Indonesia', ''),
(19, 'Gereja', -0.05240141614224753, 109.34470295906068, 300, '2026-05-06 03:48:02', 'Realme Service Center, Vigor Sport Center, Parit Tokaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 71127, Indonesia', ''),
(20, 'Gereja', -0.051046007065574835, 109.34300243854524, 300, '2026-05-06 03:48:55', 'Sushi Phe, Vigor Sport Center, Parit Tokaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 71127, Indonesia', ''),
(21, 'Gereja', -0.050502412925482874, 109.34434890747072, 300, '2026-05-06 03:49:38', 'Jalan Ahmad Yani, Parit Tokaya, Pontianak Selatan, Pontianak, Kalimantan Barat, Kalimantan, 71127, Indonesia', ''),
(22, 'Pura', -0.06357191687433154, 109.36737298965456, 300, '2026-05-06 03:51:17', 'Sungai Raya, Kubu Raya, Kalimantan Barat, Kalimantan, 78122, Indonesia', ''),
(23, 'Masjid', -0.02381954295837788, 109.33475732803345, 300, '2026-05-22 05:54:11', 'Jalan Nurali, Tengah, Pontianak Kota, Pontianak, West Kalimantan, Kalimantan, 78112, Indonesia', 'Masjid A');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$Lz6hPjGETnmDrr0VoXwMPugAILtnXybHnakZziDmiodbcIl3kDoUW', 'admin', '2026-05-22 05:43:29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `rumah`
--
ALTER TABLE `rumah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tempat_ibadah`
--
ALTER TABLE `tempat_ibadah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `rumah`
--
ALTER TABLE `rumah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tempat_ibadah`
--
ALTER TABLE `tempat_ibadah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
