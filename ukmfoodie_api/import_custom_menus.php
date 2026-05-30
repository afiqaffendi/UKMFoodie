<?php
include 'db.php';

$source_dir = '../temp_menu_images/';
$dest_dir = './uploads/';

if (!is_dir($source_dir)) {
    die("Error: temp_menu_images directory does not exist.\n");
}

// 1. Scan files in temp_menu_images
$files = scandir($source_dir);
$image_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$image_files = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($ext, $image_extensions)) {
        $image_files[] = $file;
    }
}

if (count($image_files) === 0) {
    echo "No food image files (.jpg, .jpeg, .png, .webp) found inside temp_menu_images folder.\n";
    echo "Please copy your menu images into that folder first!\n";
    exit;
}

echo "Found " . count($image_files) . " custom food images to process.\n";

// 2. Clear old menu items to keep database fresh
$conn->query("DELETE FROM menu_items");
echo "✔ Cleared old menu items from the database.\n";

// 3. Process images and create food templates
$food_templates = [];

foreach ($image_files as $file) {
    $filename_no_ext = pathinfo($file, PATHINFO_FILENAME);
    
    // Clean name: replace underscores/dashes with spaces, capitalize words
    $item_name = ucwords(str_replace(['_', '-'], ' ', $filename_no_ext));
    
    // Auto detect category based on keywords
    $lower_name = strtolower($item_name);
    $category = 'Food'; // default
    
    $beverage_keywords = ['teh', 'milo', 'kopi', 'coffee', 'tea', 'water', 'ais', 'ice', 'juice', 'jus', 'sirap', 'limau', 'barli', 'nescafe', 'drink', 'soda', 'bandung'];
    $others_keywords = ['karipap', 'lekor', 'nugget', 'fries', 'kentang', 'kuih', 'donut', 'roti', 'popia', 'burger', 'waffle', 'snack', 'dessert', 'cake', 'kek', 'chocolate', 'cheese', 'popcorn'];
    
    foreach ($beverage_keywords as $kw) {
        if (strpos($lower_name, $kw) !== false) {
            $category = 'Beverage';
            break;
        }
    }
    
    if ($category === 'Food') {
        foreach ($others_keywords as $kw) {
            if (strpos($lower_name, $kw) !== false) {
                $category = 'Others';
                break;
            }
        }
    }
    
    // Assign realistic price
    if ($category === 'Beverage') {
        $prices = [1.50, 2.00, 2.50, 3.00, 3.50];
        $price = $prices[array_rand($prices)];
    } elseif ($category === 'Others') {
        $prices = [1.00, 1.50, 2.00, 2.50, 3.00, 4.00];
        $price = $prices[array_rand($prices)];
    } else {
        $prices = [5.00, 5.50, 6.00, 6.50, 7.00, 7.50, 8.00, 8.50, 9.00];
        $price = $prices[array_rand($prices)];
    }
    
    // Unique filename for uploads
    $clean_item_name = preg_replace("/[^a-zA-Z0-9]/", "", $item_name);
    $new_filename = time() . '_' . rand(1000, 9999) . '_' . $clean_item_name . '.' . pathinfo($file, PATHINFO_EXTENSION);
    
    // Copy file to dest folder
    if (copy($source_dir . $file, $dest_dir . $new_filename)) {
        $food_templates[] = [
            'item_name' => $item_name,
            'price' => $price,
            'category' => $category,
            'food_image' => $new_filename
        ];
    }
}

echo "✔ Created " . count($food_templates) . " food templates successfully.\n";

// 4. Loop through all stalls and seed them
$stalls_res = $conn->query("SELECT id, stall_name FROM stalls");
$stalls_count = 0;
$menu_items_count = 0;

while ($stall = $stalls_res->fetch_assoc()) {
    $stall_id = $stall['id'];
    $stalls_count++;
    
    // Decide how many items to seed for this stall (e.g. between 6 and 10)
    $num_items = min(count($food_templates), rand(6, 10));
    
    // Pick unique templates
    $selected_keys = array_rand($food_templates, $num_items);
    if (!is_array($selected_keys)) {
        $selected_keys = [$selected_keys];
    }
    
    foreach ($selected_keys as $key) {
        $template = $food_templates[$key];
        $item_name = $conn->real_escape_string($template['item_name']);
        $price = $template['price'];
        $category = $template['category'];
        $food_image = $template['food_image'];
        
        $sql = "INSERT INTO menu_items (stall_id, item_name, price, category, food_image) 
                VALUES ($stall_id, '$item_name', '$price', '$category', '$food_image')";
        
        if ($conn->query($sql)) {
            $menu_items_count++;
        }
    }
}

echo "\nDone! Successfully seeded menus for $stalls_count stalls (Total: $menu_items_count menu items inserted)!\n";
$conn->close();
?>
