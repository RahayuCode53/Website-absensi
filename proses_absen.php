<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) die("Akses Ditolak");

$user_id = $_SESSION['user_id'];
$tipe = $_POST['tipe'] ?? '';
$location = $_POST['location'] ?? '';
$image = $_POST['image'] ?? '';
$mood = $_POST['mood'] ?? ''; // New Mood Variable
$today = date('Y-m-d');
$time = date('H:i:s');

// FIX ERROR LINE 16: Cek apakah gambar ada
if (empty($image)) die("Gagal: Foto tidak terkirim.");

$image_parts = explode(";base64,", $image);
if (!isset($image_parts[1])) die("Gagal: Format foto salah."); // Tambahan validasi keamanan

$image_base64 = base64_decode($image_parts[1]);
$filename = "absen_" . $tipe . "_" . $user_id . "_" . time() . ".png";
$dir = "uploads/foto_absen/";

if (!file_exists($dir)) mkdir($dir, 0777, true);

if (file_put_contents($dir . $filename, $image_base64)) {
    if ($tipe == 'masuk') {
        $q = "INSERT INTO absensi (user_id, tanggal, jam_masuk, mood_masuk, status, foto_masuk, lokasi_gps) 
              VALUES ('$user_id', '$today', '$time', '$mood', 'hadir', '$filename', '$location')";
    } else {
        $q = "UPDATE absensi SET jam_pulang = '$time', mood_pulang = '$mood', foto_pulang = '$filename' 
              WHERE user_id = '$user_id' AND tanggal = '$today'";
    }
    
    if (mysqli_query($conn, $q)) echo "success";
    else echo "Database Error: " . mysqli_error($conn);
} else {
    echo "Gagal simpan file ke folder.";
}
?>