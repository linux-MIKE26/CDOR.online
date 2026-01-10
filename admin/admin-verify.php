<?php
// CDOR v5.0 HIGH VOLTAGE - VERIFY
require __DIR__ . '/../app/config/bootstrap.php';

$msg = "";
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $emailPost = trim($_POST['email'] ?? '');

    if ($code && $emailPost) {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id, name, verification_code, verification_expires FROM users WHERE email = ? AND email_verified = 0");
            $stmt->execute([$emailPost]);
            $user = $stmt->fetch();

            if (!$user) {
                $msg = "Usuario no encontrado o ya verificado.";
            } else {
                if (strtotime($user['verification_expires']) < time()) {
                    $msg = "El código ha expirado.";
                } elseif ($user['verification_code'] == $code) {
                    $pdo->prepare("UPDATE users SET email_verified = 1, verification_code = NULL WHERE id = ?")->execute([$user['id']]);
                    $_SESSION['user'] = [
                        'id' => $user['id'], 
                        'name' => $user['name'] ?? 'Admin',
                        'email' => $emailPost, 
                        'role' => 'admin'
                    ];
                    header("Location: admin-dashboard.php");
                    exit;
                } else {
                    $msg = "Código incorrecto.";
                }
            }
        } else {
            $msg = "Error DB Offline.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDOR // SECURITY CHECK</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #050505;
            --neon-orange: #f59e0b;
            --neon-red: #ef4444;
            --glass: rgba(10, 10, 10, 0.9);
            --border: rgba(245, 158, 11, 0.3);
        }
        
        * { margin:0; padding:0; box-sizing:border-box; }
        
        body { 
            background: var(--bg-deep);
            background-image: 
                linear-gradient(rgba(245, 158, 11, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245, 158, 11, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rajdhani', sans-serif;
            color: #fff;
            overflow: hidden;
        }

        .card { 
            background: var(--glass);
            border: 1px solid var(--border);
            width: 100%; max-width: 420px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            clip-path: polygon(20px 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%, 0 20px);
            position: relative;
        }

        /* Scanline effect */
        .card::before {
            content: ''; position: absolute; top:0; left:0; width:100%; height:2px;
            background: rgba(245, 158, 11, 0.5);
            animation: scan 3s linear infinite;
            opacity: 0.5;
        }
        @keyframes scan { 0%{top:0} 100%{top:100%} }

        h1 { 
            font-family: 'Orbitron', sans-serif; 
            font-size: 1.8rem; 
            font-weight: 800; 
            color: var(--neon-orange);
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        
        p { 
            font-size: 1rem; color: #888; margin-bottom: 30px; 
            font-weight: 500;
        }
        
        .code-input { 
            width: 100%; 
            padding: 15px; 
            background: #000; 
            border: 2px solid #333; 
            color: #fff; 
            text-align: center; 
            font-size: 2.5rem; 
            font-family: 'Orbitron', monospace;
            font-weight: 900; 
            letter-spacing: 15px; 
            margin-bottom: 25px; 
            outline: none; 
            transition: 0.3s;
        }
        
        .code-input:focus { 
            border-color: var(--neon-orange); 
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
        }
        
        .btn { 
            width: 100%; 
            padding: 18px; 
            background: var(--neon-orange); 
            color: #000; 
            border: none; 
            font-family: 'Orbitron', sans-serif;
            font-weight: 900; 
            font-size: 1.1rem;
            cursor: pointer; 
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.2s;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
        }
        
        .btn:hover { 
            background: #fbbf24; 
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
            transform: scale(1.02);
        }
        
        .error { 
            background: rgba(239, 68, 68, 0.1); 
            border: 1px solid var(--neon-red);
            color: #fca5a5; 
            padding: 15px; 
            margin-bottom: 20px; 
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>ACCESS VERIFICATION</h1>
        <p>Enter the 6-digit security token sent to<br><strong style="color:#fff"><?= htmlspecialchars($email) ?></strong></p>

        <?php if ($msg): ?>
            <div class="error">⚠️ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="text" name="code" class="code-input" placeholder="000000" maxlength="6" required autofocus autocomplete="off">
            <button type="submit" class="btn">AUTHENTICATE</button>
        </form>
    </div>
</body>
</html>