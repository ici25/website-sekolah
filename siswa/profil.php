<?php
session_start();

// Cek sesi login siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Siswa Contoh';
$nis          = $_SESSION['nis'] ?? '20261001';
$nisn         = $_SESSION['nisn'] ?? '0081234567';
$kelas        = $_SESSION['kelas'] ?? 'X IPA 1';
$email        = $_SESSION['email'] ?? 'siswa@antathadore.sch.id';

// Inisial Avatar
$words = explode(" ", $nama_lengkap);
$initials = "";
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
} else {
    $initials = strtoupper(substr($nama_lengkap, 0, 2));
}

$success_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['nama_lengkap'] = $_POST['nama_lengkap'] ?? $nama_lengkap;
    $_SESSION['nis']          = $_POST['nis'] ?? $nis;
    $_SESSION['nisn']         = $_POST['nisn'] ?? $nisn;
    $_SESSION['kelas']        = $_POST['kelas'] ?? $kelas;
    $_SESSION['email']        = $_POST['email'] ?? $email;
    
    $nama_lengkap = $_SESSION['nama_lengkap'];
    $nis          = $_SESSION['nis'];
    $nisn         = $_SESSION['nisn'];
    $kelas        = $_SESSION['kelas'];
    $email        = $_SESSION['email'];
    
    $success_msg = "Profil siswa berhasil diperbarui!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa | ANTA THADORE</title>

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-bg: #0a3e31;
            --sidebar-hover: #0d4d3d;
            --sidebar-active: #135847;
            --main-bg: #f8f8f2;
            --card-bg: #ffffff;
            --lime-accent: #c0ed38;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --border-light: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--main-bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* --- SIDEBAR KIRI --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            background-color: var(--lime-accent);
            color: var(--sidebar-bg);
            font-weight: 800;
            font-size: 14px;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .brand-text strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
        }

        .menu-label {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.4);
            margin: 16px 0 8px 12px;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: #ffffff;
        }

        .nav-link.active {
            background-color: var(--sidebar-active);
            color: #ffffff;
            font-weight: 700;
        }

        .nav-link i {
            font-size: 15px;
            width: 20px;
            text-align: center;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .btn-logout {
            color: #ff9e80;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: rgba(255, 255, 255, 0.05);
            opacity: 0.85;
        }

        /* --- KONTEN UTAMA (KANAN) --- */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            padding: 36px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .portal-tag {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--sidebar-bg);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .welcome-title {
            font-size: 32px;
            font-weight: 800;
            color: var(--sidebar-bg);
        }

        .profile-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: var(--card-bg);
            padding: 6px 16px 6px 6px;
            border-radius: 30px;
            border: 1px solid var(--border-light);
            text-decoration: none;
            color: var(--text-dark);
            font-size: 13.5px;
            font-weight: 700;
        }

        .avatar-circle {
            width: 34px;
            height: 34px;
            background-color: var(--lime-accent);
            color: var(--sidebar-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
        }

        /* Form Card */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            max-width: 800px;
        }

        .card-header-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--sidebar-bg);
            margin-bottom: 4px;
        }

        .card-header-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-light);
            border-radius: 12px;
            font-size: 13.5px;
            outline: none;
            background-color: #fcfdfe;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--sidebar-bg);
            background-color: #ffffff;
        }

        .btn-save {
            background-color: var(--sidebar-bg);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-save:hover {
            background-color: var(--sidebar-hover);
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-light);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <div>
            <div class="brand-box">
                <div class="brand-logo">AT</div>
                <div class="brand-text">
                    <strong>ANTA THADORE</strong>
                    <span>Portal Siswa</span>
                </div>
            </div>

            <div class="menu-label">MENU UTAMA</div>

            <ul class="nav-list">
                <li>
                    <a href="dashboard.php" class="nav-link">
                        <i class="fa-solid fa-compass"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="profil.php" class="nav-link active">
                        <i class="fa-solid fa-graduation-cap"></i> Profil Siswa
                    </a>
                </li>
                <li>
                    <a href="buku_digital.php" class="nav-link">
                        <i class="fa-solid fa-book"></i> Buku Digital BSE
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-bottom">
            <a href="../logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout?');">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
            <a href="../index.php" class="btn-logout" style="color: rgba(255, 255, 255, 0.75);">
                <i class="fa-solid fa-arrow-left"></i> Halaman Utama
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA (KANAN) -->
    <main class="main-wrapper">
        <div>
            <!-- Header Atas -->
            <div class="top-header">
                <div>
                    <div class="portal-tag">DATA PRIBADI</div>
                    <h1 class="welcome-title">Profil Siswa</h1>
                </div>

                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <!-- Form Card Profil -->
            <div class="content-card">
                <h2 class="card-header-title">Informasi Profil</h2>
                <p class="card-header-sub">Perbarui data profil Anda agar informasi tetap sesuai.</p>

                <?php if ($success_msg !== ""): ?>
                    <div class="alert-success">
                        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($nama_lengkap) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nis">NIS</label>
                            <input type="text" id="nis" name="nis" class="form-control" value="<?= htmlspecialchars($nis) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nisn">NISN</label>
                            <input type="text" id="nisn" name="nisn" class="form-control" value="<?= htmlspecialchars($nisn) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas / Rombongan Belajar</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" value="<?= htmlspecialchars($kelas) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <div class="footer-text">
            &copy; 2026 ANTA THADORE &bull; Portal Siswa Frontend
        </div>
    </main>

</body>
</html>