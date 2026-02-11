<?php
include 'includes/db.php';

$query = "ALTER TABLE absensi 
ADD COLUMN mood_masuk VARCHAR(50) NULL AFTER jam_masuk,
ADD COLUMN mood_pulang VARCHAR(50) NULL AFTER jam_pulang";

if (mysqli_query($conn, $query)) {
    echo "Berhasil menambahkan kolom mood_masuk dan mood_pulang.";
} else {
    echo "Gagal atau kolom sudah ada: " . mysqli_error($conn);
}
?>
