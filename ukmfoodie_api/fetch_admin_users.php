<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'User';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($role === 'User') {
    // Fetch Customers
    $sql = "SELECT id, fullname, email, phone, role, created_at, profile_picture 
            FROM users 
            WHERE (role = 'customer' OR role = 'User')";
    
    if (!empty($search)) {
        $sql .= " AND (fullname LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
    }
} else {
    // Fetch Approved Sellers
    $sql = "SELECT u.id, u.fullname, u.email, u.phone, u.role, u.created_at, u.profile_picture, s.stall_name, s.approval_status, s.stall_image 
            FROM users u
            JOIN stalls s ON u.id = s.owner_id
            WHERE u.role = 'Seller' AND s.approval_status = 'Approved'";
            
    if (!empty($search)) {
        $sql .= " AND (u.fullname LIKE '%$search%' OR u.email LIKE '%$search%' OR s.stall_name LIKE '%$search%')";
    }
}

$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format ID for display
        $row['formatted_id'] = ($role === 'User' ? 'USR' : 'SLR') . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "success", "data" => []]);
}

$conn->close();
?>
