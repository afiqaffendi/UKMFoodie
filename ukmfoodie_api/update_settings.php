<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

if(isset($_POST['stall_id'])) {
    $id = $_POST['stall_id'];
    $stall_name = $conn->real_escape_string($_POST['stall_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $account_holder = $conn->real_escape_string($_POST['account_holder']);
    $opening_time = $conn->real_escape_string($_POST['opening_time']);
    $closing_time = $conn->real_escape_string($_POST['closing_time']);
    $off_days = isset($_POST['off_days']) ? $conn->real_escape_string($_POST['off_days']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'Buka';
    $latitude = isset($_POST['latitude']) ? $conn->real_escape_string($_POST['latitude']) : 2.9289;
    $longitude = isset($_POST['longitude']) ? $conn->real_escape_string($_POST['longitude']) : 101.7801;
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $address = $conn->real_escape_string($_POST['address']);

    // Update users table for owner name
    $conn->query("UPDATE users SET fullname='$owner_name' WHERE id = (SELECT owner_id FROM stalls WHERE id=$id)");

    $image_query = "";
    
    // 1. Logik Muat Naik Gambar Profil Gerai
    if(isset($_FILES['stall_image']) && $_FILES['stall_image']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . "_profile_" . $_FILES['stall_image']['name'];
        $target_dir = "uploads/" . $file_name;
        if(move_uploaded_file($_FILES['stall_image']['tmp_name'], $target_dir)) {
            $image_query .= ", stall_image='$file_name'";
        }
    }

    // 2. Logik Muat Naik QR Code (BARU)
    if(isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $qr_name = time() . "_qr_" . $_FILES['qr_image']['name'];
        $qr_target = "uploads/" . $qr_name;
        if(move_uploaded_file($_FILES['qr_image']['tmp_name'], $qr_target)) {
            $image_query .= ", qr_path='$qr_name'";
        }
    }

    $sql = "UPDATE stalls SET 
            stall_name='$stall_name', 
            description='$description', 
            phone='$phone', 
            email='$email', 
            address='$address', 
            bank_name='$bank_name', 
            account_number='$account_number', 
            account_holder='$account_holder',
            opening_time='$opening_time',
            closing_time='$closing_time',
            off_days='$off_days',
            status='$status',
            latitude='$latitude',
            longitude='$longitude'
            $image_query
            WHERE id=$id";
            
    if($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Profil & QR berjaya dikemas kini!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>