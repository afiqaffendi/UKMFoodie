<?php
include 'db.php';

$names = ["Aiman", "Afiq", "Ali", "Abu", "Ahmad", "Amin", "Amir", "Arif", "Azim", "Azizi",
          "Siti", "Sarah", "Syahirah", "Syuhada", "Salmah", "Salina", "Safiya", "Syafiq", "Syed", "Sharif",
          "Bakar", "Badrul", "Basyir", "Bilal", "Burhan", "Bukhari", "Balkis", "Batrisyia", "Bahari", "Borhan",
          "Chong", "Cheng", "Chia", "Carmen", "Choo", "Devan", "Dinesh", "Darshini", "Danial", "Danish",
          "Farhan", "Faisal", "Fakhrul", "Farid", "Firdaus", "Farah", "Fatin", "Fauziah", "Faris", "Fikri"];

$locations = [
    "Kolej Ungku Omar (KUO)", "Kolej Ibrahim Yaakub (KIY)", "Kolej Dato' Onn (KDO)", 
    "Kolej Tun Hussein Onn (KTHO)", "Kolej Aminuddin Baki (KAB)", "Kolej Rahim Kajai (KRK)", 
    "Kolej Ibu Zain (KIZ)", "Kolej Keris Mas (KKM)", "Kolej Pendeta Za'ba (KPZ)", 
    "Kolej Burhanuddin Helmi (KBH)", "Fakulti Teknologi dan Sains Maklumat (FTSM)", 
    "Fakulti Ekonomi dan Pengurusan (FEP)", "Pusat Pelajar (Pusanika)", "Fakulti Kejuruteraan dan Alam Bina (FKAB)"
];

$stall_prefixes = ["Kafe", "Kedai Makan", "Warung", "Restoran", "Bistro", "Kiosk", "Dapur", "Port", "Santai", "Selera"];
$stall_suffixes = ["Sedap", "Berkat", "Maju", "Viral", "Padu", "Legend", "Bunda", "Kita", "Ceria", "Best"];

$password_hash = password_hash("123456", PASSWORD_DEFAULT);
$phone = "0123456789";
$success_count = 0;

for ($i = 0; $i < 50; $i++) {
    $owner_name = $names[$i];
    $email = strtolower($owner_name) . "@gmail.com";
    
    // Check if user exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) continue;

    // 1. Insert User
    $sql_user = "INSERT INTO users (fullname, phone, email, password, role, last_login) 
                 VALUES ('$owner_name', '$phone', '$email', '$password_hash', 'seller', NOW())";
    
    if ($conn->query($sql_user)) {
        $user_id = $conn->insert_id;
        
        // 2. Insert Stall
        $stall_name = $stall_prefixes[array_rand($stall_prefixes)] . " " . $owner_name . " " . $stall_suffixes[array_rand($stall_suffixes)];
        $description = "Pilihan makanan terbaik di " . $stall_name . ". Jom cuba!";
        
        $location_raw = $locations[$i % count($locations)]; // Distribute evenly
        $location = $conn->real_escape_string($location_raw);
        $stall_name_safe = $conn->real_escape_string($stall_name);
        $description_safe = $conn->real_escape_string($description);
        
        $sql_stall = "INSERT INTO stalls (owner_id, stall_name, description, phone, email, address, location_area, opening_time, closing_time, bank_name, account_number, account_holder, status, approval_status, stall_image) 
                      VALUES ($user_id, '$stall_name_safe', '$description_safe', '$phone', '$email', '$location', '$location', '08:00:00', '22:00:00', 'Maybank', '1234567890', '$owner_name', 'Buka', 'Approved', 'default_stall.jpg')";
        
        if ($conn->query($sql_stall)) {
            $success_count++;
        } else {
            echo "Error inserting stall for $owner_name: " . $conn->error . "\n";
        }
    } else {
        echo "Error inserting user $owner_name: " . $conn->error . "\n";
    }
}

echo "Successfully seeded $success_count stalls and users!\n";
$conn->close();
?>
