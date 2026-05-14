<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Ambil ID dari URL (delete_menu.php?id=XX)
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;

if ($id) {
    // Pastikan nama jadual adalah 'menu_items' mengikut database awak
    $sql = "DELETE FROM menu_items WHERE id = '$id'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            "status" => "success", 
            "message" => "Menu permanently deleted."
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to delete menu: " . $conn->error
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Invalid or missing ID."
    ]);
}

$conn->close();
?>