<?php
// Tetapan Header supaya React Native boleh akses fail ini tanpa halangan (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

// Panggil fail sambungan database
include 'db.php';

// Terima data JSON dari React Native
$data = json_decode(file_get_contents("php://input"));

// Pastikan data tidak kosong
if(isset($data->fullname) && isset($data->email) && isset($data->password)) {
    
    $fullname = $data->fullname;
    $phone = $data->phone;
    $email = $data->email;
    $role = $data->role;
    // Sulitkan (Encrypt) kata laluan demi keselamatan
    $password = password_hash($data->password, PASSWORD_DEFAULT); 

    // Semak jika e-mel sudah wujud dalam pangkalan data
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if($result->num_rows > 0) {
        // Jika e-mel dah wujud
        echo json_encode(["status" => "error", "message" => "E-mel ini telah didaftarkan!"]);
    } else {
        // Jika e-mel belum wujud, masukkan data ke dalam jadual users
        $sql = "INSERT INTO users (fullname, phone, email, password, role) VALUES ('$fullname', '$phone', '$email', '$password', '$role')";
        
        if($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Pendaftaran berjaya!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Ralat pangkalan data: " . $conn->error]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Sila isikan semua maklumat yang diperlukan."]);
}

$conn->close();
?>