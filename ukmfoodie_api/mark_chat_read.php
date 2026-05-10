<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->order_id) && !empty($data->reader_type)) {
    $order_id = $conn->real_escape_string($data->order_id);
    $reader_type = $conn->real_escape_string($data->reader_type);
    
    // Mark messages sent by the OTHER party as read
    $sender_type = ($reader_type === 'customer') ? 'seller' : 'customer';

    $sql = "UPDATE chat_messages SET is_read = 1 WHERE order_id = '$order_id' AND sender_type = '$sender_type' AND is_read = 0";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data"]);
}
$conn->close();
?>
