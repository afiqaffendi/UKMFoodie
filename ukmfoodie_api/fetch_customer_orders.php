<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$user_id = isset($_GET['user_id']) ? $conn->real_escape_string($_GET['user_id']) : '';

if (empty($user_id)) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit;
}

// Ambil order beserta nama gerai (stall_name)
$sql = "SELECT o.*, s.stall_name 
        FROM orders o
        LEFT JOIN stalls s ON o.stall_id = s.id
        WHERE o.user_id = '$user_id' 
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

$orders = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        // Ambil item untuk setiap order
        $item_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        $item_result = $conn->query($item_sql);
        
        $items = [];
        while($item_row = $item_result->fetch_assoc()) {
            $item_row['quantity'] = (int)$item_row['quantity'];
            $item_row['price'] = (float)$item_row['price'];
            $items[] = $item_row;
        }
        
        $row['items'] = $items;
        $orders[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $orders]);
} else {
    echo json_encode(["status" => "success", "data" => []]);
}

$conn->close();
?>
