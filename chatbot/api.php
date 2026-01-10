<?php
// Archivo: cdoronline/chatbot/api.php

// 1. Limpieza y Configuración
ob_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 2. Input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    ob_clean();
    echo json_encode(['reply' => '']);
    exit;
}

// --- CLAVE GROQ (Sustituir por tu clave real para producción) ---
$groqKey = 'TU_CLAVE_GROQ_AQUI';

// --- GROQ API (Llama 3.1 70B - ultra rápido y gratis) ---
$apiUrl = "https://api.groq.com/openai/v1/chat/completions";
$headers = ["Content-Type: application/json", "Authorization: Bearer " . $groqKey];
$payload = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        ["role" => "system", "content" => "Eres CDOR AI, un asistente inteligente. Responde siempre en español de forma clara y útil."],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => 0.7,
    "max_tokens" => 1024
];

// --- CONEXIÓN ---
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- RESPUESTA ---
ob_clean();

if ($httpCode !== 200) {
    $msg = "Error desconocido";
    $errJson = json_decode($response, true);

    if (isset($errJson['error']['message']))
        $msg = $errJson['error']['message'];
    elseif (isset($errJson['message']))
        $msg = $errJson['message'];

    echo json_encode(['reply' => "⚠️ Error Mistral ($httpCode): $msg"]);
    exit;
}

$data = json_decode($response, true);
$replyText = $data['choices'][0]['message']['content'] ?? 'La IA no devolvió texto.';

echo json_encode(['reply' => $replyText]);
?>