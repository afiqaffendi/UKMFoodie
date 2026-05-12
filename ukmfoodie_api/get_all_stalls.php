<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Ambil semua gerai dari pangkalan data, TERMASUK stall_image
$sql = "SELECT s.id, s.stall_name, s.description, s.opening_time, s.closing_time, s.status, s.stall_image, s.off_days, s.latitude, s.longitude, s.location_area,
               COALESCE(r.avg_rating, 0) as avg_rating,
               COALESCE(r.total_reviews, 0) as total_reviews
        FROM stalls s
        LEFT JOIN (
            SELECT stall_id, AVG(rating) as avg_rating, COUNT(*) as total_reviews
            FROM reviews
            GROUP BY stall_id
        ) r ON s.id = r.stall_id
        WHERE s.approval_status = 'Approved' 
        ORDER BY s.id ASC";
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