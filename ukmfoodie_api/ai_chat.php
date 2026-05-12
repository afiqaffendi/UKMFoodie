<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS preflight request (penting untuk React Native)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

// --- KONFIGURASI (GROQ API - Percuma & Laju) ---
// Dapatkan API Key percuma di: https://console.groq.com
$GROQ_API_KEY = "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";
$GROQ_URL = "https://api.groq.com/openai/v1/chat/completions";

// Ambil input dari user
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input);
$user_message = isset($data->message) ? trim($data->message) : '';

if (empty($user_message)) {
    echo json_encode(["status" => "error", "message" => "Mesej kosong."]);
    exit;
}

// 1. Ambil data Gerai
$stalls_data = [];
$res_stalls = $conn->query("SELECT id, stall_name, description FROM stalls WHERE approval_status = 'Approved'");
if ($res_stalls && $res_stalls->num_rows > 0) {
    while ($row = $res_stalls->fetch_assoc()) {
        $stalls_data[$row['id']] = [
            "name" => $row['stall_name'],
            "desc" => $row['description'] ?? 'Tiada keterangan',
            "menu" => []
        ];
    }
}

// 2. Ambil data Menu
$res_menu = $conn->query("SELECT stall_id, item_name, price, category FROM menu_items WHERE status = 'Available'");
if ($res_menu && $res_menu->num_rows > 0) {
    while ($row = $res_menu->fetch_assoc()) {
        if (isset($stalls_data[$row['stall_id']])) {
            $stalls_data[$row['stall_id']]['menu'][] = $row['item_name'] . " (RM" . number_format((float) $row['price'], 2) . ")";
        }
    }
}

// 3. Bina Context String
$context = "Senarai Gerai dan Menu di UKMFoodie:\n";
if (empty($stalls_data)) {
    $context .= "- Tiada gerai yang aktif buat masa ini.\n";
} else {
    foreach ($stalls_data as $s) {
        $menu_list = empty($s['menu']) ? 'Tiada menu lagi' : implode(", ", $s['menu']);
        $context .= "- Gerai: " . $s['name'] . ". Keterangan: " . $s['desc'] . ". Menu: " . $menu_list . "\n";
    }
}

// 4. Bina System Prompt + User Message
$full_prompt = "Anda adalah Pembantu AI untuk aplikasi UKMFoodie, sebuah aplikasi pesanan makanan untuk pelajar UKM (Universiti Kebangsaan Malaysia).

Data semasa gerai dan menu:
$context

SYARAT PENTING:
1. HANYA jawab soalan berkaitan makanan, gerai, menu, dan aplikasi UKMFoodie.
2. Jika soalan di luar topik, jawab: 'Maaf, saya hanya boleh membantu berkaitan UKMFoodie sahaja.'
3. Jangan reka maklumat. Jika tiada dalam data di atas, katakan tidak tahu.
4. Jawab dalam Bahasa Melayu yang mesra dan santai.
5. Jawapan mestilah ringkas dan tepat.

Soalan Pengguna: $user_message";

// 5. Sediakan Data untuk Groq API (OpenAI-compatible format)
$post_data = [
    "model" => "llama-3.1-8b-instant",
    "messages" => [
        [
            "role" => "user",
            "content" => $full_prompt
        ]
    ],
    "temperature" => 0.7,
    "max_tokens" => 500
];

// 6. Panggil Groq API guna CURL
$ch = curl_init($GROQ_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $GROQ_API_KEY
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
unset($ch);

if ($http_code == 200) {
    $result = json_decode($response, true);
    $ai_response = $result['choices'][0]['message']['content'] ?? "Maaf, saya tidak dapat memproses jawapan.";
    echo json_encode([
        "status" => "success",
        "reply" => trim($ai_response)
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menghubungi AI (HTTP: $http_code)",
        "curl_error" => $curl_error,
        "raw" => $response
    ]);
}

$conn->close();
?>