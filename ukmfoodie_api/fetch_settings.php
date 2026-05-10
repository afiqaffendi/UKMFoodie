<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

$stall_id = isset($_GET['stall_id']) ? $conn->real_escape_string($_GET['stall_id']) : 1; 
// Jika stall_id 1 ialah default, kita biarkan as fallback, tapi utamakan dari parameter.
$sql = "SELECT s.*, u.fullname as owner_name 
        FROM stalls s 
        JOIN users u ON s.owner_id = u.id 
        WHERE s.id = $stall_id";
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