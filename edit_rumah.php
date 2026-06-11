<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);

$id         = (int)($_POST['id']         ?? 0);
$status     = $_POST['status']           ?? '';
$nik        = $_POST['nik']              ?? '';
$nama       = $_POST['nama']             ?? '';
$ttl        = $_POST['ttl']              ?? '';
$pendidikan = $_POST['pendidikan']       ?? '';

if ($id == 0) {
    echo json_encode(["status" => "error", "message" => "ID tidak valid"]);
    exit;
}

if (!in_array($status, ['miskin', 'tidak_miskin'])) {
    echo json_encode(["status" => "error", "message" => "Status tidak valid"]);
    exit;
}

$stmt = mysqli_prepare($koneksi,
    "UPDATE rumah SET status=?, nik=?, nama=?, ttl=?, pendidikan=? WHERE id=?"
);
mysqli_stmt_bind_param($stmt, "sssssi", $status, $nik, $nama, $ttl, $pendidikan, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}

mysqli_close($koneksi);