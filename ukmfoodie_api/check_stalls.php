<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
$result = $conn->query("SELECT id, owner_id, stall_name FROM stalls");
$stalls = [];
while($row = $result->fetch_assoc()) { $stalls[] = $row; }
echo json_encode($stalls);
?>
