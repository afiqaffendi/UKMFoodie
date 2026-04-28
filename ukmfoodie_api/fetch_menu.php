<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Kita ambil menu untuk gerai ID 1 (Gerai Pak Mus yang kita buat tadi)
$stall_id = 1; 

$sql = "SELECT * FROM menu_items WHERE stall_id = $stall_id ORDER BY created_at DESC";
$result = $conn->query($sql);

$menu_items = [];

// Jika ada data, masukkan ke dalam array
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}

// Hantar data dalam format JSON
echo json_encode([
    "status" => "success", 
    "data" => $menu_items
]);

$conn->close();
?>