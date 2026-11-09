<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "../koneksi.php";
// Cek sesi login guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Nama Guru, S.Pd.';

$success_msg = "";
$error_msg = "";

// 1. Proses Tambah Catatan Baru
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tambah_catatan'])) {
    $isi_catatan = mysqli_real_escape_string($koneksi, $_POST['isi_catatan'] ?? '');
    
    if (!empty($isi_catatan)) {
        $query_insert = "INSERT INTO catatan_guru (user_id, isi_catatan) VALUES ('$user_id', '$isi_catatan')";
        if (mysqli_query($koneksi, $query_insert)) {
            $success_msg = "Catatan berhasil ditambahkan!";
        } else {
            $error_msg = "Gagal menyimpan catatan: " . mysqli_error($koneksi);
        }
    } else {
        $error_msg = "Isi catatan tidak boleh kosong.";
    }
}

// 2. Proses Hapus Catatan
if (isset($_GET['hapus_catatan'])) {
    $id_catatan = intval($_GET['hapus_catatan']);
    $query_delete = "DELETE FROM catatan_guru WHERE id = '$id_catatan' AND user_id = '$user_id'";
    if (mysqli_query($koneksi, $query_delete)) {
        $success_msg = "Catatan berhasil dihapus!";
    } else {
        $error_msg = "Gagal menghapus catatan.";
    }
}

// Ambil daftar catatan milik guru yang sedang login
$query_catatan = "SELECT * FROM catatan_guru WHERE user_id = '$user_id' ORDER BY id DESC";
$result_catatan = mysqli_query($koneksi, $query_catatan);

