<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (isset($_GET['order_id'])) {
    $order_id = $conn->real_escape_string($_GET['order_id']);

    $sql = "SELECT o.status, o.collect_time, o.stall_id, o.accepted_at, o.total_amount, o.created_at,
                   (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as num_items,
                   (SELECT COUNT(*) FROM chat_messages WHERE order_id = o.id AND sender_type = 'seller' AND is_read = 0) as unread_chats
            FROM orders o WHERE o.id = '$order_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Ambil senarai item
        $item_sql = "SELECT item_name, quantity, price FROM order_items WHERE order_id = '$order_id'";
        $item_result = $conn->query($item_sql);
        $items = [];
        while($item_row = $item_result->fetch_assoc()) {
            $items[] = $item_row;
        }
        $data['items'] = $items;

        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Order not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Order ID missing"]);
}
$conn->close();
?>