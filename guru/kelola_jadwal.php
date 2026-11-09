<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

define('PASSWORD_KURIKULUM', 'kurikulum123');

$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Guru Kurikulum, S.Pd.';
$words = explode(" ", $nama_lengkap);
$initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($nama_lengkap, 0, 2));

$error_access = "";
$success_msg = "";

if (isset($_POST['verify_access'])) {
    $input_pw = $_POST['access_password'] ?? '';
    if ($input_pw === PASSWORD_KURIKULUM) {
        $_SESSION['is_kurikulum_verified'] = true;
    } else {
        $error_access = "Password akses Kurikulum salah! Silakan coba lagi.";
    }
}

if (isset($_GET['lock'])) {
    unset($_SESSION['is_kurikulum_verified']);
    header("Location: kelola_jadwal.php");
    exit;
}

// 1. Simpan Jadwal Baru ke Database MySQL
if (isset($_POST['save_schedule']) && ($_SESSION['is_kurikulum_verified'] ?? false)) {
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');
    $hari  = mysqli_real_escape_string($koneksi, $_POST['hari'] ?? '');
    $jam   = mysqli_real_escape_string($koneksi, $_POST['jam'] ?? '');
    $mapel = mysqli_real_escape_string($koneksi, $_POST['mapel'] ?? '');
    $guru  = mysqli_real_escape_string($koneksi, $_POST['guru'] ?? '');

    if ($kelas !== "" && $hari !== "" && $jam !== "" && $mapel !== "" && $guru !== "") {
        $query_insert = "INSERT INTO jadwal (kelas, hari, jam, mapel, guru) VALUES ('$kelas', '$hari', '$jam', '$mapel', '$guru')";
        if (mysqli_query($koneksi, $query_insert)) {
            $success_msg = "Jadwal pelajaran untuk $kelas ($hari, $jam - $mapel) berhasil disimpan permanen ke database!";
        } else {
            $error_access = "Gagal menyimpan jadwal: " . mysqli_error($koneksi);
        }
    }
}

// 2. Hapus Jadwal dari Database MySQL
if (isset($_GET['hapus_id']) && ($_SESSION['is_kurikulum_verified'] ?? false)) {
    $hapus_id = (int)$_GET['hapus_id'];
    $query_delete = "DELETE FROM jadwal WHERE id = $hapus_id";
    mysqli_query($koneksi, $query_delete);
    header("Location: kelola_jadwal.php");
    exit;
}

$is_verified = $_SESSION['is_kurikulum_verified'] ?? false;
$filter_view_kelas = $_GET['view_kelas'] ?? 'Semua';

// 3. Ambil Data Jadwal dari Database MySQL
if ($filter_view_kelas !== 'Semua') {
    $filter_clean = mysqli_real_escape_string($koneksi, $filter_view_kelas);
    $query_select = "SELECT * FROM jadwal WHERE kelas = '$filter_clean' ORDER BY id DESC";
} else {
    $query_select = "SELECT * FROM jadwal ORDER BY id DESC";
}

