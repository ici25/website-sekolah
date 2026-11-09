<?php
require_once 'config/database.php';

echo "<h1>Koneksi Database Berhasil</h1>";
echo "<p>Website ANTA THADORE sudah terhubung dengan MySQL.</p>";

$stmt = $pdo->query("SHOW TABLES");
$daftarTabel = $stmt->fetchAll();

echo "<h3>Tabel yang tersedia:</h3>";

if ($daftarTabel) {
    echo "<ul>";

    foreach ($daftarTabel as $tabel) {
        echo "<li>" . htmlspecialchars(array_values($tabel)[0]) . "</li>";
    }

    echo "</ul>";
} else {
    echo "<p>Database berhasil tersambung, tetapi belum memiliki tabel.</p>";
}