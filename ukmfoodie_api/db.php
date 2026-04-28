<?php
// Tetapan Pangkalan Data
$servername = "localhost";
$username = "root";     // Lalai untuk XAMPP
$password = "";         // Lalai untuk XAMPP (kosong)
$dbname = "ukmfoodie_db";

// Cipta sambungan
$conn = new mysqli($servername, $username, $password, $dbname);

// Semak sambungan
if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}
?>