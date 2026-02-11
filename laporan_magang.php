<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['pembimbing'])) {
    header("Location: login_pembimbing.php");
    exit;
}

// Info pembimbing yang login
$pembimbing_id   = isset($_SESSION['pembimbing_id']) ? (int)$_SESSION['pembimbing_id'] : null;
$pembimbing_nama = isset($_SESSION['pembimbing_nama']) ? $_SESSION['pembimbing_nama'] : 'Pembimbing';

// Filter periode mingguan (Senin–Minggu): pilih satu tanggal dalam minggu
$minggu = isset($_GET['minggu']) ? $_GET['minggu'] : date('Y-m-d');
$ts = strtotime($minggu);
if ($ts === false) {
    $minggu = date('Y-m-d');
    $ts = strtotime($minggu);
}
$tgl_awal = date('Y-m-d', strtotime('monday this week', $ts));
$tgl_akhir = date('Y-m-d', strtotime('sunday this week', $ts));

// Daftar peserta magang: hanya anak bimbingan pembimbing yang login (kecuali akun admin umum)
$where_users = "WHERE role = 'magang'";
if (!empty($pembimbing_id)) {
    $where_users .= " AND pembimbing_id = " . (int)$pembimbing_id;
}

$sql_peserta = "SELECT id, nama, instansi, kategori, jurusan, username, created_at 
    FROM users 
    $where_users 
    ORDER BY id ASC";
$q_peserta = mysqli_query($conn, $sql_peserta);

