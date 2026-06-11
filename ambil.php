<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_once 'config.php';

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