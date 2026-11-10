<?php
include 'config/database.php'; // Sesuaikan path config jika berada di dalam folder guru/
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'guru';
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'Pengguna';

$words = explode(" ", $nama_lengkap);
$initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) : strtoupper(substr($nama_lengkap, 0, 2));

// PROSES UPLOAD BUKU OLEH GURU
$pesan_sukses = "";
$pesan_error = "";

if ($role === 'guru' && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_buku'])) {
    $judul          = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $mata_pelajaran = mysqli_real_escape_string($koneksi, $_POST['mata_pelajaran']);
    $kelas          = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $semester       = mysqli_real_escape_string($koneksi, $_POST['semester']);
    $kategori       = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    // Validasi & Proses File PDF
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === 0) {
        $file_nama = $_FILES['file_pdf']['name'];
        $file_tmp  = $_FILES['file_pdf']['tmp_name'];
        $file_size = $_FILES['file_pdf']['size'];
        $ekstensi  = strtolower(pathinfo($file_nama, PATHINFO_EXTENSION));

        if ($ekstensi === 'pdf') {
            // Hitung ukuran dalam MB
            $ukuran_mb = round($file_size / (1024 * 1024), 1) . ' MB';
            
            // Buat nama file unik agar tidak bentrok
            nama_baru:
            $nama_file_unik = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_nama);
            $tujuan = '../uploads_buku/' . $nama_file_unik;

            if (move_uploaded_file($file_tmp, $tujuan)) {
                $query_insert = "INSERT INTO buku_digital (judul, mata_pelajaran, kelas, semester, kategori, file_pdf, ukuran) VALUES ('$judul', '$mata_pelajaran', '$kelas', '$semester', '$kategori', '$nama_file_unik', '$ukuran_mb')";
                if (mysqli_query($koneksi, $query_insert)) {
                    $pesan_sukses = "Buku digital berhasil diunggah!";
                } else {
                    $pesan_error = "Gagal menyimpan ke database.";
                }
            } else {
                $pesan_error = "Gagal memindahkan file ke folder uploads_buku.";
            }
        } else {
            $pesan_error = "Hanya file dengan format PDF yang diizinkan!";
        }
    } else {
        $pesan_error = "Pilih file PDF terlebih dahulu!";
    }
}

// AMBIL DATA FILTER
$filter_buku     = $_GET['buku'] ?? 'Semua';
$filter_kelas    = $_GET['kelas'] ?? 'Semua';
$filter_semester = $_GET['semester'] ?? 'Semua';

// AMBIL DATA DARI DATABASE
$where_clauses = [];
if ($filter_buku !== 'Semua') {
    $where_clauses[] = "mata_pelajaran = '" . mysqli_real_escape_string($koneksi, $filter_buku) . "'";
}
if ($filter_kelas !== 'Semua') {
    $where_clauses[] = "kelas = '" . mysqli_real_escape_string($koneksi, $filter_kelas) . "'";
}
if ($filter_semester !== 'Semua') {
    $where_clauses[] = "semester = '" . mysqli_real_escape_string($koneksi, $filter_semester) . "'";
}

$sql = "SELECT * FROM buku_digital";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY id DESC";

