<?php
// Archivo: cdoronline/chatbot/api-google.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Obtener mensaje
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => '']); 
    exit;
}

// --- CONFIGURACIÓN GOOGLE GEMINI ---
$apiKey = 'AIzaSyC3qgUpJleEjXXcU-jDJR5KIugC8imQWe4'; // Tu clave
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

// Personalidad
$systemPrompt = "
Eres CDOR AI, el asistente virtual de 'CDOR Online'.
- Eres experto en desarrollo web, seguridad y tecnología.
- Tus respuestas son concisas, profesionales y usan formato Markdown (negritas, listas).
- No menciones a Google. Fuiste creado por CDOR.
";

// Payload específico de Google
$payload = [
    "contents" => [
        [
            "parts" => [
                // Combinamos el prompt del sistema y el mensaje del usuario para darle contexto
                ["text" => $systemPrompt . "\n\nUsuario: " . $userMessage]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

// Petición cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix para hostings compartidos

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $data['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['reply' => $reply]);
    } else {
        echo json_encode(['reply' => 'Google AI no devolvió texto. Intenta reformular.']);
    }
} else {
    echo json_encode(['reply' => 'Error de conexión con Google Gemini.']);
}
?>