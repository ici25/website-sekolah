<?php
session_start();
require_once "config/database.php";

$pesan = "";
$status = "";

if (isset($_POST['reset_password'])) {
    $username = trim($_POST['username']);
    $password_baru = $_POST['password_baru'];

    if ($username === "" || $password_baru === "") {
        $status = "gagal";
        $pesan = "Username dan password baru wajib diisi.";
    } else {
        // Cek apakah username terdaftar di tabel users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Hash password baru agar aman sesuai sistem login Anda
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);

            // Update password langsung ke database
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            if ($update->execute([$password_hashed, $username])) {
                $status = "sukses";
                $pesan = "Password berhasil direset! Silakan kembali ke halaman login untuk masuk menggunakan password baru Anda.";
            } else {
                $status = "gagal";
                $pesan = "Terjadi kesalahan sistem saat memperbarui password.";
            }
        } else {
            $status = "gagal";
            $pesan = "Username tidak ditemukan di dalam sistem.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ANTA THADORE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f4f5; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-gray-100">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-100 text-[#0a3e31] rounded-xl mb-3 text-xl font-bold">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Reset Sandi Mandiri</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan username Anda dan ketik password baru.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="mb-4 p-4 rounded-xl text-sm <?= $status === 'sukses' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form action="lupa_password.php" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username Akun</label>
                <div class="relative flex items-center">
                    <i class="fa-regular fa-user absolute left-3.5 text-gray-400"></i>
                    <input type="text" name="username" required class="w-full border rounded-xl p-3 pl-10 border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0a3e31] text-sm" placeholder="Masukkan username...">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                <div class="relative flex items-center">
                    <i class="fa-solid fa-lock absolute left-3.5 text-gray-400"></i>
                    <input type="password" name="password_baru" id="passwordBaru" required class="w-full border rounded-xl p-3 pl-10 pr-16 border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#0a3e31] text-sm" placeholder="Masukkan password baru...">
                    <button type="button" onclick="togglePasswordNew()" id="btnToggle" class="absolute right-3.5 text-xs font-bold text-[#0a3e31]">Lihat</button>
                </div>
            </div>

            <button type="submit" name="reset_password" class="w-full bg-[#0a3e31] text-white py-3 rounded-xl hover:bg-[#062b22] font-semibold transition text-sm shadow-md mb-3">
                Reset Password Sekarang
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="text-sm text-[#0a3e31] font-bold hover:underline">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Halaman Login
            </a>
        </div>
    </div>

    <script>
        function togglePasswordNew() {
            const input = document.getElementById('passwordBaru');
            const btn = document.getElementById('btnToggle');
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerText = 'Sembunyi';
            } else {
                input.type = 'password';
                btn.innerText = 'Lihat';
            }
        }
    </script>
</body>
</html>