<?php
session_start();

/**
 * Cek apakah user sudah login.
 * Jika tidak, kembalikan ke halaman login.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

/**
 * Cek apakah user sudah login DAN role-nya guru.
 */
function requireGuru() {
    requireLogin();
    if ($_SESSION['role'] !== 'guru') {
        // Bisa diarahkan ke halaman tidak diizinkan atau dashboard sesuai role
        header("Location: ../login.php");
        exit;
    }
}

/**
 * Cek apakah user sudah login DAN role-nya siswa.
 */
function requireSiswa() {
    requireLogin();
    if ($_SESSION['role'] !== 'siswa') {
        header("Location: ../login.php");
        exit;
    }
}