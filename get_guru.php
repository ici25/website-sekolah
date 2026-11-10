<?php
// Mencegah output teks error merusak format JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once 'koneksi.php';

// Mengambil data guru
$sql = "SELECT * FROM guru";
$result = $conn->query($sql);

$teachers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Cek kolom ID / NIP
        $id = !empty($row['nip']) 
            ? $row['nip'] 
            : (!empty($row['id_guru']) ? "G" . $row['id_guru'] : "G01");

        // Cek kolom nama guru
        $name = !empty($row['nama_lengkap']) 
            ? $row['nama_lengkap'] 
            : (!empty($row['nama_guru']) ? $row['nama_guru'] : 'Nama Guru');

        // Cek kolom mata pelajaran / jabatan
        $mapel = !empty($row['mata_pelajaran']) 
            ? $row['mata_pelajaran'] 
            : (!empty($row['jabatan']) ? $row['jabatan'] : 'Pengajar');

        $title = (strpos($mapel, 'Pengajar') === false) 
            ? "Pengajar " . $mapel 
            : $mapel;

        // Cek foto
        if (!empty($row['foto'])) {
            $photo = (strpos($row['foto'], 'http') === 0) 
                ? $row['foto'] 
                : 'uploads/' . $row['foto'];
        } else {
            $photo = 'https://via.placeholder.com/150';
        }

        $teachers[] = [
            "id" => $id,
            "name" => $name,
            "title" => $title,
            "photo" => $photo
        ];
    }
}

echo json_encode($teachers);

$conn->close();
?>
