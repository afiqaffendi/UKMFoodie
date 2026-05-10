<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$role = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : 'User';

$sql = "SELECT id, fullname, role, created_at FROM users WHERE role = '$role' ORDER BY created_at DESC";
$result = $conn->query($sql);
$users = [];

while($row = $result->fetch_assoc()) {
    // Generate a mock formatted ID like U10023
    $prefix = ($row['role'] == 'Seller') ? 'S' : 'U';
    $row['formatted_id'] = $prefix . (10000 + $row['id']);
    $row['status'] = 'Active'; // For now assume all are active
    $users[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $users]);

$conn->close();
?>
