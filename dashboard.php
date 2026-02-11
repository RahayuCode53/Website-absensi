 <?php
session_start();
include 'includes/db.php';

// Proteksi Login
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];
$today = date('Y-m-d');

// 1. AMBIL DATA ABSEN HARI INI
// Menggunakan pengecekan agar tidak error "offset null" di tanggal 5 Feb
$cek_hari_ini = mysqli_query($conn, "SELECT jam_masuk, jam_pulang, status FROM absensi WHERE user_id = '$user_id' AND tanggal = '$today'");
$absen_sekarang = mysqli_fetch_assoc($cek_hari_ini);

// Logika tampilan (Null Coalescing ?? digunakan agar jika data kosong, tampil --:--)
$tampil_masuk = $absen_sekarang['jam_masuk'] ?? '--:--';
$tampil_pulang = ($absen_sekarang['jam_pulang'] ?? null) ?: '--:--';

// Status untuk mengunci tombol secara visual
$sudah_masuk = !empty($absen_sekarang['jam_masuk']);
$sudah_pulang = !empty($absen_sekarang['jam_pulang']) && $absen_sekarang['jam_pulang'] != "00:00:00";

// 2. AMBIL RINGKASAN 30 HARI (Sinkron Database)
$q_stats = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
    SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit
    FROM absensi WHERE user_id = '$user_id' AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stats = mysqli_fetch_assoc($q_stats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Presensi | PTPN IV</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-custom {
            background: #064e3b; /* Dark Green */
            color: white;
        }
        .card-custom {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .camera-container {
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
            background: #000;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        #video {
            width: 100%;
            height: auto;
            transform: scaleX(-1);
        }
        .btn-action {
            border-radius: 1.2rem;
            padding: 1.5rem;
            border: none;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }
        .btn-action:hover {
            filter: brightness(110%);
        }
        .btn-action.masuk { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .btn-action.pulang { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
        .btn-action.izin { background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); }
        .btn-action.riwayat { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }
        .btn-action:disabled { opacity: 0.7; cursor: not-allowed; filter: grayscale(80%); }
        
        .btn-action i { font-size: 2rem; margin-bottom: 0.5rem; }

        /* Custom Scrollbar for better look */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #888; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }
    </style>
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold fst-italic" href="#">
            <i class="bi bi-tree-fill me-2 text-warning"></i> PTPN IV DIGITAL
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3 text-white">
                    <div class="text-end lh-1 d-none d-lg-block">
                        <span class="d-block fw-bold"><?= $nama_user; ?></span>
                        <small class="text-white-50 text-uppercase" style="font-size: 10px;"><?= $_SESSION['instansi'] ?? 'MAGANG'; ?></small>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4 fw-bold">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4 flex-grow-1">
    
    <!-- Welcome & Clock Section -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <h4 class="fw-bold text-success mb-1">Halo, <?= explode(' ', $nama_user)[0]; ?>! 👋</h4>
            <p class="text-muted mb-0 small">Jangan lupa absen hari ini ya.</p>
        </div>
        <div class="col-md-6 text-md-end text-center">
            <h2 class="display-6 fw-bold mb-0 text-dark" id="clock">00:00:00</h2>
            <p class="text-muted small fst-italic"><?= date('l, d F Y'); ?></p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Left Column: Status & Info -->
        <div class="col-lg-7">
            
            <!-- Status Cards (Masuk/Pulang) -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="card card-custom bg-white border-start border-5 border-success p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3">🌅</div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Jam Masuk</small>
                                <h4 class="fw-bold mb-0 text-dark"><?= $tampil_masuk; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-custom bg-white border-start border-5 border-warning p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3">🌇</div>
                            <div>
                                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Jam Pulang</small>
                                <h4 class="fw-bold mb-0 text-dark"><?= $tampil_pulang; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quote / Info Card -->
            <div class="card card-custom bg-white p-4 mb-4">
                <div class="d-flex gap-3 align-items-start">
                    <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                    <div>
                        <h6 class="fw-bold text-dark">Info Presensi</h6>
                        <p class="text-muted small mb-0">
                            Silakan klik tombol absen di sebelah kanan. Pastikan Anda berada di lokasi kantor dan izinkan akses kamera serta lokasi untuk validasi.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Actions & Stats -->
        <div class="col-lg-5">
            <!-- Action Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <button onclick="<?= $sudah_masuk ? "Swal.fire('Info', 'Anda sudah melakukan absen masuk hari ini.', 'info')" : "bukaModal('masuk')" ?>" 
                            class="w-100 btn-action masuk shadow-sm" <?= $sudah_masuk ? 'disabled' : '' ?>>
                        <i class="bi bi-camera-fill"></i> Absen Masuk
                    </button>
                </div>
                <div class="col-6">
                    <button onclick="<?= (!$sudah_masuk || $sudah_pulang) ? "Swal.fire('Info', 'Belum saatnya pulang atau Anda sudah absen pulang.', 'warning')" : "bukaModal('pulang')" ?>" 
                            class="w-100 btn-action pulang shadow-sm" <?= (!$sudah_masuk || $sudah_pulang) ? 'disabled' : '' ?>>
                        <i class="bi bi-box-arrow-right"></i> Absen Pulang
                    </button>
                </div>
                <div class="col-6">
                    <button onclick="window.location.href='izin.php'" class="w-100 btn-action izin shadow-sm">
                        <i class="bi bi-file-earmark-text-fill"></i> Izin / Sakit
                    </button>
                </div>
                <div class="col-6">
                    <button onclick="window.location.href='riwayat.php'" class="w-100 btn-action riwayat shadow-sm">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="card card-custom bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold text-uppercase text-muted small letter-spacing-2">Ringkasan Bulan Ini</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center divide-x">
                        <div class="col-4 border-end">
                            <h3 class="fw-bold text-success mb-0"><?= $stats['hadir'] ?? 0; ?></h3>
                            <small class="text-muted fw-bold" style="font-size: 10px;">HADIR</small>
                        </div>
                        <div class="col-4 border-end">
                            <h3 class="fw-bold text-warning mb-0"><?= $stats['izin'] ?? 0; ?></h3>
                            <small class="text-muted fw-bold" style="font-size: 10px;">IZIN</small>
                        </div>
                        <div class="col-4">
                            <h3 class="fw-bold text-danger mb-0"><?= $stats['sakit'] ?? 0; ?></h3>
                            <small class="text-muted fw-bold" style="font-size: 10px;">SAKIT</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Absen -->
<div class="modal fade" id="modalAbsen" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">📸 Ambil Foto Presensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <!-- Camera View -->
                <div class="camera-container mb-3 position-relative">
                    <video id="video" autoplay playsinline class="w-100 rounded-3"></video>
                    <canvas id="canvas" class="d-none"></canvas>
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-dark bg-opacity-50 text-white small">
                        Pastikan wajah terlihat jelas
                    </div>
                </div>
                
                <!-- GPS Loading -->
                <div id="location-status" class="alert alert-secondary py-2 small fw-bold text-muted mb-3 rounded-pill">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Mencari Lokasi GPS...
                </div>

                <!-- Mood Selector -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Bagaimana Mood Kamu Hari Ini?</label>
                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <input type="radio" class="btn-check" name="mood" id="mood1" value="Senang 😄" autocomplete="off">
                        <label class="btn btn-outline-success rounded-circle p-2 fs-3" for="mood1" style="width: 55px; height: 55px;">😄</label>

                        <input type="radio" class="btn-check" name="mood" id="mood2" value="Biasa 😐" autocomplete="off">
                        <label class="btn btn-outline-warning rounded-circle p-2 fs-3" for="mood2" style="width: 55px; height: 55px;">😐</label>

                        <input type="radio" class="btn-check" name="mood" id="mood3" value="Lelah 😴" autocomplete="off">
                        <label class="btn btn-outline-secondary rounded-circle p-2 fs-3" for="mood3" style="width: 55px; height: 55px;">😴</label>

                        <input type="radio" class="btn-check" name="mood" id="mood4" value="Sedih 😢" autocomplete="off">
                        <label class="btn btn-outline-primary rounded-circle p-2 fs-3" for="mood4" style="width: 55px; height: 55px;">😢</label>

                        <input type="radio" class="btn-check" name="mood" id="mood5" value="Marah 😡" autocomplete="off">
                        <label class="btn btn-outline-danger rounded-circle p-2 fs-3" for="mood5" style="width: 55px; height: 55px;">😡</label>
                    </div>
                </div>

                <input type="hidden" id="tipeAbsen" value="">

                <button onclick="kirimAbsen()" id="btnKirim" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-camera-fill me-2"></i> CEKREK & HADIR
                </button>
            </div>
        </div>
    </div>
</div>

<footer class="bg-white py-4 mt-auto border-top">
    <div class="container text-center">
        <p class="text-muted small fw-bold mb-0">
            &copy; 2026 PTPN IV Regional 1. All Rights Reserved.
        </p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const locStatus = document.getElementById('location-status');
    const modalAbsen = new bootstrap.Modal(document.getElementById('modalAbsen'));
    const modalEl = document.getElementById('modalAbsen');
    let streamReference = null;
    let current_location = "";

    // Clock
    setInterval(() => {
        document.getElementById('clock').innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }, 1000);

    // Get Location on Load
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(pos => {
            current_location = pos.coords.latitude + "," + pos.coords.longitude;
            locStatus.className = "alert alert-success py-2 small fw-bold mb-3 rounded-pill";
            locStatus.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i> Lokasi Terkunci: ` + pos.coords.latitude.toFixed(5) + ", " + pos.coords.longitude.toFixed(5);
        }, (err) => {
            locStatus.className = "alert alert-danger py-2 small fw-bold mb-3 rounded-pill";
            locStatus.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> GPS Gagal: ` + err.message;
        });
    } else {
        locStatus.innerHTML = "Browser tidak mendukung GPS.";
    }

    function bukaModal(tipe) {
        document.getElementById('tipeAbsen').value = tipe;
        // Reset Mood
        document.querySelectorAll('input[name="mood"]').forEach(el => el.checked = false);
        
        modalAbsen.show();
        
        // Start Camera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
        .then(stream => { 
            video.srcObject = stream; 
            streamReference = stream;
        })
        .catch(err => {
            Swal.fire('Kamera Error', 'Akses kamera ditolak! Pastikan izin kamera aktif.', 'error');
            modalAbsen.hide();
        });
    }

    // Stop Camera when Modal Closes
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (streamReference) {
            streamReference.getTracks().forEach(track => track.stop());
        }
        video.srcObject = null;
    });

    function kirimAbsen() {
        const tipe = document.getElementById('tipeAbsen').value;
        const mood = document.querySelector('input[name="mood"]:checked');

        if (!current_location) { 
            Swal.fire('GPS Belum Siap', 'Tunggu hingga lokasi GPS terkunci (warna hijau).', 'warning');
            return; 
        }

        if (!mood) {
            Swal.fire('Mood Belum Dipilih', 'Pilih mood kamu hari ini dulu ya! 😄', 'warning');
            return;
        }
        
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataFoto = canvas.toDataURL('image/png');

        // Loading State
        const btn = document.getElementById('btnKirim');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...`;
        btn.disabled = true;

        fetch('proses_absen.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `image=${encodeURIComponent(dataFoto)}&location=${current_location}&tipe=${tipe}&mood=${encodeURIComponent(mood.value)}`
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "success") {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Absen ' + tipe + ' berhasil dicatat!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Gagal', 'Error: ' + data, 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            Swal.fire('Koneksi Error', 'Terjadi kesalahan jaringan.', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
</body>
</html>