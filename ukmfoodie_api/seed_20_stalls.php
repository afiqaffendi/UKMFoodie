<?php
include 'db.php';

$all_locations = [
    "Kolej Pendeta Za'ba", "Kolej Keris Mas", "Kolej Tun Hussein Onn", "Kolej Aminuddin Baki", 
    "Kolej Rahim Kajai", "Kolej Ibrahim Yaakob", "Kolej Ungku Omar", "Kolej Burhanuddin Helmi", 
    "Kolej Dato' Onn", "Kolej Ibu Zain", "Fakulti Teknologi & Sains Maklumat (FTSM)", 
    "Fakulti Kejuruteraan & Alam Bina (FKAB)", "Fakulti Sains & Teknologi (FST)", 
    "Fakulti Pendidikan (FPEND)", "Fakulti Ekonomi & Pengurusan (FEP)", 
    "Fakulti Sains Sosial & Kemanusiaan (FSSK)", "Fakulti Undang-Undang (FUU)", 
    "Fakulti Pengajian Islam (FPI)", "Pusat Pengajian Citra Universiti (PPCU)", 
    "Dewan Canselor Tun Abdul Razak (DECTAR)", "Pusanika", "Pusat Islam Universiti", 
    "Pusat Kesihatan Universiti (PKU)", "Perpustakaan Tun Sri Lanang (PTSL)", "Stadium UKM"
];

$names = ["Haris", "Haikal", "Haziq", "Hakim", "Irfan", "Imran", "Ikmal", "Iqbal", "Idris", "Ismail",
          "Zaim", "Zarul", "Zikri", "Zul", "Zainal", "Jamal", "Jalil", "Jamil", "Johari", "Jufri",
          "Kamal", "Kassim", "Kamil", "Khairul", "Luqman", "Lutfi", "Latif", "Mazlan", "Musa", "Mustafa"];

$stall_prefixes = ["Kafe", "Kedai Makan", "Warung", "Restoran", "Bistro", "Kiosk", "Dapur", "Port", "Santai", "Selera"];
$stall_suffixes = ["Sedap", "Berkat", "Maju", "Viral", "Padu", "Legend", "Bunda", "Kita", "Ceria", "Best"];
$password_hash = password_hash("123456", PASSWORD_DEFAULT);
$phone = "0123456789";

// Find empty locations
$empty_locations = [];
foreach ($all_locations as $loc) {
    $loc_safe = $conn->real_escape_string($loc);
    $res = $conn->query("SELECT COUNT(*) as cnt FROM stalls WHERE location_area = '$loc_safe'");
    $row = $res->fetch_assoc();
    if ($row['cnt'] == 0) {
        $empty_locations[] = $loc;
    }
}

$success_count = 0;
for ($i = 0; $i < 20; $i++) {
    $owner_name = $names[$i];
    $email = strtolower($owner_name) . "@gmail.com";
    
    // Determine location: pick from empty locations first, if none, random from all
    if (count($empty_locations) > 0) {
        $location = array_shift($empty_locations); // Take the first empty location and remove from array
    } else {
        $location = $all_locations[array_rand($all_locations)]; // Random fallback
    }

    $location_safe = $conn->real_escape_string($location);
    
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) continue;

    $sql_user = "INSERT INTO users (fullname, phone, email, password, role, last_login) 
                 VALUES ('$owner_name', '$phone', '$email', '$password_hash', 'seller', NOW())";
    
    if ($conn->query($sql_user)) {
        $user_id = $conn->insert_id;
        $stall_name = $stall_prefixes[array_rand($stall_prefixes)] . " " . $owner_name . " " . $stall_suffixes[array_rand($stall_suffixes)];
        $description = "Pilihan makanan terbaik di " . $stall_name . ". Jom cuba!";
        $stall_name_safe = $conn->real_escape_string($stall_name);
        $description_safe = $conn->real_escape_string($description);
        
        $sql_stall = "INSERT INTO stalls (owner_id, stall_name, description, phone, email, address, location_area, opening_time, closing_time, bank_name, account_number, account_holder, status, approval_status, stall_image) 
                      VALUES ($user_id, '$stall_name_safe', '$description_safe', '$phone', '$email', '$location_safe', '$location_safe', '08:00:00', '22:00:00', 'Maybank', '1234567890', '$owner_name', 'Buka', 'Approved', 'default_stall.jpg')";
        
        if ($conn->query($sql_stall)) {
            $success_count++;
        }
    }
}

echo "Successfully seeded $success_count extra stalls! Covered remaining empty locations.\n";
$conn->close();
?>
