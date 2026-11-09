<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Nama Guru, S.Pd.';
$words = explode(" ", $nama_lengkap);
$initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($nama_lengkap, 0, 2));

$success_msg = "";
$error_msg = "";

// 1. Proses Hapus Rencana Pembelajaran
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    
    $query_cek = "SELECT * FROM rencana_pembelajaran WHERE id = '$id_hapus'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    
    if ($result_cek && mysqli_num_rows($result_cek) > 0) {
        $data_file = mysqli_fetch_assoc($result_cek);
        $filename = $data_file['file_path'] ?? $data_file['nama_file'] ?? '';
        
        if (!empty($filename)) {
            $file_path = "../uploads/" . $filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $query_delete = "DELETE FROM rencana_pembelajaran WHERE id = '$id_hapus'";
        if (mysqli_query($koneksi, $query_delete)) {
            $success_msg = "Dokumen Rencana Pembelajaran berhasil dihapus!";
        } else {
            $error_msg = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// 2. Proses Upload Rencana Pembelajaran dengan Deteksi Error Spesifik
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul'] ?? '');
    
    if (isset($_FILES['file_pdf'])) {
        $file_error = $_FILES['file_pdf']['error'];
        
        if ($file_error === UPLOAD_ERR_OK) {
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
                    $query_insert = "INSERT INTO rencana_pembelajaran (guru_id, judul, file_path) VALUES ('$user_id', '$judul', '$new_file_name')";
                    if (mysqli_query($koneksi, $query_insert)) {
                        $success_msg = "Dokumen Rencana Pembelajaran berhasil diunggah dan disimpan!";
                    } else {
                        $error_msg = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
                    }
                } else {
                    $error_msg = "Gagal memindahkan file ke folder server (Periksa izin folder uploads).";
                }
            } else {
                $error_msg = "Format file harus berupa PDF!";
            }
        } elseif ($file_error === UPLOAD_ERR_INI_SIZE || $file_error === UPLOAD_ERR_FORM_SIZE) {
            $error_msg = "Ukuran file terlalu besar melebihi batas yang diizinkan server!";
        } else {
            $error_msg = "Terjadi kesalahan saat mengunggah file (Kode Error: " . $file_error . ").";
        }
    } else {
        $error_msg = "Tidak ada file yang dikirimkan.";
    }
}

// Ambil data Rencana Pembelajaran dari database secara berurutan terbaru
$query_list = "SELECT * FROM rencana_pembelajaran ORDER BY id DESC";
$result_list = mysqli_query($koneksi, $query_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Pembelajaran | ANTA THADORE</title>

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
        .profile-badge { display: flex; align-items: center; gap: 10px; background-color: var(--card-bg); padding: 6px 16px 6px 6px; border-radius: 30px; border: 1px solid var(--border-light); text-decoration: none; color: var(--text-dark); font-size: 13.5px; font-weight: 700; }
        .avatar-circle { width: 34px; height: 34px; background-color: var(--lime-accent); color: var(--sidebar-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }

        .content-card { background-color: var(--card-bg); border-radius: 24px; padding: 32px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02); margin-bottom: 24px; }
        .card-header-title { font-size: 20px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 4px; }
        .card-header-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
        
        .alert-success { background-color: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 12px; font-size: 13.5px; outline: none; background-color: #fcfdfe; }
        .btn-submit { background-color: var(--sidebar-bg); color: #ffffff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }

        .doc-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .doc-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 1px solid var(--border-light); border-radius: 16px; background-color: #fafcfb; }
        .doc-info { display: flex; align-items: center; gap: 14px; }
        .doc-icon { width: 40px; height: 40px; background-color: #fee2e2; color: #dc2626; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .doc-text h4 { font-size: 14px; font-weight: 800; color: var(--sidebar-bg); }
        .doc-text p { font-size: 12px; color: var(--text-muted); }
        
        .action-btns { display: flex; align-items: center; gap: 8px; }
        .btn-view { color: var(--sidebar-bg); text-decoration: none; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 6px; background: var(--main-bg); padding: 8px 14px; border-radius: 10px; }
        .btn-delete { color: #dc2626; text-decoration: none; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 6px; background: #fee2e2; padding: 8px 14px; border-radius: 10px; transition: background 0.2s; }
        .btn-delete:hover { background: #fecaca; }

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
                <li><a href="rencana.php" class="nav-link active"><i class="fa-solid fa-bars"></i> Rencana Pembelajaran</a></li>
                <li><a href="bank_soal.php" class="nav-link"><i class="fa-solid fa-circle-question"></i> Bank Soal PDF</a></li>
                <li><a href="laporan.php" class="nav-link"><i class="fa-solid fa-square-poll-vertical"></i> Laporan Kinerja</a></li>
                <li><a href="bse.php" class="nav-link"><i class="fa-solid fa-book"></i> Buku Digital BSE</a></li>
                <li><a href="pendaftaran.php" class="nav-link"><i class="fa-solid fa-file-shield"></i> Berkas Pendaftaran</a></li>
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
                    <div class="portal-tag">PERANGKAT MENGAJAR</div>
                    <h1 class="welcome-title">Rencana Pembelajaran</h1>
                </div>
                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <div class="content-card">
                <h2 class="card-header-title">Unggah Rencana Pembelajaran (RPP / Modul Ajar)</h2>
                <p class="card-header-sub">Kelola dan unggah modul atau silabus materi pengajaran dalam format PDF.</p>

                <?php if ($success_msg !== ""): ?>
                    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
                <?php endif; ?>

                <?php if ($error_msg !== ""): ?>
                    <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="judul">Judul Dokumen / Mata Pelajaran</label>
                        <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Modul Ajar Informatika Kelas X" required>
                    </div>
                    <div class="form-group">
                        <label for="file_pdf">Pilih File PDF</label>
                        <input type="file" id="file_pdf" name="file_pdf" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-cloud-arrow-up"></i> Unggah Dokumen PDF</button>
                </form>
            </div>

            <div class="content-card">
                <h2 class="card-header-title">Daftar Dokumen Rencana Pembelajaran</h2>
                <p class="card-header-sub">Berkas PDF yang sudah terunggah di portal pengajar.</p>

                <ul class="doc-list">
                    <?php if ($result_list && mysqli_num_rows($result_list) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_list)): ?>
                            <?php 
                                $file_name_display = $row['file_path'] ?? $row['nama_file'] ?? 'dokumen.pdf';
                                $tanggal_display = $row['uploaded_at'] ?? $row['tanggal'] ?? '-';
                            ?>
                            <li class="doc-item">
                                <div class="doc-info">
                                    <div class="doc-icon"><i class="fa-solid fa-file-pdf"></i></div>
                                    <div class="doc-text">
                                        <h4><?= htmlspecialchars($row['judul']) ?></h4>
                                        <p>File: <?= htmlspecialchars($file_name_display) ?> &middot; Tanggal: <?= htmlspecialchars($tanggal_display) ?></p>
                                    </div>
                                </div>
                                <div class="action-btns">
                                    <a href="../uploads/<?= htmlspecialchars($file_name_display) ?>" target="_blank" class="btn-view"><i class="fa-solid fa-eye"></i> Lihat PDF</a>
                                    <a href="rencana.php?hapus=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus rencana pembelajaran ini?');"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li style="text-align: center; color: var(--text-muted); padding: 20px;">
                            Belum ada dokumen rencana pembelajaran yang diunggah.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer-text">&copy; 2026 ANTA THADORE &middot; Portal Guru Frontend</div>
    </main>

</body>
</html>