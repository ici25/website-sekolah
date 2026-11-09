<?php
session_start();

include 'config/database.php';
include 'koneksi.php';

require_once "config/database.php";
require_once "koneksi.php";


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);



?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Sekolah | ANTA THADORE - SMAN 4 Tidore Kepulauan</title>
    <style>
        :root {
            --forest: #0d4135;
            --green: #1f725d;
            --lime: #cbec62;
            --paper: #fffefb;
            --cream: #f6f5ef;
            --ink: #17251f;
            --muted: #718079;
            --line: #dfe7e2;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            font-family: Arial, sans-serif;
            background: var(--cream);
        }

        /* Header / Navigasi Atas */
        .guest-header {
            padding: 19px 7%;
            color: white;
            background: var(--forest);
        }

        .guest-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            color: var(--forest);
            font-size: 13px;
            font-weight: 900;
            border-radius: 13px;
            background: var(--lime);
        }

        .brand strong, .brand span { display: block; }
        .brand strong { font-size: 13px; letter-spacing: 1px; }
        .brand span { margin-top: 4px; color: rgba(255, 255, 255, 0.68); font-size: 11px; }

        .back-button {
            padding: 10px 15px;
            color: var(--forest);
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            border-radius: 9px;
            background: var(--lime);
            transition: opacity 0.2s;
        }
        .back-button:hover { opacity: 0.9; }

        /* Hero Section */
        .hero-guest {
            padding: 84px 7%;
            color: white;
            background:
                linear-gradient(
                    90deg,
                    rgba(6, 43, 35, 0.92),
                    rgba(6, 43, 35, 0.65)
                ),
                url("assets/images/bg-sma4tikep.jpg") center/cover no-repeat;
        }

        .hero-guest-content { max-width: 800px; }

        .label {
            margin: 0;
            color: var(--lime);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.6px;
        }

        .hero-guest h1 {
            margin: 16px 0;
            font-size: clamp(36px, 5vw, 64px);
            line-height: 1.05;
            letter-spacing: -2px;
        }

        .hero-guest p {
            max-width: 590px;
            margin: 0;
            color: rgba(255, 255, 255, 0.80);
            font-size: 16px;
            line-height: 1.75;
        }

        /* Konten Utama di Bawahnya */
        .content {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
            padding: 65px 0;
        }

        .section-heading { margin-bottom: 28px; }
        .section-heading p {
            margin: 0;
            color: var(--green);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }
        .section-heading h2 {
            margin: 10px 0 0;
            font-size: clamp(26px, 3.5vw, 40px);
            letter-spacing: -1px;
            color: var(--forest);
        }

        .school-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
        }

        .card {
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--paper);
            box-shadow: 0 10px 28px rgba(13, 65, 53, 0.06);
        }

        .card h3 {
            margin: 0 0 14px;
            color: var(--forest);
            font-size: 20px;
        }

        .card p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.75;
        }

        .identity-list { display: grid; gap: 0; }
        .identity-item {
            display: flex;
            justify-content: space-between;
            gap: 22px;
            padding: 14px 0;
            border-bottom: 1px solid var(--line);
        }
        .identity-item:last-child { border-bottom: 0; }
        .identity-item span:first-child { color: var(--muted); font-size: 13px; }
        .identity-item span:last-child { color: var(--forest); font-size: 13px; font-weight: 800; text-align: right; }

        .vision-mission-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card-vision-mission {
            padding: 28px;
            border: 1px solid #cce3de;
            border-radius: 20px;
            background: #f0f7f4;
        }

        .principal-card {
            display: grid;
            grid-template-columns: 104px 1fr;
            gap: 22px;
            align-items: start;
        }

        .principal-avatar {
            width: 104px;
            height: 104px;
            display: grid;
            place-items: center;
            color: var(--forest);
            font-size: 26px;
            font-weight: 900;
            border-radius: 24px;
            background: var(--lime);
            overflow: hidden;
        }
        .principal-avatar img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            object-position: center top; 
        }

        .principal-role {
            margin: 0;
            color: var(--green);
            font-size: 13px;
            font-weight: 800;
        }

        /* Styling Kartu Guru Bisa Digeser (Horizontal Scroll) */
        .teacher-scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding-bottom: 10px;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .teacher-scroll-container::-webkit-scrollbar {
            display: none;
        }

        .teacher-card {
            min-width: 300px;
            max-width: 330px;
            flex: 0 0 auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(13, 65, 53, 0.06);
            display: flex;
            flex-direction: column;
            scroll-snap-align: start;
        }

        /* Frame Foto Guru (Mirip Kepala Sekolah) */
        .teacher-avatar {
            width: 90px;
            height: 90px;
            display: grid;
            place-items: center;
            color: var(--forest);
            font-size: 22px;
            font-weight: 900;
            border-radius: 20px;
            background: var(--lime);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .teacher-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
        }

        .teacher-badge {
            display: inline-block;
            background-color: #dceff5;
            color: var(--forest);
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .teacher-card h3 {
            margin: 0 0 4px 0;
            font-size: 18px;
            color: var(--ink);
            font-weight: 800;
        }

        /* Styling Kartu Prestasi Bisa Digeser (Horizontal Scroll) */
        .achievement-scroll-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding-bottom: 10px;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .achievement-scroll-container::-webkit-scrollbar {
            display: none;
        }

        .achievement-card {
            min-width: 320px;
            max-width: 350px;
            flex: 0 0 auto;
            min-height: 200px;
            padding: 28px;
            border-radius: 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 28px rgba(13, 65, 53, 0.06);
            scroll-snap-align: start;
        }

        /* Warna Kartu 1: Hijau Tua (Forest) */
        .achievement-card:nth-child(3n + 1) {
            background-color: #0d4135;
            color: white;
        }
        .achievement-card:nth-child(3n + 1) span {
            color: #cbec62;
        }

        /* Warna Kartu 2: Hijau Terang / Lime */
        .achievement-card:nth-child(3n + 2) {
            background-color: #d4f85e;
            color: #0d4135;
        }
        .achievement-card:nth-child(3n + 2) span {
            color: #0d4135;
            opacity: 0.8;
        }

        /* Warna Kartu 3: Biru Muda Lembut */
        .achievement-card:nth-child(3n + 3) {
            background-color: #e2f2f7;
            color: #0d4135;
        }
        .achievement-card:nth-child(3n + 3) span {
            color: var(--green);
        }

        .achievement-card span {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .achievement-card h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.35;
        }

        .guest-footer {
            padding: 30px;
            color: rgba(255, 255, 255, 0.65);
            font-size: 12px;
            text-align: center;
            background: var(--forest);
        }

        @media (max-width: 900px) {
            .school-grid, .vision-mission-grid, .principal-card { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Header -->
<?php


$portal = 'login.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'guru') {
        $portal = 'guru/dashboard.php';
    } elseif ($_SESSION['role'] === 'siswa') {
        $portal = 'siswa/dashboard.php';
    }
}
?>


<header class="guest-header">
    <nav class="guest-nav">
        <div class="brand">
            <div class="brand-logo">AT</div>
            <div>
                <strong>ANTA THADORE</strong>
                <span>Informasi Sekolah</span>
            </div>
        </div>


<?php if (isset($_SESSION['user_id'])): ?>

    <?php if ($_SESSION['role'] === 'guru'): ?>

        <!-- Jika login sebagai guru -->
        <a class="back-button" href="guru/dashboard.php">
            &rarr; Portal
        </a>

    <?php elseif ($_SESSION['role'] === 'siswa'): ?>

        <!-- Jika login sebagai siswa -->
        <a class="back-button" href="siswa/dashboard.php">
            &rarr; Portal
        </a>

    <?php endif; ?>

<?php else: ?>

    <!-- Jika belum login -->
    <a class="back-button" href="login.php">
        &larr; Login
    </a>

<?php endif; ?>

    </nav>
</header>

    <!-- Banner Hero -->
    <section class="hero-guest">
        <div class="hero-guest-content">
            <p class="label">HALAMAN UTAMA SISWA</p>
            <h1>
                Mengenal sekolah,<br>
                mengenal masa depan.
            </h1>
            <p>
                Temukan informasi umum ANTA THADORE SMAN 4 Tidore Kepulauan, mulai
                dari profil, sejarah, kepala sekolah, tenaga pendidik, akreditasi, hingga
                prestasi yang telah diraih.
            </p>
        </div>
    </section>

    <!-- Konten di Bawahnya -->
    <main class="content">

        <!-- 1. PROFIL & SEJARAH SEKOLAH -->
        <section>
            <div class="section-heading">
                <p>PROFIL SEKOLAH</p>
                <h2>Identitas ANTA THADORE</h2>
            </div>

            <?php
            $q_sekolah = mysqli_query($koneksi, "SELECT * FROM sekolah LIMIT 1");
            $s = mysqli_fetch_assoc($q_sekolah);
            $nama_sekolah = $s['nama_sekolah'] ?? 'SMA Negeri 4 Tidore Kepulauan';
            $npsn = $s['npsn'] ?? '-';
            $alamat = $s['alamat'] ?? 'Jln. Raya Payahe, Tikep';
            $akreditasi = $s['akreditasi'] ?? 'A';
            $email = $s['email'] ?? 'sman4tikep@yahoo.com';
            $sejarah = $s['sejarah'] ?? 'SMA Negeri 4 Tidore Kepulauan merupakan sekolah yang berada di daratan Oba, tepatnya di Kelurahan Payahe.';
            ?>

            <div class="school-grid">
                <article class="card">
                    <h3>Tentang sekolah</h3>
                    <p><?= nl2br(htmlspecialchars($sejarah)) ?></p>
                </article>

                <article class="card">
                    <h3>Informasi utama</h3>
                    <div class="identity-list">
                        <div class="identity-item">
                            <span>Nama Sekolah</span>
                            <span><?= htmlspecialchars($nama_sekolah) ?></span>
                        </div>
                        <div class="identity-item">
                            <span>Alamat</span>
                            <span><?= htmlspecialchars($alamat) ?></span>
                        </div>
                        <div class="identity-item">
                            <span>Akreditasi</span>
                            <span><?= htmlspecialchars($akreditasi) ?></span>
                        </div>
                        <div class="identity-item">
                            <span>Email</span>
                            <span><?= htmlspecialchars($email) ?></span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- 2. VISI & MISI -->
        <section style="margin-top: 50px;">
            <div class="section-heading">
                <p>VISI & MISI</p>
                <h2>Visi dan Misi Sekolah</h2>
            </div>

            <div class="vision-mission-grid">
                <article class="card-vision-mission">
                    <h3 style="color: var(--forest); border-bottom: 2px solid var(--lime); padding-bottom: 8px; margin-top: 0;">Visi</h3>
                    <p style="color: var(--green); margin-top: 14px; font-weight: 500; font-style: italic;">
                        <?= htmlspecialchars($s['visi'] ?? '') ?>
                    </p>
                </article>

                <article class="card-vision-mission">
                    <h3 style="color: var(--forest); border-bottom: 2px solid var(--lime); padding-bottom: 8px; margin-top: 0;">Misi</h3>
                    <div style="color: var(--green); font-size: 14px; line-height: 1.75; font-weight: 500;">
                        <?= nl2br(htmlspecialchars($s['misi'] ?? '')) ?>
                    </div>
                </article>
            </div>
        </section>

        <!-- 3. KEPALA SEKOLAH -->
        <section style="margin-top: 50px;">
            <div class="section-heading">
                <p>PIMPINAN SEKOLAH</p>
                <h2>Kepala Sekolah</h2>
            </div>

            <?php
            $q_ks = mysqli_query($koneksi, "SELECT * FROM kepala_sekolah ORDER BY id DESC LIMIT 1");
            $ks = mysqli_fetch_assoc($q_ks);
            ?>
            <article class="card principal-card">
                <div class="principal-avatar">
                    <?php if (!empty($ks['foto'])): ?>
                        <img src="uploads/<?= htmlspecialchars($ks['foto']) ?>" alt="Kepala Sekolah">
                    <?php else: ?>
                        KS
                    <?php endif; ?>
                </div>
                <div>
                    <h3 style="margin-top: 0; color: var(--forest);"><?= htmlspecialchars($ks['nama_lengkap'] ?? $ks['nama'] ?? 'Wirja Abd. Rahman, S.Pd.') ?></h3>
                    <p class="principal-role">Kepala Sekolah SMAN 4 Tidore Kepulauan</p>
                    <p style="margin-bottom: 0; color: var(--muted); font-size: 13px;">NIP: <?= htmlspecialchars($ks['nip'] ?? '-') ?></p>
                    <p style="margin-top: 10px; color: var(--ink); font-style: italic;">
                        <p style="margin-top: 10px; color: var(--ink); font-style: italic;">
                    <?= htmlspecialchars($ks['keterangan'] ?? 'Pendidikan adalah proses membangun pengetahuan, karakter, kepedulian, dan keberanian untuk terus belajar.') ?>
                </p>
                </div>
            </article>
        </section>

        <!-- 4. GURU / TENAGA PENDIDIK -->
        <section style="margin-top: 50px;">
            <div class="section-heading" style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <p>TENAGA PENDIDIK</p>
                    <h2>Guru dan staff</h2>
                </div>
                <div class="hidden md:flex" style="color: var(--muted); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <span>Geser menyamping</span> &rarr;
                </div>
            </div>

            <div class="teacher-scroll-container">
                <?php
                $guru = mysqli_query($koneksi, "SELECT * FROM guru");
                if ($guru && mysqli_num_rows($guru) > 0):
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($guru)): 
                        $nama = $row['nama_lengkap'] ?? $row['nama_guru'] ?? 'Nama Guru';
                        $jabatan = $row['jabatan'] ?? '';
                        $mapel = $row['mata_pelajaran'] ?? '';
                        $badge = !empty($row['nip']) ? substr($row['nip'], -4) : "G" . $no;
                ?>
                <article class="teacher-card">
                    <!-- Frame Foto di Bagian Atas -->
                    <div class="teacher-avatar">
                        <?php if (!empty($row['foto'])): ?>
                            <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Guru">
                        <?php else: ?>
                            <?= htmlspecialchars($badge) ?>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="teacher-badge"><?= htmlspecialchars($badge) ?></div>
                        <h3><?= htmlspecialchars($nama) ?></h3>
                    </div>
                    
                    <div style="margin-top: 12px;">
                        <?php if (!empty($jabatan)): ?>
                            <p style="margin: 0 0 4px 0; font-weight: 700; color: var(--forest); font-size: 14px;">
                                <?= htmlspecialchars($jabatan) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($mapel)): ?>
                            <p style="margin: 0; color: var(--muted); font-size: 13px;">
                                Pengajar <?= htmlspecialchars($mapel) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php 
                    $no++;
                    endwhile; 
                else: 
                ?>
                <div class="card" style="width: 100%;">
                    <p style="text-align: center; color: var(--muted);">Belum ada data guru di dalam database.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 5. PRESTASI SEKOLAH -->
        <section style="margin-top: 50px;">
            <div class="section-heading" style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <p>PRESTASI SEKOLAH</p>
                    <h2>Langkah yang telah diraih</h2>
                </div>
                <div class="hidden md:flex" style="color: var(--muted); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <span>Geser menyamping</span> &rarr;
                </div>
            </div>

            <div class="achievement-scroll-container">
                <?php
                $prestasi = mysqli_query($koneksi, "SELECT * FROM prestasi");
                if ($prestasi && mysqli_num_rows($prestasi) > 0):
                    while ($p = mysqli_fetch_assoc($prestasi)): 
                        $nama_prestasi = $p['nama_prestasi'] ?? $p['judul'] ?? 'Prestasi Sekolah';
                        $tahun = $p['tahun'] ?? $p['tanggal'] ?? '2025';
                        $tingkat = $p['tingkat'] ?? 'AKADEMIK';
                ?>
                <article class="achievement-card">
                    <span><?= htmlspecialchars($tahun) ?> &middot; <?= strtoupper(htmlspecialchars($tingkat)) ?></span>
                    <h3><?= htmlspecialchars($nama_prestasi) ?></h3>
                </article>
                <?php endwhile; else: ?>
                <article class="achievement-card">
                    <span>2025 &middot; AKADEMIK</span>
                    <h3>Juara Olimpiade Sains Tingkat Kota</h3>
                </article>
                <article class="achievement-card">
                    <span>2025 &middot; OLAHRAGA</span>
                    <h3>Prestasi Olahraga Tingkat Provinsi</h3>
                </article>
                <article class="achievement-card">
                    <span>2026 &middot; TEKNOLOGI</span>
                    <h3>Kompetisi Teknologi dan Inovasi Siswa</h3>
                </article>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="guest-footer">
        © 2026 ANTA THADORE · SMAN 4 Tidore Kepulauan. All rights reserved.
    </footer>

</body>
</html>


