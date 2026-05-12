<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->stall_id) &&
    !empty($data->customer_id) &&
    !empty($data->order_id) &&
    !empty($data->rating)
) {
    $stall_id = $conn->real_escape_string($data->stall_id);
    $customer_id = $conn->real_escape_string($data->customer_id);
    $order_id = $conn->real_escape_string($data->order_id);
    $rating = (int)$data->rating;
    $comment = isset($data->comment) ? $conn->real_escape_string($data->comment) : '';

    // Check if review already exists
    $check_sql = "SELECT id FROM reviews WHERE order_id = '$order_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "You have already reviewed this order."]);
        exit;
    }

    $sql = "INSERT INTO reviews (stall_id, customer_id, order_id, rating, comment) 
            VALUES ('$stall_id', '$customer_id', '$order_id', '$rating', '$comment')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Review submitted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}

$conn->close();
?>
