<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_admin_api();
require_once 'config.php';

$id = (int)($_POST['id'] ?? 0);
if ($id == 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM tempat_ibadah WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

mysqli_close($koneksi);