<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Ambil semua gerai dari pangkalan data, TERMASUK stall_image
$sql = "SELECT id, stall_name, description, opening_time, closing_time, status, stall_image FROM stalls ORDER BY id ASC";
$result = $conn->query($sql);

$stalls = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $stalls[] = $row;
    }
}

// Hantar data kembali dalam format JSON
echo json_encode([
    "status" => "success", 
    "data" => $stalls
]);

$conn->close();
?>