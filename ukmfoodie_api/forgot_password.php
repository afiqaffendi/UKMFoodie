<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email)) {
    $email = mysqli_real_escape_string($conn, $data->email);

    $sql = "SELECT id FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Email verified successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Email address not found. Please register first."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Please enter your email address."
    ]);
}

$conn->close();
?>
