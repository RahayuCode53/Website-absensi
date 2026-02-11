<?php
// Jalankan sekali lewat browser:
// http://localhost/absensi-ptpn/reset_pembimbing_passwords.php
// Setelah berhasil, HAPUS file ini demi keamanan.

include 'includes/db.php';

$akun = [
    ['username' => 'bobi',    'password' => 'ptpnoke'],
    ['username' => 'afrizal', 'password' => 'ptpn hebat'],
    ['username' => 'rini',    'password' => 'ptpn4'],
];

foreach ($akun as $a) {
    $user = mysqli_real_escape_string($conn, $a['username']);
    $hash = password_hash($a['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE pembimbing SET password = '$hash' WHERE username = '$user'";
    if (mysqli_query($conn, $sql)) {
        echo "Berhasil update password untuk {$a['username']}<br>";
    } else {
        echo "Gagal update {$a['username']}: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Selesai. Jika semua 'Berhasil', silakan HAPUS file ini.";
