<?php
$host = "localhost";
$user = "antathad_tes";
$pass = "brave_2026";
$db   = "antathad_antathad_db";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke pengguna di production
    die("Koneksi database gagal.");
}