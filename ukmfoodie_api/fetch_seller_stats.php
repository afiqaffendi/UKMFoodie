<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Use direct connection to be safe, exactly like login_seller.php
date_default_timezone_set('Asia/Kuala_Lumpur');
$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$stall_id = isset($_GET['stall_id']) ? (int)$_GET['stall_id'] : 0;

if ($stall_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Stall ID']);
    exit;
}

$today = date('Y-m-d');

// 1. Revenue Today (Fixed column name to total_amount)
$rev_sql = "SELECT SUM(total_amount) as total FROM orders WHERE stall_id = $stall_id AND DATE(created_at) = '$today' AND status IN ('Preparing', 'Ready', 'Completed')";
$rev_res = $conn->query($rev_sql);
$revenue = ($rev_res && $row = $rev_res->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// 2. Total Orders Today
$order_sql = "SELECT COUNT(*) as total FROM orders WHERE stall_id = $stall_id AND DATE(created_at) = '$today'";
$order_res = $conn->query($order_sql);
$total_orders = ($order_res && $row = $order_res->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// 3. Total Items Sold Today
$items_sql = "SELECT SUM(oi.quantity) as total 
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              WHERE o.stall_id = $stall_id 
              AND DATE(o.created_at) = '$today'
              AND o.status IN ('Preparing', 'Ready', 'Completed')";
$items_res = $conn->query($items_sql);
$total_items = ($items_res && $row = $items_res->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// 4. Dynamic Chart Data (Weekly or Monthly)
$view_type = isset($_GET['view_type']) ? $_GET['view_type'] : 'weekly';
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$week_num = isset($_GET['week']) ? (int)$_GET['week'] : 0; // 0 means "Current Week"

$chart_data = [];
$chart_labels = [];

if ($view_type === 'monthly') {
    // Monthly View: 12 months of the year
    $chart_data = array_fill(0, 12, 0);
    $chart_labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    
    $month_sql = "SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                  FROM orders 
                  WHERE stall_id = $stall_id 
                  AND YEAR(created_at) = $year 
                  AND status IN ('Preparing', 'Ready', 'Completed')
                  GROUP BY MONTH(created_at)";
    $month_res = $conn->query($month_sql);
    if ($month_res) {
        while($row = $month_res->fetch_assoc()) {
            $chart_data[(int)$row['month'] - 1] = (float)$row['total'];
        }
    }
} else {
    // Weekly View: 7 days
    $chart_data = array_fill(0, 7, 0);
    $chart_labels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    
    if ($week_num > 0) {
        // Specific week of a specific month
        $first_day = "$year-$month-01";
        $start_ts = strtotime($first_day . " +" . (($week_num - 1) * 7) . " days");
        $start_date = date('Y-m-d', $start_ts);
        $end_date = date('Y-m-d', strtotime($start_date . " +6 days"));
        
        $week_sql = "SELECT DAYOFWEEK(created_at) as day, SUM(total_amount) as total 
                     FROM orders 
                     WHERE stall_id = $stall_id 
                     AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                     AND status IN ('Preparing', 'Ready', 'Completed')
                     GROUP BY DAYOFWEEK(created_at)";
    } else {
        // Default: Last 7 days
        $week_sql = "SELECT DAYOFWEEK(created_at) as day, SUM(total_amount) as total 
                     FROM orders 
                     WHERE stall_id = $stall_id 
                     AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     AND status IN ('Preparing', 'Ready', 'Completed')
                     GROUP BY DAYOFWEEK(created_at)";
    }
    
    $week_res = $conn->query($week_sql);
    if ($week_res) {
        while($row = $week_res->fetch_assoc()) {
            // DAYOFWEEK: 1=Sun, 2=Mon... 7=Sat
            // We want: Mon=0, Tue=1 ... Sun=6
            $day_index = ($row['day'] + 5) % 7;
            $chart_data[$day_index] = (float)$row['total'];
        }
    }
}

// 5. Stall Rating (Cumulative)
$rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE stall_id = $stall_id";
$rating_res = $conn->query($rating_sql);
$rating_data = ($rating_res) ? $rating_res->fetch_assoc() : null;
$avg_rating = ($rating_data && $rating_data['avg_rating']) ? number_format($rating_data['avg_rating'], 1) : "0.0";
$total_reviews = ($rating_data) ? $rating_data['total_reviews'] : 0;

// 6. Recent Orders (Fixed column name to total_amount)
$recent_orders = [];
$recent_sql = "SELECT id, total_amount as total_price, status, payment_receipt, created_at FROM orders 
              WHERE stall_id = $stall_id 
              AND DATE(created_at) = '$today' 
              ORDER BY created_at DESC";
$recent_res = $conn->query($recent_sql);
if ($recent_res) {
    while($row = $recent_res->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// 7. Top Selling Items
$top_items = [];
$top_sql = "SELECT oi.item_name, SUM(oi.quantity) as sold_qty, oi.price, m.food_image
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN menu_items m ON (oi.item_name = m.item_name AND o.stall_id = m.stall_id)
            WHERE o.stall_id = $stall_id 
            AND DATE(o.created_at) = '$today'
            AND o.status IN ('Preparing', 'Ready', 'Completed')
            GROUP BY oi.item_name
            ORDER BY sold_qty DESC
            LIMIT 5";
$top_res = $conn->query($top_sql);
if ($top_res) {
    while($row = $top_res->fetch_assoc()) {
        $top_items[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'stats' => [
        'revenue' => number_format($revenue, 2),
        'total_orders' => (int)$total_orders,
        'total_items' => (int)$total_items,
        'rating' => (string)$avg_rating,
        'total_reviews' => (int)$total_reviews
    ],
    'chart_data' => $chart_data,
    'chart_labels' => $chart_labels,
    'weekly_sales' => $chart_data, // Compatibility
    'recent_orders' => $recent_orders,
    'top_items' => $top_items
]);

$conn->close();
