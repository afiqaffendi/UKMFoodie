<?php
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
$result = $conn->query('DESCRIBE orders');
$cols = [];
while($row = $result->fetch_assoc()) {
    $cols[] = $row;
}
echo json_encode($cols);
?>
