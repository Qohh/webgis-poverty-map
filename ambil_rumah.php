<?php
header('Content-Type: application/json');
require_once 'auth.php';
// Semua boleh akses — data yang dikembalikan berbeda tergantung role

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);
if (!$koneksi) {
    echo json_encode(["status" => "error", "message" => mysqli_connect_error()]);
    exit;
}

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