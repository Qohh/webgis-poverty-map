<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);
if (!$koneksi) {
    echo json_encode(["status" => "error", "message" => mysqli_connect_error()]);
    exit;
}

$lat        = $_POST['lat']        ?? '';
$lng        = $_POST['lng']        ?? '';
$status     = $_POST['status']     ?? 'miskin';
$alamat     = $_POST['alamat']     ?? '';
$nik        = $_POST['nik']        ?? '';
$nama       = $_POST['nama']       ?? '';
$ttl        = $_POST['ttl']        ?? '';
$pendidikan = $_POST['pendidikan'] ?? '';

if ($lat == '' || $lng == '' || $nik == '' || $nama == '') {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap (NIK dan Nama wajib diisi)"]);
    exit;
}

if (!in_array($status, ['miskin', 'tidak_miskin'])) {
    $status = 'miskin';
}

$stmt = mysqli_prepare($koneksi,
    "INSERT INTO rumah (lat, lng, status, alamat, nik, nama, ttl, pendidikan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ddssssss", $lat, $lng, $status, $alamat, $nik, $nama, $ttl, $pendidikan);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "id" => mysqli_insert_id($koneksi)]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}

mysqli_close($koneksi);