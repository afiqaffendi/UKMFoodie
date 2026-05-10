<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['stall_id']) || !isset($data['status'])) {
    die(json_encode(['status' => 'error', 'message' => 'Missing stall_id or status']));
}

$stall_id = $conn->real_escape_string($data['stall_id']);
$status = $conn->real_escape_string($data['status']); // Approved or Rejected

$sql = "UPDATE stalls SET approval_status = '$status' WHERE id = '$stall_id'";

if ($conn->query($sql)) {
    echo json_encode(['status' => 'success', 'message' => 'Stall status updated to ' . $status]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
}

$conn->close();
?>
