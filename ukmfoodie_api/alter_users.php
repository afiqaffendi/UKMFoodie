<?php
include 'db.php';
$sql = "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default_profile.png'";
if ($conn->query($sql)) {
    echo "Success";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
