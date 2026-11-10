<?php
session_start();

// Cek sesi login siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Siswa Contoh';

// Inisial Avatar
$words = explode(" ", $nama_lengkap);
$initials = "";
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
} else {
    $initials = strtoupper(substr($nama_lengkap, 0, 2));
}

// Contoh koneksi database untuk mengambil data buku digital (opsional, sesuaikan dengan file koneksi Anda jika ada)
// include '../koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Digital BSE | ANTA THADORE</title>

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

        /* Content Card / Grid Buku */
        .content-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            min-height: 400px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
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
                    <a href="profil.php" class="nav-link">
                        <i class="fa-solid fa-graduation-cap"></i> Profil Siswa
                    </a>
                </li>
                <li>
                    <a href="buku_digital.php" class="nav-link active">
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
                    <div class="portal-tag">PERPUSTAKAAN DIGITAL</div>
                    <h1 class="welcome-title">Buku Digital BSE</h1>
                </div>

                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <!-- Konten Halaman Buku Digital -->
            <div class="content-card">
                <h2 class="card-header-title">Daftar Buku Sekolah Elektronik</h2>
                <p class="card-header-sub">Pilih dan baca buku pelajaran digital Anda di sini.</p>

                <div class="empty-state">
                    <i class="fa-solid fa-books"></i>
                    <h3>Belum ada buku yang tersedia</h3>
                    <p>Daftar buku digital akan segera ditambahkan oleh administrator.</p>
                </div>
            </div>
        </div>

        <div class="footer-text">
            &copy; 2026 ANTA THADORE &bull; Portal Siswa Frontend
        </div>
    </main>

</body>
</html>