$result_jadwal = mysqli_query($koneksi, $query_select);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Pelajaran | ANTA THADORE</title>

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
        .card-header-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
        
        .alert-error { background-color: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background-color: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .lock-card { max-width: 480px; margin: 40px auto; text-align: center; }
        .lock-icon { width: 64px; height: 64px; background-color: #fef3c7; color: #d97706; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px auto; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 13px; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 12px; font-size: 13.5px; outline: none; background-color: #fcfdfe; }
        
        .btn-save { background-color: var(--sidebar-bg); color: #ffffff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-lock { background-color: #fee2e2; color: #991b1b; text-decoration: none; padding: 8px 14px; border-radius: 12px; font-size: 12.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }

        .table-filter-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; background-color: #f4f8f6; padding: 12px 16px; border-radius: 16px; }
        .table-filter-bar label { font-size: 13px; font-weight: 800; color: var(--sidebar-bg); }
        .select-filter { padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-light); font-size: 13px; font-weight: 700; color: var(--sidebar-bg); outline: none; background-color: #ffffff; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
        .data-table th { text-align: left; background-color: #f8faf9; color: var(--sidebar-bg); padding: 14px 16px; font-weight: 800; border-bottom: 2px solid var(--border-light); }
        .data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-light); color: var(--text-dark); vertical-align: middle; }
        .data-table tr:hover { background-color: #fafdfb; }

        .badge-kelas { background-color: var(--lime-accent); color: var(--sidebar-bg); padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 11.5px; display: inline-block; }
        .badge-hari { background-color: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; }

        .btn-delete { color: #dc2626; background: #fee2e2; border: none; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background-color: #fca5a5; }

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
                    <span>Portal Kurikulum</span>
                </div>
            </div>
            <div class="menu-label">MENU UTAMA</div>
            <ul class="nav-list">
                <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-compass"></i> Dashboard</a></li>
                <li><a href="kelola_jadwal.php" class="nav-link active"><i class="fa-solid fa-calendar-days"></i> Kelola Jadwal</a></li>
                <li><a href="profil.php" class="nav-link"><i class="fa-solid fa-diamond"></i> Profil Guru</a></li>
                <li><a href="rencana.php" class="nav-link"><i class="fa-solid fa-bars"></i> Rencana Pembelajaran</a></li>
                <li><a href="bank_soal.php" class="nav-link"><i class="fa-solid fa-circle-question"></i> Bank Soal PDF</a></li>
                <li><a href="laporan.php" class="nav-link"><i class="fa-solid fa-square-poll-vertical"></i> Laporan Kinerja</a></li>
                <li><a href="bse.php" class="nav-link"><i class="fa-solid fa-book-bookmark"></i> Buku Digital BSE</a></li>
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
                    <div class="portal-tag">BIDANG KURIKULUM</div>
                    <h1 class="welcome-title">Kelola Jadwal Pelajaran</h1>
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
                <div class="content-card lock-card">
                    <div class="lock-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <h2 class="card-header-title">Akses Terkunci</h2>
                    <p class="card-header-sub">Halaman ini khusus untuk Tim Bidang Kurikulum. Masukkan password khusus untuk membuka akses pengisian jadwal.</p>

                    <?php if ($error_access !== ""): ?>
                        <div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error_access) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label for="access_password">Password Akses Kurikulum</label>
                            <input type="password" id="access_password" name="access_password" class="form-control" placeholder="Masukkan password khusus" required autofocus>
                        </div>
                        <button type="submit" name="verify_access" class="btn-save" style="width: 100%;">
                            <i class="fa-solid fa-key"></i> Buka Akses Kurikulum
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="content-card">
                    <h2 class="card-header-title">Input Jadwal Pelajaran Sekolah</h2>
                    <p class="card-header-sub">Isi data jadwal pelajaran per kelas agar langsung tersimpan dan tampil otomatis pada akun siswa.</p>

                    <?php if ($success_msg !== ""): ?>
                        <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="kelas">Target Rombongan Belajar / Kelas</label>
                                <select id="kelas" name="kelas" class="form-control" required>
                                    <option value="Kelas E 1">Kelas E 1</option>
                                    <option value="Kelas E 2">Kelas E 2</option>
                                    <option value="Kelas E 3">Kelas E 3</option>
                                    <option value="Kelas F 1">Kelas F 1</option>
                                    <option value="Kelas F 2">Kelas F 2</option>
                                    <option value="Kelas F 3">Kelas F 3</option>
                                    <option value="Kelas F Lanjutan 1">Kelas F Lanjutan 1</option>
                                    <option value="Kelas F Lanjutan 2">Kelas F Lanjutan 2</option>
                                    <option value="Kelas F Lanjutan 3">Kelas F Lanjutan 3</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="hari">Hari</label>
                                <select id="hari" name="hari" class="form-control" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="jam">Waktu / Jam Pelajaran</label>
                                <input type="text" id="jam" name="jam" class="form-control" placeholder="Contoh: 07.30 - 09.30" required>
                            </div>
                            <div class="form-group">
                                <label for="mapel">Mata Pelajaran</label>
                                <input type="text" id="mapel" name="mapel" class="form-control" placeholder="Contoh: Informatika" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="guru">Guru Pengampu</label>
                            <input type="text" id="guru" name="guru" class="form-control" placeholder="Contoh: Salsabilah Gaila Qur'ani, S.Pd." required>
                        </div>

                        <button type="submit" name="save_schedule" class="btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal Pelajaran
                        </button>
                    </form>
                </div>

                <div class="content-card">
                    <h2 class="card-header-title">Daftar Jadwal Pelajaran Terisi</h2>
                    <p class="card-header-sub">Gunakan daftar ini untuk memantau kelas mana saja yang sudah terisi jadwalnya.</p>

                    <form method="get" action="" class="table-filter-bar">
                        <label for="view_kelas"><i class="fa-solid fa-filter"></i> Saring Tampilan Kelas:</label>
                        <select name="view_kelas" id="view_kelas" class="select-filter" onchange="this.form.submit()">
                            <option value="Semua" <?= ($filter_view_kelas === 'Semua') ? 'selected' : '' ?>>Semua Rombongan Belajar / Kelas</option>
                            <option value="Kelas E 1" <?= ($filter_view_kelas === 'Kelas E 1') ? 'selected' : '' ?>>Kelas E 1</option>
                            <option value="Kelas E 2" <?= ($filter_view_kelas === 'Kelas E 2') ? 'selected' : '' ?>>Kelas E 2</option>
                            <option value="Kelas E 3" <?= ($filter_view_kelas === 'Kelas E 3') ? 'selected' : '' ?>>Kelas E 3</option>
                            <option value="Kelas F 1" <?= ($filter_view_kelas === 'Kelas F 1') ? 'selected' : '' ?>>Kelas F 1</option>
                            <option value="Kelas F 2" <?= ($filter_view_kelas === 'Kelas F 2') ? 'selected' : '' ?>>Kelas F 2</option>
                            <option value="Kelas F 3" <?= ($filter_view_kelas === 'Kelas F 3') ? 'selected' : '' ?>>Kelas F 3</option>
                            <option value="Kelas F Lanjutan 1" <?= ($filter_view_kelas === 'Kelas F Lanjutan 1') ? 'selected' : '' ?>>Kelas F Lanjutan 1</option>
                            <option value="Kelas F Lanjutan 2" <?= ($filter_view_kelas === 'Kelas F Lanjutan 2') ? 'selected' : '' ?>>Kelas F Lanjutan 2</option>
                            <option value="Kelas F Lanjutan 3" <?= ($filter_view_kelas === 'Kelas F Lanjutan 3') ? 'selected' : '' ?>>Kelas F Lanjutan 3</option>
                        </select>
                    </form>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rombongan Belajar (Kelas)</th>
                                <th>Hari</th>
                                <th>Waktu / Jam</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_jadwal && mysqli_num_rows($result_jadwal) > 0): ?>
                                <?php while ($item = mysqli_fetch_assoc($result_jadwal)): ?>
                                    <tr>
                                        <td><span class="badge-kelas"><?= htmlspecialchars($item['kelas']) ?></span></td>
                                        <td><span class="badge-hari"><?= htmlspecialchars($item['hari']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($item['jam']) ?></strong></td>
                                        <td><strong><?= htmlspecialchars($item['mapel']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['guru']) ?></td>
                                        <td>
                                            <a href="?hapus_id=<?= $item['id'] ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus baris jadwal ini?')">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                        Belum ada jadwal yang tersimpan di database MySQL untuk kriteria kelas ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-text">&copy; 2026 ANTA THADORE &middot; Sistem Informasi Kurikulum SMAN 4</div>
    </main>

</body>
</html>