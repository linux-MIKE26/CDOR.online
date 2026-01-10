<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$serverId = $_GET['id'] ?? '';
if (empty($serverId)) {
    header('Location: /minecraft/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servidor - CDOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            min-height: 100vh;
            color: #fff;
        }
        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        
        .back-link {
            color: #888;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .back-link:hover { color: #fff; }
        
        .header {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .server-name { font-size: 28px; font-weight: 800; }
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-online { background: #00ff8833; color: #00ff88; }
        .status-offline { background: #ff444433; color: #ff4444; }
        
        .ip-box {
            background: #000;
            padding: 16px 20px;
            border-radius: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 20px;
            color: #00d4ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background: rgba(0,212,255,0.2);
            border: 1px solid #00d4ff;
            color: #00d4ff;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .copy-btn:hover { background: rgba(0,212,255,0.3); }
        
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
        }
        .btn-start { background: #00ff88; color: #000; }
        .btn-stop { background: #ff4444; color: #fff; }
        .btn:hover { transform: scale(1.02); opacity: 0.9; }
        
        .console {
            background: #000;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            height: 400px;
            overflow-y: auto;
            color: #0f0;
        }
        .console-line { margin-bottom: 4px; }
        .console-time { color: #666; }
        .console-info { color: #0f0; }
        .console-warn { color: #ff0; }
        .console-error { color: #f00; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #111;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
        }
        .modal h2 { margin-bottom: 20px; color: #00d4ff; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-primary { background: #00d4ff; color: #000; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/minecraft/" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Volver al Dashboard
        </a>
        
        <div class="header">
            <div class="header-top">
                <h1 class="server-name" id="serverName">Cargando...</h1>
                <span class="status-badge status-offline" id="statusBadge">OFFLINE</span>
            </div>
            <!-- IP DISPLAY -->
            <div style="display: grid; gap: 12px; margin-bottom: 20px;">
                <!-- Local IP -->
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 16px; border-radius: 12px;">
                    <div style="font-size: 11px; color: #888; margin-bottom: 5px;">📍 IP LOCAL (Red interna)</div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="localIP" style="font-size: 16px; font-weight: bold; color: #fff;">192.168.3.115:25565</span>
                        <button class="copy-btn" onclick="copyIP('local')" style="background: rgba(255,255,255,0.1); color: #fff; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px;">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Public IP (Playit) -->
                <div id="publicIPBox" style="background: linear-gradient(135deg, rgba(0,212,255,0.1), rgba(0,255,170,0.1)); border: 2px solid #00d4ff; padding: 16px; border-radius: 12px; display: none;">
                    <div style="font-size: 11px; color: #888; margin-bottom: 5px;">🌐 IP PÚBLICA (Acceso desde Internet)</div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="publicIP" style="font-size: 18px; font-weight: bold; color: #00d4ff;">Activación pendiente</span>
                        <button class="copy-btn" onclick="copyIP('public')" style="background: #00d4ff; color: #000; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; font-weight: bold;">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                    <div id="tunnelStatus" style="font-size: 11px; color: #00ff88; margin-top: 5px;"></div>
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-start" id="startBtn" onclick="startServer()">
                    <i class="fa-solid fa-play"></i> INICIAR
                </button>
                <button class="btn btn-stop" id="stopBtn" onclick="stopServer()" style="display:none;">
                    <i class="fa-solid fa-stop"></i> DETENER
                </button>
                <button class="btn btn-manage" onclick="loadStatus()" title="Refrescar estado">
                    <i class="fa-solid fa-rotate"></i>
                </button>
                <button class="btn btn-primary" onclick="showConnectModal()" title="Conectar Cliente Local">
                    <i class="fa-solid fa-link"></i> CONECTAR LOCAL
                </button>
                <button class="btn" id="activateOnlineBtn" onclick="activateOnline()" style="background: linear-gradient(135deg, #00d4ff, #00ff88); color: #000; font-weight: bold; display: none;">
                    <i class="fa-solid fa-globe"></i> ACTIVAR ONLINE
                </button>
                <button class="btn btn-secondary" onclick="checkSystem()" title="Ver estado del sistema web" style="background: #444; color: #fff;">
                    <i class="fa-solid fa-microchip"></i> SISTEMA
                </button>
                <button class="btn" onclick="deleteServer()" style="background: #ff4444; color: #fff; margin-left: 10px;">
                    <i class="fa-solid fa-trash"></i> ELIMINAR
                </button>
            </div>
            
            <!-- VERSION SELECTOR -->
            <div style="margin-top: 20px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px;">
                <label style="display: block; margin-bottom: 8px; color: #888;">Versión de Minecraft:</label>
                <select id="versionSelect" style="width: 100%; padding: 12px; background: #000; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #00d4ff; font-size: 16px;">
                    <option value="" disabled selected>Cargando versiones...</option>
                </select>
                <div style="margin-top: 5px; font-size: 12px; color: #666; display: flex; gap: 10px;">
                     <label><input type="checkbox" id="showSnapshots" onchange="loadVersions()"> Mostrar Snapshots</label>
                </div>
            </div>
        </div> <!-- end .header -->

        <!-- TABS -->
        <div style="display:flex; gap:10px; margin-bottom:20px;">
            <button class="btn btn-tab active" onclick="switchTab(event, 'console')"><i class="fa-solid fa-terminal"></i> Consola</button>
            <button class="btn btn-tab" onclick="switchTab(event, 'files')"><i class="fa-solid fa-folder"></i> Archivos</button>
        </div>

        <!-- CONSOLE TAB -->
        <div id="tab-console">
            <h2 style="margin-bottom: 16px;">Salida del Servidor</h2>
            <div class="console" id="console">
                <div class="console-line"><span class="console-time">[Sistema]</span> <span class="console-info">Esperando inicio del servidor...</span></div>
            </div>
        </div>

        <!-- FILES TAB -->
        <div id="tab-files" style="display:none;">
            <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px;">
                <h3 style="margin-bottom:15px; display:flex; justify-content:space-between;">
                    <span><i class="fa-solid fa-folder-open"></i> Explorador de Archivos</span>
                    <button class="btn btn-sm" onclick="loadFiles('')"><i class="fa-solid fa-rotate"></i> Recargar</button>
                </h3>
                <div id="fileBreadcrumbs" style="margin-bottom:10px; color:#888;">/</div>
                <div id="fileList" style="display:grid; gap:8px;">
                    <!-- Files injected here -->
                </div>
            </div>
        </div>
    </div>

    <!-- FILE EDITOR MODAL -->
    <div class="modal-overlay" id="editorModal">
        <div class="modal" style="max-width:900px; width:95%; background:#0a0a0a; border: 1px solid #00d4ff;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h2 id="editorTitle" style="margin:0;">Editando archivo</h2>
                <button onclick="closeEditor()" style="background:none; border:none; color:#888; cursor:pointer; font-size:24px;">&times;</button>
            </div>
            <textarea id="editorContent" spellcheck="false" style="width:100%; height:500px; background:#000; color:#0f0; border:1px solid #333; padding:15px; font-family:'JetBrains Mono', monospace; font-size:14px; line-height:1.5; outline:none; border-radius:8px;"></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditor()">Descartar</button>
                <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveFile()"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
            </div>
        </div>
    </div>

    <!-- CONNECT CLIENT MODAL -->
    <div class="modal-overlay" id="connectModal">
        <div class="modal" style="max-width:500px; width:95%; text-align:center;">
            <h2 style="color:#00d4ff;"><i class="fa-solid fa-laptop-code"></i> Conectar Cliente Local</h2>
            <p style="color:#ccc; margin-bottom:20px;">Use este token para vincular su aplicación CDOR Client con este servidor:</p>
            
            <div style="background:#000; padding:15px; border-radius:8px; border:1px solid #333; margin-bottom:20px; word-break:break-all; font-family:'JetBrains Mono', monospace; font-size:14px; color:#fff;" id="deviceTokenDisplay">
                Cargando...
            </div>
            
            <div class="modal-actions" style="justify-content:center; flex-direction:column; gap:10px;">
                <div style="display:flex; gap:10px; width:100%;">
                    <button type="button" class="btn btn-secondary" style="flex:1;" onclick="document.getElementById('connectModal').classList.remove('active')">Cerrar</button>
                    <button type="button" class="btn btn-primary" style="flex:1;" onclick="copyToken()">Copiar Token</button>
                </div>
                <a id="downloadClientLink" href="#" class="btn" style="background:#ffd700; color:#000; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fa-solid fa-download"></i> DESCARGAR CLIENTE (.JS)
                </a>
                <p style="font-size:11px; color:#555; margin-top:5px;">Requiere Node.js instalado en tu PC.</p>
            </div>
        </div>
    </div>

    <script>
        const serverId = '<?= htmlspecialchars($serverId) ?>';
        let isOnline = false;
        let currentPath = '';
        let editingFile = '';

        function clearLogs() {
            document.getElementById('console').innerHTML = '';
        }

        async function loadVersions() {
            console.log("Loading versions...");
            const select = document.getElementById('versionSelect');
            const snapshotCheck = document.getElementById('showSnapshots');
            const showSnapshots = snapshotCheck ? snapshotCheck.checked : false;
            
            if (!select) return;

            try {
                const res = await fetch(`/minecraft/versions.json?t=${Date.now()}`);
                const versions = await res.json();
                
                const currentVal = select.value;
                select.innerHTML = '';
                
                let count = 0;
                Object.keys(versions).forEach(v => {
                    const info = versions[v];
                    const isSnapshot = info.type === 'snapshot' || v.match(/^[0-9]{2}w[0-9]{2}[a-z]$/);
                    if (isSnapshot && !showSnapshots) return;
                    
                    const opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = v + (isSnapshot ? ' (Snapshot)' : '');
                    if(v === currentVal || (!currentVal && v === '1.21.4')) opt.selected = true;
                    select.appendChild(opt);
                    count++;
                });
                console.log(`Loaded ${count} versions`);
                
                if (count === 0) {
                     select.innerHTML = '<option value="">No hay versiones disponibles</option>';
                }
            } catch(e) { 
                console.error("Error loading versions:", e);
                select.innerHTML = '<option value="">Error al cargar versiones</option>';
            }
        }

        async function loadStatus() {
            try {
                const res = await fetch(`/minecraft/api.php?action=status&id=${serverId}`);
                const data = await res.json();
                
                const serverName = document.getElementById('serverName');
                if (serverName) serverName.textContent = data.name || 'Servidor';
                
                const localIP = document.getElementById('localIP');
                if (localIP) {
                    const host = (data.public_ip && !data.public_ip.includes('127.0.0.1')) ? data.public_ip : `${window.location.hostname}:${data.port}`;
                    localIP.textContent = host;
                }
                
                const publicIPBox = document.getElementById('publicIPBox');
                const publicIPSpan = document.getElementById('publicIP');
                const tunnelStatus = document.getElementById('tunnelStatus');
                const activateBtn = document.getElementById('activateOnlineBtn');
                
                const isOnline = data.status === 'online';
                const statusBadge = document.getElementById('statusBadge');
                if (statusBadge) {
                    statusBadge.textContent = data.status.toUpperCase();
                    statusBadge.className = `status-badge status-${isOnline ? 'online' : 'offline'}`;
                }

                if (isOnline || data.node_active) {
                    if (publicIPBox) publicIPBox.style.display = 'block';
                    if (publicIPSpan) publicIPSpan.textContent = data.public_ip || (data.node_active ? '127.0.0.1:25565' : 'Preparando...');
                    if (tunnelStatus) {
                        if (data.node_active) {
                            tunnelStatus.innerHTML = '<i class="fa-solid fa-circle-check"></i> ✅ NODO LOCAL VINCULADO Y ACTIVO';
                            tunnelStatus.style.color = '#00ff88';
                        } else if (isOnline) {
                            tunnelStatus.innerHTML = '<i class="fa-solid fa-cloud"></i> ✅ EJECUTANDO EN LA NUBE';
                            tunnelStatus.style.color = '#00d4ff';
                        }
                    }
                    if (activateBtn) activateBtn.style.display = 'none';
                } else {
                    if (publicIPBox) publicIPBox.style.display = 'block';
                    if (publicIPSpan) publicIPSpan.textContent = 'Nodo Desconectado';
                    if (tunnelStatus) {
                        tunnelStatus.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ❌ NO SE DETECTA TU PC (Ejecuta el archivo .js)';
                        tunnelStatus.style.color = '#ff4444';
                    }
                    if (activateBtn) activateBtn.style.display = 'inline-block';
                }
                
                const sBtn = document.getElementById('startBtn');
                const tBtn = document.getElementById('stopBtn');
                if (sBtn) sBtn.style.display = isOnline ? 'none' : 'block';
                if (tBtn) tBtn.style.display = isOnline ? 'block' : 'none';
            } catch (e) { console.error(e); }
        }

        function copyIP(type) {
            let ip;
            if (type === 'local') {
                ip = document.getElementById('localIP').textContent;
            } else {
                ip = document.getElementById('publicIP').textContent;
            }
            navigator.clipboard.writeText(ip);
            alert('IP copiada: ' + ip);
        }

        async function activateOnline() {
            const btn = document.getElementById('activateOnlineBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando enlace...';
            
            try {
                const res = await fetch(`/minecraft/api.php?action=activate_online&id=${serverId}`);
                const data = await res.json();
                
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    window.open(data.claim_url, '_blank');
                    alert('✅ ' + data.message);
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> VERIFICAR ACTIVACIÓN';
                    btn.onclick = checkActivation;
                }
            } catch (e) { alert('Error de conexión'); }
            finally { btn.disabled = false; }
        }

        async function checkActivation() {
            const btn = document.getElementById('activateOnlineBtn');
            btn.disabled = true;
            try {
                const res = await fetch(`/minecraft/api.php?action=check_activation&id=${serverId}`);
                const data = await res.json();
                if (data.activated) {
                    alert('🎉 ' + data.message);
                    loadStatus();
                } else {
                    alert('⏳ Aún pendiente...');
                }
            } catch (e) { alert('Error'); }
            finally { btn.disabled = false; }
        }

        async function startServer() {
            clearLogs();
            addLog('Iniciando servidor...', 'info');
            const version = document.getElementById('versionSelect').value;
            
            try {
                const res = await fetch(`/minecraft/api.php?action=toggle&id=${serverId}&command=start`, { method: 'POST' });
                const data = await res.json();
                
                if (data.error && data.error.includes('not installed')) {
                    if(confirm(`Requiere instalación. ¿Instalar ${version} ahora?`)) installServer();
                    return;
                }
                
                if (data.success) {
                    if (data.message) addLog(data.message, 'info');
                    addLog('Servidor iniciado correctamente', 'success');
                    const checkInterval = setInterval(async () => {
                        await loadStatus();
                        fetch(`/minecraft/api.php?action=fetch_logs&id=${serverId}`).then(r => r.json()).then(ld => {
                            if (ld.success && ld.logs && (ld.logs.includes('Done') || ld.logs.includes('Done!'))) {
                                addLog('¡Listo para jugar!', 'success');
                                clearInterval(checkInterval);
                            }
                        });
                        if (!isOnline) clearInterval(checkInterval);
                    }, 3000);
                }
            } catch (e) { addLog('Error de conexión', 'error'); }
        }

        async function installServer() {
            const version = document.getElementById('versionSelect').value;
            addLog(`Instalando ${version}...`, 'info');
            try {
                const res = await fetch(`/minecraft/api.php?action=toggle&id=${serverId}&command=install&version=${version}`);
                const data = await res.json();
                if (data.success) {
                    addLog('Instalación completa.', 'success');
                    setTimeout(startServer, 1000);
                } else { addLog('Error: ' + data.error, 'error'); }
            } catch(e) { addLog('Error', 'error'); }
        }

        async function stopServer() {
            addLog('Deteniendo...', 'warn');
            await fetch(`/minecraft/api.php?action=toggle&id=${serverId}&command=stop`, { method: 'POST' });
            setTimeout(loadStatus, 1000);
        }

        async function deleteServer() {
            if (!confirm('¿Eliminar servidor?')) return;
            const res = await fetch(`/minecraft/api.php?action=delete&id=${serverId}`);
            const data = await res.json();
            if (data.success) window.location.href = '/minecraft/';
        }

        function addLog(msg, type = 'info') {
            const console = document.getElementById('console');
            const line = document.createElement('div');
            line.className = 'console-line';
            line.innerHTML = `<span class="console-time">[${new Date().toLocaleTimeString()}]</span> <span class="console-${type}">${msg}</span>`;
            if (console) {
                console.appendChild(line);
                console.scrollTop = console.scrollHeight;
            }
        }

        function switchTab(e, tab) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
            document.getElementById('tab-' + tab).style.display = 'block';
            document.querySelectorAll('.btn-tab').forEach(el => el.classList.remove('active'));
            if (e && e.currentTarget) e.currentTarget.classList.add('active');
            if(tab === 'files') loadFiles('');
        }

        async function loadFiles(path) {
            currentPath = path;
            const breadcrumbs = document.getElementById('fileBreadcrumbs');
            if (breadcrumbs) breadcrumbs.innerText = '/' + path;
            
            try {
                const res = await fetch(`/minecraft/api.php?action=list_files&id=${serverId}&path=${path}`);
                const data = await res.json();
                const list = document.getElementById('fileList');
                if (list) {
                    list.innerHTML = '';
                    if(path !== '') {
                        const parent = path.includes('/') ? path.split('/').slice(0,-1).join('/') : '';
                        list.innerHTML += `<div class="file-item" onclick="loadFiles('${parent}')"><i class="fa-solid fa-arrow-turn-up"></i> .. (Atrás)</div>`;
                    }
                    if (data.files) {
                        data.files.forEach(f => {
                            const full = path ? path + '/' + f.name : f.name;
                            const action = f.is_dir ? `loadFiles('${full}')` : `openFile('${full}')`;
                            list.innerHTML += `<div class="file-item" onclick="${action}"><div><i class="fa-solid ${f.is_dir?'fa-folder text-warning':'fa-file-code'}"></i> ${f.name}</div></div>`;
                        });
                    }
                }
            } catch (e) { console.error(e); }
        }

        async function openFile(path) {
            const parts = path.split('/');
            editingFile = parts.pop();
            currentPath = parts.join('/');
            document.getElementById('editorTitle').innerText = 'Editando: ' + editingFile;
            document.getElementById('editorModal').classList.add('active');
            try {
                const res = await fetch(`/minecraft/api.php?action=read_file&id=${serverId}&path=${path}`);
                const data = await res.json();
                document.getElementById('editorContent').value = data.content || '';
            } catch(e) { }
        }

        async function saveFile() {
            const content = document.getElementById('editorContent').value;
            const fullPath = (currentPath ? currentPath + '/' : '') + editingFile;
            await fetch(`/minecraft/api.php?action=save_file&id=${serverId}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({path: fullPath, content})
            });
            alert('Guardado');
            closeEditor();
        }

        function closeEditor() { document.getElementById('editorModal').classList.remove('active'); }

        async function showConnectModal() {
            document.getElementById('connectModal').classList.add('active');
            const res = await fetch(`/minecraft/api.php?action=get_token&id=${serverId}`);
            const data = await res.json();
            document.getElementById('deviceTokenDisplay').innerText = data.token || 'Error';
            document.getElementById('downloadClientLink').href = `/minecraft/api.php?action=download_client&id=${serverId}`;
        }

        function copyToken() {
            navigator.clipboard.writeText(document.getElementById('deviceTokenDisplay').innerText);
            alert('Copiado');
        }

        async function checkSystem() {
            addLog('Comprobando entorno del servidor web...', 'info');
            try {
                const res = await fetch('/minecraft/api.php?action=check_sys');
                const data = await res.json();
                addLog(`Sistema: ${data.os} (Usuario: ${data.user})`, 'info');
                addLog(`Java: ${data.java}`, data.java.includes('version') ? 'success' : 'warn');
                addLog(`Curl: ${data.curl.substring(0, 50)}...`, 'info');
            } catch(e) { addLog('Error al consultar sistema', 'error'); }
        }

        loadStatus();
        loadVersions();
        setInterval(loadStatus, 5000);
    </script>
    <style>
        .btn-tab { background:none; border:none; color:#888; padding:10px 20px; cursor:pointer; font-weight:600; font-size:16px; border-bottom:2px solid transparent; }
        .btn-tab.active { color:#fff; border-bottom-color:#00d4ff; }
        .file-item {
            background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px;
            display: flex; justify-content: space-between; align-items: center;
            cursor: pointer; transition: background 0.2s;
        }
        .file-item:hover { background: rgba(255,255,255,0.1); }
        .text-warning { color: #ffd700; }
    </style>
</body>
</html>
