<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);
if (!$koneksi) {
    echo json_encode(["status" => "error", "message" => mysqli_connect_error()]);
    exit;
}

$jenis  = $_POST['jenis']  ?? '';
$nama   = $_POST['nama']   ?? '';
$lat    = $_POST['lat']    ?? '';
$lng    = $_POST['lng']    ?? '';
$radius = $_POST['radius'] ?? '';
$alamat = $_POST['alamat'] ?? '';

if ($jenis == '' || $lat == '' || $lng == '' || $radius == '') {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

$stmt = mysqli_prepare($koneksi,
    "INSERT INTO tempat_ibadah (jenis, nama, lat, lng, radius, alamat) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ssddis", $jenis, $nama, $lat, $lng, $radius, $alamat);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}

mysqli_close($koneksi);