<?php
include 'db.php';
$res = $conn->query("SELECT id, status FROM orders ORDER BY id DESC LIMIT 10");
while($row = $res->fetch_assoc()) {
    echo $row['id'] . ": " . $row['status'] . "\n";
}
$conn->close();
?>
