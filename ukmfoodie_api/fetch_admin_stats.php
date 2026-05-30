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

// 4. Total Orders Today
$orders_res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = '$today'");
$total_orders_today = $orders_res->fetch_assoc()['total'] ?? 0;

// 5. Total Platform Revenue
$platform_res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Rejected'");
$total_platform_revenue = $platform_res->fetch_assoc()['total'] ?? 0;

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'all_activities') {
    $activity_sql = "
        (SELECT 'New registration' as action, fullname as user, 'New user registration verified' as details, created_at FROM users)
        UNION
        (SELECT 'New Stall' as action, stall_name as user, 'Submitted application for approval' as details, created_at FROM stalls)
        ORDER BY created_at DESC LIMIT 100
    ";
    $activity_res = $conn->query($activity_sql);
    $activities = [];
    while($row = $activity_res->fetch_assoc()) {
        $activities[] = $row;
    }
    echo json_encode(['status' => 'success', 'activities' => $activities]);
    $conn->close();
    exit;
}

// 6. Recent System Activity (Mock/Dynamic)
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

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'weekly';

// 7. Chart Data - Sales Trend
$sales_labels = [];
$sales_data = [];

if ($filter === 'yearly') {
    // 12 Months of the current year
    $sales_data = array_fill(0, 12, 0);
    $sales_labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    $year = date('Y');
    
    $sales_sql = "SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                  FROM orders 
                  WHERE YEAR(created_at) = '$year' AND status != 'Rejected'
                  GROUP BY MONTH(created_at)";
    $sales_res = $conn->query($sales_sql);
    while($row = $sales_res->fetch_assoc()) {
        $sales_data[(int)$row['month'] - 1] = (float)$row['total'];
    }
} else if ($filter === 'monthly') {
    // 5 Weeks of the current month
    $sales_data = array_fill(0, 5, 0);
    $sales_labels = ["Week 1", "Week 2", "Week 3", "Week 4", "Week 5"];
    $month = date('m');
    $year = date('Y');
    
    $sales_sql = "SELECT CEILING(DAY(created_at) / 7) AS week_of_month, SUM(total_amount) as total 
                  FROM orders 
                  WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year' AND status != 'Rejected'
                  GROUP BY week_of_month";
    $sales_res = $conn->query($sales_sql);
    while($row = $sales_res->fetch_assoc()) {
        $week_idx = (int)$row['week_of_month'] - 1;
        if ($week_idx >= 0 && $week_idx < 5) {
            $sales_data[$week_idx] = (float)$row['total'];
        }
    }
} else {
    // Weekly (Last 7 Days)
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $sales_labels[] = date('D', strtotime($date));
        
        $sales_sql = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$date' AND status != 'Rejected'";
        $sales_res = $conn->query($sales_sql);
        $total = $sales_res->fetch_assoc()['total'] ?? 0;
        $sales_data[] = (float)$total;
    }
}

// 8. Chart Data - Order Status (Accepted vs Rejected)
$accepted_sql = "SELECT COUNT(*) as count FROM orders WHERE status IN ('Preparing', 'Ready', 'Completed')";
$accepted_count = $conn->query($accepted_sql)->fetch_assoc()['count'] ?? 0;

$rejected_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'Rejected'";
$rejected_count = $conn->query($rejected_sql)->fetch_assoc()['count'] ?? 0;

$status_labels = ['Accepted', 'Rejected'];
$status_data = [(int)$accepted_count, (int)$rejected_count];
$status_colors = ['#50CD89', '#F1416C']; // Green for Accepted, Red for Rejected

// 9. Chart Data - Top 5 Stalls
$top_stalls_sql = "
    SELECT s.stall_name, COUNT(o.id) as order_count 
    FROM orders o
    JOIN stalls s ON o.stall_id = s.id
    GROUP BY s.id
    ORDER BY order_count DESC
    LIMIT 5
";
$top_stalls_res = $conn->query($top_stalls_sql);
$top_stalls_labels = [];
$top_stalls_data = [];
while($row = $top_stalls_res->fetch_assoc()) {
    // Truncate stall name if too long
    $name = strlen($row['stall_name']) > 15 ? substr($row['stall_name'], 0, 15) . '...' : $row['stall_name'];
    $top_stalls_labels[] = $name;
    $top_stalls_data[] = (int)$row['order_count'];
}

echo json_encode([
    'status' => 'success',
    'stats' => [
        'total_users' => number_format($total_users),
        'active_stalls' => $active_stalls,
        'daily_revenue' => 'RM ' . number_format($daily_revenue, 2),
        'total_orders_today' => $total_orders_today,
        'total_platform_revenue' => 'RM ' . number_format($total_platform_revenue, 2)
    ],
    'activities' => $activities,
    'charts' => [
        'sales_trend' => [
            'labels' => $sales_labels,
            'data' => $sales_data
        ],
        'order_status' => [
            'labels' => $status_labels,
            'data' => $status_data,
            'colors' => $status_colors
        ],
        'top_stalls' => [
            'labels' => $top_stalls_labels,
            'data' => $top_stalls_data
        ]
    ]
]);

$conn->close();
?>
