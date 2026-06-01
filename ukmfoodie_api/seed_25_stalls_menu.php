<?php
// seed_25_stalls_menu.php
include 'db.php';

header("Content-Type: text/plain; charset=UTF-8");

echo "=== MEMULAKAN SEEDING MENU UNTUK 25 GERAI PERTAMA ===\n\n";

// 1. Dapatkan senarai 25 gerai pertama
$sql_stalls = "SELECT id, stall_name FROM stalls ORDER BY id ASC LIMIT 25";
$result_stalls = $conn->query($sql_stalls);

if (!$result_stalls || $result_stalls->num_rows == 0) {
    die("Error: Tiada gerai ditemui di dalam database stalls!");
}

$stalls = [];
while ($row = $result_stalls->fetch_assoc()) {
    $stalls[] = $row;
}

echo "Dijumpai sebanyak " . count($stalls) . " gerai untuk di-seed.\n\n";

// 2. Senarai Menu Items
$foods = [
    ["name" => "Nasi Goreng Kampung", "price" => 6.50],
    ["name" => "Mee Goreng Mamak", "price" => 6.00],
    ["name" => "Nasi Lemak Ayam Goreng", "price" => 8.50],
    ["name" => "Char Kway Teow", "price" => 7.00],
    ["name" => "Chicken Chop Crunchy", "price" => 12.00],
    ["name" => "Burger Daging Double Cheese", "price" => 9.50],
    ["name" => "Laksa Utara", "price" => 6.50],
    ["name" => "Roti Canai Tsunami", "price" => 5.50],
    ["name" => "Tomyam Seafood Kaw", "price" => 10.00],
    ["name" => "Nasi Kukus Ayam Dara", "price" => 8.00]
];

$beverages = [
    ["name" => "Teh Tarik Madu", "price" => 3.50],
    ["name" => "Milo Ais Kepal Kaw", "price" => 4.50],
    ["name" => "Kopi O Kampung Ais Kaw", "price" => 2.50],
    ["name" => "Sirap Bandung Cincau", "price" => 3.00],
    ["name" => "Jus Mango Susu Blender", "price" => 5.00],
    ["name" => "Lemon Tea Ais Segar", "price" => 2.50],
    ["name" => "Horlicks Ais Dinosaurs", "price" => 5.50],
    ["name" => "Carrot Susu Madu Ais Kaw", "price" => 4.00],
    ["name" => "Nescafe Ais Kaw", "price" => 3.50],
    ["name" => "Air Kelapa Muda Manis", "price" => 4.00]
];

$others = [
    ["name" => "Pisang Goreng Crispy (5pcs)", "price" => 3.00],
    ["name" => "Keropok Lekor Terengganu", "price" => 3.00],
    ["name" => "French Fries Crunchy Cheese", "price" => 6.00],
    ["name" => "Roti Bakar Kaya Butter (2pcs)", "price" => 3.50],
    ["name" => "Karipap Kentang Crispy (3pcs)", "price" => 3.00],
    ["name" => "Nugget Ayam Tempura (6pcs)", "price" => 5.00],
    ["name" => "Waffle Chocolate Peanut Butter", "price" => 5.50],
    ["name" => "Takoyaki Sotong Double Cheese (5pcs)", "price" => 8.00],
    ["name" => "Popia Otak-Otak Crispy (5pcs)", "price" => 5.00],
    ["name" => "Kuih Koci Kelapa Gula Melaka (3pcs)", "price" => 3.00]
];

$total_inserted = 0;
$total_skipped = 0;

foreach ($stalls as $stall) {
    $stall_id = $stall['id'];
    $stall_name = $stall['stall_name'];
    
    echo "⚙️ Memproses Gerai ID $stall_id: '$stall_name'...\n";
    
    // Fungsi pembantu untuk masukkan item dengan semakan pendua
    $insert_items = function($items, $category) use ($conn, $stall_id, &$total_inserted, &$total_skipped) {
        foreach ($items as $item) {
            $item_name = $conn->real_escape_string($item['name']);
            $price = $item['price'];
            $cat_safe = $conn->real_escape_string($category);
            
            // Semak jika menu dengan nama & kategori yang sama sudah wujud untuk gerai ini
            $check_sql = "SELECT id FROM menu_items WHERE stall_id = '$stall_id' AND item_name = '$item_name' AND category = '$cat_safe'";
            $check_res = $conn->query($check_sql);
            
            if ($check_res && $check_res->num_rows > 0) {
                // Skip jika sudah ada untuk elakkan duplicate
                $total_skipped++;
                continue;
            }
            
            $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image, status) 
                    VALUES ('$stall_id', '$item_name', '$price', '$cat_safe', 'default_food.jpg', 'Available')";
            
            if ($conn->query($sql)) {
                $total_inserted++;
            } else {
                echo "   ❌ Gagal memasukkan '$item_name': " . $conn->error . "\n";
            }
        }
    };
    
    // Masukkan 10 Food
    $insert_items($foods, "Food");
    
    // Masukkan 10 Beverage
    $insert_items($beverages, "Beverage");
    
    // Masukkan 10 Others
    $insert_items($others, "Others");
    
    echo "   ✅ Selesai memproses '$stall_name'.\n\n";
}

echo "====================================================\n";
echo "📊 RINGKASAN SEEDING MENU:\n";
echo "====================================================\n";
echo "👉 Jumlah Item Baru Berjaya Ditambah: $total_inserted\n";
echo "👉 Jumlah Item Dilompati (Sudah Wujud): $total_skipped\n";
echo "====================================================\n";

$conn->close();
?>
