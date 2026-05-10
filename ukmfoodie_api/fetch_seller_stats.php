<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'db.php';

$stall_id = isset($_GET['stall_id']) ? $conn->real_escape_string($_GET['stall_id']) : 0;

if ($stall_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Stall ID']);
    exit;
}

// 1. Revenue Today
$today = date('Y-m-d');
$rev_sql = "SELECT SUM(total_price) as total FROM orders WHERE stall_id = '$stall_id' AND DATE(created_at) = '$today' AND status = 'Selesai'";
$rev_res = $conn->query($rev_sql);
$revenue = $rev_res->fetch_assoc()['total'] ?? 0;

// 2. Total Orders Today
$order_sql = "SELECT COUNT(*) as total FROM orders WHERE stall_id = '$stall_id' AND DATE(created_at) = '$today'";
$order_res = $conn->query($order_sql);
$total_orders = $order_res->fetch_assoc()['total'] ?? 0;

// 3. Pending Orders
$pending_sql = "SELECT COUNT(*) as total FROM orders WHERE stall_id = '$stall_id' AND status = 'Baru'";
$pending_res = $conn->query($pending_sql);
$pending_orders = $pending_res->fetch_assoc()['total'] ?? 0;

// 4. Weekly Sales Data for Chart
$weekly_data = [0, 0, 0, 0, 0, 0, 0];
$week_sql = "SELECT DAYOFWEEK(created_at) as day, SUM(total_price) as total 
             FROM orders 
             WHERE stall_id = '$stall_id' 
             AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             AND status = 'Selesai'
             GROUP BY DAYOFWEEK(created_at)";
$week_res = $conn->query($week_sql);
while($row = $week_res->fetch_assoc()) {
    // PHP DAYOFWEEK: 1 (Sun) to 7 (Sat) -> Chart.js usually 0 (Mon) to 6 (Sun) or follows labels
    // Let's adjust to match Monday-Sunday labels in dashboard.html
    $day_index = ($row['day'] + 5) % 7; // Convert 1(Sun)->6, 2(Mon)->0, ...
    $weekly_data[$day_index] = (float)$row['total'];
}

// 5. Recent Orders
$recent_orders = [];
$recent_sql = "SELECT id, customer_name, total_price, status, created_at FROM orders WHERE stall_id = '$stall_id' ORDER BY created_at DESC LIMIT 5";
$recent_res = $conn->query($recent_sql);
while($row = $recent_res->fetch_assoc()) {
    $recent_orders[] = $row;
}

echo json_encode([
    'status' => 'success',
    'stats' => [
        'revenue' => number_format($revenue, 2),
        'total_orders' => $total_orders,
        'pending_orders' => $pending_orders,
        'rating' => '5.0' // Mock for now
    ],
    'weekly_sales' => $weekly_data,
    'recent_orders' => $recent_orders
]);

$conn->close();
?>
