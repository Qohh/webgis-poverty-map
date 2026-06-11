<?php

$koneksi = mysqli_connect(
    "mysql",
    "mysql",
    "password123",
    "gis_kemiskinan",
    3306
);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}