// Inisial untuk Avatar Profil
$words = explode(" ", $nama_lengkap);
$initials = "";
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
} else {
    $initials = strtoupper(substr($nama_lengkap, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Guru | ANTA THADORE</title>

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
            --lime-soft: #e8f7ad;
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
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }

        .profile-badge:hover {
            border-color: var(--sidebar-bg);
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

        .hero-banner {
            background: linear-gradient(135deg, #0d4638 0%, #175d4c 100%);
            border-radius: 24px;
            padding: 36px 40px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px rgba(10, 62, 49, 0.12);
        }

        .banner-content {
            max-width: 620px;
        }

        .year-badge {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--lime-accent);
            margin-bottom: 12px;
        }

        .banner-title {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .banner-desc {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.6;
        }

        .banner-icon-box {
            width: 64px;
            height: 64px;
            background-color: var(--lime-accent);
            color: var(--sidebar-bg);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 22px;
            border: 1px solid var(--border-light);
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--sidebar-bg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            background-color: #f1f7f4;
            color: var(--sidebar-bg);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            margin-bottom: 16px;
        }

        .stat-label {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-bottom: 6px;
            font-weight: 500;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: var(--sidebar-bg);
            margin-bottom: 12px;
        }

        .stat-footer {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--sidebar-bg);
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 20px;
        }

        .section-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            padding: 28px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-tag {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--sidebar-bg);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--sidebar-bg);
        }

        .header-icon-btn {
            width: 38px;
            height: 38px;
            background-color: #edf7eb;
            color: var(--sidebar-bg);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .action-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        .action-item:hover {
            border-color: var(--sidebar-bg);
            background-color: #fafdfb;
            transform: translateX(4px);
        }

        .action-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .action-icon {
            width: 42px;
            height: 42px;
            background-color: var(--lime-accent);
            color: var(--sidebar-bg);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .action-info h4 {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--sidebar-bg);
            margin-bottom: 2px;
        }

        .action-info p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .action-arrow {
            color: var(--sidebar-bg);
            font-size: 14px;
        }

        /* --- STYLING CATATAN DINAMIS --- */
        .alert-success { background-color: #dcfce7; color: #166534; padding: 10px 14px; border-radius: 10px; font-size: 12.5px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 10px; font-size: 12.5px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

        .form-group { margin-bottom: 12px; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: 10px; font-size: 13px; outline: none; background-color: #fcfdfe; }
        .btn-submit { background-color: var(--sidebar-bg); color: #ffffff; border: none; padding: 10px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }

        .notes-dynamic-list { list-style: none; display: flex; flex-direction: column; gap: 10px; margin-top: 16px; max-height: 280px; overflow-y: auto; padding-right: 4px; }
        .note-card-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--border-light); border-radius: 12px; background-color: #fafcfb; }
        .note-left { display: flex; align-items: center; gap: 10px; }
        .note-dot { width: 6px; height: 6px; background-color: var(--sidebar-bg); border-radius: 50%; }
        .note-text p { font-size: 13px; font-weight: 700; color: var(--text-dark); }
        .note-text span { font-size: 10.5px; color: var(--text-muted); }
        .btn-delete-note { color: #dc2626; text-decoration: none; font-size: 11.5px; font-weight: 700; background: #fee2e2; padding: 6px 10px; border-radius: 8px; transition: background 0.2s; }
        .btn-delete-note:hover { background: #fecaca; }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-light);
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .bottom-grid {
                grid-template-columns: 1fr;
            }
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
                    <span>Portal Guru</span>
                </div>
            </div>

            <div class="menu-label">MENU UTAMA</div>

            <ul class="nav-list">
                <li>
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fa-solid fa-compass"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="kelola_jadwal.php" class="nav-link">
                        <i class="fa-solid fa-calendar"></i> Kelola Jadwal
                    </a>
                </li>
                <li>
                    <a href="profil.php" class="nav-link">
                        <i class="fa-solid fa-diamond"></i> Profil Guru
                    </a>
                </li>
                <li>
                    <a href="rencana.php" class="nav-link">
                        <i class="fa-solid fa-bars"></i> Rencana Pembelajaran
                    </a>
                </li>
                <li>
                    <a href="bank_soal.php" class="nav-link">
                        <i class="fa-solid fa-circle-question"></i> Bank Soal PDF
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="nav-link">
                        <i class="fa-solid fa-square-poll-vertical"></i> Laporan Kinerja
                    </a>
                </li>
                <li>
                    <a href="bse.php" class="nav-link">
                        <i class="fa-solid fa-book"></i> Buku Digital BSE
                    </a>
                </li>
                <li>
                    <a href="pendaftaran.php" class="nav-link">
                        <i class="fa-solid fa-file-shield"></i> Berkas Pendaftaran
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
                    <div class="portal-tag">PORTAL GURU</div>
                    <h1 class="welcome-title">Selamat datang, <?= htmlspecialchars($nama_lengkap) ?></h1>
                </div>

                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <!-- Banner Hero -->
            <div class="hero-banner">
                <div class="banner-content">
                    <div class="year-badge">TAHUN AJARAN 2026/2027</div>
                    <h2 class="banner-title">Kelola dokumen dan kinerja pembelajaran dalam satu portal.</h2>
                    <p class="banner-desc">Portal ini menampilkan rancangan tata kelola untuk guru. Anda dapat mengedit profil, mengatur rencana pembelajaran, serta mengunggah dokumen PDF dan buku digital.</p>
                </div>
                <div class="banner-icon-box">
                    <i class="fa-solid fa-sparkles"></i>
                </div>
            </div>

            <!-- Grid Kartu Statistik -->
            <div class="stats-grid">
                <a href="rencana.php" class="stat-card">
                    <div>
                        <div class="stat-icon"><i class="fa-solid fa-bars"></i></div>
                        <div class="stat-label">Rencana Pembelajaran</div>
                        <div class="stat-number">1</div>
                    </div>
                    <div class="stat-footer">Dokumen PDF</div>
                </a>

                <a href="bank_soal.php" class="stat-card">
                    <div>
                        <div class="stat-icon"><i class="fa-solid fa-circle-question"></i></div>
                        <div class="stat-label">Bank Soal</div>
                        <div class="stat-number">1</div>
                    </div>
                    <div class="stat-footer">Dokumen PDF</div>
                </a>

                <a href="bse.php" class="stat-card">
                    <div>
                        <div class="stat-icon"><i class="fa-solid fa-book-bookmark"></i></div>
                        <div class="stat-label">Buku Digital BSE</div>
                        <div class="stat-number">New</div>
                    </div>
                    <div class="stat-footer">Upload & Kelola</div>
                </a>

                <a href="profil.php" class="stat-card">
                    <div>
                        <div class="stat-icon"><i class="fa-solid fa-diamond"></i></div>
                        <div class="stat-label">Status Profil</div>
                        <div class="stat-number">Siap</div>
                    </div>
                    <div class="stat-footer">Data aktif</div>
                </a>
            </div>

            <!-- Bottom Grid: Akses Cepat & Catatan Dinamis -->
            <div class="bottom-grid">
                <div class="section-card">
                    <div class="section-header">
                        <div>
                            <div class="section-tag">AKSES CEPAT</div>
                            <h3 class="section-title">Kelola dokumen</h3>
                        </div>
                        <div class="header-icon-btn">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>

                    <div class="action-list">
                        <!-- Akses Cepat ke Buku Digital BSE -->
                        <a href="bse.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-book-bookmark"></i></div>
                                <div class="action-info">
                                    <h4>Buku Digital BSE</h4>
                                    <p>Unggah dan bagikan buku/modul ke siswa</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right action-arrow"></i>
                        </a>

                        <a href="profil.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-diamond"></i></div>
                                <div class="action-info">
                                    <h4>Edit Profil Guru</h4>
                                    <p>Ubah biodata dan informasi mengajar</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right action-arrow"></i>
                        </a>

                        <a href="rencana.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-bars"></i></div>
                                <div class="action-info">
                                    <h4>Tambah Rencana Pembelajaran</h4>
                                    <p>Unggah atau perbarui dokumen PDF</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right action-arrow"></i>
                        </a>

                        <a href="bank_soal.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-circle-question"></i></div>
                                <div class="action-info">
                                    <h4>Tambah Bank Soal</h4>
                                    <p>Simpan kumpulan soal dalam PDF</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right action-arrow"></i>
                        </a>

                        <a href="pendaftaran.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-file-shield"></i></div>
                                <div class="action-info">
                                    <h4>Berkas Pendaftaran</h4>
                                    <p>Arsip dokumen rahasia dengan sandi</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right action-arrow"></i>
                        </a>
                    </div>
                </div>

                <!-- Kolom Catatan Pengingat Interaktif (Tambah & Hapus) -->
                <div class="section-card">
                    <div class="section-header">
                        <div>
                            <div class="section-tag">CATATAN PRIBADI</div>
                            <h3 class="section-title">Pengingat & Rencana</h3>
                        </div>
                        <div class="header-icon-btn">
                            <i class="fa-solid fa-note-sticky"></i>
                        </div>
                    </div>

                    <?php if ($success_msg !== ""): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <?php if ($error_msg !== ""): ?>
                        <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <!-- Form Tambah Catatan -->
                    <form method="post" action="">
                        <div class="form-group">
                            <input type="text" name="isi_catatan" class="form-control" placeholder="Contoh: Rencana upload modul minggu depan..." required>
                        </div>
                        <button type="submit" name="tambah_catatan" class="btn-submit">
                            <i class="fa-solid fa-plus"></i> Tambah Catatan
                        </button>
                    </form>

                    <!-- Daftar Catatan Dinamis -->
                    <ul class="notes-dynamic-list">
                        <?php if ($result_catatan && mysqli_num_rows($result_catatan) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_catatan)): ?>
                                <li class="note-card-item">
                                    <div class="note-left">
                                        <div class="note-dot"></div>
                                        <div class="note-text">
                                            <p><?= htmlspecialchars($row['isi_catatan']) ?></p>
                                            <span><?= htmlspecialchars($row['created_at']) ?></span>
                                        </div>
                                    </div>
                                    <a href="dashboard.php?hapus_catatan=<?= $row['id'] ?>" class="btn-delete-note" onclick="return confirm('Hapus catatan ini?');">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li style="text-align: center; color: var(--text-muted); padding: 20px; font-size: 13px;">
                                Belum ada catatan. Silakan buat catatan pengingat di atas.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-text">
            &copy; 2026 ANTA THADORE &middot; Portal Guru
        </div>
    </main>

</body>
</html>