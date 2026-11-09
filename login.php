<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'guru') {
        header("Location: guru/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'siswa') {
        header("Location: siswa/dashboard.php");
        exit;
    }
}

require_once "config/database.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $role     = $_POST["role"] ?? "guru";

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        $passwordValid = false;
        if ($user) {
            if (password_verify($password, $user["password"]) || $password === $user["password"]) {
                $passwordValid = true;
            }
        }

        if ($user && $passwordValid) {
            if ($user["role"] === $role) {
                $_SESSION["user_id"]  = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"]     = $user["role"];

                if ($role === "guru") {
                    $s = $pdo->prepare("SELECT nama_lengkap FROM guru WHERE user_id = ?");
                    $s->execute([$user["id"]]);
                    $g = $s->fetch();
                    $_SESSION["nama_lengkap"] = $g["nama_lengkap"] ?? $user["username"];
                    header("Location: guru/dashboard.php");
                } else {
                    $s = $pdo->prepare("SELECT nama_lengkap FROM siswa WHERE user_id = ?");
                    $s->execute([$user["id"]]);
                    $sw = $s->fetch();
                    $_SESSION["nama_lengkap"] = $sw["nama_lengkap"] ?? $user["username"];
                    header("Location: siswa/dashboard.php");
                }
                exit;
            } else {
                $error = "Role tidak sesuai dengan akun ini.";
            }
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANTA THADORE | SMAN 4 Tidore Kepulauan</title>
    
    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --green-dark: #0a3e31;
            --green-hover: #062b22;
            --green-light-bg: #f4f8f6;
            --border-green: #e1ebe6;
            --lime-logo: #c0ed38;
            --text-muted: #6b7280;
            --guest-bg: #f5f9e8;
            --guest-icon-bg: #d2f152;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f4f4f5;
        }

        .login-container {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }

        /* SISI KIRI: Foto Gedung SMAN 4 */
        .left-side {
            flex: 1.2;
            position: relative;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.1) 40%, rgba(0, 0, 0, 0.5) 100%), 
                        url('assets/images/bg-sma4tikep.jpg') no-repeat center center;
            background-size: cover;
            padding: 40px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
        }

        /* TATA LETAK HEADER BRAND */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            background-color: var(--lime-logo);
            color: #083c2f;
            font-weight: 800;
            font-size: 15px;
            padding: 8px 10px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .brand-text strong {
            display: block;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .brand-text span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .logo-group-right {
            display: flex;
            align-items: center;
            gap: 12px;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
        }

        .logo-img {
            height: 50px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.4));
        }

        .copyright-text {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        /* SISI KANAN */
        .right-side {
            flex: 0.8;
            background-color: #f4f4f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 410px;
            padding: 36px 32px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e4e4e7;
        }

        .card-tag {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: var(--green-dark);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .card-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
        }

        .card-sub {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .role-tabs {
            display: flex;
            background-color: var(--green-light-bg);
            padding: 4px;
            border-radius: 14px;
            margin-bottom: 18px;
            gap: 4px;
        }

        .role-btn {
            flex: 1;
            padding: 10px 6px;
            border: none;
            background: transparent;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text-muted);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .role-btn.active {
            background-color: #ffffff;
            color: var(--green-dark);
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            font-weight: 800;
            color: var(--green-dark);
            margin-bottom: 20px;
        }

        .dot {
            width: 10px;
            height: 10px;
            background-color: #34d399;
            border-radius: 50%;
        }

        .error-msg {
            background-color: #fee2e2;
            color: #991b1b;
            font-size: 12px;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-box i.icon-left {
            position: absolute;
            left: 14px;
            color: var(--green-dark);
            font-size: 14px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1px solid var(--border-green);
            background-color: #fafcfb;
            border-radius: 12px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s;
        }

        .input-box input:focus {
            border-color: var(--green-dark);
            background-color: #ffffff;
        }

        .btn-toggle-pw {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            font-size: 12.5px;
            font-weight: 800;
            color: var(--green-dark);
            cursor: pointer;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 24px;
            margin-top: 16px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .forgot-link {
            color: var(--green-dark);
            text-decoration: none;
            font-weight: 800;
        }

        .btn-submit {
            width: 100%;
            background-color: var(--green-dark);
            color: #ffffff;
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.2s;
            text-decoration: none;
        }

        .btn-submit:hover {
            background-color: var(--green-hover);
        }

        .guest-card {
            background-color: var(--guest-bg);
            border-radius: 20px;
            padding: 28px 22px;
            text-align: center;
            margin-bottom: 20px;
        }

        .guest-icon-box {
            width: 52px;
            height: 52px;
            background-color: var(--guest-icon-bg);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            color: var(--green-dark);
            font-size: 22px;
        }

        .guest-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--green-dark);
            margin-bottom: 10px;
        }

        .guest-desc {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 22px;
            font-weight: 500;
        }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        @media (max-width: 850px) {
            .login-container { flex-direction: column; }
            .left-side { padding: 24px; min-height: 160px; }
            .right-side { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- SISI KIRI -->
        <div class="left-side">
            <div class="brand-header">
                <div class="brand-left">
                    <div class="brand-logo">AT</div>
                    <div class="brand-text">
                        <strong>ANTA THADORE</strong>
                        <span>Sistem Informasi Sekolah</span>
                    </div>
                </div>

                <div class="logo-group-right">
                    <img src="assets/images/logo-dinas-malut.png" alt="Dinas Pendidikan Malut" class="logo-img" onerror="this.style.display='none'">
                    <img src="assets/images/logo-sma4tikep.png" alt="SMAN 4 Tikep" class="logo-img" onerror="this.style.display='none'">
                </div>
            </div>

               <div class="copyright-text">
                    &copy; 2026 ANTA THADORE &middot; SMAN 4 Tidore Kepulauan
               </div>
    </div>

        <!-- SISI KANAN -->
<div class="right-side">
    <div class="login-card">
        <div class="card-tag">AKSES PORTAL</div>
        <h2 class="card-title">Masuk ke sistem</h2>
        <p class="card-sub">Pilih peran Anda untuk melanjutkan.</p>

        <div class="role-tabs">
            <button type="button" class="role-btn active" id="btnGuru" onclick="selectRole('guru')">
                <i class="fa-solid fa-user-tie"></i> Guru
            </button>

            <button type="button" class="role-btn" id="btnSiswa" onclick="selectRole('siswa')">
                <i class="fa-solid fa-graduation-cap"></i> Siswa
            </button>
        </div>

        <div class="status-indicator">
            <span class="dot"></span>
            <span id="statusText">Anda masuk sebagai Guru</span>
        </div>

        <?php if ($error !== ""): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="loginForm" method="post" action="">
            <input type="hidden" name="role" id="roleInput" value="guru">

            <div class="form-group">
                <label for="username">Username</label>

                <div class="input-box">
                    <i class="fa-regular fa-compass icon-left"></i>

                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username" 
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-box">
                    <i class="fa-solid fa-diamond icon-left"></i>

                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password" 
                        required
                    >

                    <button 
                        type="button" 
                        class="btn-toggle-pw" 
                        id="togglePwBtn" 
                        onclick="togglePassword()"
                    >
                        Lihat
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-label">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>

                <a href="lupa_password.php" class="forgot-link">
                    Lupa password?
                </a>
            </div>

            <button type="submit" class="btn-submit">
                <span id="btnSubmitText">Masuk sebagai Guru</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
    function selectRole(role) {
        const btnGuru = document.getElementById('btnGuru');
        const btnSiswa = document.getElementById('btnSiswa');
        const loginForm = document.getElementById('loginForm');
        const statusText = document.getElementById('statusText');
        const roleInput = document.getElementById('roleInput');
        const btnSubmitText = document.getElementById('btnSubmitText');

        btnGuru.classList.remove('active');
        btnSiswa.classList.remove('active');

        loginForm.style.display = 'block';
        roleInput.value = role;

        if (role === 'guru') {
            btnGuru.classList.add('active');
            statusText.innerText = 'Anda masuk sebagai Guru';
            btnSubmitText.innerText = 'Masuk sebagai Guru';
        } else {
            btnSiswa.classList.add('active');
            statusText.innerText = 'Anda masuk sebagai Siswa';
            btnSubmitText.innerText = 'Masuk sebagai Siswa';
        }
    }

    function togglePassword() {
        const pwInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePwBtn');

        if (pwInput.type === 'password') {
            pwInput.type = 'text';
            toggleBtn.innerText = 'Sembunyi';
        } else {
            pwInput.type = 'password';
            toggleBtn.innerText = 'Lihat';
        }
    }
</script>

</body>
</html>


