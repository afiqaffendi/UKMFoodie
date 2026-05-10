<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Ambil stall_id dari GET request
$stall_id = isset($_GET['stall_id']) ? $conn->real_escape_string($_GET['stall_id']) : 1;

// 1. Ambil order yang aktif (Pending, Preparing, Ready)
// Query ini akan menarik semua column termasuk payment_receipt dan customer_note
$sql = "SELECT *, 
               (SELECT COUNT(*) FROM chat_messages WHERE order_id = orders.id AND sender_type = 'customer' AND is_read = 0) as unread_chats
        FROM orders 
        WHERE stall_id = '$stall_id' 
        AND status IN ('Pending', 'Preparing', 'Ready') 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$orders = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $order_id = $row['id'];
        
        // 2. Ambil item untuk setiap order
        $item_sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        $item_result = $conn->query($item_sql);
        
        $items = [];
        while($item_row = $item_result->fetch_assoc()) {
            // Memastikan quantity dan price dihantar sebagai nombor yang betul
            $item_row['quantity'] = (int)$item_row['quantity'];
            $item_row['price'] = (float)$item_row['price'];
            $items[] = $item_row;
        }
        
        // Gabungkan items ke dalam objek order
        $row['items'] = $items;
        
        // Tambahkan order ke dalam array utama
        $orders[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $orders]);
} else {
    // Jika tiada order, hantar array kosong supaya frontend tidak error
    echo json_encode(["status" => "success", "data" => []]);
}

$conn->close();
?>