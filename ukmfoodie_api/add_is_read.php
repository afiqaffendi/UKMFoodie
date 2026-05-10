<?php
include 'db.php';
$sql = "ALTER TABLE chat_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0";
if ($conn->query($sql) === TRUE) {
    echo "Column is_read added successfully";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
