<?php
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update categories to 'Food' if they are not Food, Beverage, or Others
$sql = "UPDATE menu_items SET category = 'Food' WHERE category NOT IN ('Food', 'Beverage', 'Others') OR category IS NULL OR category = ''";

if ($conn->query($sql) === TRUE) {
    echo "Categories updated successfully. Rows affected: " . $conn->affected_rows . "\n";
} else {
    echo "Error updating categories: " . $conn->error . "\n";
}

$conn->close();
?>
