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

if (!isset($data['email']) || !isset($data['password']) || !isset($data['stall_name'])) {
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
}

$fullname = $conn->real_escape_string($data['fullname']);
$email = $conn->real_escape_string($data['email']);
$password = $conn->real_escape_string($data['password']);
$phone = $conn->real_escape_string($data['phone']);
$stall_name = $conn->real_escape_string($data['stall_name']);
$address = $conn->real_escape_string($data['address']);
$lat = $conn->real_escape_string($data['latitude']);
$lng = $conn->real_escape_string($data['longitude']);

// 1. Semak jika email sudah wujud
$check_sql = "SELECT id FROM users WHERE email = '$email'";
$check_res = $conn->query($check_sql);
if ($check_res->num_rows > 0) {
    die(json_encode(['status' => 'error', 'message' => 'Email already registered']));
}

// 2. Insert ke table users
$conn->begin_transaction();

try {
    $user_sql = "INSERT INTO users (fullname, email, password, phone, role) VALUES ('$fullname', '$email', '$password', '$phone', 'Seller')";
    if (!$conn->query($user_sql)) throw new Exception("Failed to create user account");
    
    $user_id = $conn->insert_id;

    // 3. Insert ke table stalls
    $stall_sql = "INSERT INTO stalls (owner_id, stall_name, phone, email, address, latitude, longitude, approval_status, status) 
                  VALUES ('$user_id', '$stall_name', '$phone', '$email', '$address', '$lat', '$lng', 'Pending', 'Tutup')";
    
    if (!$conn->query($stall_sql)) throw new Exception("Failed to register stall");

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Registration request submitted! Please wait for admin approval.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
