<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$customer_name = isset($_GET['customer_name']) ? $conn->real_escape_string($_GET['customer_name']) : '';

if (empty($customer_name)) {
    echo json_encode(["status" => "error", "message" => "Customer name is required"]);
    exit;
}

// Ambil order beserta nama gerai (stall_name)
$sql = "SELECT o.*, s.stall_name 
        FROM orders o
        LEFT JOIN stalls s ON o.stall_id = s.id
        WHERE o.customer_name = '$customer_name' 
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
