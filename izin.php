<?php
session_start();
include 'includes/db.php';
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }

if (isset($_POST['submit_izin'])) {
    $user_id = $_SESSION['user_id'];
    $status = $_POST['status']; // 'izin' atau 'sakit'
    $keterangan = $_POST['keterangan'];
    $nama_file = "";

    // Logika upload jika statusnya 'sakit'
    if ($status == 'sakit' && isset($_FILES['surat_sakit'])) {
        $nama_file = time() . "_" . $_FILES['surat_sakit']['name'];
        move_uploaded_file($_FILES['surat_sakit']['tmp_name'], 'uploads/surat_sakit/' . $nama_file);
    }

    $query = "INSERT INTO absensi (user_id, status, keterangan, file_surat_sakit, tanggal) 
              VALUES ('$user_id', '$status', '$keterangan', '$nama_file', CURDATE())";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil dikirim!'); window.location='dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Izin / Sakit | PTPN IV</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; min-height: 100vh; }
        .header-bg { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); height: 200px; border-bottom-left-radius: 2rem; border-bottom-right-radius: 2rem; position: absolute; top: 0; width: 100%; z-index: -1; }
        .card-custom { border: none; border-radius: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        .form-control, .form-select { border-radius: 1rem; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; font-weight: 500; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); border-color: #10b981; }
        .nav-bottom { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 0.8rem 1rem; border-top: 1px solid #eee; display: flex; justify-content: space-around; z-index: 1000; box-shadow: 0 -4px 20px rgba(0,0,0,0.05); }
        .nav-item-btm { text-align: center; color: #94a3b8; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: color 0.3s; }
        .nav-item-btm.active { color: #059669; }
        .nav-item-btm i { font-size: 1.4rem; }
    </style>
</head>
<body class="position-relative pb-5">

<div class="header-bg"></div>

<div class="container pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="d-flex align-items-center mb-4 text-white">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0">Pengajuan Izin</h4>
            </div>

            <div class="card card-custom bg-white p-4">
                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-4 text-center">
                        <div class="d-inline-block p-3 rounded-circle bg-success-subtle text-success mb-3">
                            <i class="bi bi-file-text-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold">Form Ketidakhadiran</h5>
                        <p class="text-muted small">Silakan isi data dengan benar</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Jenis Keperluan</label>
                        <select name="status" id="status" class="form-select" onchange="toggleUpload()">
                            <option value="izin">Izin (Keperluan Mendesak)</option>
                            <option value="sakit">Sakit (Wajib Surat Dokter)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Alasan / Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="4" placeholder="Contoh: Ada urusan keluarga / Demam tinggi..." required></textarea>
                    </div>

                    <div id="upload-section" class="mb-4 d-none">
                        <label class="form-label small fw-bold text-uppercase text-danger">Upload Surat Sakit</label>
                        <div class="p-3 border border-2 border-dashed border-danger-subtle rounded-4 text-center bg-danger-subtle">
                            <i class="bi bi-cloud-upload fs-3 text-danger mb-2 d-block"></i>
                            <input type="file" name="surat_sakit" class="form-control form-control-sm">
                            <small class="text-danger mt-1 d-block" style="font-size: 10px;">Format: JPG/PNG/PDF (Max 2MB)</small>
                        </div>
                    </div>

                    <button name="submit_izin" class="btn btn-success w-100 py-3 rounded-4 fw-bold shadow-sm">
                        Kirim Pengajuan <i class="bi bi-send-fill ms-2"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Bottom Navigation (Mobile) -->
<div class="nav-bottom d-md-none">
    <a href="dashboard.php" class="nav-item-btm">
        <i class="bi bi-house-door"></i> Home
    </a>
    <a href="riwayat.php" class="nav-item-btm">
        <i class="bi bi-clock-history"></i> Riwayat
    </a>
    <a href="izin.php" class="nav-item-btm active">
        <i class="bi bi-file-earmark-text"></i> Izin
    </a>
    <a href="logout.php" class="nav-item-btm text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<script>
    function toggleUpload() {
        const status = document.getElementById('status').value;
        const section = document.getElementById('upload-section');
        if (status === 'sakit') {
            section.classList.remove('d-none');
            // Add simple animation
            section.style.opacity = 0;
            setTimeout(() => { section.style.opacity = 1; section.style.transition = 'opacity 0.3s'; }, 50);
        } else {
            section.classList.add('d-none');
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>