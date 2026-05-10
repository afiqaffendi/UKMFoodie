<?php
include 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_type ENUM('customer', 'seller') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table chat_messages created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
