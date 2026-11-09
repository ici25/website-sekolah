<?php
$host = "localhost";
$user = "root";       
$pass = "";           
$db   = "anta_thadore";

// Membuat koneksi ke database menggunakan MySQLi
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek jika koneksi gagal
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}
?>