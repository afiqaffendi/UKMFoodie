<?php
// reseed_600_unique_menu.php
include 'db.php';

header("Content-Type: text/plain; charset=UTF-8");

echo "=== MEMULAKAN PROSES PEMBERSIHAN & RE-SEED 600 MENU UNIK ===\n\n";

// 1. Bersihkan Jadual menu_items
echo "🧹 1. Membersihkan semua menu sedia ada dari jadual `menu_items`...\n";
if ($conn->query("DELETE FROM menu_items")) {
    echo "   ✅ Jadual `menu_items` berjaya dibersihkan sepenuhnya.\n\n";
} else {
    die("   ❌ Gagal membersihkan jadual `menu_items`: " . $conn->error . "\n");
}

// 2. Padam Gambar Menu Lama di Folder uploads/
echo "📁 2. Memadam fail imej menu lama dari folder `uploads/`...\n";
$files_to_delete = [
    "air_kelapa_muda_manis.jpg", "burger_daging_double_cheese.jpg", "carrot_susu_madu_ais_kaw.jpg",
    "char_kway_teow.jpg", "chicken_chop_crunchy.jpg", "french_fries_crunchy_cheese.jpg",
    "horlicks_ais_dinosaurs.jpg", "jus_mango_susu_blender.jpg", "karipap_kentang_crispy.jpg",
    "keropok_lekor_terengganu.jpg", "kopi_o_kampung_ais_kaw.jpg", "kuih_koci_kelapa_gula_melaka.jpg",
    "laksa_utara.jpg", "lemon_tea_ais_segar.jpg", "mee_goreng_mamak.jpg", "milo_ais_kepal_kaw.jpg",
    "nasi_goreng_kampung.jpg", "nasi_kukus_ayam_dara.jpg", "nasi_lemak_ayam_goreng.jpg",
    "nescafe_ais_kaw.jpg", "nugget_ayam_tempura.jpg", "pisang_goreng_crispy.jpg",
    "popia_otak_otak_crispy.jpg", "roti_bakar_kaya_butter.jpg", "roti_canai_tsunami.jpg",
    "sirap_bandung_cincau.jpg", "takoyaki_sotong_double_cheese.jpg", "teh_tarik_madu.jpg",
    "tomyam_seafood_kaw.jpg", "waffle_chocolate_peanut_butter.jpg"
];

$deleted_count = 0;
foreach ($files_to_delete as $file) {
    $filepath = "uploads/" . $file;
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            $deleted_count++;
        }
    }
}
echo "   ✅ Sebanyak $deleted_count fail imej lama berjaya dibuang dari disk.\n\n";

// 3. Ambil 15 Gerai Pertama
echo "🏪 3. Mengambil maklumat 15 gerai pertama...\n";
$sql_stalls = "SELECT id, stall_name FROM stalls ORDER BY id ASC LIMIT 15";
$result_stalls = $conn->query($sql_stalls);

if (!$result_stalls || $result_stalls->num_rows == 0) {
    die("   ❌ Error: Tiada gerai ditemui di dalam database stalls!");
}

$stalls = [];
while ($row = $result_stalls->fetch_assoc()) {
    $stalls[] = $row;
}
echo "   ✅ Dijumpai " . count($stalls) . " gerai untuk di-seed.\n\n";

// 4. Set Generator Data Menu Unik
// Kosmetik & Variasi Food
$food_bases = [
    "Nasi Goreng", "Nasi Lemak", "Nasi Kukus", "Nasi Kerabu", "Nasi Kandar", "Nasi Hujan Panas",
    "Mee Goreng", "Mee Bandung", "Mee Hailam", "Mee Rebus", "Mee Kari", "Mee Sup",
    "Char Kway Teow", "Kuey Teow Basah", "Kuey Teow Ladna", "Kuey Teow Kungfu",
    "Bihun Goreng", "Bihun Sup", "Bihun Singapore", "Bihun Tomyam",
    "Burger", "Roti John", "Roti Canai", "Roti Bom",
    "Chicken Chop", "Lamb Chop", "Fish and Chips", "Meatball",
    "Laksa", "Laksam", "Soto", "Tomyam"
];

