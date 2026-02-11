<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['pembimbing'])) {
    header("Location: login_pembimbing.php");
    exit;
}

// Info pembimbing yang login
$pembimbing_id = isset($_SESSION['pembimbing_id']) ? (int)$_SESSION['pembimbing_id'] : null;

// Filter: kategori (Semua / Universitas / SMK) dan pencarian
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
$cari = isset($_GET['cari']) ? trim(mysqli_real_escape_string($conn, $_GET['cari'])) : '';

// Query seluruh data peserta magang dari database (mahasiswa/i, siswa/i + asal)
$where = "WHERE role = 'magang'";

// Batasi hanya ke peserta yang dibimbing oleh pembimbing yang sedang login
if (!empty($pembimbing_id)) {
    $where .= " AND pembimbing_id = " . (int)$pembimbing_id;
}

if ($filter_kategori === 'Universitas' || $filter_kategori === 'SMK') {
    $where .= " AND kategori = '$filter_kategori'";
}
if ($cari !== '') {
    $where .= " AND (nama LIKE '%$cari%' OR instansi LIKE '%$cari%' OR username LIKE '%$cari%' OR jurusan LIKE '%$cari%')";
}

$q_peserta = mysqli_query($conn, "SELECT id, nama, foto, username, instansi, kategori, jurusan, created_at 
    FROM users 
    $where 
    ORDER BY kategori ASC, nama ASC");

$list_peserta = [];
while ($p = mysqli_fetch_assoc($q_peserta)) {
    $uid = (int)$p['id'];
    $q_abs = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi WHERE user_id = '$uid'");
    $abs = mysqli_fetch_assoc($q_abs);
    $total_absensi = (int)($abs['total'] ?? 0);
    $list_peserta[] = [
        'id' => $p['id'],
        'nama' => $p['nama'],
        'foto' => $p['foto'],
        'username' => $p['username'],
        'instansi' => $p['instansi'] !== null && $p['instansi'] !== '' ? $p['instansi'] : '-',
        'kategori' => $p['kategori'],
        'jurusan' => isset($p['jurusan']) && $p['jurusan'] !== null && $p['jurusan'] !== '' ? $p['jurusan'] : '-',
        'created_at' => $p['created_at'],
        'total_absensi' => $total_absensi
    ];
}

// Statistik
$jumlah_total = count($list_peserta);
$q_univ = mysqli_query($conn, "SELECT COUNT(*) as n FROM users WHERE role = 'magang' AND kategori = 'Universitas' " . (!empty($pembimbing_id) ? "AND pembimbing_id = " . (int)$pembimbing_id : ""));
$q_smk = mysqli_query($conn, "SELECT COUNT(*) as n FROM users WHERE role = 'magang' AND kategori = 'SMK' " . (!empty($pembimbing_id) ? "AND pembimbing_id = " . (int)$pembimbing_id : ""));
$jumlah_universitas = (int)mysqli_fetch_assoc($q_univ)['n'];
$jumlah_smk = (int)mysqli_fetch_assoc($q_smk)['n'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Magang | PTPN IV</title>
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
        .avatar-sm { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .avatar-initial { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; }
        .btn-filter { background: #059669; color: white; border: none; padding: 0.5rem 1.25rem; border-radius: 0.8rem; font-weight: 600; }
        .btn-filter:hover { background: #047857; color: white; }
        .badge-absensi { font-size: 0.75rem; }
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
                <a class="nav-link active" href="data_magang.php">
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
            <div class="d-md-none d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Data Magang</h4>
                <a href="logout_pembimbing.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-left"></i></a>
            </div>

            <h2 class="fw-bold text-dark mb-1">Data Magang</h2>
            <p class="text-muted mb-4">Keseluruhan data mahasiswa/i, siswa/i peserta magang dan asal universitas/sekolah.</p>

            <!-- Filter & Cari dalam card -->
            <div class="card card-wrap bg-white mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.08em;">Filter & Pencarian</h6>
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Kategori</label>
                            <select name="kategori" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <option value="Universitas" <?= $filter_kategori === 'Universitas' ? 'selected' : '' ?>>Universitas</option>
                                <option value="SMK" <?= $filter_kategori === 'SMK' ? 'selected' : '' ?>>SMK</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-muted mb-1">Cari nama / instansi</label>
                            <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" class="form-control form-control-sm" placeholder="Nama, username, atau asal...">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn-filter btn-sm"><i class="bi bi-search me-1"></i> Terapkan</button>
                            <a href="data_magang.php" class="btn btn-outline-secondary btn-sm rounded-3">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-md-4">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Total Peserta</small>
                                <h2 class="fw-bold text-dark mb-0 mt-1"><?= $jumlah_total ?></h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">Universitas</small>
                                <h2 class="fw-bold text-info mb-0 mt-1"><?= $jumlah_universitas ?></h2>
                            </div>
                            <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card card-stat bg-white h-100">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 10px;">SMK</small>
                                <h2 class="fw-bold text-secondary mb-0 mt-1"><?= $jumlah_smk ?></h2>
                            </div>
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-mortarboard fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Keseluruhan Peserta -->
            <div class="card card-wrap bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.06em; color: #64748b;">Keseluruhan Peserta Magang</h6>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold"><?= count($list_peserta) ?> peserta</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3">No</th>
                                <th class="py-3">Peserta</th>
                                <th class="py-3">Username</th>
                                <th class="py-3">Kategori</th>
                                <th class="py-3">Jurusan</th>
                                <th class="py-3">Asal Instansi</th>
                                <th class="py-3 text-center">Total Absensi</th>
                                <th class="pe-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($list_peserta) > 0): ?>
                                <?php foreach ($list_peserta as $i => $row): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-muted"><?= $i + 1 ?></td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($row['foto']) && file_exists('uploads/' . $row['foto'])): ?>
                                                <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" class="avatar-sm" alt="">
                                            <?php else: ?>
                                                <div class="avatar-initial bg-success bg-opacity-80 text-white">
                                                    <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="fw-bold small d-block"><?= htmlspecialchars($row['nama']) ?></span>
                                                <span class="text-muted" style="font-size: 0.7rem;">ID #<?= $row['id'] ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 small"><?= htmlspecialchars($row['username']) ?></td>
                                    <td class="py-3">
                                        <span class="badge <?= $row['kategori'] === 'Universitas' ? 'bg-info bg-opacity-10 text-info' : 'bg-secondary bg-opacity-10 text-secondary' ?> rounded-pill px-3">
                                            <?= htmlspecialchars($row['kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 small text-muted"><?= htmlspecialchars($row['jurusan']) ?></td>
                                    <td class="py-3 small text-muted"><?= htmlspecialchars($row['instansi']) ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 badge-absensi">
                                            <i class="bi bi-calendar-check me-1"></i><?= $row['total_absensi'] ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="detail_riwayat.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary rounded-3" title="Lihat riwayat absensi">
                                            <i class="bi bi-eye-fill me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="opacity-50">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <small class="fw-bold text-uppercase">Tidak ada data peserta magang</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 text-muted small text-center">
                <p class="mb-0">Sistem Monitoring PTPN IV — Data Magang</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
