<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// 1. Tambah column address dan approval_status ke table stalls
// 2. Tambah fullname ke users (jika belum ada, tapi rasanya dah ada)
$queries = [
    "ALTER TABLE stalls ADD COLUMN address TEXT AFTER description",
    "ALTER TABLE stalls ADD COLUMN approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending' AFTER status"
];

$results = [];
foreach ($queries as $query) {
    if ($conn->query($query)) {
        $results[] = "Success: " . $query;
    } else {
        $results[] = "Error/Already Exists: " . $conn->error;
    }
}

echo json_encode(['status' => 'done', 'results' => $results]);
?>