$list_peserta = [];
while ($p = mysqli_fetch_assoc($q_peserta)) {
    $uid = (int)$p['id'];
    // Hitung per status berdasarkan tanggal absensi (tanggal saat data absensi diinput)
    $q_abs = mysqli_query($conn, "SELECT 
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit
        FROM absensi 
        WHERE user_id = '$uid' 
        AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $abs = mysqli_fetch_assoc($q_abs);
    $hadir = (int)($abs['hadir'] ?? 0);
    $izin = (int)($abs['izin'] ?? 0);
    $sakit = (int)($abs['sakit'] ?? 0);
    $total = $hadir + $izin + $sakit;
    $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
    // Data peserta dari database (users) — tampil sesuai yang diinput dari awal
    $list_peserta[] = [
        'id' => $p['id'],
        'nama' => $p['nama'],
        'instansi' => $p['instansi'] !== null && $p['instansi'] !== '' ? $p['instansi'] : '-',
        'kategori' => $p['kategori'],
        'jurusan' => isset($p['jurusan']) && $p['jurusan'] !== null && $p['jurusan'] !== '' ? $p['jurusan'] : '-',
        'hadir' => $hadir,
        'izin' => $izin,
        'sakit' => $sakit,
        'total' => $total,
        'persen' => $persen
    ];
}

// Statistik keseluruhan
$total_hadir = array_sum(array_column($list_peserta, 'hadir'));
$total_izin = array_sum(array_column($list_peserta, 'izin'));
$total_sakit = array_sum(array_column($list_peserta, 'sakit'));
$jumlah_peserta = count($list_peserta);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Magang | PTPN IV</title>
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
        .card-stat { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
        .card-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .card-wrap { border: none; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .table-custom th { background: #f8fafc; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; font-weight: 700; border-bottom: 2px solid #e2e8f0; }
        .table-custom td { vertical-align: middle; font-size: 0.9rem; border-bottom: 1px solid #f1f5f9; }
        .table-custom tbody tr:hover { background: #f8fafc; }
        .btn-filter { background: #059669; color: white; border: none; padding: 0.5rem 1.25rem; border-radius: 0.5rem; font-weight: 600; }
        .btn-filter:hover { background: #047857; color: white; }
        .progress-custom { height: 8px; border-radius: 1rem; background: #e2e8f0; }
        .info-callout { background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 0 0.5rem 0.5rem 0; }
        @media print {
            .sidebar, .no-print, .btn, nav, .filter-form { display: none !important; }
            .main-content { width: 100% !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-4 d-none d-md-block no-print">
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
                <a class="nav-link active" href="laporan_magang.php">
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
        <div class="col-md-9 col-lg-10 p-4 p-lg-5 main-content">
            <div class="d-md-none d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <h4 class="fw-bold mb-0">Laporan Magang</h4>
                    <p class="text-muted mb-0 small">Pembimbing: <span class="fw-semibold"><?= htmlspecialchars($pembimbing_nama); ?></span></p>
                </div>
                <a href="logout_pembimbing.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i></a>
            </div>

            <h2 class="fw-bold text-dark mb-1">Laporan Magang</h2>
            <p class="text-muted mb-1">Rangkuman data absensi peserta magang per periode mingguan.</p>
            <p class="text-muted mb-4 small">Pembimbing: <span class="fw-semibold"><?= htmlspecialchars($pembimbing_nama); ?></span></p>

            <!-- Filter dalam card -->
            <div class="card card-wrap bg-white mb-4 no-print">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Filter Periode</h6>
                    <form action="" method="GET" class="row g-3 align-items-end filter-form">
                        <div class="col-auto">
                            <label class="form-label small fw-bold text-muted mb-1">Minggu</label>
                            <input type="date" name="minggu" value="<?= $minggu ?>" class="form-control form-control-sm" style="width: 160px;" title="Pilih satu hari dalam minggu">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn-filter btn-sm"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                        </div>
                        <div class="col-auto ms-md-auto">
                            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold">
                                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </form>
                    <p class="text-muted small mb-0 mt-2">
                        Periode: <strong><?= date('d M Y', strtotime($tgl_awal)) ?></strong> (Senin) s/d <strong><?= date('d M Y', strtotime($tgl_akhir)) ?></strong> (Minggu)
                    </p>
                </div>
            </div>

            <div class="alert info-callout py-2 px-3 mb-4 no-print">
                <small><i class="bi bi-info-circle me-1 text-success"></i> Data mengikuti <strong>tanggal saat absensi diinput</strong>. Daftar peserta sesuai database.</small>
            </div>

            <!-- Statistik Ringkuman -->
            <div class="row g-4 mb-5">
                <div class="col-6 col-md-3">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Peserta Magang</small>
                                <h2 class="fw-bold text-dark mb-0 mt-1"><?= $jumlah_peserta ?></h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Total Hadir</small>
                                <h2 class="fw-bold text-success mb-0 mt-1"><?= $total_hadir ?></h2>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Total Izin</small>
                                <h2 class="fw-bold text-warning mb-0 mt-1"><?= $total_izin ?></h2>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Total Sakit</small>
                                <h2 class="fw-bold text-danger mb-0 mt-1"><?= $total_sakit ?></h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-heart-pulse-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rangkuman per Peserta -->
            <div class="card card-wrap bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.06em; color: #64748b;">Rangkuman per Peserta Magang</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">No</th>
                                <th class="py-3">Nama</th>
                                <th class="py-3">Instansi</th>
                                <th class="py-3">Jurusan</th>
                                <th class="py-3 text-center">Kategori</th>
                                <th class="py-3 text-center">Hadir</th>
                                <th class="py-3 text-center">Izin</th>
                                <th class="py-3 text-center">Sakit</th>
                                <th class="py-3 text-center">Persen Hadir</th>
                                <th class="pe-4 py-3 text-end no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($list_peserta) > 0): ?>
                                <?php foreach ($list_peserta as $i => $row): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-muted"><?= $i + 1 ?></td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white bg-success bg-opacity-80" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                            </div>
                                            <span class="fw-bold small"><?= htmlspecialchars($row['nama']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 small text-muted"><?= htmlspecialchars($row['instansi']) ?></td>
                                    <td class="py-3 small text-muted"><?= htmlspecialchars($row['jurusan']) ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill"><?= htmlspecialchars($row['kategori']) ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><?= $row['hadir'] ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3"><?= $row['izin'] ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><?= $row['sakit'] ?></span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 progress-custom" style="min-width: 60px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, $row['persen']) ?>%;"></div>
                                            </div>
                                            <span class="small fw-bold"><?= $row['persen'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end no-print">
                                        <a href="detail_riwayat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-3" title="Detail riwayat">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="opacity-50">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <small class="fw-bold text-uppercase">Belum ada data peserta magang</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 text-muted small text-center no-print">
                <p class="mb-0">Sistem Monitoring PTPN IV — Laporan Magang</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
