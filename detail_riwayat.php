<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['pembimbing'])) {
    header("Location: login_pembimbing.php");
    exit;
}

$id_user = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// 1. Ambil Data Mahasiswa
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id_user'");
$user = mysqli_fetch_assoc($query_user);

// 2. Ambil Riwayat Lengkap
$query_riwayat = mysqli_query($conn, "SELECT * FROM absensi WHERE user_id = '$id_user' ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - <?= $user['nama'] ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at top right, #f1f5f9, #f8fafc);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #e2e8f0;
            border-radius: 20px;
        }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-5xl mx-auto">
        <div class="mb-8">
            <a href="dashboard_pembimbing.php" class="inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 transition-all group">
                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center mr-3 group-hover:bg-emerald-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>
                KEMBALI KE DASHBOARD
            </a>
        </div>
        
        <div class="glass-card p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 flex flex-col md:flex-row items-center justify-between gap-6 mb-8 border-b-4 border-b-emerald-600">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="relative">
                    <div class="w-24 h-24 bg-gradient-to-tr from-emerald-600 to-teal-400 rounded-[2rem] flex items-center justify-center text-4xl shadow-lg transform -rotate-3 rotate-hover transition-transform">
                        <span class="drop-shadow-md text-white">👤</span>
                    </div>
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-extrabold text-slate-800 uppercase tracking-tighter leading-none mb-2"><?= $user['nama'] ?></h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-2">
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-emerald-100 italic">
                            <?= htmlspecialchars($user['instansi'] ?? 'Mahasiswa Magang') ?>
                        </span>
                        <?php if (!empty($user['jurusan'])): ?>
                        <span class="bg-teal-50 text-teal-700 text-[10px] font-bold px-3 py-1 rounded-full tracking-wider border border-teal-100">
                            <?= htmlspecialchars($user['jurusan']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-slate-200">
                            ID: #00<?= $user['id'] ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-2 w-full md:w-auto">
                <button onclick="window.print()" class="flex items-center justify-center gap-2 bg-slate-800 text-white px-6 py-3 rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-slate-900 transition-all active:scale-95 shadow-lg shadow-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/60 overflow-hidden border border-slate-100 border-t-0">
            <div class="p-8 bg-slate-900 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-extrabold text-white uppercase tracking-[0.2em] italic">Log Aktivitas Kehadiran</h3>
                    <p class="text-[10px] text-slate-400 font-medium mt-1 uppercase tracking-widest">PTPN IV Regional 1</p>
                </div>
                <div class="bg-slate-800 px-4 py-2 rounded-2xl border border-slate-700">
                    <span class="text-[11px] font-black text-emerald-400 uppercase tracking-widest"><?= mysqli_num_rows($query_riwayat) ?> Entri</span>
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Tanggal</th>
                            <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu</th>
                            <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Lokasi</th>
                            <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Dokumentasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($row = mysqli_fetch_assoc($query_riwayat)): ?>
                        <tr class="hover:bg-slate-50/80 transition-all group">
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-800"><?= date('d F Y', strtotime($row['tanggal'])) ?></span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1"><?= date('l', strtotime($row['tanggal'])) ?></span>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <?php if($row['status'] == 'hadir'): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-tighter">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span> HADIR
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black bg-orange-50 text-orange-600 border border-orange-100 uppercase tracking-tighter">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-2 animate-pulse"></span> <?= $row['status'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-black text-slate-700 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100"><?= $row['jam_masuk'] ?? '--:--' ?></span>
                                    <?php if($row['jam_pulang']): ?>
                                        <span class="text-[9px] text-slate-300 font-bold mt-1 italic">Pulang: <?= $row['jam_pulang'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <?php if($row['status'] == 'hadir' && $row['lokasi_gps']): ?>
                                    <a href="https://www.google.com/maps?q=<?= $row['lokasi_gps'] ?>" target="_blank" class="inline-flex items-center text-[9px] font-black text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 transition-all hover:shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        GOOGLE MAPS
                                    </a>
                                <?php else: ?>
                                    <span class="text-[9px] text-slate-200 font-bold italic tracking-widest">OFFLINE</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 text-center">
                                <?php 
                                if ($row['status'] == 'hadir' && !empty($row['foto_masuk'])) {
                                    $path = "uploads/foto_absen/" . $row['foto_masuk'];
                                } elseif (($row['status'] == 'sakit' || $row['status'] == 'izin') && !empty($row['file_surat_sakit'])) {
                                    $path = "uploads/surat_sakit/" . $row['file_surat_sakit'];
                                } else {
                                    $path = "";
                                }

                                if ($path && file_exists($path)): ?>
                                    <a href="<?= $path ?>" target="_blank" class="inline-block relative">
                                        <div class="absolute inset-0 bg-emerald-600 rounded-2xl rotate-3 group-hover:rotate-6 transition-transform opacity-0 group-hover:opacity-20 blur-sm"></div>
                                        <img src="<?= $path ?>" class="w-12 h-12 object-cover rounded-2xl border-4 border-white shadow-lg relative z-10 hover:scale-110 transition-all duration-300">
                                    </a>
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center mx-auto shadow-inner">
                                        <span class="text-[8px] text-slate-300 font-bold italic">EMPTY</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-12 text-center">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.5em] italic">Sistem Monitoring PTPN IV - Version 2.0</p>
        </div>
    </div>

</body>
</html>