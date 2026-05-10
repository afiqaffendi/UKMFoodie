<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
$result = $conn->query("SELECT id, fullname, email, password, role FROM users WHERE role = 'Seller'");
$users = [];
while($row = $result->fetch_assoc()) { $users[] = $row; }
echo json_encode($users);
?>
