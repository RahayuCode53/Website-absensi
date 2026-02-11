<?php
session_start();
include 'includes/db.php';

// Ambil daftar 3 pembimbing dari tabel pembimbing
$list_pembimbing = [];
$q_list = mysqli_query($conn, "SELECT id, nama FROM pembimbing ORDER BY nama ASC");
if ($q_list) {
    while ($row = mysqli_fetch_assoc($q_list)) {
        $list_pembimbing[] = $row;
    }
}

if (isset($_POST['login'])) {
    $pembimbing_id = isset($_POST['pembimbing_id']) ? (int)$_POST['pembimbing_id'] : 0;
    $pass          = $_POST['password'];

    if ($pembimbing_id <= 0) {
        $error = "Silakan pilih nama pembimbing.";
    } else {
        $q_pemb = mysqli_query($conn, "SELECT * FROM pembimbing WHERE id = $pembimbing_id LIMIT 1");

        if ($q_pemb && mysqli_num_rows($q_pemb) === 1) {
            $p = mysqli_fetch_assoc($q_pemb);
            if (password_verify($pass, $p['password'])) {
                // Set session khusus pembimbing
                $_SESSION['pembimbing'] = true;
                $_SESSION['pembimbing_id'] = (int)$p['id'];
                $_SESSION['pembimbing_nama'] = $p['nama'];
                $_SESSION['pembimbing_username'] = $p['username'];

                header("Location: dashboard_pembimbing.php");
                exit;
            } else {
                $error = "Password pembimbing salah!";
            }
        } else {
            $error = "Data pembimbing tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pembimbing | PTPN Presensi</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #006432 0%, #00a859 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
<div class="max-w-md w-full glass-card rounded-[2.5rem] shadow-2xl overflow-hidden p-8 md:p-12">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-3xl shadow-lg mb-6 p-3">
                <img src="assets/img/logo.png" alt="PTPN Logo" class="max-w-full">
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">PTPN Pembimbing</h1>
            <p class="text-slate-400 text-xs font-bold tracking-[0.2em] uppercase mt-2">Portal Khusus Pembimbing</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 text-rose-500 border border-rose-100 text-[10px] font-black uppercase text-center italic">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <select name="pembimbing_id" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 outline-none focus:border-emerald-500 font-bold text-slate-700 transition-all text-sm" required>
                <option value="">Pilih Nama Pembimbing</option>
                <?php foreach ($list_pembimbing as $pb): ?>
                    <option value="<?= $pb['id']; ?>"><?= htmlspecialchars($pb['nama']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="password" name="password" placeholder="Password Pembimbing" class="w-full p-4 rounded-2xl bg-slate-50 border border-slate-200 outline-none focus:border-emerald-500 font-bold text-slate-700 transition-all" required>
            <button type="submit" name="login" class="w-full bg-slate-900 text-white p-4 rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg active:scale-95 duration-300">
                Masuk Dashboard ➜
            </button>
        </form>

        <div class="text-center mt-8 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Contoh sandi awal: <span class="text-emerald-600">ptpnoke / ptpn hebat / ptpn4</span>
        </div>
    </div>
</body>
</html>