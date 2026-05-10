<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$sql = "SELECT stalls.id, stalls.stall_name, stalls.address, stalls.phone, stalls.email, stalls.latitude, stalls.longitude, stalls.created_at, users.fullname as owner_name 
        FROM stalls 
        JOIN users ON stalls.owner_id = users.id 
        WHERE stalls.approval_status = 'Pending'
        ORDER BY stalls.created_at DESC";

$result = $conn->query($sql);
$stalls = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $stalls[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $stalls]);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$conn->close();
?>
