<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari $_POST (kerana kita hantar FormData dari React Native)
    $stall_id      = $conn->real_escape_string($_POST['stall_id']);
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $total_amount  = $conn->real_escape_string($_POST['total_amount']);
    $customer_note = $conn->real_escape_string($_POST['customer_note']);
    $collect_time  = $conn->real_escape_string($_POST['collect_time']);
    
    // Terima array item sebagai string JSON dan tukar balik kepada array PHP
    $cart_items = json_decode($_POST['cart_items'], true);

    $receipt_name = 'no_receipt.jpg';

    // 1. PROSES MUAT NAIK RESIT
    if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/receipts/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["payment_receipt"]["name"], PATHINFO_EXTENSION);
        $receipt_name = "REC_" . time() . "_" . uniqid() . "." . $file_extension;
        
        move_uploaded_file($_FILES["payment_receipt"]["tmp_name"], $target_dir . $receipt_name);
    }

    // 2. SIMPAN KE JADUAL orders
    // Pastikan database awak ada kolum: customer_note, payment_receipt
    $sql_order = "INSERT INTO orders (stall_id, customer_name, total_amount, status, collect_time, customer_note, payment_receipt) 
                  VALUES ('$stall_id', '$customer_name', '$total_amount', 'Pending', '$collect_time', '$customer_note', '$receipt_name')";

    if ($conn->query($sql_order) === TRUE) {
        $order_id = $conn->insert_id;

        // 3. SIMPAN KE JADUAL order_items
        foreach ($cart_items as $item) {
            $item_name = $conn->real_escape_string($item['item_name']);
            $qty       = $conn->real_escape_string($item['quantity']);
            $price     = $conn->real_escape_string($item['price']);

            $sql_item = "INSERT INTO order_items (order_id, item_name, quantity, price) 
                         VALUES ('$order_id', '$item_name', '$qty', '$price')";
            $conn->query($sql_item);
        }

        echo json_encode([
            "status" => "success", 
            "message" => "Order placed successfully!", 
            "order_id" => $order_id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>