<?php
// Archivo: cdoronline/chatbot/test.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de APIs</h1>";

// TUS CLAVES
$keys = [
    'google' => 'AIzaSyC3qgUpJleEjXXcU-jDJR5KIugC8imQWe4',
    'mistral' => 'CtghTZVTaZTakhpe7YEDHW955YYKE7X6',
    'deepseek' => 'sk-c808fd7ae1194180896b6b3f1e568684'
];

function probarAPI($nombre, $url, $headers, $data) {
    echo "<h3>Probando $nombre...</h3>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "<p style='color:red'>❌ Error cURL: $error</p>";
    } elseif ($httpCode == 200) {
        echo "<p style='color:green'>✅ <strong>ÉXITO (200 OK)</strong></p>";
        echo "<pre style='background:#eee; padding:10px; font-size:10px;'>" . substr($response, 0, 300) . "...</pre>";
    } else {
        echo "<p style='color:red'>❌ Error API (Código $httpCode)</p>";
        echo "Respuesta: " . $response;
    }
    echo "<hr>";
}

// 1. TEST GOOGLE
probarAPI('Google Gemini', 
    "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $keys['google'],
    ['Content-Type: application/json'],
    ['contents' => [['parts' => [['text' => 'Hola']]]]]
);

// 2. TEST MISTRAL
probarAPI('Mistral AI', 
    "https://api.mistral.ai/v1/chat/completions",
    ['Content-Type: application/json', 'Authorization: Bearer ' . $keys['mistral']],
    ['model' => 'mistral-tiny', 'messages' => [['role' => 'user', 'content' => 'Hola']]]
);

// 3. TEST DEEPSEEK
probarAPI('DeepSeek', 
    "https://api.deepseek.com/chat/completions",
    ['Content-Type: application/json', 'Authorization: Bearer ' . $keys['deepseek']],
    ['model' => 'deepseek-chat', 'messages' => [['role' => 'system', 'content'=>'Hola'], ['role' => 'user', 'content' => 'Hola']]]
);
?>