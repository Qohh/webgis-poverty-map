<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_once 'config.php';

$result = mysqli_query($koneksi, "SELECT * FROM rumah");
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    if (is_admin()) {
        // Admin: semua field
        $data[] = [
            "id"         => (int)$row['id'],
            "lat"        => (float)$row['lat'],
            "lng"        => (float)$row['lng'],
            "status"     => $row['status'],
            "alamat"     => $row['alamat'] ?? '',
            "nik"        => $row['nik'] ?? '',
            "nama"       => $row['nama'] ?? '',
            "ttl"        => $row['ttl'] ?? '',
            "pendidikan" => $row['pendidikan'] ?? '',
        ];
    } else {
        // Guest / user biasa: nama, NIK, alamat, status
        $data[] = [
            "id"     => (int)$row['id'],
            "lat"    => (float)$row['lat'],
            "lng"    => (float)$row['lng'],
            "status" => $row['status'],
            "alamat" => $row['alamat'] ?? '',
            "nik"    => $row['nik'] ?? '',
            "nama"   => $row['nama'] ?? '',
        ];
    }
}

echo json_encode($data);
mysqli_close($koneksi);