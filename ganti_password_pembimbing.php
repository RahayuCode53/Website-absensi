<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['pembimbing'])) {
    header("Location: login_pembimbing.php");
    exit;
}

$pembimbing_id   = isset($_SESSION['pembimbing_id']) ? (int)$_SESSION['pembimbing_id'] : 0;
$pembimbing_nama = isset($_SESSION['pembimbing_nama']) ? $_SESSION['pembimbing_nama'] : 'Pembimbing';

if ($pembimbing_id <= 0) {
    $error = "Akun admin umum tidak bisa mengganti sandi di halaman ini.";
} else {
    // Ambil data pembimbing
    $q = mysqli_query($conn, "SELECT password FROM pembimbing WHERE id = $pembimbing_id LIMIT 1");
    if ($q && mysqli_num_rows($q) === 1) {
        $pembimbing = mysqli_fetch_assoc($q);
    } else {
        $error = "Data pembimbing tidak ditemukan.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $lama  = $_POST['password_lama'] ?? '';
    $baru1 = $_POST['password_baru'] ?? '';
    $baru2 = $_POST['password_baru_konfirmasi'] ?? '';

    if ($lama === '' || $baru1 === '' || $baru2 === '') {
        $error = "Semua kolom wajib diisi.";
    } elseif ($baru1 !== $baru2) {
        $error = "Konfirmasi sandi baru tidak sama.";
    } elseif (strlen($baru1) < 6) {
        $error = "Sandi baru minimal 6 karakter.";
    } elseif (!password_verify($lama, $pembimbing['password'])) {
        $error = "Sandi lama salah.";
    } else {
        $hash_baru = password_hash($baru1, PASSWORD_DEFAULT);
        if (mysqli_query($conn, "UPDATE pembimbing SET password = '$hash_baru' WHERE id = $pembimbing_id")) {
            $success = "Sandi berhasil diganti.";
        } else {
            $error = "Gagal mengupdate sandi: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Pembimbing | PTPN IV</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; min-height: 100vh; }
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #022c22 100%); min-height: 100vh; color: white; }
        .brand-logo-box {
            width: 44px;
            height: 44px;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-logo-box img {
            max-height: 32px;
            display: block;
        }
        .nav-link { color: #94a3b8; font-weight: 600; padding: 0.75rem 1rem; border-radius: 0.8rem; transition: all 0.2s; }
        .nav-link:hover { background: rgba(148,163,184,0.25); color: #e5e7eb; }
        .nav-link.active { background: rgba(16, 185, 129, 0.3); color: #bbf7d0; }
        .nav-link i { width: 24px; display: inline-block; }
        .card-wrap { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-4 d-none d-md-block">
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="brand-logo-box">
                    <img src="assets/img/logo.png" alt="PTPN IV">
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">PTPN IV</h6>
                    <small class="text-white-50" style="font-size: 10px;">Monitoring System</small>
                </div>
            </div>
            <nav class="nav flex-column gap-2">
                <a class="nav-link" href="dashboard_pembimbing.php">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a class="nav-link" href="data_magang.php">
                    <i class="bi bi-people-fill"></i> Data Magang
                </a>
                <a class="nav-link" href="laporan_magang.php">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan
                </a>
                <a class="nav-link active" href="ganti_password_pembimbing.php">
                    <i class="bi bi-lock-fill"></i> Ganti Password
                </a>
                <a class="nav-link text-danger mt-5" href="logout_pembimbing.php">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4 p-lg-5">
            <div class="d-md-none d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Ganti Password</h4>
                <a href="logout_pembimbing.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i></a>
            </div>

            <h2 class="fw-bold text-dark mb-1">Ganti Password</h2>
            <p class="text-muted mb-4">Ubah sandi akun Anda untuk keamanan yang lebih baik.</p>

            <div class="card card-wrap bg-white">
                <div class="card-body p-4 p-md-5">
                    <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                        Pembimbing: <?= htmlspecialchars($pembimbing_nama); ?>
                    </h6>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger small"><?= htmlspecialchars($error); ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="alert alert-success small"><?= htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sandi Lama</label>
                            <input type="password" name="password_lama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sandi Baru</label>
                            <input type="password" name="password_baru" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Ulangi Sandi Baru</label>
                            <input type="password" name="password_baru_konfirmasi" class="form-control" required>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-success px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

