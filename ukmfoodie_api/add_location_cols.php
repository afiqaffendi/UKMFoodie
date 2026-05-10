<?php
include 'db.php';
$conn->query("ALTER TABLE stalls ADD COLUMN latitude DECIMAL(10, 8) DEFAULT 2.9289");
$conn->query("ALTER TABLE stalls ADD COLUMN longitude DECIMAL(11, 8) DEFAULT 101.7801");
if ($conn->error) {
    echo "Error: " . $conn->error;
} else {
    echo "Success: Latitude and Longitude columns added.";
}
$conn->close();
?>
