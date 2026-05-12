<?php
include 'db.php';

// Tambah kolum location_area kepada table stalls
$sql = "ALTER TABLE stalls ADD COLUMN IF NOT EXISTS location_area VARCHAR(150) DEFAULT NULL AFTER address";
if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Kolum location_area berjaya ditambah."]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
$conn->close();
?>
