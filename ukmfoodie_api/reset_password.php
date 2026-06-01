<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email) && isset($data->password)) {
    $email = mysqli_real_escape_string($conn, $data->email);
    $new_password = password_hash($data->password, PASSWORD_DEFAULT);

    // Update query
    $sql = "UPDATE users SET password='$new_password' WHERE email='$email'";
    
    if($conn->query($sql) === TRUE) {
        echo json_encode([
            "status" => "success",
            "message" => "Your password has been reset successfully. Please login with your new password!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update password. Please try again."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters."
    ]);
}

$conn->close();
?>
