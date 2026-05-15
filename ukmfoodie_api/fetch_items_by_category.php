<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

try {
    // Fetch menu items for a specific category from approved stalls
    $sql = "SELECT m.*, s.stall_name, s.location_area 
            FROM menu_items m 
            JOIN stalls s ON m.stall_id = s.id 
            WHERE s.approval_status = 'Approved' 
            AND m.category = '$category'
            ORDER BY m.id DESC";
    
    $result = $conn->query($sql);
    
    $items = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $items
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn->close();
?>
