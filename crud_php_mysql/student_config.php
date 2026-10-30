<?php
    // Pakai koneksi dari connection.php milikmu (nama variabel apa pun, otomatis terdeteksi)
    include_once("connection.php");

    $db = null;
    foreach (get_defined_vars() as $value) {
        if ($value instanceof mysqli) { $db = $value; break; }
    }
    if ($db === null) {
        die("Koneksi mysqli tidak ditemukan di connection.php");
    }

    // === SESUAIKAN kalau nama tabel/kolom di databasemu berbeda ===
    const T_TABLE   = "mahasiswa";
    const C_NIM     = "nim";
    const C_NAMA    = "nama";
    const C_TEMPAT  = "tempat_lahir";
    const C_TGL     = "tanggal_lahir";
    const C_FAK     = "fakultas";
    const C_JURUSAN = "jurusan";
    const C_IPK     = "ipk";