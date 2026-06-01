<?php
// check_menu_db.php
include 'db.php';
header('Content-Type: application/json');

$sql = "SELECT id, stall_id, item_name, price, category, food_image FROM menu_items ORDER BY id DESC LIMIT 50";
$result = $conn->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows, JSON_PRETTY_PRINT);
$conn->close();
?>