$food_proteins = [
    "Ayam Goreng", "Ayam Bakar", "Ayam Crispy", "Ayam Masak Merah", "Ayam Percik", "Ayam Kunyit",
    "Daging Kunyit", "Daging Blackpepper", "Daging Merah", "Daging Salai",
    "Seafood", "Udang Galah", "Sotong Crispy", "Sotong Sumbat",
    "Kambing Golek", "Kambing Bakar", "Salmon", "Dori Crispy",
    "Telur Dadar", "Telur Mata", "Telur Goyang", "Telur Rebus"
];

$food_styles = [
    "Kaw-Kaw", "Padu", "Special", "Double Cheese", "Banjir", "Tsunami",
    "Crispy", "Pedas Cili Padi", "Buttercream", "Salted Egg", "Kam Heong",
    "Teriyaki", "Black Forest", "Kampung", "Pattaya", "Cina", "USA"
];

// Kosmetik & Variasi Beverage
$bev_bases = [
    "Teh", "Milo", "Kopi", "Nescafe", "Sirap", "Sunquick", "Horlicks",
    "Barli", "Jus Mango", "Jus Apple", "Jus Orange", "Jus Carrot", "Jus Watermelon",
    "Lemon Tea", "Green Tea", "Lemonade", "Chocolate", "Vanilla Latte",
    "Matcha Latte", "Soda Blue Lagoon", "Bandung", "Susu Segar", "Air Kelapa"
];

$bev_modifiers = [
    "Ais", "Panas", "Tarik", "O", "C", "Susu", "Madu", "Cincau", "Lemon", "Mint",
    "Lychee", "Float", "Boba", "Jelly", "Selasih"
];

$bev_extras = [
    "Kaw", "Biasa", "Dinosaurs", "Kurang Manis", "Kaw-Kaw", "Extra Ice",
    "Special", "Supreme", "Blended", "Kepal", "Premium", "Signature"
];

// Kosmetik & Variasi Others
$oth_bases = [
    "Pisang Goreng", "Keropok Lekor", "French Fries", "Karipap", "Nugget Ayam",
    "Waffle", "Takoyaki", "Popia", "Samosa", "Roti Bakar", "Kuih Koci",
    "Kuih Seri Muka", "Kuih Karipap", "Donut", "Onion Rings", "Potato Wedges",
    "Cheese Sticks", "Apam Balik", "Cendol", "ABC Ais Kacang", "Puding Diraja",
    "Kek Coklat", "Kek Keju"
];

$oth_flavors = [
    "Crispy", "Cheese", "Chocolate", "Peanut", "Butter", "Kaya", "Spicy",
    "BBQ", "Original", "Gula Melaka", "Caramel", "Strawberry", "Susu",
    "Sotong", "Ketam", "Daging", "Ayam", "Kentang", "Kelapa"
];

$oth_sizes = [
    "5pcs", "3pcs", "6pcs", "10pcs", "Single", "Double", "Set A", "Set B",
    "Viral", "Melenting", "Leleh", "Premium", "Platter", "Combo"
];

// Array semakan keunikan
$seen_food = [];
$seen_bev = [];
$seen_oth = [];

// Fungsi penjana nama unik
$get_unique_food = function($stall_idx, $item_idx) use (&$seen_food, $food_bases, $food_proteins, $food_styles) {
    $offset = 0;
    while (true) {
        $b_idx = ($item_idx + $offset) % count($food_bases);
        $p_idx = ($stall_idx + $item_idx + $offset) % count($food_proteins);
        $s_idx = ($stall_idx * 3 + $item_idx * 7 + $offset) % count($food_styles);
        
        $base = $food_bases[$b_idx];
        $protein = $food_proteins[$p_idx];
        $style = $food_styles[$s_idx];
        
        $choice = ($item_idx + $offset) % 3;
        if ($choice == 0) {
            $name = "$base $protein";
        } elseif ($choice == 1) {
            $name = "$base $style";
        } else {
            $name = "$base $protein $style";
        }
        
        if (!in_array($name, $seen_food)) {
            $seen_food[] = $name;
            return $name;
        }
        $offset++;
    }
};

