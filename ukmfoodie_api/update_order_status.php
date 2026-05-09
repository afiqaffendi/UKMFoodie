<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Ambil data yang dihantar dari JavaScript
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['order_id']) && isset($data['status'])) {
    $order_id = $conn->real_escape_string($data['order_id']);
    $status = $conn->real_escape_string($data['status']);

    if ($status === 'Preparing') {
        $sql = "UPDATE orders SET status = '$status', accepted_at = NOW() WHERE id = '$order_id'";
    } else {
        $sql = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Status pesanan dikemaskini."]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
}

$conn->close();
?>