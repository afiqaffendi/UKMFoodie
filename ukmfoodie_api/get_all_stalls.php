<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Ambil semua gerai dari pangkalan data, TERMASUK stall_image
$sql = "SELECT id, stall_name, description, opening_time, closing_time, status, stall_image, off_days, latitude, longitude 
        FROM stalls 
        WHERE approval_status = 'Approved' 
        ORDER BY id ASC";
$result = $conn->query($sql);

$stalls = [];
date_default_timezone_set("Asia/Kuala_Lumpur");
$current_time = date("H:i:s");
$current_day = date("l");

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $computed_status = $row['status']; // Default to manual status
        
        if ($computed_status === 'Buka') {
            // Check off days
            $off_days_array = array_map('trim', explode(',', $row['off_days'] ?? ''));
            if (in_array($current_day, $off_days_array)) {
                $computed_status = 'Tutup';
            } 
            // Check time
            elseif ($row['opening_time'] && $row['closing_time']) {
                if ($current_time < $row['opening_time'] || $current_time > $row['closing_time']) {
                    $computed_status = 'Tutup';
                }
            }
        }
        
        $row['status'] = $computed_status;
        $stalls[] = $row;
    }
}

// Hantar data kembali dalam format JSON
echo json_encode([
    "status" => "success", 
    "data" => $stalls
]);

$conn->close();
?>