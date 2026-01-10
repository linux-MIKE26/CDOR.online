<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDOR Minecraft Hosting</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            min-height: 100vh;
            color: #fff;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 40px;
        }
        .logo { font-size: 24px; font-weight: 800; color: #00d4ff; }
        .user-info { color: #888; }
        
        h1 { font-size: 32px; margin-bottom: 30px; }
        
        .server-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .server-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s;
        }
        .server-card:hover {
            border-color: #00d4ff;
            transform: translateY(-2px);
        }
        
        .server-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .server-name { font-size: 20px; font-weight: 600; }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-online { background: #00ff8833; color: #00ff88; }
        .status-offline { background: #ff444433; color: #ff4444; }
        .status-starting { background: #ffd70033; color: #ffd700; }
        .status-stopping { background: #ffa50033; color: #ffa500; }
        
        .server-ip {
            background: #000;
            padding: 12px 16px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 16px;
            color: #00d4ff;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 16px;
        }
        .copy-btn:hover { color: #fff; }
        
        .server-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-start { background: #00ff88; color: #000; }
        .btn-stop { background: #ff4444; color: #fff; }
        .btn-manage { background: rgba(255,255,255,0.1); color: #fff; }
        .btn:hover { opacity: 0.8; transform: scale(1.02); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .create-card {
            background: rgba(0,212,255,0.1);
            border: 2px dashed rgba(0,212,255,0.3);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 200px;
            cursor: pointer;
        }
        .create-card:hover {
            border-color: #00d4ff;
            background: rgba(0,212,255,0.15);
        }
        .create-card i { font-size: 48px; color: #00d4ff; margin-bottom: 16px; }
        .create-card span { color: #00d4ff; font-weight: 600; }
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #1a1a2e;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 450px;
        }
        .modal h2 { margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; color: #888; }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        .form-input:focus { outline: none; border-color: #00d4ff; }
        .modal-actions { display: flex; gap: 10px; margin-top: 24px; }
        .btn-primary { background: #00d4ff; color: #000; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo"><i class="fa-solid fa-cube"></i> CDOR HOSTING</div>
            <div class="user-info">Hola, <?= htmlspecialchars($user['username'] ?? 'Usuario') ?></div>
        </header>
        
        <h1>Mis Servidores</h1>
        
        <div class="server-grid" id="serverGrid">
            <!-- Servers load here -->
            <div class="server-card create-card" onclick="openCreateModal()">
                <i class="fa-solid fa-plus"></i>
                <span>Crear Nuevo Servidor</span>
            </div>
        </div>
    </div>
    
    <!-- Create Modal -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <h2><i class="fa-solid fa-rocket"></i> Nuevo Servidor</h2>
            <form id="createForm">
                <div class="form-group">
                    <label class="form-label">Nombre del servidor</label>
                    <input type="text" class="form-input" name="name" placeholder="Mi Servidor" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Servidor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function loadServers() {
            try {
                const res = await fetch('/minecraft/api.php?action=list');
                const servers = await res.json();
                
                const grid = document.getElementById('serverGrid');
                grid.innerHTML = '';
                
                servers.forEach(s => {
                    const status = s.status.toLowerCase();
                    const isOnline = status === 'online';
                    const isTransition = status === 'starting' || status === 'stopping';
                    
                    grid.innerHTML += `
                        <div class="server-card">
                            <div class="server-header">
                                <span class="server-name">${s.name}</span>
                                <span class="status-badge status-${status}">
                                    ${s.status.toUpperCase()}
                                </span>
                            </div>
                            <div class="server-ip">
                                <span>${s.public_ip || window.location.hostname}:${s.port}</span>
                                <button class="copy-btn" onclick="copyIP('${s.public_ip || window.location.hostname}:${s.port}')">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </div>
                            <div class="server-actions">
                                ${isTransition 
                                    ? `<button class="btn btn-secondary" disabled><i class="fa-solid fa-spinner fa-spin"></i> ...</button>`
                                    : (isOnline 
                                        ? `<button class="btn btn-stop" onclick="toggleServer('${s.id}', 'stop')">Detener</button>`
                                        : `<button class="btn btn-start" onclick="toggleServer('${s.id}', 'start')">Iniciar</button>`)
                                }
                                <button class="btn btn-manage" onclick="location.href='/minecraft/manage.php?id=${s.id}'"><i class="fa-solid fa-gear"></i> Gestionar</button>
                            </div>
                        </div>
                    `;
                });
                
                // Add create card at the end
                grid.innerHTML += `
                    <div class="server-card create-card" onclick="openCreateModal()">
                        <i class="fa-solid fa-plus"></i>
                        <span>Crear Nuevo Servidor</span>
                    </div>
                `;
            } catch (e) {
                console.error('Error loading servers:', e);
            }
        }

        async function toggleServer(id, action) {
            try {
                await fetch(`/minecraft/api.php?action=toggle&id=${id}&command=${action}`, { method: 'POST' });
                // Poll for status change
                setTimeout(loadServers, 2000);
                setTimeout(loadServers, 5000);
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        function copyIP(ip) {
            navigator.clipboard.writeText(ip);
            alert('IP copiada: ' + ip);
        }

        function joinServer(ip) {
            navigator.clipboard.writeText(ip);
            // alert('¡IP Copiada!\n\nEntra a Minecraft > Multiplayer > Direct Connect\npegala y entra. \n\nTu usuario ya está autorizado.');
            
            // Create a nice custom toast/alert div instead of native alert?
            // For now, let's just use a clean alert or modify the UI to show a "Copied" tooltip.
            // Let's stick to alert for simplicity but make it informative.
            alert(`⚡ CONEXIÓN ESTABLECIDA ⚡\n\nIP: ${ip}\n(Copiada al portapapeles)\n\n1. Abre Minecraft 1.21.4\n2. Multiplayer > Direct Connect\n3. Pega la IP y entra.\n\nEl servidor ya te reconoce.`);
        }

        function openCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }

        document.getElementById('createForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = e.target.name.value;
            try {
                const res = await fetch('/minecraft/api.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, type: 'java' })
                });
                const result = await res.json();
                if (result.success) {
                    closeCreateModal();
                    loadServers();
                    e.target.reset();
                } else {
                    alert(result.error);
                }
            } catch (err) {
                alert('Error creating server');
            }
        });

        // Load on start
        loadServers();
        // Auto-refresh every 10s
        setInterval(loadServers, 10000);
    </script>
</body>
</html>
