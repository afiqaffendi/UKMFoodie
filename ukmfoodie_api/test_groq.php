<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$GROQ_API_KEY = "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";
$GROQ_URL = "https://api.groq.com/openai/v1/chat/completions";

$post_data = [
    "model" => "llama-3.1-8b-instant",
    "messages" => [["role" => "user", "content" => "hello, jawab dalam satu perkataan"]],
    "max_tokens" => 20
];

$ch = curl_init($GROQ_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . trim($GROQ_API_KEY)
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
unset($ch);

echo json_encode([
    "http_code" => $http_code,
    "curl_err" => $curl_err,
    "key_length" => strlen($GROQ_API_KEY),
    "key_preview" => substr($GROQ_API_KEY, 0, 10) . "...",
    "response" => json_decode($response)
]);
?>