$result_buku = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Digital BSE | ANTA THADORE</title>

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
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 24px 16px; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100;
        }
        .brand-box { display: flex; align-items: center; gap: 12px; padding-bottom: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .brand-logo { background-color: var(--lime-accent); color: var(--sidebar-bg); font-weight: 800; font-size: 14px; padding: 8px 10px; border-radius: 10px; }
        .brand-text strong { display: block; font-size: 15px; font-weight: 800; line-height: 1.2; }
        .brand-text span { font-size: 11px; color: rgba(255, 255, 255, 0.6); }

        .menu-label { font-size: 10.5px; font-weight: 800; letter-spacing: 1.5px; color: rgba(255, 255, 255, 0.4); margin: 24px 0 12px 12px; }
        .nav-list { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .nav-link {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            color: rgba(255, 255, 255, 0.75); text-decoration: none; font-size: 13.5px; font-weight: 600;
            border-radius: 12px; transition: all 0.2s ease;
        }
        .nav-link:hover { background-color: var(--sidebar-hover); color: #ffffff; }
        .nav-link.active { background-color: var(--sidebar-active); color: #ffffff; font-weight: 700; }
        .nav-link i { font-size: 15px; width: 20px; text-align: center; }

        .sidebar-bottom { padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-logout { color: rgba(255, 255, 255, 0.65); text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; transition: all 0.2s; }
        .btn-logout:hover { color: #ff8b8b; background-color: rgba(255, 255, 255, 0.05); }

        .main-wrapper { margin-left: 260px; flex: 1; padding: 36px 48px; display: flex; flex-direction: column; justify-content: space-between; }
        .top-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .portal-tag { font-size: 11px; font-weight: 800; letter-spacing: 1.5px; color: var(--sidebar-bg); text-transform: uppercase; margin-bottom: 4px; }
        .welcome-title { font-size: 32px; font-weight: 800; color: var(--sidebar-bg); }

        .profile-badge { display: flex; align-items: center; gap: 10px; background-color: var(--card-bg); padding: 6px 16px 6px 6px; border-radius: 30px; border: 1px solid var(--border-light); text-decoration: none; color: var(--text-dark); font-size: 13.5px; font-weight: 700; }
        .avatar-circle { width: 34px; height: 34px; background-color: var(--lime-accent); color: var(--sidebar-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }

        /* Tombol Upload & Modal Sederhana */
        .btn-upload-trigger {
            background-color: var(--lime-accent); color: var(--sidebar-bg); border: none; padding: 10px 18px;
            border-radius: 12px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .btn-upload-trigger:hover { opacity: 0.9; }

        .upload-card {
            background-color: var(--card-bg); border-radius: 20px; padding: 24px 28px;
            border: 1px solid var(--border-light); margin-bottom: 24px; display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .upload-card.active { display: block; }
        .upload-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 12px; font-weight: 800; color: var(--text-muted); }
        .form-control { padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border-light); font-size: 13px; font-weight: 600; outline: none; background: #fafcfb; }
        .form-control:focus { border-color: var(--sidebar-bg); background: #fff; }

        .filter-card { background-color: var(--card-bg); border-radius: 20px; padding: 22px 28px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0,0,0,0.02); margin-bottom: 24px; }
        .filter-title { font-size: 14px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .filter-form { display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 16px; align-items: end; }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-group label { font-size: 12px; font-weight: 800; color: var(--text-muted); }
        .filter-select { padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border-light); font-size: 13px; font-weight: 700; color: var(--sidebar-bg); background-color: #fafcfb; outline: none; cursor: pointer; }
        .btn-reset { background-color: var(--main-bg); color: var(--sidebar-bg); text-decoration: none; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border-light); }

        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }

        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .book-card { background-color: var(--card-bg); border: 1px solid var(--border-light); border-radius: 20px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02); }
        .book-card:hover { transform: translateY(-4px); border-color: var(--sidebar-bg); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06); }
        .book-cover { width: 54px; height: 54px; background-color: #f1f8f5; color: var(--sidebar-bg); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 16px; }
        .book-title { font-size: 16px; font-weight: 800; color: var(--sidebar-bg); margin-bottom: 6px; line-height: 1.3; }
        .book-meta { font-size: 12px; color: var(--text-muted); margin-bottom: 20px; }
        .badge-group { display: flex; gap: 6px; margin-bottom: 10px; }
        .book-badge { background-color: var(--lime-accent); color: var(--sidebar-bg); padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; }
        .semester-badge { background-color: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; }
        .btn-read { background-color: var(--sidebar-bg); color: #ffffff; text-decoration: none; text-align: center; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
        .btn-read:hover { background-color: var(--sidebar-hover); }

        .empty-state { grid-column: 1 / -1; background-color: var(--card-bg); border: 1px dashed var(--border-light); border-radius: 20px; padding: 40px; text-align: center; color: var(--text-muted); }
        .empty-state i { font-size: 36px; color: var(--sidebar-bg); margin-bottom: 12px; }
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
                    <span><?= ($role === 'guru') ? 'Portal Guru' : 'Portal Siswa' ?></span>
                </div>
            </div>

            <div class="menu-label">MENU UTAMA</div>

            <ul class="nav-list">
                <?php if ($role === 'guru'): ?>
                    <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-compass"></i> Dashboard</a></li>
                    <li><a href="kelola_jadwal.php" class="nav-link"><i class="fa-solid fa-calendar-days"></i> Kelola Jadwal</a></li>
                    <li><a href="profil.php" class="nav-link"><i class="fa-solid fa-diamond"></i> Profil Guru</a></li>
                    <li><a href="rencana.php" class="nav-link"><i class="fa-solid fa-bars"></i> Rencana Pembelajaran</a></li>
                    <li><a href="bank_soal.php" class="nav-link"><i class="fa-solid fa-circle-question"></i> Bank Soal PDF</a></li>
                    <li><a href="laporan.php" class="nav-link"><i class="fa-solid fa-square-poll-vertical"></i> Laporan Kinerja</a></li>
                    <li><a href="bse.php" class="nav-link active"><i class="fa-solid fa-book-bookmark"></i> Buku Digital BSE</a></li>
                <?php else: ?>
                    <li><a href="../siswa/dashboard.php" class="nav-link"><i class="fa-solid fa-compass"></i> Dashboard</a></li>
                    <li><a href="../siswa/profil.php" class="nav-link"><i class="fa-solid fa-graduation-cap"></i> Profil Siswa</a></li>
                    <li><a href="bse.php" class="nav-link active"><i class="fa-solid fa-book-bookmark"></i> Buku Digital BSE</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-bottom">
            <a href="../logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-left"></i> Keluar / Kembali
            </a>
        </div>
    </aside>

    <main class="main-wrapper">
        <div>
            <div class="top-header">
                <div>
                    <div class="portal-tag">PERPUSTAKAAN DIGITAL</div>
                    <h1 class="welcome-title">Buku Sekolah Elektronik (BSE)</h1>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <?php if ($role === 'guru'): ?>
                        <button type="button" class="btn-upload-trigger" onclick="toggleUploadForm()">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Buku Baru
                        </button>
                    <?php endif; ?>

                    <a href="<?= ($role === 'guru') ? 'profil.php' : '../siswa/profil.php' ?>" class="profile-badge">
                        <div class="avatar-circle"><?= $initials ?></div>
                        <span>Profil</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= $pesan_sukses ?></div>
            <?php endif; ?>
            <?php if (!empty($pesan_error)): ?>
                <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= $pesan_error ?></div>
            <?php endif; ?>

            <!-- FORM UPLOAD KHUSUS GURU -->
            <?php if ($role === 'guru'): ?>
                <div id="formUploadCard" class="upload-card">
                    <div class="filter-title" style="margin-bottom: 16px;">
                        <i class="fa-solid fa-file-arrow-up"></i> Unggah Dokumen / Buku Digital PDF Baru
                    </div>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="upload-grid">
                            <div class="form-group">
                                <label>Judul Buku:</label>
                                <input type="text" name="judul" class="form-control" placeholder="Contoh: Modul Ajar Bab 1" required>
                            </div>
                            <div class="form-group">
                                <label>Mata Pelajaran:</label>
                                <select name="mata_pelajaran" class="form-control" required>
                                    <option value="Informatika">Informatika</option>
                                    <option value="Matematika Wajib">Matematika Wajib</option>
                                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                    <option value="Fisika">Fisika</option>
                                    <option value="Biologi">Biologi</option>
                                    <option value="Sejarah Indonesia">Sejarah Indonesia</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tingkat Kelas:</label>
                                <select name="kelas" class="form-control" required>
                                    <option value="Kelas 10">Kelas 10 (X)</option>
                                    <option value="Kelas 11">Kelas 11 (XI)</option>
                                    <option value="Kelas 12">Kelas 12 (XII)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester:</label>
                                <select name="semester" class="form-control" required>
                                    <option value="Ganjil">Semester Ganjil</option>
                                    <option value="Genap">Semester Genap</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Kategori:</label>
                                <input type="text" name="kategori" class="form-control" value="Materi Pembelajaran" required>
                            </div>
                            <div class="form-group">
                                <label>Pilih File PDF (.pdf):</label>
                                <input type="file" name="file_pdf" class="form-control" accept=".pdf" required>
                            </div>
                        </div>
                        <button type="submit" name="upload_buku" class="btn-upload-trigger">
                            <i class="fa-solid fa-save"></i> Simpan & Unggah Buku
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="filter-card">
                <div class="filter-title">
                    <i class="fa-solid fa-filter"></i> Saring Katalog Buku Digital
                </div>

                <form method="get" action="" class="filter-form">
                    <div class="filter-group">
                        <label for="buku">Mata Pelajaran / Buku:</label>
                        <select name="buku" id="buku" class="filter-select" onchange="this.form.submit()">
                            <option value="Semua" <?= ($filter_buku === 'Semua') ? 'selected' : '' ?>>Semua Mata Pelajaran</option>
                            <option value="Informatika" <?= ($filter_buku === 'Informatika') ? 'selected' : '' ?>>Informatika</option>
                            <option value="Matematika Wajib" <?= ($filter_buku === 'Matematika Wajib') ? 'selected' : '' ?>>Matematika Wajib</option>
                            <option value="Bahasa Indonesia" <?= ($filter_buku === 'Bahasa Indonesia') ? 'selected' : '' ?>>Bahasa Indonesia</option>
                            <option value="Fisika" <?= ($filter_buku === 'Fisika') ? 'selected' : '' ?>>Fisika</option>
                            <option value="Biologi" <?= ($filter_buku === 'Biologi') ? 'selected' : '' ?>>Biologi</option>
                            <option value="Sejarah Indonesia" <?= ($filter_buku === 'Sejarah Indonesia') ? 'selected' : '' ?>>Sejarah Indonesia</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="kelas">Tingkat Kelas:</label>
                        <select name="kelas" id="kelas" class="filter-select" onchange="this.form.submit()">
                            <option value="Semua" <?= ($filter_kelas === 'Semua') ? 'selected' : '' ?>>Semua Kelas</option>
                            <option value="Kelas 10" <?= ($filter_kelas === 'Kelas 10') ? 'selected' : '' ?>>Kelas 10 (X)</option>
                            <option value="Kelas 11" <?= ($filter_kelas === 'Kelas 11') ? 'selected' : '' ?>>Kelas 11 (XI)</option>
                            <option value="Kelas 12" <?= ($filter_kelas === 'Kelas 12') ? 'selected' : '' ?>>Kelas 12 (XII)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="semester">Semester:</label>
                        <select name="semester" id="semester" class="filter-select" onchange="this.form.submit()">
                            <option value="Semua" <?= ($filter_semester === 'Semua') ? 'selected' : '' ?>>Semua Semester</option>
                            <option value="Ganjil" <?= ($filter_semester === 'Ganjil') ? 'selected' : '' ?>>Semester Ganjil</option>
                            <option value="Genap" <?= ($filter_semester === 'Genap') ? 'selected' : '' ?>>Semester Genap</option>
                        </select>
                    </div>

                    <a href="bse.php" class="btn-reset">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </form>
            </div>

            <div class="book-grid">
                <?php if ($result_buku && mysqli_num_rows($result_buku) > 0): ?>
                    <?php while ($buku = mysqli_fetch_assoc($result_buku)): ?>
                        <div class="book-card">
                            <div>
                                <div class="book-cover">
                                    <i class="fa-solid fa-book"></i>
                                </div>
                                <div class="badge-group">
                                    <span class="book-badge"><?= htmlspecialchars($buku['kelas']) ?></span>
                                    <span class="semester-badge"><?= htmlspecialchars($buku['semester']) ?></span>
                                </div>
                                <h3 class="book-title"><?= htmlspecialchars($buku['judul']) ?></h3>
                                <p class="book-meta"><?= htmlspecialchars($buku['kategori']) ?> · <?= htmlspecialchars($buku['ukuran']) ?></p>
                            </div>

                            <a href="../uploads_buku/<?= htmlspecialchars($buku['file_pdf']) ?>" target="_blank" class="btn-read">
                                <i class="fa-solid fa-book-open"></i> Baca / Unduh PDF
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <h3>Buku tidak ditemukan</h3>
                        <p>Belum ada buku digital yang diunggah atau sesuai dengan filter pilihan Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-text">
            © 2026 ANTA THADORE · Perpustakaan Digital BSE
        </div>
    </main>

    <script>
        function toggleUploadForm() {
            const card = document.getElementById('formUploadCard');
            card.classList.toggle('active');
        }
    </script>
</body>
</html>