<?php
ob_start();
session_start();
include 'includes/db.php';

if (isset($_POST['login'])) {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $password = trim($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            // SIMPAN SESSION DENGAN TELITI
            $_SESSION['login'] = true; // Ini kunci utamanya
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = strtolower(trim($row['role']));

            header("Location: dashboard.php");
            exit;
        } else { $error = "Password Salah!"; }
    } else { $error = "User tidak ditemukan!"; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PTPN IV Regional 1</title>
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
        .login-sidebar {
            background: url('https://source.unsplash.com/random/800x600/?plantation,nature') no-repeat center center;
            background-size: cover;
            position: relative;
        }
        .login-sidebar::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(6, 78, 59, 0.6);
        }
        .form-control {
            border-radius: 1rem;
            padding: 0.8rem 1.2rem;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }
        .form-control:focus {
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
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card card-login bg-white">
                <div class="row g-0">
                    <!-- Image Section (Visible on Tablet+) -->
                    <div class="col-md-6 d-none d-md-block login-sidebar">
                        <div class="position-relative z-1 h-100 d-flex flex-col justify-content-center align-items-center text-white p-5 text-center">
                            <div>
                                <h2 class="fw-bold mb-2">Selamat Datang</h2>
                                <p class="small opacity-75">Sistem Absensi Digital PTPN IV</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="col-12 col-md-6 p-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-4" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold; margin-bottom: 1rem;">
                                P
                            </div>
                            <h3 class="fw-bold text-success text-uppercase">Absensi PTPN</h3>
                            <p class="text-muted small fw-bold text-uppercase letter-spacing-2">Regional 1</p>
                        </div>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger rounded-4 fw-bold small text-uppercase text-center border-0 bg-danger-subtle text-danger mb-4">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button name="login" class="btn btn-primary w-100 mb-4">
                                Masuk Dashboard
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="small text-muted fw-bold mb-0">Belum punya akun?</p>
                            <a href="register.php" class="small fw-bold text-success text-decoration-none text-uppercase">Daftar Sekarang</a>
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