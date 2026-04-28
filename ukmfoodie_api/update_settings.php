<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

// Ambil data dari $_POST (bukan JSON kerana kita hantar fail)
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

    $image_query = "";
    
    // Semak jika ada fail gambar dimuat naik
    if(isset($_FILES['stall_image'])) {
        $file_name = time() . "_" . $_FILES['stall_image']['name'];
        $target_dir = "uploads/" . $file_name;
        
        if(move_uploaded_file($_FILES['stall_image']['tmp_name'], $target_dir)) {
            $image_query = ", stall_image='$file_name'";
        }
    }

    $sql = "UPDATE stalls SET 
            stall_name='$stall_name', 
            description='$description', 
            phone='$phone', 
            email='$email', 
            bank_name='$bank_name', 
            account_number='$account_number', 
            account_holder='$account_holder',
            opening_time='$opening_time',
            closing_time='$closing_time'
            $image_query
            WHERE id=$id";
            
    if($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Profil berjaya dikemas kini!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>