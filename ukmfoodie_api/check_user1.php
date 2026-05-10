<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
$result = $conn->query("SELECT * FROM users WHERE id = 1");
echo json_encode($result->fetch_assoc());
?>
