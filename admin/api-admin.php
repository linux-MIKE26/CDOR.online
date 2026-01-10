<?php
require __DIR__ . '/../app/config/bootstrap.php';
header('Content-Type: application/json');

// Verificar sesión de Admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    // 1. ESTADÍSTICAS
    if ($action === 'stats') {
        $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        // Verificar si existe la tabla antes de consultar para evitar error fatal
        try {
            $msgs = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
            $unread = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
        } catch (Exception $e) {
            $msgs = 0;
            $unread = 0; // Si falla, devolver 0
        }

        echo json_encode([
            'users' => $users,
            'messages' => $msgs,
            'unread' => $unread
        ]);
        exit;
    }

    // 2. DATOS GRÁFICA
    if ($action === 'chart_data') {
        $labels = [];
        $uData = [];
        $mData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($date));
            $uData[] = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)='$date'")->fetchColumn();
            // Try/Catch por si la tabla no existe
            try {
                $mData[] = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE DATE(created_at)='$date'")->fetchColumn();
            } catch (Exception $e) {
                $mData[] = 0;
            }
        }
        echo json_encode(['labels' => $labels, 'users' => $uData, 'messages' => $mData]);
        exit;
    }

    // 3. OBTENER MENSAJES
    if ($action === 'get_mails') {
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 4. LEER MENSAJE
    if ($action === 'get_message') {
        $id = $_GET['id'];
        $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$id]);
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id=?");
        $stmt->execute([$id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        $msg['nice_date'] = date('d/m/Y H:i', strtotime($msg['created_at']));
        echo json_encode($msg);
        exit;
    }

    // 5. ENVIAR RESPUESTA PREMIUM
    if ($action === 'send_reply') {
        require_once __DIR__ . '/../app/utils/EmailDesign.php';

        $to = $input['to'];
        $subject = "RE: " . $input['subject'];
        $body = $input['message'];
        $name = $input['name'];

        if (isset($input['id'])) {
            $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$input['id']]);
        }

        $msgBody = "<p>Hola <strong>$name</strong>,</p>
                <p>" . nl2br(htmlspecialchars($body)) . "</p>
                <div style='margin-top: 30px; padding: 15px; background: rgba(255, 215, 0, 0.05); border-left: 3px solid #FFD700; color: #888; font-size: 14px;'>
                    <strong>CONEXTO:</strong> {$input['subject']}
                </div>";

        if (EmailDesign::send($to, $subject, "RESPUESTA DEL SISTEMA", $msgBody)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fallo en el servidor de correo']);
        }
        exit;
    }

    // ... rest of file ...

    // 8. ENVIAR NUEVO CORREO (COMPOSE)
    if ($action === 'send_new_email') {
        require_once __DIR__ . '/../app/utils/EmailDesign.php';

        $to = $input['to'];
        $subject = $input['subject'];
        $message = $input['message'];

        if (EmailDesign::send($to, $subject, "COMUNICADO OFICIAL", nl2br(htmlspecialchars($message)))) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al enviar correo.']);
        }
        exit;
    }

    // 9. GESTIÓN DE USUARIOS (EXTENDED)
    if ($action === 'delete_user') {
        $id = $input['id'];

        // PROTECTION: CEO
        $check = $pdo->prepare("SELECT email FROM users WHERE id=?");
        $check->execute([$id]);
        $u = $check->fetch();
        if ($u && $u['email'] === 'ceo@cdor.online') {
            echo json_encode(['success' => false, 'error' => 'Acción denegada: Cuenta protegida (CEO).']);
            exit;
        }

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // BAN USER
    if ($action === 'ban_user') {
        $id = $input['id'];

        try {
            // 1. Check if column exists
            $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
            if (!$check->fetch()) {
                // Column missing, add it
                $pdo->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0");
            }

            // 2. EXPLICIT TOGGLE (Safer than SQL toggle)
            $stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id=?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();

            // Force integer casting for comparison
            $currentState = intval($current);
            $newState = ($currentState === 1) ? 0 : 1;

            $pdo->prepare("UPDATE users SET is_banned = ? WHERE id=?")->execute([$newState, $id]);

            echo json_encode(['success' => true, 'new_state' => $newState]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>