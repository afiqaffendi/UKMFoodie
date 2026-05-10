<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->order_id) && !empty($data->sender_id) && !empty($data->sender_type) && !empty($data->message)) {
    $order_id = $conn->real_escape_string($data->order_id);
    $sender_id = $conn->real_escape_string($data->sender_id);
    $sender_type = $conn->real_escape_string($data->sender_type);
    $message = $conn->real_escape_string($data->message);

    $sql = "INSERT INTO chat_messages (order_id, sender_id, sender_type, message) VALUES ('$order_id', '$sender_id', '$sender_type', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Message sent"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data"]);
}
$conn->close();
?>
