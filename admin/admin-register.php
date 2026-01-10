<?php
// CDOR v5.0 HIGH VOLTAGE - ADMIN REGISTRATION
require_once __DIR__ . '/../app/config/bootstrap.php';

// Validar si ya hay sesión
if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    // Use a predefined system key for security, or check against existing admin logic
    // For this context, we'll use a hardcoded key 'CDOR_CORE_V5' as planned
    $systemKey = $_POST['system_key'] ?? '';

    if (!$name || !$email || !$pass || !$systemKey) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($systemKey !== 'CDOR_CORE_V5') {
        $error = "Llave de Sistema Inválida. Acceso Denegado.";
    } elseif (!$pdo) {
        $error = "ERROR DE SISTEMA: No hay conexión a base de datos.";
    } else {
        // Verificar duplicados
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email ya registrado en el sistema.";
        } else {
            // Crear Admin
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, 'admin', 1)";
            try {
                $pdo->prepare($sql)->execute([$name, $email, $hash]);
                // Auto-login
                $id = $pdo->lastInsertId();
                $_SESSION['user'] = [
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'admin',
                    'email_verified' => 1
                ];
                header("Location: admin-dashboard.php");
                exit;
            } catch (PDOException $e) {
                $error = "Error crítico de base de datos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDOR // ADMIN REGISTRATION</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #050505;
            --neon-blue: #3b82f6;
            --neon-cyan: #06b6d4;
            --bg-panel: #0a0a0a;
            --border: rgba(59, 130, 246, 0.3);
        }
        
        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            background: var(--bg-deep);
            /* Sci-Fi Grid Blue Variant */
            background-image: 
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: 'Rajdhani', sans-serif; color: #fff; overflow: hidden;
        }

        body::after {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background: radial-gradient(circle, transparent 40%, #000 90%);
            pointer-events: none;
        }

        .login-box {
            position: relative; z-index: 10; width: 100%; max-width: 420px; padding: 40px;
            background: rgba(10, 10, 10, 0.95); border: 1px solid var(--border);
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px);
            clip-path: polygon(20px 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%, 0 20px);
        }

        .login-box::before {
            content: ''; position: absolute; top:0; left:20px; right:0; height:2px;
            background: var(--neon-blue); box-shadow: 0 0 10px var(--neon-blue);
        }

        h1 {
            font-family: 'Orbitron', sans-serif; text-align: center; font-size: 2rem; margin-bottom: 5px; letter-spacing: 2px;
            background: linear-gradient(to bottom, #fff, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
        
        .subtitle {
            text-align: center; color: var(--neon-blue); font-size: 0.8rem; letter-spacing: 4px;
            text-transform: uppercase; margin-bottom: 25px;
        }
        
        .input-group { margin-bottom: 20px; position: relative; }
        
        .label {
            position: absolute; top: -10px; left: 15px; background: #0a0a0a; padding: 0 5px;
            font-size: 0.75rem; color: var(--neon-blue); font-weight: bold; letter-spacing: 1px;
        }

        input {
            width: 100%; background: rgba(0,0,0,0.5); border: 1px solid #333; color: #fff;
            padding: 15px; font-size: 1rem; font-family: 'Rajdhani', sans-serif; outline: none; transition: 0.3s;
        }
        
        input:focus { border-color: var(--neon-blue); box-shadow: 0 0 15px rgba(59, 130, 246, 0.2); }

        button {
            width: 100%; padding: 15px; background: var(--neon-blue); color: #fff; border: none;
            font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 0.9rem; cursor: pointer;
            letter-spacing: 1px; text-transform: uppercase; margin-top: 10px; transition: 0.2s;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
        }
        
        button:hover { background: #60a5fa; box-shadow: 0 0 30px rgba(59, 130, 246, 0.6); transform: scale(1.02); }

        .err {
            border: 1px solid #ef4444; background: rgba(239, 68, 68, 0.1); color: #fca5a5;
            padding: 10px; font-size: 0.9rem; text-align: center; margin-bottom: 15px;
        }
        
        .back-link {
            display: block; text-align: center; margin-top: 20px; color: #666; font-size: 0.8rem; text-decoration: none; transition: 0.3s;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>ACCESS GRANT</h1>
        <div class="subtitle">New Administrator</div>
        
        <?php if($error): ?><div class="err">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <span class="label">FULL NAME</span>
                <input type="text" name="name" required placeholder="Agent Name">
            </div>

            <div class="input-group">
                <span class="label">SYSTEM EMAIL</span>
                <input type="email" name="email" required placeholder="admin@cdor.online">
            </div>
            
            <div class="input-group">
                <span class="label">SECURE PASSWORD</span>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="input-group">
                <span class="label" style="color: #fca5a5; border-color: #ef4444;">SYSTEM KEY</span>
                <input type="password" name="system_key" required placeholder="REQUIRED FOR PERMISSION" style="border-color: rgba(239,68,68,0.5);">
            </div>
            
            <button type="submit">Authorize & Initialize</button>
            <a href="admin-login.php" class="back-link">Return to Login Terminal</a>
        </form>
    </div>
</body>
</html>
