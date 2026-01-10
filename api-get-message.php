<?php
require __DIR__ . '/../app/config/bootstrap.php';

header('Content-Type: application/json');

// Verificar Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    try {
        // 1. Marcar como leído
        $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        
        // 2. Obtener mensaje
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($msg) {
            // Formatear fecha para mostrarla bonita
            $msg['formatted_date'] = date('d/m/Y H:i', strtotime($msg['created_at']));
            $msg['subject'] = "Nuevo contacto de " . $msg['name'];
            echo json_encode($msg);
        } else {
            echo json_encode(['error' => 'Mensaje no encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error de BD']);
    }
} else {
    echo json_encode(['error' => 'ID inválido']);
}