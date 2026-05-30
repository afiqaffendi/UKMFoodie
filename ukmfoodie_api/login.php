<?php
// Tetapan Header
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Terima data JSON dari React Native
$data = json_decode(file_get_contents("php://input"));

if(isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Cari pengguna berdasarkan e-mel
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Semak kata laluan (kerana kita encrypt masa register tadi)
        if(password_verify($password, $user['password'])) {
            // Update last_login timestamp
            $updateSql = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
            $conn->query($updateSql);

            echo json_encode([
                "status" => "success", 
                "message" => "Login successful!",
                "data" => [
                    "id" => $user['id'],
                    "fullname" => $user['fullname'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email does not exist! Please register first."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Please enter email and password."]);
}

$conn->close();
?>