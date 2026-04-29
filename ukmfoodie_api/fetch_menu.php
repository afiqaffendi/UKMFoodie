<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Ambil stall_id dari URL (contoh: fetch_menu.php?stall_id=1)
$stall_id = isset($_GET['stall_id']) ? $conn->real_escape_string($_GET['stall_id']) : 1; 

// Kita ambil data dari jadual menu_items
$sql = "SELECT * FROM menu_items WHERE stall_id = '$stall_id' ORDER BY id DESC";
$result = $conn->query($sql);

$menu_items = [];

// Jika ada data, masukkan ke dalam array
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $menu_items[] = [
            "id" => $row['id'],
            "stall_id" => $row['stall_id'],
            "item_name" => $row['item_name'],
            "price" => $row['price'],
            "category" => $row['category'],
            "food_image" => $row['food_image'],
            "status" => $row['status'] // <--- PENTING: Kena tambah baris ini supaya toggle kekal hijau!
        ];
    }
    
    echo json_encode([
        "status" => "success", 
        "data" => $menu_items
    ]);
} else {
    echo json_encode([
        "status" => "success", 
        "data" => [],
        "message" => "Tiada menu dijumpai."
    ]);
}

$conn->close();
?>