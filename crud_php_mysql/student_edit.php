<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

$nim = $_GET["nim"] ?? ($_POST["nim"] ?? "");

// Ambil data mahasiswa yang akan diedit
$stmt = mysqli_prepare($connection, "SELECT * FROM student WHERE nim = ?");
mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$student) {
    $message = urlencode("Data mahasiswa tidak ditemukan");
    header("Location: student_view.php?message={$message}");
    die();
}

$arr_month = [
    "1"=>"Januari", "2"=>"Februari", "3"=>"Maret", "4"=>"April",
    "5"=>"Mei", "6"=>"Juni", "7"=>"Juli", "8"=>"Agustus",
    "9"=>"September", "10"=>"Oktober", "11"=>"Nopember", "12"=>"Desember"
];

$error_message = "";

if (isset($_POST["submit"])) {
    $name        = htmlentities(strip_tags(trim($_POST["name"])));
    $birth_city  = htmlentities(strip_tags(trim($_POST["birth_city"])));
    $faculty     = htmlentities(strip_tags(trim($_POST["faculty"])));
    $department  = htmlentities(strip_tags(trim($_POST["department"])));
    $gpa         = htmlentities(strip_tags(trim($_POST["gpa"])));
    $birth_date  = (int) $_POST["birth_date"];
    $birth_month = (int) $_POST["birth_month"];
    $birth_year  = (int) $_POST["birth_year"];

    if (empty($name)) {
        $error_message .= "- Nama belum diisi <br>";
    }
    if (empty($birth_city)) {
        $error_message .= "- Tempat lahir belum diisi <br>";
    }
    if (empty($department)) {
        $error_message .= "- Jurusan belum diisi <br>";
    }
    if (!checkdate($birth_month, $birth_date, $birth_year)) {
        $error_message .= "- Tanggal lahir tidak valid <br>";
    }
    if (!in_array($faculty, ["FTIB", "FTEIC"])) {
        $error_message .= "- Fakultas tidak valid <br>";
    }
    if (!is_numeric($gpa) OR ($gpa <= 0) OR ($gpa > 4)) {
        $error_message .= "- IPK harus diisi dengan angka 0 - 4 <br>";
    }

    if ($error_message === "") {
        $birth_date_full = sprintf("%04d-%02d-%02d", $birth_year, $birth_month, $birth_date);
        $gpa = (float) $gpa;

        $stmt = mysqli_prepare($connection,
            "UPDATE student SET name=?, birth_city=?, birth_date=?, faculty=?, department=?, gpa=? WHERE nim=?");
        mysqli_stmt_bind_param($stmt, "sssssds",
            $name, $birth_city, $birth_date_full, $faculty, $department, $gpa, $nim);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($result) {
            $message = "Mahasiswa dengan nama = \"<b>$name</b>\" sudah berhasil diubah";
            $message = urlencode($message);
            header("Location: student_view.php?message={$message}");
            die();
        } else {
            die ("Query gagal dijalankan: ".mysqli_errno($connection)." - ".mysqli_error($connection));
        }
    }
} else {
    // Pertama kali dibuka: isi form dengan data dari database
    $name        = $student["name"];
    $birth_city  = $student["birth_city"];
    $faculty     = $student["faculty"];
    $department  = $student["department"];
    $gpa         = $student["gpa"];
    $birth_year  = (int) substr($student["birth_date"], 0, 4);
    $birth_month = (int) substr($student["birth_date"], 5, 2);
    $birth_date  = (int) substr($student["birth_date"], 8, 2);
}

$select_ftib  = ($faculty === "FTIB")  ? "selected" : "";
$select_fteic = ($faculty === "FTEIC") ? "selected" : "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa</title>
    <link href="assets/style.css" rel="stylesheet" >
</head>
<body>
    <div class="container">
        <div id="header">
            <h1 id="logo">Data Mahasiswa</h1>
        </div>
        <hr>
        <nav>
            <ul>
                <li><a href="student_view.php">Tampil</a></li>
                <li><a href="student_add.php">Tambah</a>
                <li><a href="logout.php">Logout</a>
            </ul>
        </nav>
        <h2>Edit Data Mahasiswa</h2>
        <?php
        if ($error_message !== "") {
            echo "<div class='error'>$error_message</div>";
        }
        ?>
        <form id="form_mahasiswa" action="student_edit.php?nim=<?= urlencode($nim) ?>" method="post">
            <input type="hidden" name="nim" value="<?= htmlspecialchars($nim) ?>">
            <fieldset>
                <legend>Edit Mahasiswa</legend>
                <p>
                    <label for="nim_view">NIM : </label>
                    <input type="text" id="nim_view" value="<?= htmlspecialchars($nim) ?>" readonly>
                    (NIM tidak dapat diubah)
                </p>
                <p>
                    <label for="name">Nama : </label>
                    <input type="text" name="name" id="name" value="<?= $name ?>">
                </p>
                <p>
                    <label for="birth_city">Tempat Lahir : </label>
                    <input type="text" name="birth_city" id="birth_city" value="<?= $birth_city ?>">
                </p>
                <p>
                    <label for="birth_date">Tanggal Lahir : </label>
                    <select name="birth_date" id="birth_date">
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            $sel = ($i == $birth_date) ? "selected" : "";
                            echo "<option value=$i $sel>" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>";
                        }
                        ?>
                    </select>
                    <select name="birth_month">
                        <?php
                        foreach ($arr_month as $key => $value) {
                            $sel = ($key == $birth_month) ? "selected" : "";
                            echo "<option value=\"{$key}\" $sel>{$value}</option>";
                        }
                        ?>
                    </select>
                    <select name="birth_year">
                        <?php
                        $year_start = min(1990, $birth_year);
                        $year_end   = max(2005, $birth_year);
                        for ($i = $year_start; $i <= $year_end; $i++) {
                            $sel = ($i == $birth_year) ? "selected" : "";
                            echo "<option value=$i $sel>$i</option>";
                        }
                        ?>
                    </select>
                </p>
                <p>
                    <label for="faculty">Fakultas : </label>
                    <select name="faculty" id="faculty">
                        <option value="FTIB" <?= $select_ftib ?>>FTIB</option>
                        <option value="FTEIC" <?= $select_fteic ?>>FTEIC</option>
                    </select>
                </p>
                <p>
                    <label for="department">Jurusan : </label>
                    <input type="text" name="department" id="department" value="<?= $department ?>">
                </p>
                <p>
                    <label for="gpa">IPK : </label>
                    <input type="text" name="gpa" id="gpa" value="<?= $gpa ?>" placeholder="Contoh: 2.75">
                    (angka desimal dipisah dengan karakter titik ".")
                </p>
            </fieldset>
            <br>
            <p>
                <input type="submit" name="submit" value="Simpan Perubahan">
                <a href="student_view.php">Batal</a>
            </p>
        </form>
    </div>
</body>
</html>
<?php
mysqli_close($connection);
?>