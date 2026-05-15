<?php
include 'db.php';
$sql = "ALTER TABLE order_items ADD COLUMN note TEXT AFTER price";
if ($conn->query($sql) === TRUE) {
    echo "Column 'note' added successfully to order_items";
} else {
    echo "Error adding column: " . $conn->error;
}
$conn->close();
?>
