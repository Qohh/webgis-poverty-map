<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();

$koneksi = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);

$id = (int)($_POST['id'] ?? 0);
if ($id == 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM rumah WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

mysqli_close($koneksi);