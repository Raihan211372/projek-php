<?php
// Dikerjakan oleh Alif - Mahasiswa B (hapus mahasiswa)
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

// Hapus hanya lewat POST supaya tidak bisa terpicu dari link/URL biasa
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["nim"])) {
    header("Location: student_view.php");
    die();
}

$nim = $_POST["nim"];

// Ambil nama dulu untuk pesan konfirmasi
$stmt = mysqli_prepare($connection, "SELECT name FROM student WHERE nim = ?");
mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($student) {
    $stmt = mysqli_prepare($connection, "DELETE FROM student WHERE nim = ?");
    mysqli_stmt_bind_param($stmt, "s", $nim);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        $message = "Mahasiswa dengan nama = \"<b>" . $student["name"] . "</b>\" sudah berhasil dihapus";
    } else {
        $message = "Data mahasiswa gagal dihapus";
    }
} else {
    $message = "Data mahasiswa tidak ditemukan";
}

mysqli_close($connection);
$message = urlencode($message);
header("Location: student_view.php?message={$message}");
die();  