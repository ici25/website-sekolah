<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

// Password khusus untuk membuka Berkas Pendaftaran
define('PASSWORD_PENDAFTARAN', 'pendaftaran123');

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Nama Guru, S.Pd.';
$words = explode(" ", $nama_lengkap);
$initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($nama_lengkap, 0, 2));

$error_access = "";
$success_msg = "";
$error_msg = "";

// 1. Verifikasi Password Pendaftaran
if (isset($_POST['verify_access'])) {
    $input_pw = $_POST['access_password'] ?? '';
    if ($input_pw === PASSWORD_PENDAFTARAN) {
        $_SESSION['is_pendaftaran_verified'] = true;
    } else {
        $error_access = "Password akses Berkas Pendaftaran salah! Silakan coba lagi.";
    }
}

// Tombol Kunci Akses Kembali
if (isset($_GET['lock'])) {
    unset($_SESSION['is_pendaftaran_verified']);
    header("Location: pendaftaran.php");
    exit;
}

$is_verified = $_SESSION['is_pendaftaran_verified'] ?? false;

// 2. Proses Upload Berkas sesuai struktur tabel (user_id, nama_berkas, keterangan, file_pdf)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['upload_berkas']) && $is_verified) {
    $nama_berkas = mysqli_real_escape_string($koneksi, $_POST['nama_berkas'] ?? '');
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? 'Berkas pendaftaran guru');
    
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_pdf']['tmp_name'];
        $file_name = $_FILES['file_pdf']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext === 'pdf') {
            $upload_dir = "../uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._]/", "_", $file_name);
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Menyimpan ke database menggunakan kolom yang tepat sesuai struktur tabel Anda
                $query_insert = "INSERT INTO berkas_pendaftaran (user_id, nama_berkas, keterangan, file_pdf) VALUES ('$user_id', '$nama_berkas', '$keterangan', '$new_file_name')";
                if (mysqli_query($koneksi, $query_insert)) {
                    $success_msg = "Berkas pendaftaran berhasil diunggah dan disimpan!";
                } else {
                    $error_msg = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
                }
            } else {
                $error_msg = "Gagal memindahkan file ke folder server.";
            }
        } else {
            $error_msg = "Format file harus berupa PDF!";
        }
    } else {
        $error_msg = "Silakan pilih file PDF terlebih dahulu.";
    }
}

