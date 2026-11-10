<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "../koneksi.php";

$success_msg = "";
$error_msg = "";

// Daftar password akses yang disederhanakan (Wakasek dan TU masuk ke peran 'guru')
$role_passwords = [
    'kepala_sekolah' => 'kepsek2026',
    'guru'           => 'guru123',
    'satpam'         => 'satpam123'
];

$role_names = [
    'kepala_sekolah' => 'Kepala Sekolah',
    'guru'           => 'Guru',
    'satpam'         => 'Satpam'
];

// Buat folder 'uploads_laporan' secara otomatis jika belum ada
$target_dir = "uploads_laporan/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// File database sementara berbasis JSON untuk rekap laporan
$db_json_file = "data_laporan.json";
if (!file_exists($db_json_file)) {
    file_put_contents($db_json_file, json_encode([]));
}

// PROSES GANTI PERAN (Membersihkan sesi laporan dan kembali ke form password)
$current_page_name = basename($_SERVER['PHP_SELF']);
if (isset($_GET['action']) && $_GET['action'] === 'logout_laporan') {
    unset($_SESSION['laporan_active_role']);
    unset($_SESSION['kepsek_view']);
    header("Location: " . $current_page_name);
    exit();
}

// Proses Login Peran Laporan Kinerja
if (isset($_POST['login_role'])) {
    $selected_role = $_POST['role'] ?? '';
    $input_password = $_POST['password'] ?? '';

    if (isset($role_passwords[$selected_role]) && $role_passwords[$selected_role] === $input_password) {
        $_SESSION['laporan_active_role'] = $selected_role;
        $success_msg = "Berhasil masuk ke Laporan Kinerja " . $role_names[$selected_role];
    } else {
        $error_msg = "Kata sandi salah untuk peran tersebut!";
    }
}

// Tombol navigasi khusus Kepala Sekolah
if (isset($_GET['kepsek_view']) && isset($_SESSION['laporan_active_role']) && $_SESSION['laporan_active_role'] === 'kepala_sekolah') {
    $_SESSION['kepsek_view'] = $_GET['kepsek_view'];
}

// PROSES HAPUS LAPORAN
if (isset($_GET['hapus_id']) && isset($_SESSION['laporan_active_role'])) {
    $id_to_delete = $_GET['hapus_id'];
    $current_role = $_SESSION['laporan_active_role'];
    $all_reports = file_exists($db_json_file) ? json_decode(file_get_contents($db_json_file), true) : [];
    
    $updated_reports = [];
    foreach ($all_reports as $rep) {
        if ($rep['id'] === $id_to_delete) {
            if ($current_role === 'kepala_sekolah' || $rep['role_key'] === $current_role) {
                if (!empty($rep['file_path']) && file_exists($rep['file_path'])) {
                    unlink($rep['file_path']);
                }
                continue;
            }
        }
        $updated_reports[] = $rep;
    }
    
    file_put_contents($db_json_file, json_encode($updated_reports, JSON_PRETTY_PRINT));
    $success_msg = "Laporan berhasil dihapus dari sistem.";
    if ($current_role === 'kepala_sekolah') {
        $_SESSION['kepsek_view'] = 'rekap';
    }
}

// Inisial untuk Avatar Profil dari Session Utama Guru
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Guru Pengguna';

// Proses Upload Laporan Kinerja
if (isset($_POST['submit_laporan']) && isset($_SESSION['laporan_active_role'])) {
    $current_role = $_SESSION['laporan_active_role'];
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $nama_pelapor = htmlspecialchars($_POST['nama_pelapor'] ?? $nama_lengkap);
    $kegiatan = htmlspecialchars($_POST['kegiatan'] ?? '');
    $kendala = htmlspecialchars($_POST['kendala'] ?? '-');
    
    if (!empty($nama_pelapor) && !empty($kegiatan) && isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_laporan']['tmp_name'];
        $original_name = basename($_FILES['file_laporan']['name']);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name_final = time() . "_" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $nama_pelapor) . "." . $file_extension;
            $target_file_path = $target_dir . $file_name_final;
            
            if (move_uploaded_file($file_tmp, $target_file_path)) {
                $all_reports = file_exists($db_json_file) ? json_decode(file_get_contents($db_json_file), true) : [];
                $new_report = [
                    'id' => uniqid(),
                    'role_key' => $current_role,
                    'role_name' => $role_names[$current_role],
                    'tanggal' => $tanggal,
                    'nama_pelapor' => $nama_pelapor,
                    'kegiatan' => $kegiatan,
                    'kendala' => $kendala,
                    'file_path' => $target_file_path,
                    'file_original_name' => $original_name,
                    'waktu_kirim' => date('Y-m-d H:i:s')
                ];
                
                array_unshift($all_reports, $new_report);
                file_put_contents($db_json_file, json_encode($all_reports, JSON_PRETTY_PRINT));
                
                $_SESSION['last_pelapor'] = $nama_pelapor;
                
                $success_msg = "Laporan kinerja berhasil dikirim dan tercatat!";
                if ($current_role === 'kepala_sekolah') {
                    $_SESSION['kepsek_view'] = 'rekap';
                }
            } else {
                $error_msg = "Gagal mengunggah file ke direktori server!";
            }
        } else {
            $error_msg = "Format file tidak valid! Hanya diperbolehkan format PDF, DOC, atau DOCX.";
        }
    } else {
        $error_msg = "Harap lengkapi formulir dan lampirkan dokumen laporan (PDF/Word)!";
    }
}

