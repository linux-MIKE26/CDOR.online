<?php
// Archivo: cdoronline/chatbot/api-mistral.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Obtener mensaje
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => '']); 
    exit;
}

// --- CONFIGURACIÓN MISTRAL AI ---
$apiKey = '4cu9ZMb1XksXBq9b9WvuASZVDjFZdLXK'; // Tu clave
$apiUrl = "https://api.mistral.ai/v1/chat/completions";

// Personalidad
$systemPrompt = "
Eres CDOR AI, desarrollado por el equipo de ingeniería de CDOR Online.
- Asistes en temas de programación, servidores y ciberseguridad.
- Responde siempre en español.
- Usa formato Markdown para estructurar la información.
- Sé directo y evita rellenos innecesarios.
";

// Payload estándar (tipo OpenAI)
$payload = [
    "model" => "mistral-tiny", // Modelo rápido
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => 0.7,
    "max_tokens" => 800
];

// Petición cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix para hostings compartidos

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['choices'][0]['message']['content'])) {
        $reply = $data['choices'][0]['message']['content'];
        echo json_encode(['reply' => $reply]);
    } else {
        echo json_encode(['reply' => 'Mistral AI está pensando pero no dijo nada.']);
    }
} else {
    echo json_encode(['reply' => 'Error de conexión con Mistral AI. Verifica la cuota o la clave.']);
}
?>