// Ambil data arsip dari database berdasarkan user_id
$result_arsip = null;
if ($is_verified) {
    $query_arsip = "SELECT * FROM berkas_pendaftaran WHERE user_id = '$user_id' ORDER BY id DESC";
    $result_arsip = mysqli_query($koneksi, $query_arsip);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Pendaftaran | ANTA THADORE</title>

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

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); color: var(--text-dark); min-height: 100vh; display: flex; }

        .sidebar {
            width: 260px; background-color: var(--sidebar-bg); color: #ffffff;
            display: flex; flex-direction: column; padding: 24px 16px;
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
            overflow-y: auto;
        }
        .brand-box { display: flex; align-items: center; gap: 12px; padding-bottom: 20px; margin-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .brand-logo { background-color: var(--lime-accent); color: var(--sidebar-bg); font-weight: 800; font-size: 14px; padding: 8px 10px; border-radius: 10px; }
        .brand-text strong { display: block; font-size: 15px; font-weight: 800; line-height: 1.2; }
        .brand-text span { font-size: 11px; color: rgba(255, 255, 255, 0.6); }

        .menu-label { font-size: 10.5px; font-weight: 800; letter-spacing: 1.5px; color: rgba(255, 255, 255, 0.4); margin: 16px 0 8px 12px; }
        .nav-list { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .nav-link {
            display: flex; align-items: center; gap: 12px; padding: 10px 16px;
            color: rgba(255, 255, 255, 0.75); text-decoration: none; font-size: 13.5px; font-weight: 600;
            border-radius: 12px; transition: all 0.2s ease;
        }
        .nav-link:hover { background-color: var(--sidebar-hover); color: #ffffff; }
        .nav-link.active { background-color: var(--sidebar-active); color: #ffffff; font-weight: 700; }
        .nav-link i { font-size: 15px; width: 20px; text-align: center; }

        .sidebar-bottom { margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; flex-direction: column; gap: 6px; }
        .btn-logout { color: #ff9e80; text-decoration: none; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 12px; transition: all 0.2s; }
        .btn-logout:hover { background-color: rgba(255, 255, 255, 0.05); opacity: 0.85; }

        .main-wrapper { margin-left: 260px; flex: 1; padding: 36px 48px; display: flex; flex-direction: column; justify-content: space-between; }
        .top-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .portal-tag { font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: var(--sidebar-bg); text-transform: uppercase; margin-bottom: 4px; }
        .welcome-title { font-size: 32px; font-weight: 800; color: var(--sidebar-bg); }
        .header-actions { display: flex; align-items: center; gap: 12px; }
        .profile-badge { display: flex; align-items: center; gap: 10px; background-color: var(--card-bg); padding: 6px 16px 6px 6px; border-radius: 30px; border: 1px solid var(--border-light); text-decoration: none; color: var(--text-dark); font-size: 13.5px; font-weight: 700; }
        .avatar-circle { width: 34px; height: 34px; background-color: var(--lime-accent); color: var(--sidebar-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }

        .content-card { background-color: var(--card-bg); border-radius: 24px; padding: 32px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02); margin-bottom: 24px; }
        .card-header-title { font-size: 20px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 4px; }
        .card-header-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
        
        .alert-success { background-color: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .lock-card { max-width: 480px; margin: 40px auto; text-align: center; }
        .lock-icon { width: 64px; height: 64px; background-color: #fef3c7; color: #d97706; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px auto; }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 13px; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 12px; font-size: 13.5px; outline: none; background-color: #fcfdfe; }
        textarea.form-control { resize: vertical; min-height: 90px; }
        
        .btn-submit { background-color: var(--sidebar-bg); color: #ffffff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-lock { background-color: #fee2e2; color: #991b1b; text-decoration: none; padding: 8px 14px; border-radius: 12px; font-size: 12.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }

        .doc-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .doc-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 1px solid var(--border-light); border-radius: 16px; background-color: #fafcfb; }
        .doc-info { display: flex; align-items: center; gap: 14px; }
        .doc-icon { width: 40px; height: 40px; background-color: #fef3c7; color: #d97706; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .doc-text h4 { font-size: 14px; font-weight: 800; color: var(--sidebar-bg); }
        .doc-text p { font-size: 12px; color: var(--text-muted); }
        .btn-view { color: var(--sidebar-bg); text-decoration: none; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 6px; background: var(--main-bg); padding: 8px 14px; border-radius: 10px; }

        .footer-text { text-align: center; font-size: 12px; color: var(--text-muted); margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border-light); }
    </style>
</head>
<body>

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
                <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-compass"></i> Dashboard</a></li>
                <li><a href="kelola_jadwal.php" class="nav-link"><i class="fa-solid fa-calendar"></i> Kelola Jadwal</a></li>
                <li><a href="profil.php" class="nav-link"><i class="fa-solid fa-diamond"></i> Profil Guru</a></li>
                <li><a href="rencana.php" class="nav-link"><i class="fa-solid fa-bars"></i> Rencana Pembelajaran</a></li>
                <li><a href="bank_soal.php" class="nav-link"><i class="fa-solid fa-circle-question"></i> Bank Soal PDF</a></li>
                <li><a href="laporan.php" class="nav-link"><i class="fa-solid fa-square-poll-vertical"></i> Laporan Kinerja</a></li>
                <li><a href="bse.php" class="nav-link"><i class="fa-solid fa-book"></i> Buku Digital BSE</a></li>
                <li><a href="pendaftaran.php" class="nav-link active"><i class="fa-solid fa-file-shield"></i> Berkas Pendaftaran</a></li>
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

    <main class="main-wrapper">
        <div>
            <div class="top-header">
                <div>
                    <div class="portal-tag">ARSIP RAHASIA</div>
                    <h1 class="welcome-title">Berkas Pendaftaran</h1>
                </div>
                <div class="header-actions">
                    <?php if ($is_verified): ?>
                        <a href="?lock=true" class="btn-lock" title="Kunci Akses Kembali">
                            <i class="fa-solid fa-lock"></i> Kunci Akses
                        </a>
                    <?php endif; ?>
                    <a href="profil.php" class="profile-badge">
                        <div class="avatar-circle"><?= $initials ?></div>
                        <span>Profil</span>
                    </a>
                </div>
            </div>

            <?php if (!$is_verified): ?>
                <!-- FORM LOCK / INPUT PASSWORD -->
                <div class="content-card lock-card">
                    <div class="lock-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <h2 class="card-header-title">Akses Terkunci</h2>
                    <p class="card-header-sub">Halaman ini bersifat rahasia. Masukkan password khusus untuk membuka menu Berkas Pendaftaran.</p>

                    <?php if ($error_access !== ""): ?>
                        <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_access) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label for="access_password">Password Akses Berkas</label>
                            <input type="password" id="access_password" name="access_password" class="form-control" placeholder="Masukkan password rahasia" required autofocus>
                        </div>
                        <button type="submit" name="verify_access" class="btn-submit" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-key"></i> Buka Akses Berkas
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- KONTEN UTAMA SETELAH PASSWORD BENAR -->
                <div class="content-card">
                    <h2 class="card-header-title">Unggah Berkas Pendaftaran</h2>
                    <p class="card-header-sub">Simpan dokumen penting atau berkas pendaftaran dengan aman.</p>

                    <?php if ($success_msg !== ""): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <?php if ($error_msg !== ""): ?>
                        <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nama_berkas">Nama / Jenis Berkas</label>
                            <input type="text" id="nama_berkas" name="nama_berkas" class="form-control" placeholder="Contoh: Ijazah / Sertifikat Pendidik" required>
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan Tambahan</label>
                            <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Tuliskan keterangan singkat berkas..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="file_pdf">Pilih File PDF</label>
                            <input type="file" id="file_pdf" name="file_pdf" class="form-control" accept=".pdf" required>
                        </div>
                        <button type="submit" name="upload_berkas" class="btn-submit"><i class="fa-solid fa-cloud-arrow-up"></i> Unggah Berkas</button>
                    </form>
                </div>

                <div class="content-card">
                    <h2 class="card-header-title">Arsip Berkas Tersimpan</h2>
                    <p class="card-header-sub">Daftar dokumen berkas pendaftaran yang telah diunggah.</p>

                    <ul class="doc-list">
                        <?php if ($result_arsip && mysqli_num_rows($result_arsip) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_arsip)): ?>
                                <li class="doc-item">
                                    <div class="doc-info">
                                        <div class="doc-icon"><i class="fa-solid fa-file-shield"></i></div>
                                        <div class="doc-text">
                                            <h4><?= htmlspecialchars($row['nama_berkas']) ?></h4>
                                            <p>Keterangan: <?= htmlspecialchars($row['keterangan'] ?? '-') ?> &middot; File: <?= htmlspecialchars($row['file_pdf']) ?></p>
                                        </div>
                                    </div>
                                    <a href="../uploads/<?= htmlspecialchars($row['file_pdf']) ?>" target="_blank" class="btn-view"><i class="fa-solid fa-eye"></i> Lihat Berkas</a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li style="text-align: center; color: var(--text-muted); padding: 20px;">
                                Belum ada berkas pendaftaran yang diunggah.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-text">&copy; 2026 ANTA THADORE &middot; Portal Guru Frontend</div>
    </main>

</body>
</html>