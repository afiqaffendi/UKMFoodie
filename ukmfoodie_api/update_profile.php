<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$user_id = isset($_POST['user_id']) ? $conn->real_escape_string($_POST['user_id']) : '';
$fullname = isset($_POST['fullname']) ? $conn->real_escape_string($_POST['fullname']) : '';
$phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';

if (empty($user_id)) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit;
}

$updateQuery = "UPDATE users SET fullname='$fullname', phone='$phone'";

// Check if a new profile picture is uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_ext = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
    $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $updateQuery .= ", profile_picture='$new_filename'";
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        exit;
    }
}

$updateQuery .= " WHERE id='$user_id'";

if ($conn->query($updateQuery) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Profile updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update profile: " . $conn->error]);
}

$conn->close();
?>
