<?php
$host = "localhost";
$user = "root";      // Default XAMPP
$pass = "";          // Default XAMPP kosong
$db   = "db_absensi_ptpn";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>