$get_unique_bev = function($stall_idx, $item_idx) use (&$seen_bev, $bev_bases, $bev_modifiers, $bev_extras) {
    $offset = 0;
    while (true) {
        $b_idx = ($item_idx + $offset) % count($bev_bases);
        $m_idx = ($stall_idx + $item_idx + $offset) % count($bev_modifiers);
        $e_idx = ($stall_idx * 3 + $item_idx * 7 + $offset) % count($bev_extras);
        
        $base = $bev_bases[$b_idx];
        $mod = $bev_modifiers[$m_idx];
        $extra = $bev_extras[$e_idx];
        
        $choice = ($item_idx + $offset) % 3;
        if ($choice == 0) {
            $name = "$base $mod";
        } elseif ($choice == 1) {
            $name = "$base $extra";
        } else {
            $name = "$base $mod $extra";
        }
        
        if (!in_array($name, $seen_bev)) {
            $seen_bev[] = $name;
            return $name;
        }
        $offset++;
    }
};

$get_unique_oth = function($stall_idx, $item_idx) use (&$seen_oth, $oth_bases, $oth_flavors, $oth_sizes) {
    $offset = 0;
    while (true) {
        $b_idx = ($item_idx + $offset) % count($oth_bases);
        $f_idx = ($stall_idx + $item_idx + $offset) % count($oth_flavors);
        $s_idx = ($stall_idx * 3 + $item_idx * 7 + $offset) % count($oth_sizes);
        
        $base = $oth_bases[$b_idx];
        $flavor = $oth_flavors[$f_idx];
        $size = $oth_sizes[$s_idx];
        
        $choice = ($item_idx + $offset) % 3;
        if ($choice == 0) {
            $name = "$base $flavor";
        } elseif ($choice == 1) {
            $name = "$base ($size)";
        } else {
            $name = "$base $flavor ($size)";
        }
        
        if (!in_array($name, $seen_oth)) {
            $seen_oth[] = $name;
            return $name;
        }
        $offset++;
    }
};

echo "🚀 4. Memulakan seeding 600 menu unik untuk 15 gerai pertama...\n";
$total_inserted = 0;

foreach ($stalls as $stall_idx => $stall) {
    $stall_id = $stall['id'];
    $stall_name = $stall['stall_name'];
    
    echo "👉 Gerai " . ($stall_idx + 1) . "/15 (ID $stall_id: '$stall_name')...\n";
    
    // a. Seed 15 Food
    for ($i = 0; $i < 15; $i++) {
        $food_name = $get_unique_food($stall_idx, $i);
        $price = number_format(rand(50, 150) / 10, 2); // RM 5.00 - RM 15.00
        $food_name_safe = $conn->real_escape_string($food_name);
        
        $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image, status) 
                VALUES ('$stall_id', '$food_name_safe', '$price', 'Food', 'default_food.jpg', 'Available')";
        if ($conn->query($sql)) {
            $total_inserted++;
        }
    }
    
    // b. Seed 15 Beverage
    for ($i = 0; $i < 15; $i++) {
        $bev_name = $get_unique_bev($stall_idx, $i);
        $price = number_format(rand(20, 60) / 10, 2); // RM 2.00 - RM 6.00
        $bev_name_safe = $conn->real_escape_string($bev_name);
        
        $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image, status) 
                VALUES ('$stall_id', '$bev_name_safe', '$price', 'Beverage', 'default_food.jpg', 'Available')";
        if ($conn->query($sql)) {
            $total_inserted++;
        }
    }
    
    // c. Seed 10 Others
    for ($i = 0; $i < 10; $i++) {
        $oth_name = $get_unique_oth($stall_idx, $i);
        $price = number_format(rand(30, 100) / 10, 2); // RM 3.00 - RM 10.00
        $oth_name_safe = $conn->real_escape_string($oth_name);
        
        $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image, status) 
                VALUES ('$stall_id', '$oth_name_safe', '$price', 'Others', 'default_food.jpg', 'Available')";
        if ($conn->query($sql)) {
            $total_inserted++;
        }
    }
}

echo "\n====================================================\n";
echo "📊 RINGKASAN RE-SEED 600 MENU UNIK:\n";
echo "====================================================\n";
echo "👉 Jumlah Gerai Terlibat: " . count($stalls) . " gerai pertama\n";
echo "👉 Menu Baharu Berjaya Dimasukkan: $total_inserted / 600 items\n";
echo "👉 Semua 600 menu dijamin 100% UNIK dan menggunakan fallback 'default_food.jpg'!\n";
echo "====================================================\n";

$conn->close();
?>
