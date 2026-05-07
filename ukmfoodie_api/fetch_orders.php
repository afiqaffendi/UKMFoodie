<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$stall_id = isset($_GET['stall_id']) ? $_GET['stall_id'] : 1;

// Ambil semua order yang belum siap (Pending, Preparing, Ready)
$sql = "SELECT * FROM orders WHERE stall_id = '$stall_id' AND status != 'Completed' AND status != 'Rejected' ORDER BY created_at DESC";
$result = $conn->query($sql);

$orders = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        // Ambil item untuk setiap order
        $item_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        $item_result = $conn->query($item_sql);
        $items = [];
        while($item_row = $item_result->fetch_assoc()) {
            $items[] = $item_row;
        }
        
        $row['items'] = $items;
        $orders[] = $row;
    }
}

echo json_encode(["status" => "success", "data" => $orders]);
?>