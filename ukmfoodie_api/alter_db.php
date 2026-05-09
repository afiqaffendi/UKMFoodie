<?php
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
if ($conn->query("ALTER TABLE stalls ADD off_days VARCHAR(255) NULL") === TRUE) {
    echo "Success";
} else {
    echo "Error: " . $conn->error;
}
?>
