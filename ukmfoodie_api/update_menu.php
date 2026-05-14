<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan ID sentiasa ada
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "error", "message" => "Invalid ID"]);
        exit;
    }

    $id = $conn->real_escape_string($_POST['id']);
    $update_fields = [];

    // 1. Semak data teks (Hanya masukkan ke SQL jika data tersebut dihantar)
    if (isset($_POST['item_name'])) {
        $item_name = $conn->real_escape_string($_POST['item_name']);
        $update_fields[] = "item_name = '$item_name'";
    }

    if (isset($_POST['price'])) {
        $price = $conn->real_escape_string($_POST['price']);
        $update_fields[] = "price = '$price'";
    }

    if (isset($_POST['category'])) {
        $category = $conn->real_escape_string($_POST['category']);
        $update_fields[] = "category = '$category'";
    }

    if (isset($_POST['status'])) {
        $status = $conn->real_escape_string($_POST['status']);
        $update_fields[] = "status = '$status'";
    }

    // 2. Logik Gambar: Jika ada gambar baru diupload
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES["food_image"]["name"], PATHINFO_EXTENSION);
        
        // Nama fail unik
        $image_name = time() . "_updated." . $file_extension;
        
        if (move_uploaded_file($_FILES["food_image"]["tmp_name"], $target_dir . $image_name)) {
            $update_fields[] = "food_image = '$image_name'";
        }
    }

    // 3. Jika tiada data yang dihantar untuk dikemaskini
    if (empty($update_fields)) {
        echo json_encode(["status" => "error", "message" => "No data to update"]);
        exit;
    }

    // 4. Bina Query SQL Dinamik
    $sql = "UPDATE menu_items SET " . implode(', ', $update_fields) . " WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $conn->error]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method (Use POST)"]);
}
?>