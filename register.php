<?php
include 'includes/db.php';

// Ambil daftar pembimbing untuk pilihan di form
$pembimbingResult = mysqli_query($conn, "SELECT id, nama FROM pembimbing ORDER BY nama");
if (!$pembimbingResult) {
    $pembimbingError = "Gagal memuat data pembimbing.";
}

if (isset($_POST['register'])) {
    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $instansi = mysqli_real_escape_string($conn, trim($_POST['instansi']));
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jurusan  = isset($_POST['jurusan']) && trim($_POST['jurusan']) !== '' ? mysqli_real_escape_string($conn, trim($_POST['jurusan'])) : '';
    $pembimbing_id = isset($_POST['pembimbing_id']) && $_POST['pembimbing_id'] !== '' 
        ? (int) $_POST['pembimbing_id'] 
        : null;

    // Siapkan nilai pembimbing_id untuk query (NULL jika tidak dipilih)
    $pembimbing_id_value = is_null($pembimbing_id) ? "NULL" : $pembimbing_id;

    $query = "INSERT INTO users (nama, username, password, instansi, kategori, jurusan, pembimbing_id) 
              VALUES ('$nama', '$username', '$password', '$instansi', '$kategori', '$jurusan', $pembimbing_id_value)";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registrasi Berhasil! Silakan Login'); window.location='login.php';</script>";
    } else {
        echo 'Error: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Magang | PTPN IV Regional 1</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #10b981 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            border: none;
            border-radius: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .register-sidebar {
            background: url('https://source.unsplash.com/random/800x800/?tea_plantation') no-repeat center center;
            background-size: cover;
            position: relative;
        }
        .register-sidebar::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(6, 78, 59, 0.6);
        }
        .form-control, .form-select {
            border-radius: 1rem;
            padding: 0.8rem 1.2rem;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        .btn-primary {
            background-color: #065f46;
            border: none;
            border-radius: 1rem;
            padding: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #047857;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container p-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-11 col-lg-9">
            <div class="card card-login bg-white">
                <div class="row g-0 flex-row-reverse">
                    <!-- Image Section (Visible on Tablet+) -->
                    <div class="col-md-5 d-none d-md-block register-sidebar">
                        <div class="position-relative z-1 h-100 d-flex flex-col justify-content-center align-items-center text-white p-5 text-center">
                            <div class="d-flex flex-column justify-content-center h-100">
                                <h2 class="fw-bold mb-2">Bergabunglah</h2>
                                <p class="small opacity-75">Daftarkan diri Anda sebagai peserta magang di PTPN IV Regional 1</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="col-12 col-md-7 p-5">
                        <div class="mb-4">
                            <h3 class="fw-bold text-success">Registrasi Akun</h3>
                            <p class="text-muted small">Lengkapi data diri Anda di bawah ini</p>
                        </div>

                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
                                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Asal Instansi</label>
                                    <input type="text" name="instansi" class="form-control" placeholder="Nama Kampus / Sekolah" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Jurusan / Program Studi</label>
                                    <input type="text" name="jurusan" class="form-control" placeholder="Contoh: Teknik Informatika" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Kategori</label>
                                    <select name="kategori" class="form-select">
                                        <option value="Universitas">Mahasiswa Universitas</option>
                                        <option value="SMK">Siswa SMK</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Pembimbing</label>
                                    <select name="pembimbing_id" class="form-select" required>
                                        <option value="">-- Pilih Pembimbing --</option>
                                        <?php if (isset($pembimbingResult) && $pembimbingResult): ?>
                                            <?php while ($p = mysqli_fetch_assoc($pembimbingResult)): ?>
                                                <option value="<?php echo $p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['nama']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <option value="">Data pembimbing belum tersedia</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <button name="register" class="btn btn-primary w-100 mt-4 mb-3">
                                Daftar Sekarang
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="small text-muted fw-bold mb-0">Sudah punya akun?</p>
                            <a href="login.php" class="small fw-bold text-success text-decoration-none text-uppercase">Login Disini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>