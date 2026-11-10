<?php
$host = "localhost";
$user = "antathad_tes";
$pass = "brave_2026";
$db   = "antathad_antathad_db";

// Membuat koneksi menggunakan mysqli
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Periksa apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>