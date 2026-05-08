<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (isset($_GET['order_id'])) {
    $order_id = $conn->real_escape_string($_GET['order_id']);

    $sql = "SELECT status, collect_time, stall_id FROM orders WHERE id = '$order_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Order not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Order ID missing"]);
}
$conn->close();
?>