<?php
$koneksi = mysqli_connect(
    "jo80kso4w8gswcsg48osc8k0",
    "mysql",
    "3SAW6x4yndARgn9WABLjx8bvo8u9tR4hLSHuHqosP4atyaCdBH8vCRtmqCC6Fg8J",
    "gis-kemiskinan",
    3306
);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>