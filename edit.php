<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();
require_once 'config.php';

$id     = (int)($_POST['id']     ?? 0);
$radius = (int)($_POST['radius'] ?? 0);

if ($id == 0) {
    echo json_encode(["status" => "error", "message" => "ID tidak valid"]);
    exit;
}

if (isset($_POST['_only_radius'])) {
    $stmt = mysqli_prepare($koneksi, "UPDATE tempat_ibadah SET radius=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ii", $radius, $id);
} else {
    $jenis = $_POST['jenis'] ?? '';
    $nama  = $_POST['nama']  ?? '';
    $stmt  = mysqli_prepare($koneksi,
        "UPDATE tempat_ibadah SET jenis=?, nama=?, radius=? WHERE id=?"
    );
    mysqli_stmt_bind_param($stmt, "ssii", $jenis, $nama, $radius, $id);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}

mysqli_close($koneksi);