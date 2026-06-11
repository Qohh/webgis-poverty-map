<?php
header('Content-Type: application/json');
require_once 'auth.php';
// Semua boleh akses (admin, user login, maupun guest)

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);
if (!$koneksi) {
    echo json_encode(["status" => "error", "message" => mysqli_connect_error()]);
    exit;
}

$result = mysqli_query($koneksi, "SELECT * FROM tempat_ibadah");
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        "id"     => (int)$row['id'],
        "jenis"  => $row['jenis'],
        "nama"   => $row['nama'] ?? '',
        "lat"    => (float)$row['lat'],
        "lng"    => (float)$row['lng'],
        "radius" => (int)$row['radius'],
        "alamat" => $row['alamat'] ?? ''
    ];
}

echo json_encode($data);
mysqli_close($koneksi);