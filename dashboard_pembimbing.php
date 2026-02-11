<?php
session_start();
include 'includes/db.php';

// Proteksi Halaman
if (!isset($_SESSION['pembimbing'])) {
    header("Location: login_pembimbing.php");
    exit;
}

// Info pembimbing yang login
$pembimbing_id = isset($_SESSION['pembimbing_id']) ? (int)$_SESSION['pembimbing_id'] : null;
$pembimbing_nama = isset($_SESSION['pembimbing_nama']) ? $_SESSION['pembimbing_nama'] : 'Pembimbing';

// 1. SET TANGGAL (Default ke Hari Ini)
$tgl_filter = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// Tambahan filter berdasarkan pembimbing yang login: 
// setiap pembimbing hanya melihat peserta bimbingannya sendiri.
$filter_pembimbing = '';
if (!empty($pembimbing_id)) {
    $filter_pembimbing = " AND u.pembimbing_id = " . (int)$pembimbing_id . " ";
}

// 2. QUERY UTAMA (Hanya tampilkan yang sudah absen di tanggal terpilih dan menjadi anak bimbingannya)
$sql_main = "SELECT 
    u.id, u.nama, u.instansi, u.jurusan,
    a.status, a.jam_masuk, a.mood_masuk, a.lokasi_gps, a.foto_masuk, a.file_surat_sakit
    FROM users u 
    INNER JOIN absensi a ON u.id = a.user_id 
    WHERE a.tanggal = '$tgl_filter'
    AND u.nama NOT IN ('admin', 'pembimbing') " . $filter_pembimbing . "
    ORDER BY a.jam_masuk DESC";
$query_main = mysqli_query($conn, $sql_main);

// 3. STATISTIK (Khusus Tanggal Terpilih dan anak bimbingan)
$sql_stats = "SELECT 
    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
    SUM(CASE WHEN a.status IN ('izin', 'sakit') THEN 1 ELSE 0 END) as izin
    FROM absensi a";

if (!empty($pembimbing_id)) {
    $sql_stats .= " INNER JOIN users u ON a.user_id = u.id 
        WHERE a.tanggal = '$tgl_filter' AND u.pembimbing_id = " . (int)$pembimbing_id;
} else {
    $sql_stats .= " WHERE a.tanggal = '$tgl_filter'";
}

$q_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($q_stats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Monitoring | PTPN IV</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        .sidebar { 
            background: linear-gradient(180deg, #064e3b 0%, #022c22 100%); 
            min-height: 100vh; 
            color: white; 
        }
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
        .nav-link { color: #94a3b8; font-weight: 600; padding: 0.8rem 1rem; border-radius: 0.8rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(148, 163, 184, 0.2); color: #e5e7eb; }
        .nav-link i { width: 24px; display: inline-block; }
        .card-stat { border: none; border-radius: 1rem; transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
        .card-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(15,23,42,0.12); }
        .table-custom th { background-color: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; }
        .avatar-sm { width: 32px; height: 32px; object-fit: cover; border-radius: 50%; }
        .btn-filter { background: #059669; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 0.8rem; font-weight: 600; }
        .btn-filter:hover { background: #047857; }
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
                    <h6 class="mb-0 fw-bold text-white">PTPN IV</h6>
                    <small class="text-emerald-200" style="font-size: 10px;">Pembimbing Panel</small>
                </div>
            </div>
            
            <nav class="nav flex-column gap-2">
                <a class="nav-link active" href="dashboard_pembimbing.php">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                <a class="nav-link" href="data_magang.php">
                    <i class="bi bi-people-fill"></i> Data Magang
                </a>
                <a class="nav-link" href="laporan_magang.php">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan
                </a>
                <a class="nav-link" href="ganti_password_pembimbing.php">
                    <i class="bi bi-lock-fill"></i> Ganti Password
                </a>
                <a class="nav-link text-danger mt-5" href="logout_pembimbing.php">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4 p-lg-5">
            
            <!-- Header Mobile -->
            <div class="d-md-none d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Dashboard</h4>
                <a href="logout_pembimbing.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i></a>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Monitoring Kehadiran</h2>
                    <p class="text-muted mb-0">Pantau kehadiran peserta magang bimbingan Anda.</p>
                    <p class="text-muted mb-0 small">Pembimbing: <span class="fw-semibold"><?= htmlspecialchars($pembimbing_nama); ?></span></p>
                </div>
                <form action="" method="GET" class="d-flex gap-2 bg-white p-2 rounded-3 shadow-sm">
                    <input type="date" name="tgl" value="<?= $tgl_filter ?>" class="form-control border-0 bg-transparent fw-bold" style="width: 150px;">
                    <button type="submit" class="btn-filter">Filter</button>
                </form>
            </div>

            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-xl-3">
                    <div class="card card-stat bg-white shadow-sm h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Total Hadir</small>
                                <h2 class="fw-bold text-success mb-0 mt-1"><?= $stats['hadir'] ?? 0 ?></h2>
                            </div>
                            <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card card-stat bg-white shadow-sm h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Izin / Sakit</small>
                                <h2 class="fw-bold text-warning mb-0 mt-1"><?= $stats['izin'] ?? 0 ?></h2>
                            </div>
                            <div class="bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-exclamation-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="mb-0 fw-bold text-uppercase letter-spacing-2">Log Aktivitas: <?= date('d M Y', strtotime($tgl_filter)) ?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">Mahasiswa</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-center">Mood</th>
                                <th class="py-3 text-center">Jam Masuk</th>
                                <th class="py-3 text-center">Lokasi</th>
                                <th class="py-3 text-center">Bukti</th>
                                <th class="pe-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query_main) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($query_main)): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 32px; height: 32px;">
                                                <?= substr($row['nama'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold small"><?= $row['nama'] ?></h6>
                                                <small class="text-muted d-block" style="font-size: 10px;"><?= htmlspecialchars($row['instansi'] ?? 'MAGANG') ?></small>
                                                <small class="text-muted" style="font-size: 10px;">Jurusan: <?= htmlspecialchars(!empty($row['jurusan']) ? $row['jurusan'] : '-') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['status'] == 'hadir'): ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3">HADIR</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 text-uppercase"><?= $row['status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-5" title="Mood Masuk"><?= $row['mood_masuk'] ?? '😐' ?></span>
                                    </td>
                                    <td class="text-center fw-bold text-dark small">
                                        <?= $row['jam_masuk'] ? substr($row['jam_masuk'], 0, 5) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['lokasi_gps']): ?>
                                            <a href="https://www.google.com/maps?q=<?= $row['lokasi_gps'] ?>" target="_blank" class="btn btn-light btn-sm rounded-pill text-primary fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-geo-alt-fill"></i> Peta
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            // Logic Path
                                            if ($row['status'] == 'hadir') {
                                                $path = "uploads/foto_absen/" . $row['foto_masuk'];
                                            } else {
                                                $path = "uploads/surat_sakit/" . $row['file_surat_sakit'];
                                            }

                                            if (!empty($path) && file_exists($path)): 
                                        ?>
                                            <a href="<?= $path ?>" target="_blank">
                                                <img src="<?= $path ?>" class="rounded-3 border" style="width: 35px; height: 35px; object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small italic">No File</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="detail_riwayat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-3">
                                            <i class="bi bi-three-dots"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="opacity-50">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <small class="fw-bold text-uppercase">Tidak ada data absensi</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>