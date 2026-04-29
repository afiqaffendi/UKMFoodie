<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Pastikan data dihantar melalui POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari $_POST (Guna real_escape_string untuk keselamatan SQL Injection)
    // Nota: Nama kunci di sini mestilah sepadan dengan formData.append di menu.html
    $stall_id    = isset($_POST['stall_id']) ? $conn->real_escape_string($_POST['stall_id']) : 1;
    $item_name   = isset($_POST['item_name']) ? $conn->real_escape_string($_POST['item_name']) : '';
    $price       = isset($_POST['price']) ? $conn->real_escape_string($_POST['price']) : 0;
    $category    = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
    // Jika awak mahu simpan description, pastikan kolum ini wujud di database
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';

    $image_name = 'default_food.jpg'; // Nama gambar lalai jika user tak upload

    // 1. PROSES MUAT NAIK GAMBAR
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        
        // Buat folder uploads jika belum wujud
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["food_image"]["name"], PATHINFO_EXTENSION);
        
        // Nama fail unik: masa + nama makanan (buang simbol pelik)
        $clean_name = preg_replace("/[^a-zA-Z0-9]/", "", $item_name);
        $image_name = time() . "_" . $clean_name . "." . $file_extension;
        $target_file = $target_dir . $image_name;

        // Pindahkan fail dari folder sementara ke folder uploads/
        if (!move_uploaded_file($_FILES["food_image"]["tmp_name"], $target_file)) {
            $image_name = 'default_food.jpg'; 
        }
    }

    // 2. SIMPAN KE DATABASE (Pastikan nama kolum menu_items tepat)
    // Berdasarkan gambar phpMyAdmin awak: stall_id, item_name, price, category, food_image
    $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image) 
            VALUES ('$stall_id', '$item_name', '$price', '$category', '$image_name')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            "status" => "success", 
            "message" => "Menu berjaya ditambah!",
            "image" => $image_name
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Ralat Database: " . $conn->error
        ]);
    }

} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Request method tidak sah (Guna POST)"
    ]);
}
?>