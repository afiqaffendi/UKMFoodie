<?php
// Tetapan Header untuk membenarkan akses dari web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Terima data JSON dari web peniaga
$data = json_decode(file_get_contents("php://input"));

// Semak jika data yang diperlukan wujud
if(isset($data->stall_id) && isset($data->item_name) && isset($data->price) && isset($data->category)) {
    
    $stall_id = $data->stall_id;
    $item_name = $conn->real_escape_string($data->item_name);
    $price = $data->price;
    $category = $conn->real_escape_string($data->category);
    $status = isset($data->status) ? $data->status : 'Available';
    
    // Untuk permulaan, kita biarkan image_url kosong atau guna dummy
    $image_url = "default_food.png"; 

    // Masukkan data ke dalam jadual menu_items
    $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, status, image_url) 
            VALUES ('$stall_id', '$item_name', '$price', '$category', '$status', '$image_url')";
    
    if($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Menu baharu berjaya ditambah!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ralat pangkalan data: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Sila isikan semua maklumat menu yang diperlukan."]);
}

$conn->close();
?>