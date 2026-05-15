<?php
include 'db.php';
$result = $conn->query("SHOW COLUMNS FROM order_items");
$columns = [];
while($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo json_encode($columns);
?>
