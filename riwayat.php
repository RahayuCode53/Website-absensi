<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];

// Mengambil data absensi user - Dibatasi 30 data terakhir untuk performa
$query = "SELECT * FROM absensi WHERE user_id = '$user_id' ORDER BY tanggal DESC LIMIT 30";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Presensi | PTPN IV</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; padding-bottom: 80px; }
        .header-bg { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); color: white; padding-bottom: 3rem; border-bottom-left-radius: 2rem; border-bottom-right-radius: 2rem; }
        .card-history { border: none; border-radius: 1.2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 1rem; transition: transform 0.2s; }
        .card-history:hover { transform: translateY(-3px); }
        .status-badge { font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; padding: 0.5em 1em; border-radius: 50rem; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 0.8rem; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-bottom { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 0.8rem 1rem; border-top: 1px solid #eee; display: flex; justify-content: space-around; z-index: 1000; box-shadow: 0 -4px 20px rgba(0,0,0,0.05); }
        .nav-item-btm { text-align: center; color: #94a3b8; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: color 0.3s; }
        .nav-item-btm.active { color: #059669; }
        .nav-item-btm i { font-size: 1.4rem; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header-bg pt-4 px-4 mb-n4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Riwayat Presensi</h4>
            <p class="small text-white-50 mb-0">Laporan aktivitas magang Anda</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="container" style="margin-top: -2rem;">
    <!-- Logs List -->
    <?php if (mysqli_num_rows($result) > 0) : ?>
        <div class="d-flex flex-column gap-3">
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <div class="card card-history bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0">
                                    <?php if ($row['status'] == 'hadir' && !empty($row['foto_masuk'])) : ?>
                                        <img src="uploads/foto_absen/<?= $row['foto_masuk']; ?>" class="img-thumb" onclick="window.open(this.src)">
                                    <?php else : ?>
                                        <div class="img-thumb d-flex align-items-center justify-content-center bg-light text-secondary">
                                            <i class="bi bi-file-text-fill fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1"><?= date('d F Y', strtotime($row['tanggal'])); ?></h6>
                                    
                                    <!-- Status Badge -->
                                    <?php 
                                        if($row['status'] == 'hadir') echo '<span class="badge bg-success-subtle text-success status-badge">HADIR</span>';
                                        elseif($row['status'] == 'izin') echo '<span class="badge bg-warning-subtle text-warning status-badge">IZIN</span>';
                                        else echo '<span class="badge bg-danger-subtle text-danger status-badge">SAKIT</span>';
                                    ?>
                                </div>
                            </div>
                            <!-- Time Info -->
                            <div class="text-end">
                                <div class="d-flex flex-column gap-1">
                                    <small class="badge bg-light text-dark border fw-bold">
                                        IN: <?= $row['jam_masuk'] ? substr($row['jam_masuk'], 0, 5) : '--:--'; ?>
                                    </small>
                                    <small class="badge bg-light text-dark border fw-bold">
                                        OUT: <?= $row['jam_pulang'] ? substr($row['jam_pulang'], 0, 5) : '--:--'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2 border-light">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fst-italic truncate" style="max-width: 150px;">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= !empty($row['keterangan']) ? $row['keterangan'] : '-'; ?>
                            </small>
                            <?php if (!empty($row['lokasi_gps'])) : ?>
                                <a href="https://www.google.com/maps?q=<?= $row['lokasi_gps']; ?>" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                    <i class="bi bi-geo-alt-fill"></i> Peta
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <div class="text-center py-5 text-muted opacity-50">
            <i class="bi bi-calendar-x display-1"></i>
            <p class="mt-3 fw-bold text-uppercase small letter-spacing-2">Belum ada riwayat</p>
        </div>
    <?php endif; ?>
</div>

<!-- Bottom Navigation (Mobile) -->
<div class="nav-bottom d-md-none">
    <a href="dashboard.php" class="nav-item-btm">
        <i class="bi bi-house-door"></i> Home
    </a>
    <a href="riwayat.php" class="nav-item-btm active">
        <i class="bi bi-clock-history"></i> Riwayat
    </a>
    <a href="izin.php" class="nav-item-btm">
        <i class="bi bi-file-earmark-text"></i> Izin
    </a>
    <a href="logout.php" class="nav-item-btm text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>