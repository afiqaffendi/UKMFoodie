<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

// 1. Cipta user Seller baru
$email = 'seller@ukm.com';
$password = 'password123';
$fullname = 'Seller UKMFoodie';

$check = $conn->query("SELECT id FROM users WHERE email = '$email'");
if ($check->num_rows > 0) {
    $user_id = $check->fetch_assoc()['id'];
    $conn->query("UPDATE users SET role = 'Seller', password = '$password' WHERE id = $user_id");
} else {
    $conn->query("INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$password', 'Seller')");
    $user_id = $conn->insert_id;
}

// 2. Update stall supaya owner_id merujuk kepada seller baru ini
$conn->query("UPDATE stalls SET owner_id = $user_id WHERE id = 1");

echo json_encode(['status' => 'success', 'message' => 'Test account created', 'email' => $email, 'password' => $password]);
?>
