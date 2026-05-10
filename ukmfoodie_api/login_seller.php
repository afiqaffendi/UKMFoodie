<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli('localhost', 'root', '', 'ukmfoodie_db');

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    die(json_encode(['status' => 'error', 'message' => 'Email and Password are required']));
}

$email = $conn->real_escape_string($data['email']);
$password = $data['password'];

// 1. Cari user dalam table users
$user_sql = "SELECT * FROM users WHERE email = '$email' AND role = 'Seller' LIMIT 1";
$user_res = $conn->query($user_sql);

if ($user_res->num_rows > 0) {
    $user = $user_res->fetch_assoc();
    
    // 2. Sahkan password (andaikan plain text buat masa ni, atau guna password_verify jika hash)
    // Untuk tujuan development, kita check direct. Tapi elok guna password_verify.
    if ($password === $user['password']) {
        
        // 3. Cari stall yang dimiliki oleh user ini
        $owner_id = $user['id'];
        $stall_sql = "SELECT id as stall_id, stall_name FROM stalls WHERE owner_id = '$owner_id' LIMIT 1";
        $stall_res = $conn->query($stall_sql);
        
        if ($stall_res->num_rows > 0) {
            $stall = $stall_res->fetch_assoc();
            
            if ($stall['approval_status'] === 'Approved') {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'user_id' => $user['id'],
                        'fullname' => $user['fullname'],
                        'email' => $user['email'],
                        'stall_id' => $stall['stall_id'],
                        'stall_name' => $stall['stall_name']
                    ]
                ]);
            } else if ($stall['approval_status'] === 'Pending') {
                echo json_encode(['status' => 'error', 'message' => 'Your stall registration is still pending approval from admin.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Your stall registration has been rejected. Please contact admin.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Seller account found but no stall registered to this user.']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Seller account not found']);
}

$conn->close();
?>
