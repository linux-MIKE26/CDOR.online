<?php
// CDOR v5.0 HIGH VOLTAGE - LOGIN
require_once __DIR__ . '/../app/config/bootstrap.php';

// FORCE LOGOUT IF REQUESTED
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin-login.php");
    exit;
}

// SESSION CHECK
if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';



    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            $_SESSION['user'] = $u;
            header('Location: admin-dashboard.php');
            exit;
        } else {
            $error = "Acceso Denegado";
        }
    } else {
        $error = "Modo Offline: Usa credenciales maestras";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDOR // HIGH VOLTAGE ACCESS</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-deep: #050505;
            --primary: #FFD700;
            --primary-glow: rgba(255, 215, 0, 0.4);
            --border: rgba(255, 215, 0, 0.3);
            --glass: rgba(20, 20, 20, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-deep);
            /* Sci-Fi Grid Background CSS Only */
            background-image:
                linear-gradient(rgba(255, 215, 0, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 215, 0, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rajdhani', sans-serif;
            color: #fff;
            overflow: hidden;
        }

        /* VIGNETTE */
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, transparent 40%, #000 90%);
            pointer-events: none;
        }

        .login-box {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 380px;
            padding: 40px;
            background: rgba(10, 10, 10, 0.9);
            border: 1px solid var(--border);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.15);
            backdrop-filter: blur(10px);
            /* CLIP PATH CORNERS */
            clip-path: polygon(20px 0, 100% 0,
                    100% calc(100% - 20px), calc(100% - 20px) 100%,
                    0 100%, 0 20px);
        }

        /* DECORATIVE LINES */
        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20px;
            right: 0;
            height: 2px;
            background: var(--primary);
            box-shadow: 0 0 10px var(--primary);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 5px;
            letter-spacing: 2px;
            background: linear-gradient(to bottom, #fff, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px var(--primary-glow);
        }

        .subtitle {
            text-align: center;
            color: var(--primary);
            font-size: 0.9rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 30px;
            text-shadow: 0 0 5px var(--primary-glow);
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .label {
            position: absolute;
            top: -10px;
            left: 15px;
            background: #0a0a0a;
            padding: 0 5px;
            font-size: 0.8rem;
            color: var(--primary);
            font-weight: bold;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
            color: #fff;
            padding: 15px;
            font-size: 1.1rem;
            font-family: 'Rajdhani', sans-serif;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
        }

        button {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: #000;
            border: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            font-size: 1rem;
            cursor: pointer;
            letter-spacing: 1px;
            text-transform: uppercase;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
            transition: 0.2s;
            margin-top: 10px;
        }

        button:hover {
            background: #ffe033;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
            transform: scale(1.02);
        }

        .err {
            border: 1px solid #ef4444;
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 10px;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 15px;
        }

        .version {
            position: fixed;
            bottom: 20px;
            right: 20px;
            color: #333;
            font-size: 12px;
            font-family: sans-serif;
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            color: #aaa;
            text-align: center;
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 215, 0, 0.1);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>CDOR</h1>
        <div class="subtitle">Command Center</div>

        <?php if ($error): ?>
            <div class="err">⚠️ <?= $error ?></div><?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <div class="input-group">
                <span class="label">CREDENCIAL ID</span>
                <input type="email" name="email" required placeholder="admin@sistema">
            </div>

            <div class="input-group">
                <span class="label">LLAVE DE ACCESO</span>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit">Inicializar Sistema</button>
            <a href="../index.php" class="btn-back">← Volver al Inicio</a>
        </form>
    </div>

    <div class="version">v5.0 HV</div>
</body>

</html>