<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "gis_kemiskinan", 3307);

if ($conn) {
    echo "Koneksi berhasil!";
} else {
    echo "Gagal koneksi!";
}
?>