$active_pelapor_name = $_SESSION['last_pelapor'] ?? $nama_lengkap;
$words = explode(" ", $nama_lengkap);
$initials = "";
if (count($words) >= 2) {
    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
} else {
    $initials = strtoupper(substr($nama_lengkap, 0, 2));
}

$all_reports = file_exists($db_json_file) ? json_decode(file_get_contents($db_json_file), true) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja | Portal Guru - ANTA THADORE</title>

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

        .section-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            padding: 28px;
            border: 1px solid var(--border-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            margin-bottom: 24px;
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

        .alert-success { background-color: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 12px; font-size: 13.5px; outline: none; background-color: #fcfdfe; }
        .form-control:focus { border-color: var(--sidebar-bg); }
        .btn-submit { background-color: var(--sidebar-bg); color: #ffffff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        .btn-submit:hover { background-color: var(--sidebar-hover); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13.5px; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border-light); }
        th { font-weight: 800; color: var(--sidebar-bg); background-color: #f8faf9; }
        tr:hover { background-color: #fafbfc; }

        .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; }
        .btn-download { background-color: #e6f4ea; color: #137333; }
        .btn-download:hover { background-color: #ceead6; }
        .btn-delete { background-color: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background-color: #fecaca; }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-light);
        }

        @media (max-width: 1024px) {
            .form-grid { grid-template-columns: 1fr; }
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
                    <a href="dashboard.php" class="nav-link">
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
                    <a href="<?= $current_page_name ?>" class="nav-link active">
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
            <?php if (isset($_SESSION['laporan_active_role'])): ?>
                <a href="<?= $current_page_name ?>?action=logout_laporan" class="btn-logout" onclick="return confirm('Keluar dari sesi laporan ini?');">
                    <i class="fa-solid fa-right-from-bracket"></i> Ganti Peran
                </a>
            <?php endif; ?>
            <a href="../logout.php" class="btn-logout" onclick="return confirm('Yakin ingin logout dari portal?');">
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
                    <h1 class="welcome-title">Laporan Kinerja</h1>
                </div>

                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <?php if ($success_msg !== ""): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <?php if ($error_msg !== ""): ?>
                <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <!-- KONDISI 1: JIKA BELUM LOGIN SEBAGAI PERAN TERTENTU DI LAPORAN -->
            <?php if (!isset($_SESSION['laporan_active_role'])): ?>
                <div class="section-card" style="max-width: 500px; margin: 40px auto;">
                    <div class="text-center" style="margin-bottom: 20px; text-align: center;">
                        <div style="width: 50px; height: 50px; background-color: #e6f4ea; color: var(--sidebar-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 20px;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h3 class="section-title" style="font-size: 20px;">Autentikasi Laporan Kinerja</h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Pilih jabatan dan masukkan sandi khusus untuk mengakses menu laporan.</p>
                    </div>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Pilih Peran / Jabatan</label>
                            <select name="role" required class="form-control">
                                <option value="" disabled selected>-- Pilih Peran Anda --</option>
                                <?php foreach ($role_names as $key => $name): ?>
                                    <option value="<?= $key ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kata Sandi Khusus</label>
                            <input type="password" name="password" required placeholder="Masukkan sandi akses..." class="form-control">
                        </div>

                        <button type="submit" name="login_role" class="btn-submit" style="width: 100%; justify-content: center; margin-top: 6px;">
                            Buka Menu Laporan
                        </button>
                    </form>
                </div>

            <!-- KONDISI 2: JIKA LOGIN SEBAGAI KEPALA SEKOLAH -->
            <?php elseif ($_SESSION['laporan_active_role'] === 'kepala_sekolah'): ?>
                <?php 
                $kepsek_view = $_SESSION['kepsek_view'] ?? 'rekap';
                ?>

                <!-- Navigasi khusus Kepala Sekolah -->
                <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 16px 20px; border-radius: 16px; border: 1px solid var(--border-light); margin-bottom: 20px;">
                    <div style="font-size: 13.5px; font-weight: 700; color: var(--sidebar-bg);">
                        <i class="fa-solid fa-shield-halved mr-1"></i> Mode Aktif: Kepala Sekolah
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="?kepsek_view=rekap" class="btn-action" style="background: <?= $kepsek_view === 'rekap' ? 'var(--sidebar-bg)' : '#f1f5f9' ?>; color: <?= $kepsek_view === 'rekap' ? '#fff' : 'var(--text-dark)' ?>;">
                            <i class="fa-solid fa-list-check"></i> Lihat Rekap Keseluruhan
                        </a>
                        <a href="?kepsek_view=upload" class="btn-action" style="background: <?= $kepsek_view === 'upload' ? 'var(--sidebar-bg)' : '#f1f5f9' ?>; color: <?= $kepsek_view === 'upload' ? '#fff' : 'var(--text-dark)' ?>;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Laporan & Riwayat Saya
                        </a>
                    </div>
                </div>

                <?php if ($kepsek_view === 'rekap'): ?>
                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <div class="section-tag">REKAPITULASI KESELURUHAN</div>
                                <h3 class="section-title">Dashboard Rekapitulasi Laporan (Semua Peran)</h3>
                            </div>
                            <span style="background: var(--sidebar-bg); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                                Total: <?= count($all_reports) ?> Laporan
                            </span>
                        </div>

                        <?php if (empty($all_reports)): ?>
                            <p style="text-align: center; color: var(--text-muted); padding: 30px; font-size: 13.5px;">Belum ada laporan kinerja yang diunggah.</p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Waktu Kirim</th>
                                            <th>Peran / Jabatan</th>
                                            <th>Nama Pelapor</th>
                                            <th>Tanggal</th>
                                            <th>Uraian Kegiatan</th>
                                            <th>Kendala</th>
                                            <th style="text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_reports as $rep): ?>
                                            <tr>
                                                <td style="font-size: 11.5px; color: var(--text-muted);"><?= $rep['waktu_kirim'] ?></td>
                                                <td style="font-weight: 700; color: var(--sidebar-bg);"><?= $rep['role_name'] ?></td>
                                                <td><?= $rep['nama_pelapor'] ?></td>
                                                <td><?= $rep['tanggal'] ?></td>
                                                <td><?= $rep['kegiatan'] ?></td>
                                                <td><?= $rep['kendala'] ?></td>
                                                <td style="text-align: center; white-space: nowrap;">
                                                    <a href="<?= $rep['file_path'] ?>" target="_blank" class="btn-action btn-download" title="Unduh File">
                                                        <i class="fa-solid fa-download"></i> Unduh
                                                    </a>
                                                    <a href="<?= $current_page_name ?>?hapus_id=<?= $rep['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus laporan ini?');" title="Hapus Laporan">
                                                        <i class="fa-solid fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <div class="section-tag">FORMULIR UPLOAD</div>
                                <h3 class="section-title">Upload Laporan Kepala Sekolah</h3>
                            </div>
                        </div>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tanggal Laporan</label>
                                    <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nama Kepala Sekolah & NIP</label>
                                    <input type="text" name="nama_pelapor" required value="<?= htmlspecialchars($active_pelapor_name) ?>" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Uraian Ringkas Kegiatan Manajerial / Supervisi</label>
                                <textarea name="kegiatan" rows="3" required placeholder="Tuliskan ringkasan kegiatan..." class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Catatan / Evaluasi (Opsional)</label>
                                <input type="text" name="kendala" placeholder="Catatan tambahan..." class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Unggah Dokumen Laporan (PDF, DOC, DOCX)</label>
                                <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="form-control" style="background: #fff; padding: 10px;">
                            </div>

                            <button type="submit" name="submit_laporan" class="btn-submit">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Simpan Laporan
                            </button>
                        </form>
                    </div>

                    <!-- RIWAYAT KHUSUS KEPALA SEKOLAH SENDIRI -->
                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <div class="section-tag">RIWAYAT SAYA</div>
                                <h3 class="section-title">Laporan yang Telah Anda Unggah</h3>
                            </div>
                        </div>
                        <?php 
                        $my_reports = array_filter($all_reports, function($r) use ($active_pelapor_name) {
                            return $r['role_key'] === 'kepala_sekolah' && trim(strtolower($r['nama_pelapor'])) === trim(strtolower($active_pelapor_name));
                        });
                        ?>
                        <?php if (empty($my_reports)): ?>
                            <p style="text-align: center; color: var(--text-muted); padding: 20px; font-size: 13.5px;">Belum ada laporan yang Anda unggah.</p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Waktu Kirim</th>
                                            <th>Nama Pelapor</th>
                                            <th>Tanggal</th>
                                            <th>Uraian Kegiatan</th>
                                            <th>Kendala</th>
                                            <th style="text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($my_reports as $rep): ?>
                                            <tr>
                                                <td style="font-size: 11.5px; color: var(--text-muted);"><?= $rep['waktu_kirim'] ?></td>
                                                <td style="font-weight: 700;"><?= $rep['nama_pelapor'] ?></td>
                                                <td><?= $rep['tanggal'] ?></td>
                                                <td><?= $rep['kegiatan'] ?></td>
                                                <td><?= $rep['kendala'] ?></td>
                                                <td style="text-align: center; white-space: nowrap;">
                                                    <a href="<?= $rep['file_path'] ?>" target="_blank" class="btn-action btn-download" title="Unduh File">
                                                        <i class="fa-solid fa-download"></i> Unduh
                                                    </a>
                                                    <a href="<?= $current_page_name ?>?hapus_id=<?= $rep['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus laporan ini?');" title="Hapus Laporan">
                                                        <i class="fa-solid fa-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <!-- KONDISI 3: JIKA LOGIN SEBAGAI GURU / SATPAM -->
            <?php else: ?>
                <?php 
                $current_role = $_SESSION['laporan_active_role']; 
                ?>
                <div class="section-card">
                    <div class="section-header">
                        <div>
                            <div class="section-tag">FORMULIR UPLOAD</div>
                            <h3 class="section-title">Kirim Laporan Kinerja: <?= $role_names[$current_role] ?></h3>
                        </div>
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Tanggal Laporan</label>
                                <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap & NIP/ID</label>
                                <input type="text" name="nama_pelapor" required value="<?= htmlspecialchars($active_pelapor_name) ?>" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Uraian Ringkas Kegiatan / Kinerja</label>
                            <textarea name="kegiatan" rows="3" required placeholder="Tuliskan ringkasan tugas harian atau kegiatan mingguan..." class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kendala / Catatan Tambahan (Opsional)</label>
                            <input type="text" name="kendala" placeholder="Tuliskan kendala jika ada..." class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Unggah Dokumen Laporan (Format: PDF, DOC, DOCX)</label>
                            <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="form-control" style="background: #fff; padding: 10px;">
                        </div>

                        <button type="submit" name="submit_laporan" class="btn-submit">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Kirim Laporan ke Kepala Sekolah
                        </button>
                    </form>
                </div>

                <!-- RIWAYAT HANYA MILIK PENGGUNA TERSEBUT SENDIRI -->
                <div class="section-card">
                    <div class="section-header">
                        <div>
                            <div class="section-tag">RIWAYAT SAYA</div>
                            <h3 class="section-title">Daftar Laporan yang Telah Anda Unggah</h3>
                        </div>
                    </div>

                    <?php 
                    $my_reports = array_filter($all_reports, function($r) use ($current_role, $active_pelapor_name) {
                        return $r['role_key'] === $current_role && trim(strtolower($r['nama_pelapor'])) === trim(strtolower($active_pelapor_name));
                    });
                    ?>

                    <?php if (empty($my_reports)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 30px; font-size: 13.5px;">Belum ada laporan kinerja yang Anda unggah.</p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Waktu Kirim</th>
                                        <th>Nama Pelapor</th>
                                        <th>Tanggal</th>
                                        <th>Uraian Kegiatan</th>
                                        <th>Kendala</th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_reports as $rep): ?>
                                        <tr>
                                            <td style="font-size: 11.5px; color: var(--text-muted);"><?= $rep['waktu_kirim'] ?></td>
                                            <td style="font-weight: 700;"><?= $rep['nama_pelapor'] ?></td>
                                            <td><?= $rep['tanggal'] ?></td>
                                            <td><?= $rep['kegiatan'] ?></td>
                                            <td><?= $rep['kendala'] ?></td>
                                            <td style="text-align: center; white-space: nowrap;">
                                                <a href="<?= $rep['file_path'] ?>" target="_blank" class="btn-action btn-download" title="Unduh File">
                                                    <i class="fa-solid fa-download"></i> Unduh
                                                </a>
                                                <a href="<?= $current_page_name ?>?hapus_id=<?= $rep['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus laporan ini?');" title="Hapus Laporan">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-text">
            &copy; 2026 ANTA THADORE &middot; Portal Guru
        </div>
    </main>

</body>
</html>