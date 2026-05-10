<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'db.php';

if (!empty($_GET['order_id'])) {
    $order_id = $conn->real_escape_string($_GET['order_id']);

    $sql = "SELECT * FROM chat_messages WHERE order_id = '$order_id' ORDER BY created_at ASC";
    $result = $conn->query($sql);

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(["status" => "success", "data" => $messages]);
} else {
    echo json_encode(["status" => "error", "message" => "Order ID missing"]);
}
$conn->close();
?>
