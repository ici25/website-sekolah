<?php
session_start();
require_once '../koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Siswa Contoh';
$words = explode(" ", $nama_lengkap);
$initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($nama_lengkap, 0, 2));

$opsi_kelas = [
    'Kelas E 1',
    'Kelas E 2',
    'Kelas E 3',
    'Kelas F 1',
    'Kelas F 2',
    'Kelas F 3',
    'Kelas F Lanjutan 1',
    'Kelas F Lanjutan 2',
    'Kelas F Lanjutan 3'
];

$kelas_pilihan = $_GET['kelas'] ?? 'Kelas E 1';
$kelas_clean   = mysqli_real_escape_string($koneksi, $kelas_pilihan);

// Ambil Jadwal dari MySQL berdasarkan Pilihan Kelas Siswa
$query_siswa = "SELECT * FROM jadwal WHERE kelas = '$kelas_clean' ORDER BY id DESC";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$total_sesi   = mysqli_num_rows($result_siswa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa | ANTA THADORE</title>

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

        .hero-banner { background: linear-gradient(135deg, #0d4638 0%, #175d4c 100%); border-radius: 24px; padding: 36px 40px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .banner-title { font-size: 26px; font-weight: 800; margin-bottom: 12px; }
        .banner-desc { font-size: 13.5px; color: rgba(255, 255, 255, 0.75); }
        .banner-icon-box { width: 64px; height: 64px; background-color: var(--lime-accent); color: var(--sidebar-bg); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 24px; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
        .stat-card { background-color: var(--card-bg); border-radius: 20px; padding: 22px; border: 1px solid var(--border-light); text-decoration: none; color: var(--text-dark); }
        .stat-icon { width: 38px; height: 38px; background-color: #f1f7f4; color: var(--sidebar-bg); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; margin-bottom: 16px; }
        .stat-label { font-size: 12.5px; color: var(--text-muted); margin-bottom: 6px; }
        .stat-number { font-size: 22px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 12px; }
        .stat-footer { font-size: 11.5px; font-weight: 800; color: var(--sidebar-bg); }

        .bottom-grid { display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 20px; }
        .section-card { background-color: var(--card-bg); border-radius: 24px; padding: 28px; border: 1px solid var(--border-light); }
        .section-tag { font-size: 10.5px; font-weight: 800; letter-spacing: 1.5px; color: var(--sidebar-bg); text-transform: uppercase; margin-bottom: 4px; }
        .section-title { font-size: 22px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 16px; }

        .class-filter-form { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; background-color: #f4f8f6; padding: 12px 16px; border-radius: 16px; }
        .class-filter-form label { font-size: 13px; font-weight: 800; color: var(--sidebar-bg); }
        .select-kelas { padding: 8px 14px; border-radius: 10px; border: 1px solid var(--border-light); font-size: 13px; font-weight: 700; color: var(--sidebar-bg); outline: none; background-color: #ffffff; }

        .jadwal-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .jadwal-table th { text-align: left; background-color: #f8faf9; color: var(--sidebar-bg); padding: 12px 14px; font-weight: 800; border-bottom: 2px solid var(--border-light); }
        .jadwal-table td { padding: 12px 14px; border-bottom: 1px solid var(--border-light); color: var(--text-dark); }
        .badge-hari { background-color: var(--lime-accent); color: var(--sidebar-bg); padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 11px; }

        .action-list { display: flex; flex-direction: column; gap: 14px; }
        .action-item { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; border: 1px solid var(--border-light); border-radius: 18px; text-decoration: none; color: var(--text-dark); }
        .action-left { display: flex; align-items: center; gap: 14px; }
        .action-icon { width: 44px; height: 44px; background-color: var(--lime-accent); color: var(--sidebar-bg); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .action-info h4 { font-size: 14.5px; font-weight: 800; color: var(--sidebar-bg); }
        .action-info p { font-size: 12px; color: var(--text-muted); }

        .footer-text { text-align: center; font-size: 12px; color: var(--text-muted); margin-top: 36px; padding-top: 20px; border-top: 1px dashed var(--border-light); }
    </style>
</head>
<body>

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
                <li><a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-compass"></i> Dashboard</a></li>
                <li><a href="profil.php" class="nav-link"><i class="fa-solid fa-graduation-cap"></i> Profil Siswa</a></li>
                <li><a href="../guru/bse.php" class="nav-link"><i class="fa-solid fa-book-bookmark"></i> Buku Digital BSE</a></li>
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
                    <div class="portal-tag">PORTAL PESERTA DIDIK</div>
                    <h1 class="welcome-title">Selamat datang, <?= htmlspecialchars($nama_lengkap) ?></h1>
                </div>
                <a href="profil.php" class="profile-badge">
                    <div class="avatar-circle"><?= $initials ?></div>
                    <span>Profil</span>
                </a>
            </div>

            <div class="hero-banner">
                <div>
                    <div style="font-size: 10.5px; font-weight: 800; color: var(--lime-accent); margin-bottom: 8px;">TAHUN AJARAN 2026/2027</div>
                    <h2 class="banner-title">Jadwal Pelajaran Terintegrasi</h2>
                    <p class="banner-desc">Jadwal pelajaran di bawah ini dikelola langsung oleh Tim Kurikulum.</p>
                </div>
                <div class="banner-icon-box"><i class="fa-solid fa-graduation-cap"></i></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-school"></i></div>
                    <div class="stat-label">Kelas Dipilih</div>
                    <div class="stat-number"><?= htmlspecialchars($kelas_pilihan) ?></div>
                    <div class="stat-footer">Tahun Ajaran 2026/2027</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="stat-label">Total Jam Pelajaran</div>
                    <div class="stat-number"><?= $total_sesi ?> Sesi</div>
                    <div class="stat-footer">Tersimpan di Database</div>
                </div>
                <a href="../guru/bse.php" class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-book-bookmark"></i></div>
                    <div class="stat-label">Buku Digital</div>
                    <div class="stat-number">BSE</div>
                    <div class="stat-footer">Pustaka Siswa</div>
                </a>
                <a href="profil.php" class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-label">Status Siswa</div>
                    <div class="stat-number">Aktif</div>
                    <div class="stat-footer">Terverifikasi</div>
                </a>
            </div>

            <div class="bottom-grid">
                <div class="section-card">
                    <div class="section-tag">AKSES CEPAT</div>
                    <h3 class="section-title">Jadwal Pelajaran</h3>

                    <form method="get" action="" class="class-filter-form">
                        <label for="kelas"><i class="fa-solid fa-filter"></i> Pilih Rombongan Belajar (Kelas):</label>
                        <select name="kelas" id="kelas" class="select-kelas" onchange="this.form.submit()">
                            <?php foreach ($opsi_kelas as $k): ?>
                                <option value="<?= $k ?>" <?= ($kelas_pilihan === $k) ? 'selected' : '' ?>><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <table class="jadwal-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Waktu / Jam</th>
                                <th>Mata Pelajaran</th>
                                <th>Pengajar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_siswa && $total_sesi > 0): ?>
                                <?php while ($j = mysqli_fetch_assoc($result_siswa)): ?>
                                    <tr>
                                        <td><span class="badge-hari"><?= htmlspecialchars($j['hari']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($j['jam']) ?></strong></td>
                                        <td><strong><?= htmlspecialchars($j['mapel']) ?></strong></td>
                                        <td><?= htmlspecialchars($j['guru']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        Belum ada jadwal yang diisi oleh Kurikulum di database untuk kelas ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="section-card">
                    <div class="section-tag">LAYANAN SISWA</div>
                    <h3 class="section-title">Akses Portal</h3>

                    <div class="action-list">
                        <a href="profil.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-user-pen"></i></div>
                                <div class="action-info">
                                    <h4>Edit Profil Siswa</h4>
                                    <p>Perbarui biodata, NISN, dan kontak Anda</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                        <a href="../guru/bse.php" class="action-item">
                            <div class="action-left">
                                <div class="action-icon"><i class="fa-solid fa-book-open"></i></div>
                                <div class="action-info">
                                    <h4>Buku Digital BSE</h4>
                                    <p>Akses koleksi buku pelajaran elektronik gratis</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-text">
            &copy; 2026 ANTA THADORE &middot; Portal Siswa Frontend
        </div>
    </main>

</body>
</html>