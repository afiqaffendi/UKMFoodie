<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

try {
    // Fetch 12 random menu items from menu_items table
    // Join with stalls to get the stall_name and ensure the stall is approved
    // Using mysqli as per db.php
    $sql = "SELECT m.*, s.stall_name, s.location_area 
            FROM menu_items m 
            JOIN stalls s ON m.stall_id = s.id 
            WHERE s.approval_status = 'Approved' 
            ORDER BY RAND() 
            LIMIT 12";
    
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
