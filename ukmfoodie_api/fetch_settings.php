<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Kita ambil tetapan untuk gerai ID 1 (Gerai Pak Mus)
$stall_id = 1; 

$sql = "SELECT * FROM stalls WHERE id = $stall_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ambil data tersebut
    $data = $result->fetch_assoc();
    
    // Hantar data kembali dalam format JSON
    echo json_encode([
        "status" => "success", 
        "data" => $data
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Maklumat gerai tidak dijumpai."]);
}

$conn->close();
?>