<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// 1. Total Registered Users
$users_res = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $users_res->fetch_assoc()['total'];

// 2. Active Stalls (Approved)
$stalls_res = $conn->query("SELECT COUNT(*) as total FROM stalls WHERE approval_status = 'Approved'");
$active_stalls = $stalls_res->fetch_assoc()['total'];

// 3. Total Daily Transactions (Today)
$today = date('Y-m-d');
$trans_res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$today' AND status != 'Rejected'");
$daily_revenue = $trans_res->fetch_assoc()['total'] ?? 0;

// 4. Recent System Activity (Mock/Dynamic)
// Here we combine new user registrations and new stall applications
$activity_sql = "
    (SELECT 'New registration' as action, fullname as user, 'New user registration verified' as details, created_at FROM users)
    UNION
    (SELECT 'New Stall' as action, stall_name as user, 'Submitted application for approval' as details, created_at FROM stalls)
    ORDER BY created_at DESC LIMIT 6
";
$activity_res = $conn->query($activity_sql);
$activities = [];
while($row = $activity_res->fetch_assoc()) {
    $activities[] = $row;
}

echo json_encode([
    'status' => 'success',
    'stats' => [
        'total_users' => number_format($total_users),
        'active_stalls' => $active_stalls,
        'daily_revenue' => 'RM ' . number_format($daily_revenue, 2)
    ],
    'activities' => $activities
]);

$conn->close();
?>
